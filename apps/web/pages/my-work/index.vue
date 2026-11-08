<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
          {{ t('nav.my_work') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ isRtl ? 'مساحتك الخاصة للتركيز وإنجاز الالتزامات اليومية دون تشتت' : 'Your personal workspace to execute daily commitments with deep focus' }}
        </p>
      </div>

      <NuxtLink to="/tasks">
        <UiButton size="sm" variant="outline">
          <template #prefix>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </template>
          {{ isRtl ? 'استعراض المهام العامة' : 'All Tasks' }}
        </UiButton>
      </NuxtLink>
    </div>

    <!-- View Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        :class="[
          'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-2',
          activeTab === tab.id
            ? 'bg-brand-blue text-white shadow-sm'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
        ]"
        @click="activeTab = tab.id"
      >
        <span>{{ tab.label }}</span>
        <span
          :class="[
            'px-1.5 py-0.2 rounded-full text-[10px] font-mono',
            activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
          ]"
        >
          {{ counts[tab.id] || 0 }}
        </span>
      </button>
    </div>

    <!-- Active Tab Content -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl animate-pulse" />
    </div>

    <div v-else-if="currentTasks.length === 0" class="py-16 text-center">
      <div class="h-12 w-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
        {{ isRtl ? 'لا توجد مهام في هذا القسم حالياً' : 'No tasks in this section' }}
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
        {{ isRtl ? 'أنت جاهز تماماً أو يمكنك إضافة مهام جديدة لجدولك.' : 'All clear! You can add tasks or enjoy your day.' }}
      </p>
    </div>

    <div v-else class="space-y-2.5">
      <div
        v-for="task in currentTasks"
        :key="task.id"
        class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-slate-300 dark:hover:border-slate-700 transition-shadow hover:shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3"
      >
        <div class="flex items-start sm:items-center gap-3 min-w-0">
          <button
            type="button"
            class="h-5 w-5 rounded border border-slate-300 dark:border-slate-600 hover:border-brand-teal flex items-center justify-center shrink-0 mt-0.5 sm:mt-0"
            @click="completeTask(task)"
          >
            <svg class="w-3.5 h-3.5 text-transparent hover:text-brand-teal" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono font-bold text-slate-400">{{ task.code }}</span>
              <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                {{ task.title_ar }}
              </h3>
            </div>
            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500">
              <span v-if="task.project" class="font-medium text-brand-blue">{{ task.project.name_ar }}</span>
              <span v-if="task.due_date" class="tabular-nums" :class="task.is_overdue ? 'text-rose-600 font-bold' : ''">
                {{ isRtl ? 'الموعد:' : 'Due:' }} {{ task.due_date }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2.5 ms-8 sm:ms-0">
          <!-- Toggle My Day Button -->
          <button
            type="button"
            class="p-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-brand-blue hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            :title="t('tasks.toggle_my_day')"
            @click="toggleMyDay(task)"
          >
            <!-- Sun icon -->
            <svg
              class="w-4 h-4"
              :class="task.is_my_day ? 'text-amber-500 fill-amber-500' : 'text-slate-400'"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </button>

          <UiBadge :priority="task.priority">
            {{ t(`priority.${task.priority}`) }}
          </UiBadge>

          <UiBadge :status="task.status">
            {{ t(`status.${task.status}`) }}
          </UiBadge>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import UiBadge from '~/components/ui/UiBadge.vue'
import UiButton from '~/components/ui/UiButton.vue'

const { t, isRtl } = useI18n()
const api = useApi()

const route = useRoute()
const activeTab = ref<string>((route.query.tab as string) || 'my_day')
const loading = ref(true)

const myDayTasks = ref<any[]>([])
const myTasks = ref<any[]>([])
const overdueTasks = ref<any[]>([])
const assignedByMe = ref<any[]>([])

const counts = ref<Record<string, number>>({
  my_day: 0,
  my_tasks: 0,
  overdue: 0,
  assigned_by_me: 0,
})

const tabs = computed(() => [
  { id: 'my_day', label: t('nav.my_day') },
  { id: 'my_tasks', label: t('nav.my_tasks') },
  { id: 'overdue', label: t('nav.overdue') },
  { id: 'assigned_by_me', label: t('nav.assigned_by_me') },
])

const currentTasks = computed(() => {
  switch (activeTab.value) {
    case 'my_day': return myDayTasks.value
    case 'my_tasks': return myTasks.value
    case 'overdue': return overdueTasks.value
    case 'assigned_by_me': return assignedByMe.value
    default: return myDayTasks.value
  }
})

onMounted(() => {
  loadMyWork()
})

async function loadMyWork() {
  loading.value = true
  try {
    const res = await api.get<any>('/tasks/my-work')
    myDayTasks.value = res.my_day || []
    myTasks.value = res.my_tasks || []
    overdueTasks.value = res.overdue || []
    assignedByMe.value = res.assigned_by_me || []
    counts.value = res.counts || {}
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function toggleMyDay(task: any) {
  try {
    await api.patch(`/tasks/${task.id}/my-day`)
    task.is_my_day = !task.is_my_day
    loadMyWork()
  } catch (e) {}
}

async function completeTask(task: any) {
  try {
    await api.patch(`/tasks/${task.id}/status`, { status: 'done' })
    loadMyWork()
  } catch (e) {}
}
</script>
