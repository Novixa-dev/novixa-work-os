<template>
  <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6">
    <!-- Left: Mobile Toggle & Search Button -->
    <div class="flex items-center gap-3 flex-1 max-w-xl">
      <button
        type="button"
        class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
        @click="$emit('toggle-mobile')"
      >
        <span class="sr-only">Toggle Sidebar</span>
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Global Search Command Trigger -->
      <button
        type="button"
        class="w-full flex items-center justify-between text-xs text-slate-400 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-800 px-3.5 py-2 rounded-lg border border-slate-200/60 dark:border-slate-700/60 transition-colors"
        @click="$emit('open-search')"
      >
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span class="hidden sm:inline">{{ t('app.search_placeholder') }}</span>
          <span class="sm:hidden">{{ isRtl ? 'بحث...' : 'Search...' }}</span>
        </div>
        <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded">
          Ctrl+K
        </kbd>
      </button>
    </div>

    <!-- Right: Quick Actions & Profile -->
    <div class="flex items-center gap-2 sm:gap-3 ms-4">
      <!-- Quick Create Button -->
      <UiButton
        v-if="!isGuest"
        size="sm"
        variant="primary"
        class="hidden sm:inline-flex"
        @click="$emit('open-quick-create')"
      >
        <template #prefix>
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </template>
        {{ t('nav.quick_create') }}
      </UiButton>

      <!-- Language Toggle -->
      <button
        type="button"
        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-colors"
        @click="toggleLocale"
      >
        {{ locale === 'ar' ? 'English' : 'عربي' }}
      </button>

      <!-- Theme Toggle -->
      <button
        type="button"
        class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        @click="toggleTheme"
      >
        <span class="sr-only">Toggle Theme</span>
        <!-- Sun icon (when dark) -->
        <svg v-if="isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <!-- Moon icon (when light) -->
        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>

      <!-- Notifications Bell -->
      <button
        type="button"
        class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        @click="$emit('open-notifications')"
      >
        <span class="sr-only">Notifications</span>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
          v-if="(unreadCount ?? 0) > 0"
          class="absolute top-1.5 end-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"
        />
      </button>

      <!-- Logout Button -->
      <button
        type="button"
        class="p-2 rounded-lg text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        :title="t('nav.logout')"
        @click="logout"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import UiButton from '~/components/ui/UiButton.vue'

defineProps<{
  unreadCount?: number
}>()

defineEmits<{
  (e: 'toggle-mobile'): void
  (e: 'open-search'): void
  (e: 'open-quick-create'): void
  (e: 'open-notifications'): void
}>()

const { locale, setLocale, t, isRtl } = useI18n()
const { isDark, toggleTheme } = useTheme()
const { isGuest, logout } = useAuth()

function toggleLocale() {
  setLocale(locale.value === 'ar' ? 'en' : 'ar')
}
</script>
