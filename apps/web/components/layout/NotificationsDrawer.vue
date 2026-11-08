<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 overflow-hidden"
      @keydown.esc="close"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm transition-opacity" @click="close" />

      <!-- Slide-over Drawer -->
      <div class="fixed inset-y-0 end-0 max-w-full flex ps-10">
        <div class="w-screen max-w-md bg-white dark:bg-slate-900 border-s border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col">
          <!-- Header -->
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                {{ t('nav.notifications') }}
              </h3>
              <span
                v-if="unreadCount > 0"
                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900"
              >
                {{ unreadCount }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="unreadCount > 0"
                type="button"
                class="text-xs text-brand-blue hover:underline font-medium"
                @click="markAllRead"
              >
                {{ isRtl ? 'تعيين الكل كمقروء' : 'Mark all read' }}
              </button>
              <button
                type="button"
                class="p-1 rounded-lg text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                @click="close"
              >
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Notification Items -->
          <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
            <div v-if="loading" class="py-12 text-center text-xs text-slate-400">
              {{ t('app.loading') }}
            </div>

            <div v-else-if="items.length === 0" class="py-16 text-center text-xs text-slate-400">
              {{ isRtl ? 'لا توجد إشعارات حالياً' : 'No notifications yet' }}
            </div>

            <div
              v-for="item in items"
              :key="item.id"
              :class="[
                'p-3.5 rounded-xl border text-sm transition-all cursor-pointer',
                !item.is_read
                  ? 'bg-blue-50/60 dark:bg-blue-950/20 border-blue-200/80 dark:border-blue-900/50'
                  : 'bg-white dark:bg-slate-800/60 border-slate-100 dark:border-slate-800 hover:border-slate-200',
              ]"
              @click="handleClick(item)"
            >
              <div class="flex items-start justify-between gap-2">
                <h4 class="text-xs font-semibold text-slate-900 dark:text-slate-100">
                  {{ isRtl ? item.title_ar : (item.title_en || item.title_ar) }}
                </h4>
                <span v-if="!item.is_read" class="h-2 w-2 rounded-full bg-brand-blue shrink-0 mt-1" />
              </div>

              <p v-if="item.body_ar || item.body_en" class="text-xs text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
                {{ isRtl ? item.body_ar : (item.body_en || item.body_ar) }}
              </p>

              <span class="block text-[10px] text-slate-400 mt-2">
                {{ formatDate(item.created_at) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'updated'): void
}>()

const { t, isRtl } = useI18n()
const api = useApi()
const router = useRouter()

const loading = ref(false)
const items = ref<any[]>([])
const unreadCount = ref(0)

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    fetchNotifications()
  }
})

async function fetchNotifications() {
  loading.value = true
  try {
    const res = await api.get<{ unread_count: number; data: any[] }>('/notifications')
    items.value = res.data || []
    unreadCount.value = res.unread_count || 0
  } catch (e) {
    // Ignore error
  } finally {
    loading.value = false
  }
}

async function handleClick(item: any) {
  if (!item.is_read) {
    try {
      await api.patch(`/notifications/${item.id}/read`)
      item.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
      emit('updated')
    } catch (e) {}
  }

  if (item.link) {
    close()
    router.push(item.link)
  }
}

async function markAllRead() {
  try {
    await api.post('/notifications/mark-all-read')
    items.value.forEach(i => i.is_read = true)
    unreadCount.value = 0
    emit('updated')
  } catch (e) {}
}

function formatDate(dateStr: string) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString(isRtl.value ? 'ar-SA' : 'en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function close() {
  emit('update:modelValue', false)
}
</script>
