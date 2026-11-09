# syntax=docker/dockerfile:1.7
# Novixa WorkOS — web gateway image: public landing page (/), Nuxt SPA (/app/) and /api -> php-fpm.

FROM node:22-alpine AS spa
WORKDIR /app
RUN corepack enable && corepack prepare pnpm@11.0.8 --activate
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
COPY apps/web/package.json ./apps/web/
RUN pnpm install --frozen-lockfile
COPY apps/web ./apps/web
ENV NUXT_APP_BASE_URL=/app/ \
    NUXT_PUBLIC_API_BASE=/api/v1
RUN pnpm --filter web generate

FROM nginxinc/nginx-unprivileged:1.28-alpine AS runtime
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf
COPY landing/ /usr/share/nginx/html/
COPY --from=spa /app/apps/web/.output/public/ /usr/share/nginx/html/app/
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget -qO- http://127.0.0.1:8080/up || exit 1
