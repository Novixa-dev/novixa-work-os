<template>
  <aside
    :class="[
      'fixed inset-y-0 start-0 z-40 w-64 bg-slate-900 border-e border-slate-800 text-slate-300 flex flex-col transition-transform duration-200 lg:translate-x-0',
      mobileOpen ? 'translate-x-0' : (isRtl ? 'translate-x-full' : '-translate-x-full'),
    ]"
  >
    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800">
      <NuxtLink to="/dashboard" class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-brand-blue to-brand-teal flex items-center justify-center text-white font-bold text-lg shadow-sm">
          N
        </div>
        <div>
          <span class="font-bold text-white tracking-wide text-base block leading-none">Novixa</span>
          <span class="text-[11px] text-slate-400 font-medium block mt-1">WorkOS</span>
        </div>
      </NuxtLink>

      <button
        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800"
        @click="$emit('close-mobile')"
      >
        <span class="sr-only">Close sidebar</span>
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Navigation Groups -->
    <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6">
      <!-- Section 1: Overview & Daily Execution -->
      <div>
        <div class="px-2.5 mb-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
          {{ isRtl ? 'العمليات والتنفيذ' : 'Operations' }}
        </div>
        <nav class="space-y-1">
          <NuxtLink
            to="/dashboard"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/dashboard')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ t('nav.dashboard') }}</span>
          </NuxtLink>

          <NuxtLink
            to="/my-work"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/my-work')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span>{{ t('nav.my_work') }}</span>
          </NuxtLink>

          <NuxtLink
            to="/tasks"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/tasks')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>{{ t('nav.tasks') }}</span>
          </NuxtLink>

          <NuxtLink
            to="/projects"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/projects')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>{{ t('nav.projects') }}</span>
          </NuxtLink>
        </nav>
      </div>

      <!-- Section 2: Novixa Company Context -->
      <div>
        <div class="px-2.5 mb-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
          {{ isRtl ? 'كيانات نوڤيكسا' : 'Company Context' }}
        </div>
        <nav class="space-y-1">
          <NuxtLink
            to="/products"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/products')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span>{{ t('nav.products') }}</span>
          </NuxtLink>

          <NuxtLink
            v-if="!isGuest"
            to="/crm"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/crm')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>{{ t('nav.crm') }}</span>
          </NuxtLink>

          <NuxtLink
            to="/knowledge"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/knowledge')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>{{ t('nav.knowledge') }}</span>
          </NuxtLink>
        </nav>
      </div>

      <!-- Section 3: Workspace & Settings -->
      <div>
        <div class="px-2.5 mb-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
          {{ isRtl ? 'المنظومة' : 'Workspace' }}
        </div>
        <nav class="space-y-1">
          <NuxtLink
            to="/settings"
            :class="[
              'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              isActive('/settings')
                ? 'bg-brand-blue text-white shadow-sm'
                : 'text-slate-300 hover:bg-slate-800/80 hover:text-white',
            ]"
            @click="$emit('close-mobile')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ t('nav.settings') }}</span>
          </NuxtLink>
        </nav>
      </div>
    </div>

    <!-- User Footnote -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/60">
      <div class="flex items-center gap-3 px-2 py-1.5 rounded-lg">
        <div class="h-8 w-8 rounded-full bg-brand-blue/20 border border-brand-blue/40 flex items-center justify-center text-xs font-bold text-brand-blue shrink-0">
          {{ userInitials }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-medium text-white truncate">{{ user?.name || 'User' }}</p>
          <p class="text-[11px] text-slate-400 truncate">{{ t(`roles.${user?.role || 'member'}`) }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
const props = defineProps<{
  mobileOpen: boolean
}>()

defineEmits<{
  (e: 'close-mobile'): void
}>()

const route = useRoute()
const { t, isRtl } = useI18n()
const { user, isGuest } = useAuth()

function isActive(path: string): boolean {
  if (path === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/'
  }
  return route.path.startsWith(path)
}

const userInitials = computed(() => {
  if (!user.value?.name) return 'N'
  const parts = user.value.name.split(' ')
  return parts.length > 1 ? (parts[0][0] + parts[1][0]) : parts[0].slice(0, 2)
})
</script>
