import { describe, it, expect, beforeEach } from 'vitest'
import { useToast } from '../composables/useToast'

describe('useToast', () => {
  beforeEach(() => {
    const t = useToast()
    t.toasts.value.forEach((x) => t.dismiss(x.id))
  })

  it('adds and dismisses toasts', () => {
    const t = useToast()
    t.success('Saved')
    expect(t.toasts.value).toHaveLength(1)
    t.dismiss(t.toasts.value[0].id)
    expect(t.toasts.value).toHaveLength(0)
  })

  it('de-duplicates identical messages', () => {
    const t = useToast()
    t.error('Network down')
    t.error('Network down')
    expect(t.toasts.value).toHaveLength(1)
  })

  it('keeps at most four toasts on screen', () => {
    const t = useToast()
    for (let i = 0; i < 8; i++) t.info(`m${i}`)
    expect(t.toasts.value.length).toBeLessThanOrEqual(4)
  })
})
