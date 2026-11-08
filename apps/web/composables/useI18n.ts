import arDict from '~/locales/ar.json'
import enDict from '~/locales/en.json'
import { translate, type Dictionary } from '~/utils/translate'

type Locale = 'ar' | 'en'

const currentLocale = ref<Locale>('ar')

export function useI18n() {
  const isRtl = computed(() => currentLocale.value === 'ar')

  function initLocale() {
    if (import.meta.client) {
      const saved = localStorage.getItem('novixa_locale') as Locale | null
      if (saved && (saved === 'ar' || saved === 'en')) {
        currentLocale.value = saved
      }
      applyDocumentDirection()
    }
  }

  function applyDocumentDirection() {
    if (import.meta.client) {
      document.documentElement.dir = isRtl.value ? 'rtl' : 'ltr'
      document.documentElement.lang = currentLocale.value
      localStorage.setItem('novixa_locale', currentLocale.value)
    }
  }

  function setLocale(locale: Locale) {
    currentLocale.value = locale
    applyDocumentDirection()
  }

  function t(path: string, params: Record<string, string | number> = {}): string {
    const arabic = currentLocale.value === 'ar'
    return translate(
      (arabic ? arDict : enDict) as Dictionary,
      (arabic ? enDict : arDict) as Dictionary,
      path,
      params,
    )
  }

  return {
    locale: readonly(currentLocale),
    isRtl,
    setLocale,
    initLocale,
    t,
  }
}
