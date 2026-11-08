# 17 — Deployment and DevOps: Novixa WorkOS

## 1. Hosting Environment & Deployment Architecture

Novixa WorkOS is designed for clean, reproducible deployment using **Docker Compose** on an isolated Linux VPS (Ubuntu 22.04 / 24.04 LTS) or bare-metal host.

```
                             Internet / VPN
                                   │
                                   ▼
                         [ Nginx Reverse Proxy ]
                           (Port 80 / 443 TLS)
                                   │
               ┌───────────────────┴───────────────────┐
               ▼                                       ▼
       [ Nuxt 4 Frontend ]                     [ Laravel 12 API ]
       (Node.js / Port 3000)                  (PHP 8.4-FPM / Port 9000)
                                                       │
                                   ┌───────────────────┴───────────────────┐
                                   ▼                                       ▼
                         [ PostgreSQL 16/17 ]                    [ Local Private Disk ]
                          (Port 5432 Internal)                    (/var/www/storage)
```

---

## 2. Docker Compose Specification (`docker-compose.yml`)

The multi-container orchestration comprises:
1. `web`: Node 24 alpine image building and running the Nuxt 4 SSR/client application.
2. `api`: PHP 8.4-FPM container running the Laravel modular monolith.
3. `nginx`: Production reverse proxy routing `/api/*` to Laravel and all other traffic to Nuxt, with SSL termination and security headers.
4. `db`: PostgreSQL 16 container with persistent named volume for production data.
5. `queue`: Dedicated PHP 8.4 worker running `php artisan queue:work --sleep=3 --tries=3`.
6. `cron`: Scheduler container executing `php artisan schedule:run` every minute.

---

## 3. Backup and Disaster Recovery Automation

### 3.1 Automated Database Dump Script (`scripts/backup.sh`)
- Generates a timestamped, gzip-compressed PostgreSQL database dump (`pg_dump`).
- Encrypts the archive using AES-256 via OpenSSL (`openssl enc -aes-256-cbc`).
- Copies the encrypted dump and private file storage to offsite storage.
- Enforces a 30-day retention rotation policy.

### 3.2 Restoration Verification Drill (`scripts/restore.sh`)
- Accepts an encrypted backup archive.
- Decrypts and tests restoration into a temporary isolated database container.
- Verifies database integrity via sanity checks (`SELECT COUNT(*) FROM users;`).
- Logs the result to the operational audit log.

---

## 4. Continuous Integration Pipeline (GitHub Actions)

The CI workflow `.github/workflows/ci.yml` runs on every push and pull request:
1. **Frontend Gate:**
   - Install dependencies (`pnpm install --frozen-lockfile`).
   - Lint and code formatting verification (`pnpm lint`).
   - TypeScript strict typecheck (`pnpm typecheck`).
   - Unit tests (`pnpm test`).
   - Production bundle build (`pnpm build`).
2. **Backend Gate:**
   - Setup PHP 8.4 with required extensions (pdo_pgsql, pdo_sqlite, intl, mbstring).
   - Composer install (`composer install --prefer-dist --no-progress`).
   - Code style check (Laravel Pint).
   - Static analysis (PHPStan / Larastan level 6+).
   - Database migrations & unit/feature tests (`php artisan test`).
3. **End-to-End Gate:**
   - Playwright E2E browser tests across desktop and mobile viewports.
