import { describe, it, expect } from 'vitest'
import { pickLocalizedMessage } from '../utils/localized-message'

describe('pickLocalizedMessage', () => {
  const msg = 'تم تسجيل الدخول بنجاح / Successfully logged in'

  it('returns the Arabic half for Arabic UI', () => {
    expect(pickLocalizedMessage(msg, true)).toBe('تم تسجيل الدخول بنجاح')
  })

  it('returns the English half for English UI', () => {
    expect(pickLocalizedMessage(msg, false)).toBe('Successfully logged in')
  })

  it('handles English-first messages', () => {
    expect(pickLocalizedMessage('Saved / تم الحفظ', true)).toBe('تم الحفظ')
  })

  it('leaves non-bilingual messages untouched', () => {
    expect(pickLocalizedMessage('تم تعطيل الحساب', false)).toBe('تم تعطيل الحساب')
  })
})
