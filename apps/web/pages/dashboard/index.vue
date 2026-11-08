<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
          {{ t('dashboard.welcome', { name: user?.name || '' }) }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ t('dashboard.overview_title') }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <NuxtLink to="/tasks">
          <UiButton size="sm" variant="outline">
            {{ isRtl ? 'عرض كافة المهام' : 'View All Tasks' }}
          </UiButton>
        </NuxtLink>
        <NuxtLink to="/my-work">
          <UiButton size="sm" variant="primary">
            {{ isRtl ? 'تركيزي اليوم' : 'My Day' }}
          </UiButton>
        </NuxtLink>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-5 gap-4">
      <div v-for="i in 5" :key="i" class="h-24 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl animate-pulse" />
    </div>

    <!-- Metrics Cards -->
    <div v-else class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
      <!-- Active Projects -->
      <UiCard body-class="p-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('dashboard.active_projects') }}</span>
          <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-brand-blue">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-2 font-mono">
          {{ metrics.active_projects }}
        </p>
      </UiCard>

      <!-- Pending Tasks -->
      <UiCard body-class="p-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('dashboard.pending_tasks') }}</span>
          <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-2 font-mono">
          {{ metrics.pending_tasks }}
        </p>
      </UiCard>

      <!-- Overdue Tasks -->
      <UiCard body-class="p-4" :class="metrics.overdue_tasks > 0 ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20' : ''">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ t('dashboard.overdue_tasks') }}</span>
          <span class="p-1.5 rounded-lg bg-rose-100 dark:bg-rose-950 text-rose-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2 font-mono">
          {{ metrics.overdue_tasks }}
        </p>
      </UiCard>

      <!-- Today Tasks -->
      <UiCard body-class="p-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('dashboard.today_tasks') }}</span>
          <span class="p-1.5 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-brand-teal">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-2 font-mono">
          {{ metrics.today_tasks }}
        </p>
      </UiCard>

      <!-- Pipeline Value -->
      <UiCard body-class="p-4" class="col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('dashboard.pipeline_value') }}</span>
          <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
        </div>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 font-mono">
          ${{ Number(metrics.pipeline_value || 0).toLocaleString() }}
        </p>
      </UiCard>
    </div>

    <!-- Overdue Alert Banner (if any) -->
    <div
      v-if="overdueTasks.length > 0"
      class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
    >
      <div class="flex items-center gap-3">
        <span class="h-9 w-9 rounded-lg bg-rose-100 dark:bg-rose-900/60 text-rose-600 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </span>
        <div>
          <h4 class="text-sm font-semibold text-rose-900 dark:text-rose-200">{{ t('dashboard.overdue_title') }}</h4>
          <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">
            {{ isRtl ? `لديك ${overdueTasks.length} مهام تتطلب اتخاذ إجراء فوري.` : `You have ${overdueTasks.length} tasks requiring immediate attention.` }}
          </p>
        </div>
      </div>
      <NuxtLink to="/my-work?tab=overdue">
        <UiButton size="sm" variant="danger">
          {{ isRtl ? 'معالجة المتأخرات' : 'Resolve Overdue' }}
        </UiButton>
      </NuxtLink>
    </div>

    <!-- Two-Column Grid: My Day Focus & Active Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Left: My Day Focus -->
      <UiCard :title="t('dashboard.my_day_title')">
        <template #action>
          <NuxtLink to="/my-work">
            <span class="text-xs text-brand-blue hover:underline">{{ isRtl ? 'عرض الكل' : 'View all' }}</span>
          </NuxtLink>
        </template>

        <div v-if="myDayTasks.length === 0" class="py-8 text-center text-xs text-slate-400">
          {{ t('dashboard.my_day_empty') }}
        </div>

        <div v-else class="space-y-2.5">
          <div
            v-for="task in myDayTasks"
            :key="task.id"
            class="p-3 rounded-lg border border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between gap-3 transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <button
                type="button"
                class="h-5 w-5 rounded border border-slate-300 dark:border-slate-600 hover:border-brand-teal flex items-center justify-center shrink-0"
                @click="completeTask(task)"
              >
                <svg class="w-3.5 h-3.5 text-transparent hover:text-brand-teal" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
              <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">
                  <span class="font-mono text-slate-400 me-1.5">{{ task.code }}</span>
                  {{ task.title_ar }}
                </p>
                <p v-if="task.project" class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                  {{ task.project.name_ar }}
                </p>
              </div>
            </div>

            <UiBadge :priority="task.priority">
              {{ t(`priority.${task.priority}`) }}
            </UiBadge>
          </div>
        </div>
      </UiCard>

      <!-- Right: Active Projects -->
      <UiCard :title="t('dashboard.active_projects_title')">
        <template #action>
          <NuxtLink to="/projects">
            <span class="text-xs text-brand-blue hover:underline">{{ isRtl ? 'عرض الكل' : 'View all' }}</span>
          </NuxtLink>
        </template>

        <div class="space-y-3.5">
          <div
            v-for="project in activeProjects"
            :key="project.id"
            class="p-3.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40"
          >
            <div class="flex items-center justify-between gap-2 mb-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-brand-blue/10 text-brand-blue border border-brand-blue/20">
                  {{ project.code }}
                </span>
                <h4 class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">
                  {{ project.name_ar }}
                </h4>
              </div>
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                {{ project.progress_percentage }}%
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
              <div
                class="bg-brand-blue h-1.5 rounded-full transition-all duration-300"
                :style="{ width: `${project.progress_percentage}%` }"
              />
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2">
              <span>{{ project.lead ? project.lead.name : (isRtl ? 'بدون قائد' : 'No lead') }}</span>
              <span v-if="project.due_date">{{ isRtl ? 'التسليم:' : 'Due:' }} {{ project.due_date }}</span>
            </div>
          </div>
        </div>
      </UiCard>
    </div>

    <!-- Bottom Two-Column: Client Follow-ups & Recent Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Client Follow-ups (CRM) -->
      <UiCard v-if="!isGuest" :title="t('dashboard.upcoming_followups')">
        <div v-if="followUps.length === 0" class="py-8 text-center text-xs text-slate-400">
          {{ t('dashboard.followup_empty') }}
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="f in followUps"
            :key="f.id"
            class="p-3 rounded-lg border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-3"
          >
            <div>
              <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">
                {{ f.client_name }}
              </p>
              <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 line-clamp-1">
                {{ f.summary }}
              </p>
            </div>
            <UiBadge color="blue" size="sm">
              {{ f.date }}
            </UiBadge>
          </div>
        </div>
      </UiCard>

      <!-- Recent Activity Audit Log -->
      <UiCard :title="t('dashboard.recent_activity_title')">
        <div class="space-y-2.5">
          <div
            v-for="log in recentActivity"
            :key="log.id"
            class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 dark:border-slate-800/60 last:border-0"
          >
            <div class="flex items-center gap-2 min-w-0">
              <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                {{ log.user ? log.user.name : 'System' }}
              </span>
              <span class="text-slate-400 font-mono text-[11px] truncate">
                {{ log.action }}
              </span>
            </div>
            <span class="text-[10px] text-slate-400 shrink-0 tabular-nums">
              {{ formatTime(log.created_at) }}
            </span>
          </div>
        </div>
      </UiCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import UiBadge from '~/components/ui/UiBadge.vue'
import UiButton from '~/components/ui/UiButton.vue'
import UiCard from '~/components/ui/UiCard.vue'

const { t, isRtl } = useI18n()
const { user, isGuest } = useAuth()
const api = useApi()

const loading = ref(true)
const metrics = ref<any>({
  active_projects: 0,
  pending_tasks: 0,
  overdue_tasks: 0,
  today_tasks: 0,
  pipeline_value: 0,
})

const myDayTasks = ref<any[]>([])
const overdueTasks = ref<any[]>([])
const activeProjects = ref<any[]>([])
const followUps = ref<any[]>([])
const recentActivity = ref<any[]>([])

onMounted(async () => {
  await loadDashboard()
})

async function loadDashboard() {
  loading.value = true
  try {
    const res = await api.get<any>('/dashboard')
    metrics.value = res.metrics || {}
    myDayTasks.value = res.my_day_tasks || []
    overdueTasks.value = res.overdue_tasks || []
    activeProjects.value = res.active_projects || []
    followUps.value = res.follow_ups || []
    recentActivity.value = res.recent_activity || []
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function completeTask(task: any) {
  try {
    await api.patch(`/tasks/${task.id}/status`, { status: 'done' })
    myDayTasks.value = myDayTasks.value.filter(t => t.id !== task.id)
    metrics.value.pending_tasks = Math.max(0, metrics.value.pending_tasks - 1)
  } catch (e) {}
}

function formatTime(isoStr: string) {
  if (!isoStr) return ''
  const d = new Date(isoStr)
  return d.toLocaleTimeString(isRtl.value ? 'ar-SA' : 'en-US', { hour: '2-digit', minute: '2-digit' })
}
</script>
