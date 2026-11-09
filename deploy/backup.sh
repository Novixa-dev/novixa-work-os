#!/usr/bin/env bash
# Nightly PostgreSQL backup for Novixa WorkOS (cron, user "workos"). Keeps 14 days locally.
set -euo pipefail
APP_DIR="${APP_DIR:-/opt/novixa-work-os}"
OUT="$APP_DIR/backups"
KEEP_DAYS="${KEEP_DAYS:-14}"
cd "$APP_DIR"
set -a; . ./.env; set +a
mkdir -p "$OUT"
F="$OUT/workos_$(date +%Y%m%d_%H%M%S).sql.gz"
# NOTE: no -t here — a TTY would corrupt the dump with CR characters.
docker exec -i novixa-work-os-db pg_dump -U "$DB_USERNAME" -d "$DB_DATABASE" --clean --if-exists | gzip -9 > "$F"
gzip -t "$F"
sha256sum "$F" > "$F.sha256"
find "$OUT" -name 'workos_*.sql.gz*' -mtime +"$KEEP_DAYS" -delete
echo "$(date -Is) backup ok: $F ($(stat -c%s "$F") bytes)"
