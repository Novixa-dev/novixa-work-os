#!/usr/bin/env bash
# Zero-touch rollout executed on the VPS (as user "workos") by the Deploy workflow.
#   remote-deploy.sh <tag>        env: GHCR_USER, GHCR_TOKEN (short-lived), OWNER_EMAIL, OWNER_NAME
# Bootstraps .env on first run, pulls images, runs migrations, restarts the stack, waits for health
# and rolls back to the previous tag if the new release is unhealthy.
# It never restarts mailcow or touches the other sites on this VPS.
set -euo pipefail

TAG="${1:?usage: remote-deploy.sh <tag>}"
APP_DIR="${APP_DIR:-/opt/novixa-work-os}"
API_IMAGE="ghcr.io/novixa-dev/novixa-work-os-api"
WEB_IMAGE="ghcr.io/novixa-dev/novixa-work-os-web"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-240}"
cd "$APP_DIR"

set_kv() { if grep -q "^$1=" .env; then sed -i "s#^$1=.*#$1=$2#" .env; else printf '%s=%s\n' "$1" "$2" >> .env; fi; }
get_kv() { grep -E "^$1=" .env 2>/dev/null | head -n1 | cut -d= -f2- || true; }

if [ -n "${GHCR_TOKEN:-}" ]; then
  echo "$GHCR_TOKEN" | docker login ghcr.io -u "${GHCR_USER:-github}" --password-stdin >/dev/null
  trap 'docker logout ghcr.io >/dev/null 2>&1 || true' EXIT
fi

# ---- one-time bootstrap (idempotent) ----------------------------------------
FIRST_RUN=0
if [ ! -f .env ]; then
  FIRST_RUN=1
  docker pull -q "$API_IMAGE:$TAG" >/dev/null
  APP_KEY="$(docker run --rm "$API_IMAGE:$TAG" php artisan key:generate --show)"
  umask 077
  cat > .env <<ENV
# Novixa WorkOS production configuration — private (chmod 600). Never commit.
APP_NAME="Novixa WorkOS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://novixa-work-os.novixa.dev
APP_KEY=$APP_KEY
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
LOG_CHANNEL=stderr
LOG_LEVEL=info
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=novixa_work_os
DB_USERNAME=novixa
DB_PASSWORD=$(openssl rand -hex 24)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
SANCTUM_STATEFUL_DOMAINS=novixa-work-os.novixa.dev
SANCTUM_TOKEN_EXPIRATION=480
FRONTEND_URL=https://novixa-work-os.novixa.dev
BCRYPT_ROUNDS=12
API_IMAGE=$API_IMAGE
WEB_IMAGE=$WEB_IMAGE
TAG=$TAG
ENV
  echo "✔ .env created"
fi
chmod 600 .env

wait_healthy() { # wait_healthy <container>...
  local waited=0 ok c status
  while [ "$waited" -lt "$HEALTH_TIMEOUT" ]; do
    ok=1
    for c in "$@"; do
      status="$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$c" 2>/dev/null || echo missing)"
      case "$status" in healthy|running) ;; *) ok=0 ;; esac
    done
    [ "$ok" = 1 ] && return 0
    sleep 5; waited=$((waited + 5))
  done
  return 1
}

# ---- rollout -----------------------------------------------------------------
PREV_TAG="$(get_kv TAG)"
echo "▶ Deploying $TAG (previous: ${PREV_TAG:-none})"
set_kv TAG "$TAG"
docker compose pull
docker compose up -d db
wait_healthy novixa-work-os-db
docker compose run --rm --no-deps api php artisan migrate --force

docker compose up -d --remove-orphans

if wait_healthy novixa-work-os-api novixa-work-os-web novixa-work-os-queue && curl -fsS "http://127.0.0.1:${WEB_PORT:-3120}/up" >/dev/null; then
  echo "✅ stack is healthy on $TAG"
else
  echo "::error::$TAG failed its health check — recent logs:"
  docker compose logs --tail=60 api web || true
  if [ -n "$PREV_TAG" ] && [ "$PREV_TAG" != "$TAG" ]; then
    echo "↩ Rolling back to $PREV_TAG"
    set_kv TAG "$PREV_TAG"
    docker compose up -d
    wait_healthy novixa-work-os-api novixa-work-os-web && echo "↩ Rollback healthy" || echo "::error::Rollback is unhealthy too"
  fi
  exit 1
fi

# ---- first-run owner account (no demo data in production) ------------------
if [ "$FIRST_RUN" = 1 ] || [ ! -f .owner-created ]; then
  OUT="$(docker compose exec -T api php artisan workos:create-owner "${OWNER_EMAIL:-admin@novixa.dev}" "${OWNER_NAME:-Novixa Admin}" --print-password || true)"
  PW="$(printf '%s\n' "$OUT" | sed -n 's/^PASSWORD=//p' | head -n1)"
  if [ -n "$PW" ]; then
    umask 077
    printf 'email=%s\npassword=%s\n' "${OWNER_EMAIL:-admin@novixa.dev}" "$PW" > .owner-credentials
    echo "✔ Owner created — credentials stored in $APP_DIR/.owner-credentials (read once, then delete)"
  fi
  touch .owner-created
fi

# ---- nginx site block (mailcow) + nightly backups ---------------------------
sudo /usr/local/sbin/workos-sync-site
if ! { crontab -l 2>/dev/null || true; } | grep -q "novixa-work-os-backup"; then
  { ( crontab -l 2>/dev/null || true; echo "17 3 * * * $APP_DIR/backup.sh >> $APP_DIR/backups/backup.log 2>&1 # novixa-work-os-backup" ) | crontab - && echo "✔ nightly backup cron installed"; } || echo "::warning::could not install backup cron"
fi

# Keep current + previous release for instant rollback; prune the rest.
for img in "$API_IMAGE" "$WEB_IMAGE"; do
  docker images "$img" --format '{{.Tag}}' | grep -vxE "$TAG|${PREV_TAG:-__none__}|<none>" | xargs -r -I{} docker rmi "$img:{}" >/dev/null 2>&1 || true
done
docker image prune -f >/dev/null 2>&1 || true
echo "🚀 Done: https://novixa-work-os.novixa.dev"
