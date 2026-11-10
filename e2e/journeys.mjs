// Journey tests against a LOCAL stack seeded with demo data (API :8000, web :3000).
// Run by CI (job "e2e") — guards against regressions such as empty lists or a broken auth guard.
import { chromium } from 'playwright-core'

const base = process.env.WEB_URL || 'http://localhost:3000'
const chrome = process.env.CHROME_PATH
const browser = await chromium.launch(chrome ? { executablePath: chrome, headless: true } : { channel: 'chrome', headless: true })
const failures = []
const check = (ok, msg) => { console.log(`${ok ? 'PASS' : 'FAIL'}  ${msg}`); if (!ok) failures.push(msg) }

try {
  const page = await (await browser.newContext({ viewport: { width: 1366, height: 850 } })).newPage()
  const pageErrors = []
  page.on('pageerror', (e) => pageErrors.push(e.message))

  // Auth guard: anonymous visit is redirected and remembers the target.
  await page.goto(`${base}/projects`, { waitUntil: 'networkidle' })
  check(/\/auth\/login\?redirect=\/projects/.test(page.url()), 'guard: anonymous /projects -> login with redirect')

  // Wrong password shows a localized error (single language, no "ar / en" leak).
  await page.fill('#email', 'ahmed@novixa.dev')
  await page.fill('#password', 'wrong-password')
  await page.click('button[type="submit"]')
  await page.waitForSelector('[role="alert"]', { timeout: 10000 })
  const alertText = await page.locator('[role="alert"]').first().innerText()
  check(!alertText.includes(' / '), `login: error message is single-language ("${alertText.trim().slice(0, 40)}")`)

  // Valid login returns to the originally requested page.
  await page.fill('#password', 'password123')
  await page.click('button[type="submit"]')
  await page.waitForURL((u) => !u.pathname.includes('/auth'), { timeout: 15000 })
  check(new URL(page.url()).pathname === '/projects', 'login: redirected back to /projects')

  // Seeded data must actually render in every list page (regression: lists read the wrong response shape).
  const expectations = {
    '/projects': /DAR-DLV|AQR-CORE/,
    '/products': /Novixa Aqar|نوڤيكسا عقار|عقار/,
    '/crm': /شركة الريان|Al-Rayyan|الريان/,
    '/knowledge': /./,
    '/tasks': /./,
  }
  for (const [path, pattern] of Object.entries(expectations)) {
    await page.goto(`${base}${path}`, { waitUntil: 'networkidle' })
    await page.waitForTimeout(800)
    const text = await page.locator('main').innerText()
    check(pattern.test(text) && !/لا توجد (نتائج|بيانات)/.test(text.slice(0, 400)), `data: ${path} shows seeded records`)
  }

  // Quick-create dialog is accessible: role=dialog, focus moves inside, Escape closes it.
  await page.goto(`${base}/dashboard`, { waitUntil: 'networkidle' })
  await page.keyboard.press('c')
  await page.waitForSelector('[role="dialog"]', { timeout: 5000 })
  check(await page.evaluate(() => !!document.activeElement?.closest('[role="dialog"]')), 'a11y: focus moves into the dialog')
  await page.keyboard.press('Escape')
  await page.waitForTimeout(400)
  check((await page.locator('[role="dialog"]').count()) === 0, 'a11y: Escape closes the dialog')

  check(pageErrors.length === 0, `no uncaught page errors${pageErrors.length ? ': ' + pageErrors[0] : ''}`)
} finally {
  await browser.close()
}

if (failures.length) { console.error(`\n${failures.length} journey check(s) failed`); process.exit(1) }
console.log('\nAll journey checks passed')
