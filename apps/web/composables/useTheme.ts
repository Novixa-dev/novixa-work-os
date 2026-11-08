export type Theme = 'dark' | 'light' | 'system'

const currentTheme = ref<Theme>('dark')

export function useTheme() {
  function initTheme() {
    if (import.meta.client) {
      const saved = localStorage.getItem('novixa_theme') as Theme | null
      if (saved && ['dark', 'light', 'system'].includes(saved)) {
        currentTheme.value = saved
      }
      applyTheme()
    }
  }

  function applyTheme() {
    if (import.meta.client) {
      const isDark = currentTheme.value === 'dark' ||
        (currentTheme.value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)

      if (isDark) {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }

      localStorage.setItem('novixa_theme', currentTheme.value)
    }
  }

  function setTheme(theme: Theme) {
    currentTheme.value = theme
    applyTheme()
  }

  function toggleTheme() {
    setTheme(currentTheme.value === 'dark' ? 'light' : 'dark')
  }

  return {
    theme: readonly(currentTheme),
    isDark: computed(() => currentTheme.value === 'dark'),
    setTheme,
    toggleTheme,
    initTheme,
  }
}
