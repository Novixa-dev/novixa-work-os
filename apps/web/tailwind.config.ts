import type { Config } from 'tailwindcss'

export default {
  darkMode: 'class',
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.{js,ts}',
    './plugins/**/*.{js,ts}',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          blue: '#2563EB',
          'blue-dark': '#1D4ED8',
          navy: '#0F172A',
          darker: '#020617',
          teal: '#14B8A6',
          'teal-dark': '#0D9488',
        },
      },
      fontFamily: {
        sans: ['Inter', 'IBM Plex Sans Arabic', 'Cairo', 'sans-serif'],
        arabic: ['IBM Plex Sans Arabic', 'Cairo', 'sans-serif'],
        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
      },
    },
  },
  plugins: [],
} satisfies Config
