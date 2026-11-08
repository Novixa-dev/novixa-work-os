# ==============================================================================
# Novixa WorkOS — 1-Click Windows Local Setup Script
# ==============================================================================
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "       NOVIXA WORKOS — LOCAL BOOTSTRAP ENVIRONMENT        " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Check PHP version
try {
    $phpVersion = php -r "echo PHP_VERSION;"
    Write-Host "[OK] Detected PHP: $phpVersion" -ForegroundColor Green
} catch {
    Write-Error "PHP is not found in PATH. Please install PHP 8.3+ or Laravel Herd."
    exit 1
}

# 2. Check Composer
try {
    $composerVersion = composer --version
    Write-Host "[OK] Detected Composer: $composerVersion" -ForegroundColor Green
} catch {
    Write-Error "Composer is not found in PATH."
    exit 1
}

# 3. Check Node & pnpm
try {
    $nodeVersion = node -v
    $pnpmVersion = pnpm -v
    Write-Host "[OK] Detected Node: $nodeVersion | pnpm: $pnpmVersion" -ForegroundColor Green
} catch {
    Write-Error "Node.js or pnpm is not found in PATH."
    exit 1
}

# 4. Setup Backend API
Write-Host "`n[STEP 1/3] Preparing Backend Laravel API..." -ForegroundColor Yellow
Set-Location -Path "apps/api"

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  -> Created .env from .env.example" -ForegroundColor Gray
    php artisan key:generate
}

if (-not (Test-Path "database/database.sqlite")) {
    New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
    Write-Host "  -> Created database.sqlite" -ForegroundColor Gray
}

Write-Host "  -> Running database migrations and authentic Novixa seeder..." -ForegroundColor Gray
php artisan migrate:fresh --seed --force

Set-Location -Path "../.."

# 5. Setup Frontend Web
Write-Host "`n[STEP 2/3] Preparing Frontend Nuxt 4 Web Client..." -ForegroundColor Yellow
pnpm install
pnpm approve-builds --all
pnpm --filter web build

# 6. Summary and dev instructions
Write-Host "`n==========================================================" -ForegroundColor Green
Write-Host "               NOVIXA WORKOS IS READY!                    " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "To start local development, run two terminal sessions:"
Write-Host "  1. Backend API:  cd apps/api && php artisan serve" -ForegroundColor Cyan
Write-Host "  2. Web Client:   pnpm dev:web" -ForegroundColor Cyan
Write-Host "`nDefault Demo Accounts:"
Write-Host "  - Owner:       ak01redwan@novixa.dev   | Password123!" -ForegroundColor White
Write-Host "  - Tech Lead:   lead@novixa.dev         | Password123!" -ForegroundColor White
Write-Host "  - Engineer:    engineer@novixa.dev     | Password123!" -ForegroundColor White
Write-Host "  - Guest:       client@hadramout.com    | Password123!" -ForegroundColor White
