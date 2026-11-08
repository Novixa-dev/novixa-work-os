import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import UiBadge from '../components/ui/UiBadge.vue'
import UiButton from '../components/ui/UiButton.vue'

describe('UiBadge', () => {
  it('renders slot content', () => {
    const wrapper = mount(UiBadge, { slots: { default: 'جديد' } })
    expect(wrapper.text()).toBe('جديد')
  })

  it.each([
    ['done', 'emerald'],
    ['in_progress', 'blue'],
    ['cancelled', 'rose'],
  ])('maps status %s to the %s palette', (status, color) => {
    const wrapper = mount(UiBadge, { props: { status }, slots: { default: 'x' } })
    expect(wrapper.html()).toContain(color)
  })

  it('maps urgent priority to rose, overriding color', () => {
    const wrapper = mount(UiBadge, { props: { priority: 'urgent', color: 'blue' }, slots: { default: 'x' } })
    expect(wrapper.html()).toContain('rose')
  })

  it('shows a dot only when requested', () => {
    expect(mount(UiBadge, { props: { dot: true } }).findAll('span')).toHaveLength(2)
    expect(mount(UiBadge).findAll('span')).toHaveLength(1)
  })
})

describe('UiButton', () => {
  it('renders its label and is enabled by default', () => {
    const wrapper = mount(UiButton, { slots: { default: 'حفظ' } })
    expect(wrapper.text()).toContain('حفظ')
    expect(wrapper.attributes('disabled')).toBeUndefined()
  })

  it('is disabled while loading to prevent double submit', () => {
    const wrapper = mount(UiButton, { props: { loading: true }, slots: { default: 'حفظ' } })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('is disabled when disabled prop is set', () => {
    const wrapper = mount(UiButton, { props: { disabled: true } })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })
})
