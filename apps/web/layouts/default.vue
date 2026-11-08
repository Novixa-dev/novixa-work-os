<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex">
    <!-- Sidebar -->
    <AppSidebar
      :mobile-open="mobileOpen"
      @close-mobile="mobileOpen = false"
    />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:ps-64">
      <AppHeader
        :unread-count="unreadNotificationsCount"
        @toggle-mobile="mobileOpen = !mobileOpen"
        @open-search="searchOpen = true"
        @open-quick-create="quickCreateOpen = true"
        @open-notifications="notificationsOpen = true"
      />

      <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
        <slot />
      </main>
    </div>

    <!-- Modals & Drawers -->
    <CommandPalette v-model="searchOpen" />
    <NotificationsDrawer v-model="notificationsOpen" @updated="fetchNotificationCount" />
    <QuickCreateModal v-model="quickCreateOpen" @created="handleQuickCreated" />
  </div>
</template>

<script setup lang="ts">
import AppHeader from '~/components/layout/AppHeader.vue'
import AppSidebar from '~/components/layout/AppSidebar.vue'
import CommandPalette from '~/components/layout/CommandPalette.vue'
import NotificationsDrawer from '~/components/layout/NotificationsDrawer.vue'
import QuickCreateModal from '~/components/layout/QuickCreateModal.vue'

const { initLocale } = useI18n()
const { initTheme } = useTheme()
const { initAuth, isAuthenticated } = useAuth()
const api = useApi()
const router = useRouter()

const mobileOpen = ref(false)
const searchOpen = ref(false)
const quickCreateOpen = ref(false)
const notificationsOpen = ref(false)
const unreadNotificationsCount = ref(0)

onMounted(async () => {
  initLocale()
  initTheme()
  await initAuth()

  if (!isAuthenticated.value) {
    router.push('/auth/login')
    return
  }

  fetchNotificationCount()

  // Global Keyboard Shortcuts (Cmd+K / Ctrl+K, C for create)
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  if (import.meta.client) {
    window.removeEventListener('keydown', handleGlobalKeydown)
  }
})

function handleGlobalKeydown(e: KeyboardEvent) {
  // If typing in an input or textarea, don't trigger global hotkeys
  const target = e.target as HTMLElement
  if (target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)) {
    return
  }

  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    searchOpen.value = !searchOpen.value
  } else if (e.key === 'c' || e.key === 'C') {
    e.preventDefault()
    quickCreateOpen.value = true
  }
}

async function fetchNotificationCount() {
  try {
    const res = await api.get<{ unread_count: number }>('/notifications')
    unreadNotificationsCount.value = res.unread_count || 0
  } catch (e) {}
}

function handleQuickCreated() {
  // Reload current route data
  router.go(0)
}
</script>
