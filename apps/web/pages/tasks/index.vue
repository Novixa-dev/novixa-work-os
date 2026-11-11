<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
          {{ t('tasks.title') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ isRtl ? 'إدارة وتتبع المهام اليومية، التعيينات، وموانع العمل' : 'Track sprint tasks, assignments, and blocking dependencies' }}
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <!-- List / Kanban View Switcher -->
        <div class="p-1 bg-slate-200/70 dark:bg-slate-800 rounded-lg flex items-center gap-1">
          <button
            type="button"
            :class="[
              'p-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1.5',
              viewMode === 'list'
                ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
            ]"
            @click="viewMode = 'list'"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="hidden sm:inline">{{ t('tasks.list_view') }}</span>
          </button>

          <button
            type="button"
            :class="[
              'p-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1.5',
              viewMode === 'kanban'
                ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm'
                : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
            ]"
            @click="viewMode = 'kanban'"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            <span class="hidden sm:inline">{{ t('tasks.kanban_view') }}</span>
          </button>
        </div>

        <UiButton v-if="!isGuest" size="sm" variant="primary" @click="createTaskModalOpen = true">
          <template #prefix>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </template>
          {{ t('tasks.create_task') }}
        </UiButton>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-wrap items-center gap-3 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
      <div class="w-full sm:w-64">
        <UiInput
          v-model="searchQuery"
          :placeholder="isRtl ? 'بحث في المهام بالرمز أو العنوان...' : 'Search tasks by code or title...'"
          @input="loadTasks"
        />
      </div>

      <div class="w-40">
        <UiSelect
          v-model="filterStatus"
          :options="statusFilterOptions"
          @change="loadTasks"
        />
      </div>

      <div class="w-40">
        <UiSelect
          v-model="filterPriority"
          :options="priorityFilterOptions"
          @change="loadTasks"
        />
      </div>

      <button
        type="button"
        :aria-pressed="onlyUnassigned"
        class="px-3 py-2 text-xs font-semibold rounded-lg border transition-colors"
        :class="onlyUnassigned ? 'bg-primary-600 text-white border-primary-600' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'"
        @click="onlyUnassigned = !onlyUnassigned; loadTasks()"
      >
        {{ isRtl ? 'المتاحة للاستلام' : 'Available to take' }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="py-16 text-center text-xs text-slate-400">
      {{ t('app.loading') }}
    </div>

    <!-- View Mode 1: LIST VIEW -->
    <div v-else-if="viewMode === 'list'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
      <div v-if="tasks.length === 0" class="py-16 text-center text-xs text-slate-400">
        {{ t('tasks.empty_tasks') }}
      </div>

      <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors cursor-pointer"
          @click="openTaskDrawer(task)"
        >
          <div class="flex items-start sm:items-center gap-3 min-w-0">
            <span class="text-xs font-mono font-bold text-slate-400 shrink-0">
              {{ task.code }}
            </span>
            <div class="min-w-0">
              <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">
                {{ task.title_ar }}
              </h3>
              <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500">
                <span v-if="task.project" class="font-medium text-brand-blue">{{ task.project.name_ar }}</span>
                <span v-if="task.assignee" class="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                  • {{ task.assignee.name }}
                  <span v-if="task.is_taken" class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold">
                    {{ isRtl ? 'قيد العمل الآن' : 'In progress' }}
                  </span>
                </span>
                <span v-else class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-semibold">
                  {{ isRtl ? 'متاحة' : 'Available' }}
                </span>
                <span v-if="task.due_date" class="tabular-nums" :class="task.is_overdue ? 'text-rose-600 font-bold' : ''">
                  • {{ task.due_date }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 ms-8 sm:ms-0 shrink-0">
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

    <!-- View Mode 2: KANBAN BOARD -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="col in kanbanColumns"
        :key="col.status"
        class="bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl p-3 flex flex-col min-h-[500px]"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full" :class="col.dotClass" />
            <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
              {{ col.title }}
            </h3>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
            {{ getTasksByStatus(col.status).length }}
          </span>
        </div>

        <!-- Task Cards in Column -->
        <div class="flex-1 space-y-2.5 overflow-y-auto">
          <div
            v-for="task in getTasksByStatus(col.status)"
            :key="task.id"
            class="p-3.5 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer"
            @click="openTaskDrawer(task)"
          >
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <span class="text-[11px] font-mono font-bold text-slate-400">{{ task.code }}</span>
              <UiBadge :priority="task.priority" size="sm">
                {{ t(`priority.${task.priority}`) }}
              </UiBadge>
            </div>

            <h4 class="text-xs font-semibold text-slate-900 dark:text-slate-100 line-clamp-2">
              {{ task.title_ar }}
            </h4>

            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-[11px] text-slate-500">
              <span v-if="task.project" class="text-brand-blue truncate max-w-[120px]">{{ task.project.name_ar }}</span>
              <span v-else class="text-slate-400">{{ isRtl ? 'مستقلة' : 'Standalone' }}</span>
              <span v-if="task.assignee" class="truncate max-w-[80px]" :title="task.assignee.name">{{ task.assignee.name }}</span>
              <span v-else class="text-emerald-600 font-semibold">{{ isRtl ? 'متاحة' : 'Available' }}</span>
              <span v-if="task.due_date" class="tabular-nums" :class="task.is_overdue ? 'text-rose-500 font-bold' : ''">
                {{ task.due_date }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <UiLoadMore :shown="tasks.length" :total="tasksTotal" :loading="loadingMore" @more="loadMoreTasks" />

    <!-- Task Detail Drawer (Slide-over) -->
    <Teleport to="body">
      <div v-if="selectedTask" class="fixed inset-0 z-50 overflow-hidden" @keydown.esc="closeDrawer">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="closeDrawer" />

        <div class="fixed inset-y-0 end-0 max-w-full flex ps-10">
          <div class="w-screen max-w-xl bg-white dark:bg-slate-900 border-s border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-brand-blue/10 text-brand-blue">
                  {{ selectedTask.code }}
                </span>
                <UiBadge :status="selectedTask.status">
                  {{ t(`status.${selectedTask.status}`) }}
                </UiBadge>
              </div>

              <div class="flex items-center gap-2">
                <!-- Status changer -->
                <select
                  :value="selectedTask.status"
                  class="text-xs font-medium bg-slate-100 dark:bg-slate-800 border-0 rounded-lg px-2.5 py-1 text-slate-800 dark:text-slate-200"
                  @change="handleStatusChange(($event.target as HTMLSelectElement).value)"
                >
                  <option value="todo">{{ t('status.todo') }}</option>
                  <option value="in_progress">{{ t('status.in_progress') }}</option>
                  <option value="in_review">{{ t('status.in_review') }}</option>
                  <option value="done">{{ t('status.done') }}</option>
                </select>

                <button
                  type="button"
                  class="p-1 rounded-lg text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                  @click="closeDrawer"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
              <!-- Title & Meta -->
              <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-snug">
                  {{ selectedTask.title_ar }}
                </h2>
                <p v-if="selectedTask.description_ar" class="text-xs text-slate-600 dark:text-slate-300 mt-2 whitespace-pre-line leading-relaxed">
                  {{ selectedTask.description_ar }}
                </p>
              </div>

              <!-- Metadata Grid -->
              <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-xs">
                <div>
                  <span class="text-slate-400 block mb-0.5">{{ t('tasks.project') }}</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ selectedTask.project ? selectedTask.project.name_ar : (isRtl ? 'بدون مشروع' : 'None') }}
                  </span>
                </div>
                <div>
                  <span class="text-slate-400 block mb-0.5">{{ t('tasks.assignee') }}</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ selectedTask.assignee ? selectedTask.assignee.name : (isRtl ? 'غير معين' : 'Unassigned') }}
                  </span>
                  <div v-if="!isGuest && !['done', 'cancelled'].includes(selectedTask.status)" class="mt-1.5">
                    <button
                      v-if="!selectedTask.assignee_id"
                      type="button"
                      class="px-2.5 py-1 text-[11px] font-semibold rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50"
                      :disabled="claiming"
                      @click="claimTask"
                    >
                      {{ isRtl ? 'استلام المهمة' : 'Take this task' }}
                    </button>
                    <button
                      v-else-if="selectedTask.assignee_id === currentUser?.id"
                      type="button"
                      class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50"
                      :disabled="claiming"
                      @click="releaseTask"
                    >
                      {{ isRtl ? 'إعادة للقائمة' : 'Release' }}
                    </button>
                    <span v-else class="text-[11px] text-blue-600 dark:text-blue-300">
                      {{ isRtl ? 'قيد العمل بواسطة زميل — لا تكرر الجهد' : 'A teammate is on it — avoid duplicate work' }}
                    </span>
                  </div>
                </div>
                <div>
                  <span class="text-slate-400 block mb-0.5">{{ t('tasks.priority') }}</span>
                  <UiBadge :priority="selectedTask.priority">
                    {{ t(`priority.${selectedTask.priority}`) }}
                  </UiBadge>
                </div>
                <div>
                  <span class="text-slate-400 block mb-0.5">{{ t('tasks.due_date') }}</span>
                  <span class="font-semibold tabular-nums text-slate-800 dark:text-slate-200">
                    {{ selectedTask.due_date || (isRtl ? 'غير محدد' : 'No date') }}
                  </span>
                </div>
              </div>

              <!-- Subtasks Section -->
              <div>
                <div class="flex items-center justify-between mb-2.5">
                  <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                    {{ t('tasks.subtasks') }}
                  </h3>
                </div>

                <div v-if="selectedTask.subtasks && selectedTask.subtasks.length > 0" class="space-y-1.5 mb-3">
                  <div
                    v-for="sub in selectedTask.subtasks"
                    :key="sub.id"
                    class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-lg text-xs flex items-center justify-between"
                  >
                    <span>{{ sub.title_ar }}</span>
                    <UiBadge :status="sub.status">{{ t(`status.${sub.status}`) }}</UiBadge>
                  </div>
                </div>

                <!-- Add subtask input -->
                <form class="flex items-center gap-2" @submit.prevent="addSubtask">
                  <input
                    v-model="newSubtaskTitle"
                    type="text"
                    :placeholder="isRtl ? 'إضافة مهمة فرعية جديدة...' : 'Add a new subtask...'"
                    class="text-xs rounded-lg px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border-0 flex-1 text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-brand-blue"
                  />
                  <UiButton size="sm" type="submit">{{ isRtl ? 'إضافة' : 'Add' }}</UiButton>
                </form>
              </div>

              <!-- Comments Section -->
              <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">
                  {{ t('tasks.comments') }}
                </h3>

                <div v-if="selectedTask.comments && selectedTask.comments.length > 0" class="space-y-3 mb-4">
                  <div
                    v-for="comment in selectedTask.comments"
                    :key="comment.id"
                    class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 text-xs"
                  >
                    <div class="flex items-center justify-between mb-1.5 text-slate-500">
                      <span class="font-semibold text-slate-800 dark:text-slate-200">{{ comment.user.name }}</span>
                      <span class="text-[10px]">{{ formatCommentTime(comment.created_at) }}</span>
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                      {{ comment.comment }}
                    </p>
                  </div>
                </div>

                <!-- Add comment form -->
                <form class="space-y-2" @submit.prevent="postComment">
                  <UiTextarea
                    v-model="newCommentText"
                    :placeholder="t('tasks.add_comment')"
                    :rows="2"
                  />
                  <div class="flex justify-end">
                    <UiButton size="sm" variant="primary" type="submit">
                      {{ t('tasks.send_comment') }}
                    </UiButton>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create Task Modal -->
    <UiModal
      v-model="createTaskModalOpen"
      :title="t('tasks.create_task')"
      @close="createTaskModalOpen = false"
    >
      <form class="space-y-4" @submit.prevent="handleCreateTask">
        <UiInput
          v-model="createForm.title_ar"
          :label="t('tasks.task_title')"
          placeholder="مثال: ترقية معمارية قواعد البيانات..."
          required
        />

        <div class="grid grid-cols-2 gap-3">
          <UiSelect
            v-model="createForm.project_id"
            :label="t('tasks.project')"
            :options="projectOptions"
            :placeholder="isRtl ? 'بدون مشروع' : 'No Project'"
          />
          <UiSelect
            v-model="createForm.priority"
            :label="t('tasks.priority')"
            :options="priorityOptions"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <UiSelect
            v-model="createForm.assignee_id"
            :label="t('tasks.assignee')"
            :options="userOptions"
            :placeholder="isRtl ? 'غير معين' : 'Unassigned'"
          />
          <UiInput
            v-model="createForm.due_date"
            type="date"
            :label="t('tasks.due_date')"
          />
        </div>

        <UiTextarea
          v-model="createForm.description_ar"
          :label="t('tasks.description')"
          :rows="3"
        />

        <div class="pt-2 flex justify-end gap-2.5">
          <UiButton variant="outline" type="button" @click="createTaskModalOpen = false">
            {{ isRtl ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton variant="primary" type="submit" :loading="creating">
            {{ isRtl ? 'إنشاء المهمة' : 'Create Task' }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
import UiBadge from '~/components/ui/UiBadge.vue'
import UiButton from '~/components/ui/UiButton.vue'
import UiInput from '~/components/ui/UiInput.vue'
import UiModal from '~/components/ui/UiModal.vue'
import UiSelect from '~/components/ui/UiSelect.vue'
import UiTextarea from '~/components/ui/UiTextarea.vue'

const { t, isRtl } = useI18n()
const { isGuest, user: currentUser } = useAuth()
const onlyUnassigned = ref(false)
const claiming = ref(false)
const api = useApi()
const route = useRoute()

const viewMode = ref<'list' | 'kanban'>('list')
const loading = ref(true)
const tasks = ref<any[]>([])

const searchQuery = ref('')
const filterStatus = ref('')
const filterPriority = ref('')

const createTaskModalOpen = ref(false)
const creating = ref(false)
const selectedTask = ref<any | null>(null)
const newSubtaskTitle = ref('')
const newCommentText = ref('')

const projectOptions = ref<any[]>([])
const userOptions = ref<any[]>([])

const createForm = ref({
  title_ar: '',
  project_id: '',
  priority: 'medium',
  assignee_id: '',
  due_date: '',
  description_ar: '',
})

const kanbanColumns = computed(() => [
  { status: 'todo', title: t('status.todo'), dotClass: 'bg-teal-500' },
  { status: 'in_progress', title: t('status.in_progress'), dotClass: 'bg-blue-500' },
  { status: 'in_review', title: t('status.in_review'), dotClass: 'bg-amber-500' },
  { status: 'done', title: t('status.done'), dotClass: 'bg-emerald-500' },
])

const statusFilterOptions = computed(() => [
  { label: isRtl.value ? 'كافة الحالات' : 'All Statuses', value: '' },
  { label: t('status.todo'), value: 'todo' },
  { label: t('status.in_progress'), value: 'in_progress' },
  { label: t('status.in_review'), value: 'in_review' },
  { label: t('status.done'), value: 'done' },
])

const priorityFilterOptions = computed(() => [
  { label: isRtl.value ? 'كافة الأولويات' : 'All Priorities', value: '' },
  { label: t('priority.urgent'), value: 'urgent' },
  { label: t('priority.high'), value: 'high' },
  { label: t('priority.medium'), value: 'medium' },
  { label: t('priority.low'), value: 'low' },
])

const priorityOptions = computed(() => [
  { label: t('priority.low'), value: 'low' },
  { label: t('priority.medium'), value: 'medium' },
  { label: t('priority.high'), value: 'high' },
  { label: t('priority.urgent'), value: 'urgent' },
])

onMounted(async () => {
  await Promise.all([loadTasks(), loadLookups()])

  // Check URL query code
  if (route.query.code) {
    const code = route.query.code as string
    const match = tasks.value.find(t => t.code === code)
    if (match) {
      openTaskDrawer(match)
    }
  }
})

const tasksTotal = ref(0)
const tasksPage = ref(1)
const loadingMore = ref(false)

function taskQueryUrl(page: number) {
  let url = `/tasks?page=${page}&per_page=50&`
  if (searchQuery.value) url += `search=${encodeURIComponent(searchQuery.value)}&`
  if (filterStatus.value) url += `status=${filterStatus.value}&`
  if (filterPriority.value) url += `priority=${filterPriority.value}&`
  if (onlyUnassigned.value) url += 'unassigned=1&'
  return url
}

async function loadTasks() {
  loading.value = true
  try {
    const res = await api.get<{ data: any[]; meta?: { total: number } }>(taskQueryUrl(1))
    tasks.value = res.data || []
    tasksTotal.value = res.meta?.total ?? tasks.value.length
    tasksPage.value = 1
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function loadMoreTasks() {
  loadingMore.value = true
  try {
    const res = await api.get<{ data: any[]; meta?: { total: number } }>(taskQueryUrl(tasksPage.value + 1))
    tasks.value = [...tasks.value, ...(res.data || [])]
    tasksTotal.value = res.meta?.total ?? tasksTotal.value
    tasksPage.value++
  } catch (e) {
  } finally {
    loadingMore.value = false
  }
}

async function loadLookups() {
  try {
    const [projRes, userRes] = await Promise.all([
      api.get<{ data: any[] }>('/projects'),
      api.get<{ data: any[] }>('/users'),
    ])

    projectOptions.value = (projRes.data || []).map(p => ({
      label: `${p.code} - ${p.name_ar}`,
      value: p.id,
    }))

    userOptions.value = (userRes.data || []).map(u => ({
      label: u.name,
      value: u.id,
    }))
  } catch (e) {}
}

function getTasksByStatus(status: string) {
  return tasks.value.filter(t => t.status === status)
}

async function openTaskDrawer(task: any) {
  try {
    const res = await api.get<{ task: any }>(`/tasks/${task.id}`)
    selectedTask.value = res.task
  } catch (e) {
    selectedTask.value = task
  }
}

function closeDrawer() {
  selectedTask.value = null
}

function applyTaskUpdate(updated: any) {
  if (!updated || !selectedTask.value) return
  Object.assign(selectedTask.value, updated)
  const idx = tasks.value.findIndex(x => x.id === updated.id)
  if (idx !== -1) tasks.value[idx] = { ...tasks.value[idx], ...updated }
}

async function claimTask() {
  if (!selectedTask.value || claiming.value) return
  claiming.value = true
  try {
    const res = await api.post<{ task: any }>(`/tasks/${selectedTask.value.id}/claim`)
    applyTaskUpdate(res.task)
  } catch {
    // 409 (taken by a teammate): useApi already toasted the owner's name; refresh to show the truth.
    await openTaskDrawer(selectedTask.value)
    await loadTasks()
  } finally {
    claiming.value = false
  }
}

async function releaseTask() {
  if (!selectedTask.value || claiming.value) return
  claiming.value = true
  try {
    const res = await api.post<{ task: any }>(`/tasks/${selectedTask.value.id}/release`)
    applyTaskUpdate(res.task)
  } catch {
    // error toast shown by useApi
  } finally {
    claiming.value = false
  }
}

async function handleStatusChange(newStatus: string) {
  if (!selectedTask.value) return
  try {
    await api.patch(`/tasks/${selectedTask.value.id}/status`, { status: newStatus })
    selectedTask.value.status = newStatus
    const t = tasks.value.find(x => x.id === selectedTask.value.id)
    if (t) t.status = newStatus
  } catch (e) {}
}

async function addSubtask() {
  if (!newSubtaskTitle.value.trim() || !selectedTask.value) return
  try {
    const res = await api.post<{ task: any }>('/tasks', {
      title_ar: newSubtaskTitle.value.trim(),
      parent_id: selectedTask.value.id,
      project_id: selectedTask.value.project_id,
      status: 'todo',
    })

    if (!selectedTask.value.subtasks) selectedTask.value.subtasks = []
    selectedTask.value.subtasks.push(res.task)
    newSubtaskTitle.value = ''
  } catch (e) {}
}

async function postComment() {
  if (!newCommentText.value.trim() || !selectedTask.value) return
  try {
    const res = await api.post<{ comment: any }>(`/tasks/${selectedTask.value.id}/comments`, {
      comment: newCommentText.value.trim(),
    })

    if (!selectedTask.value.comments) selectedTask.value.comments = []
    selectedTask.value.comments.push(res.comment)
    newCommentText.value = ''
  } catch (e) {}
}

async function handleCreateTask() {
  creating.value = true
  try {
    await api.post('/tasks', {
      title_ar: createForm.value.title_ar,
      project_id: createForm.value.project_id || null,
      priority: createForm.value.priority,
      assignee_id: createForm.value.assignee_id || null,
      due_date: createForm.value.due_date || null,
      description_ar: createForm.value.description_ar || null,
    })

    createTaskModalOpen.value = false
    createForm.value.title_ar = ''
    createForm.value.description_ar = ''
    await loadTasks()
  } catch (e) {
  } finally {
    creating.value = false
  }
}

function formatCommentTime(isoStr: string) {
  if (!isoStr) return ''
  const d = new Date(isoStr)
  return d.toLocaleDateString(isRtl.value ? 'ar-SA' : 'en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
