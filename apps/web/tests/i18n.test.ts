import { describe, it, expect } from 'vitest'
import arDict from '../locales/ar.json'
import enDict from '../locales/en.json'
import { translate, flattenKeys, type Dictionary } from '../utils/translate'

const ar = arDict as Dictionary
const en = enDict as Dictionary

describe('translate()', () => {
  it('resolves Arabic and English keys', () => {
    expect(translate(ar, en, 'nav.dashboard')).toBe('لوحة التحكم')
    expect(translate(en, ar, 'nav.dashboard')).toBe('Dashboard')
  })

  it('interpolates parameters', () => {
    expect(translate(ar, en, 'dashboard.welcome', { name: 'عمر' })).toBe('مرحباً، عمر')
    expect(translate(en, ar, 'dashboard.welcome', { name: 'Omar' })).toBe('Welcome back, Omar')
  })

  it('keeps unknown placeholders intact', () => {
    expect(translate({ a: 'Hi {who}' }, {}, 'a')).toBe('Hi {who}')
  })

  it('falls back to the secondary dictionary, then to the key', () => {
    expect(translate({}, { x: 'fallback' }, 'x')).toBe('fallback')
    expect(translate(ar, en, 'non.existent.key')).toBe('non.existent.key')
  })

  it('does not return objects for non-leaf keys', () => {
    expect(translate(ar, en, 'nav')).toBe('nav')
  })
})

describe('locale dictionaries', () => {
  it('have identical key sets in Arabic and English', () => {
    const arKeys = flattenKeys(ar).sort()
    const enKeys = flattenKeys(en).sort()
    expect(arKeys.filter((k) => !enKeys.includes(k))).toEqual([])
    expect(enKeys.filter((k) => !arKeys.includes(k))).toEqual([])
  })

  it('have no empty translations', () => {
    for (const dict of [ar, en]) {
      const empty = flattenKeys(dict).filter((k) => translate(dict, {}, k).trim() === '')
      expect(empty).toEqual([])
    }
  })
})
