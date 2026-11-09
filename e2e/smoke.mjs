// Post-deploy browser smoke test (real Chrome). Usage: BASE_URL=https://... node smoke.mjs
// Optional: SMOKE_EMAIL / SMOKE_PASSWORD to also verify a full login -> dashboard journey.
import { chromium } from 'playwright-core'

const base = (process.env.BASE_URL || 'https://novixa-work-os.novixa.dev').replace(/\/$/, '')
const chrome = process.env.CHROME_PATH
const browser = await chromium.launch(chrome ? { executablePath: chrome, headless: true } : { channel: 'chrome', headless: true })
const failures = []
const check = (ok, msg) => { console.log(`${ok ? 'PASS' : 'FAIL'}  ${msg}`); if (!ok) failures.push(msg) }

try {
  const page = await (await browser.newContext({ viewport: { width: 1280, height: 800 } })).newPage()
  const apiCalls = []
  const consoleErrors = []
  page.on('response', (r) => { if (r.url().includes('/api/v1/')) apiCalls.push({ url: r.url(), status: r.status() }) })
  page.on('pageerror', (e) => consoleErrors.push(e.message))

  // 1. Public landing page (Arabic default, EN toggle)
  await page.goto(`${base}/`, { waitUntil: 'networkidle' })
  check((await page.title()).includes('Novixa'), 'landing: title mentions Novixa')
  check((await page.getAttribute('html', 'dir')) === 'rtl', 'landing: Arabic RTL by default')
  await page.click('#lang')
  check((await page.getAttribute('html', 'dir')) === 'ltr', 'landing: EN toggle switches to LTR')
  check((await page.locator('a[href="/app/"]').count()) > 0, 'landing: link to /app/ present')
  check((await page.locator('a[href*="linkedin.com/in/novixa"]').count()) > 0, 'landing: LinkedIn link present')

  // 2. SPA loads and redirects anonymous users to login
  await page.goto(`${base}/app/`, { waitUntil: 'networkidle' })
  await page.waitForURL(/\/app\/auth\/login/, { timeout: 15000 }).catch(() => {})
  check(/\/app\/auth\/login/.test(page.url()), 'app: anonymous user lands on /app/auth/login')

  // 3. Wrong credentials hit the real API (/api/v1, not /app/api/v1) and are rejected
  await page.fill('input[type="email"]', 'nobody@example.com')
  await page.fill('input[type="password"]', 'wrong-password')
  await page.click('button[type="submit"]')
  await page.waitForTimeout(2500)
  const login = apiCalls.find((c) => c.url.endsWith('/api/v1/auth/login'))
  check(!!login && !login.url.includes('/app/api/'), 'app: login request goes to /api/v1/auth/login')
  check(login?.status === 422, `app: invalid credentials rejected with 422 (got ${login?.status})`)

  // 4. Optional full journey
  if (process.env.SMOKE_EMAIL && process.env.SMOKE_PASSWORD) {
    await page.fill('input[type="email"]', process.env.SMOKE_EMAIL)
    await page.fill('input[type="password"]', process.env.SMOKE_PASSWORD)
    await Promise.all([page.waitForURL(/\/app\/dashboard/, { timeout: 20000 }).catch(() => {}), page.click('button[type="submit"]')])
    check(/\/app\/dashboard/.test(page.url()), 'app: valid login reaches the dashboard')
  }

  check(consoleErrors.length === 0, `no uncaught page errors${consoleErrors.length ? ': ' + consoleErrors[0] : ''}`)
} finally {
  await browser.close()
}

if (failures.length) { console.error(`\n${failures.length} check(s) failed`); process.exit(1) }
console.log('\nAll smoke checks passed')
