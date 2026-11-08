# Novixa WorkOS — Production Nuxt 4 Web Dockerfile
FROM node:22-alpine AS builder

WORKDIR /app

RUN corepack enable && corepack prepare pnpm@11.0.8 --activate

# Copy monorepo manifests
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
COPY apps/web/package.json ./apps/web/

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy web client source
COPY apps/web ./apps/web

# Build Nuxt production output
RUN pnpm --filter web build

# Production Runner stage
FROM node:22-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV PORT=3000
ENV HOST=0.0.0.0

COPY --from=builder /app/apps/web/.output ./

USER node

EXPOSE 3000

CMD ["node", "server/index.mjs"]
