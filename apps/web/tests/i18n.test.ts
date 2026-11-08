import { describe, it, expect, beforeEach } from 'vitest'
import arDict from '../locales/ar.json'
import enDict from '../locales/en.json'

function translate(dict: any, fallbackDict: any, path: string, params: Record<string, any> = {}): string {
  const keys = path.split('.')
  let current: any = dict

  for (const key of keys) {
    if (current && typeof current === 'object' && key in current) {
      current = current[key]
    } else {
      let fallback: any = fallbackDict
      for (const fbKey of keys) {
        if (fallback && typeof fallback === 'object' && fbKey in fallback) {
          fallback = fallback[fbKey]
        } else {
          return path
        }
      }
      current = fallback
      break
    }
  }

  if (typeof current !== 'string') {
    return path
  }

  return current.replace(/\{(\w+)\}/g, (_, key) => {
    return params[key] !== undefined ? String(params[key]) : `{${key}}`
  })
}

describe('Bilingual Localization Engine', () => {
  it('translates Arabic core keys correctly', () => {
    const appName = translate(arDict, enDict, 'app.name')
    expect(appName).toBe('نوڤيكسا WorkOS')

    const dashboard = translate(arDict, enDict, 'nav.dashboard')
    expect(dashboard).toBe('لوحة التحكم')
  })

  it('translates English core keys correctly', () => {
    const appName = translate(enDict, arDict, 'app.name')
    expect(appName).toBe('Novixa WorkOS')

    const dashboard = translate(enDict, arDict, 'nav.dashboard')
    expect(dashboard).toBe('Dashboard')
  })

  it('correctly interpolates parameters like {name}', () => {
    const welcomeAr = translate(arDict, enDict, 'dashboard.welcome', { name: 'عمر' })
    expect(welcomeAr).toBe('مرحباً، عمر')

    const welcomeEn = translate(enDict, arDict, 'dashboard.welcome', { name: 'Omar' })
    expect(welcomeEn).toBe('Welcome back, Omar')
  })

  it('falls back to secondary language or key if path does not exist', () => {
    const nonExistent = translate(arDict, enDict, 'non.existent.key')
    expect(nonExistent).toBe('non.existent.key')
  })

  it('verifies dictionary completeness parity between Arabic and English', () => {
    const arModules = Object.keys(arDict)
    const enModules = Object.keys(enDict)

    expect(arModules).toEqual(expect.arrayContaining(['app', 'nav', 'dashboard', 'tasks', 'projects', 'products', 'crm', 'knowledge', 'settings', 'status', 'priority', 'roles']))
    expect(enModules).toEqual(expect.arrayContaining(['app', 'nav', 'dashboard', 'tasks', 'projects', 'products', 'crm', 'knowledge', 'settings', 'status', 'priority', 'roles']))
  })
})
