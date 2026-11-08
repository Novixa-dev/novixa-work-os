import arDict from '~/locales/ar.json'
import enDict from '~/locales/en.json'

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

  function t(path: string, params: Record<string, any> = {}): string {
    const dict = currentLocale.value === 'ar' ? arDict : enDict
    const keys = path.split('.')
    let current: any = dict

    for (const key of keys) {
      if (current && typeof current === 'object' && key in current) {
        current = current[key]
      } else {
        // Fallback to English if key missing in Arabic
        let fallback: any = enDict
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

    let output = current
    for (const [paramKey, paramVal] of Object.entries(params)) {
      output = output.replace(new RegExp(`{${paramKey}}`, 'g'), String(paramVal))
    }

    return output
  }

  return {
    locale: readonly(currentLocale),
    isRtl,
    setLocale,
    initLocale,
    t,
  }
}
