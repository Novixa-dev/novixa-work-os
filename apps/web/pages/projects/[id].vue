<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  FolderKanban,
  CheckSquare,
  Key,
  FileText,
  FileCheck,
  Link as LinkIcon,
  Flag,
  Plus,
  ArrowRight,
  ArrowLeft,
  Search,
  ExternalLink,
  Copy,
  Check,
  Eye,
  EyeOff,
  Pin,
  PinOff,
  Trash2,
  User as UserIcon,
  Clock,
  AlertTriangle,
  Lightbulb,
  TrendingUp,
  LayoutGrid,
  List as ListIcon,
  Calendar,
  Building2,
  Boxes,
  Lock,
  Globe,
  Share2
} from 'lucide-vue-next'
import { useI18n } from '~/composables/useI18n'
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import UiCard from '~/components/ui/UiCard.vue'
import UiBadge from '~/components/ui/UiBadge.vue'
import UiButton from '~/components/ui/UiButton.vue'
import UiInput from '~/components/ui/UiInput.vue'
import UiSelect from '~/components/ui/UiSelect.vue'
import UiTextarea from '~/components/ui/UiTextarea.vue'
import UiModal from '~/components/ui/UiModal.vue'

const { t, locale, isRtl } = useI18n()
const api = useApi()
const auth = useAuth()
const route = useRoute()
const router = useRouter()

const projectId = route.params.id as string
const project = ref<any>(null)
const tasks = ref<any[]>([])
const loading = ref(true)
const activeTab = ref<'tasks' | 'notes' | 'reports' | 'links' | 'milestones'>('tasks')

// Tasks filters & view
const tasksViewMode = ref<'list' | 'kanban'>('list')
const taskFilter = ref<'all' | 'mine' | 'unassigned' | 'in_progress' | 'done'>('all')
const taskSearchQuery = ref('')

// Secret visibility & copied state
const visiblePasswords = ref<Record<number, boolean>>({})
const copiedField = ref<string | null>(null)

// Modals
const showCreateTaskModal = ref(false)
const showCreateNoteModal = ref(false)
const showCreateReportModal = ref(false)
const showCreateLinkModal = ref(false)
const showCreateMilestoneModal = ref(false)
const submitting = ref(false)
const modalError = ref('')

// Forms
const taskForm = ref({
  title_ar: '',
  title_en: '',
  description_ar: '',
  status: 'todo',
  priority: 'medium',
  assignee_id: '',
  due_date: '',
})

const noteForm = ref({
  title: '',
  type: 'credential',
  content: '',
  url: '',
  username: '',
  password: '',
  environment: 'staging',
  color: 'emerald',
  is_pinned: false,
})

const reportForm = ref({
  title: '',
  type: 'progress_report',
  content: '',
  status: 'open',
})

const linkForm = ref({
  title: '',
  category: 'dashboard',
  url: '',
  note: '',
})

const milestoneForm = ref({
  title_ar: '',
  title_en: '',
  due_date: '',
})

// Users for select
const usersList = ref<any[]>([])

async function fetchProjectData() {
  loading.value = true
  try {
    const [pRes, tRes, uRes] = await Promise.all([
      api.get(`/projects/${projectId}`),
      api.get('/tasks', { params: { project_id: projectId, per_page: 100 } }),
      api.get('/users')
    ])
    project.value = pRes.project || pRes.data?.project || pRes.data || null
    tasks.value = tRes.data?.data || (Array.isArray(tRes.data) ? tRes.data : (Array.isArray(tRes) ? tRes : []))
    usersList.value = uRes.data?.data || (Array.isArray(uRes.data) ? uRes.data : (Array.isArray(uRes) ? uRes : []))
  } catch (err) {
    console.error('Failed to load project hub', err)
  } finally {
    loading.value = false
  }
}

// Task Claiming
async function handleClaimTask(task: any) {
  try {
    const res = await api.post(`/tasks/${task.id}/claim`)
    if (res.data?.task) {
      const idx = tasks.value.findIndex(t => t.id === task.id)
      if (idx !== -1) tasks.value[idx] = res.data.task
      // refresh project to update progress
      const pRes = await api.get(`/projects/${projectId}`)
      project.value = pRes.project || pRes.data?.project || project.value
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to claim task')
  }
}

async function handleReleaseTask(task: any) {
  try {
    const res = await api.post(`/tasks/${task.id}/release`)
    if (res.data?.task) {
      const idx = tasks.value.findIndex(t => t.id === task.id)
      if (idx !== -1) tasks.value[idx] = res.data.task
      const pRes = await api.get(`/projects/${projectId}`)
      project.value = pRes.project || pRes.data?.project || project.value
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to release task')
  }
}

// Create Task
async function handleCreateTask() {
  if (!taskForm.value.title_ar && !taskForm.value.title_en) {
    modalError.value = locale.value === 'ar' ? 'عنوان المهمة مطلوب' : 'Task title is required'
    return
  }
  submitting.value = true
  modalError.value = ''
  try {
    const payload: any = {
      ...taskForm.value,
      project_id: projectId,
      code: `T-${Math.floor(1000 + Math.random() * 9000)}`,
    }
    if (!payload.assignee_id) delete payload.assignee_id
    if (!payload.due_date) delete payload.due_date

    await api.post('/tasks', payload)
    showCreateTaskModal.value = false
    await fetchProjectData()
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Failed to create task'
  } finally {
    submitting.value = false
  }
}

// Create Note / Info Card / Credential
async function handleCreateNote() {
  if (!noteForm.value.title) {
    modalError.value = locale.value === 'ar' ? 'عنوان البطاقة مطلوب' : 'Card title is required'
    return
  }
  submitting.value = true
  modalError.value = ''
  try {
    await api.post(`/projects/${projectId}/notes`, noteForm.value)
    showCreateNoteModal.value = false
    const pRes = await api.get(`/projects/${projectId}`)
    project.value = pRes.project || pRes.data?.project || project.value
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Failed to save card'
  } finally {
    submitting.value = false
  }
}

async function togglePinNote(note: any) {
  try {
    const res = await api.patch(`/projects/${projectId}/notes/${note.id}/pin`)
    note.is_pinned = res.data?.note?.is_pinned
  } catch (err) {
    console.error('Failed to toggle pin', err)
  }
}

async function handleDeleteNote(note: any) {
  if (!confirm(locale.value === 'ar' ? 'هل أنت متأكد من حذف هذه البطاقة؟' : 'Delete this card?')) return
  try {
    await api.delete(`/projects/${projectId}/notes/${note.id}`)
    if (project.value && project.value.notes) {
      project.value.notes = project.value.notes.filter((n: any) => n.id !== note.id)
    }
  } catch (err) {
    console.error('Failed to delete note', err)
  }
}

// Create Report / Suggestion
async function handleCreateReport() {
  if (!reportForm.value.title || !reportForm.value.content) {
    modalError.value = locale.value === 'ar' ? 'العنوان والمحتوى مطلوبان' : 'Title and content are required'
    return
  }
  submitting.value = true
  modalError.value = ''
  try {
    await api.post(`/projects/${projectId}/reports`, reportForm.value)
    showCreateReportModal.value = false
    const pRes = await api.get(`/projects/${projectId}`)
    project.value = pRes.project || pRes.data?.project || project.value
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Failed to save report'
  } finally {
    submitting.value = false
  }
}

async function handleUpdateReportStatus(report: any, newStatus: string) {
  try {
    await api.patch(`/projects/${projectId}/reports/${report.id}/status`, { status: newStatus })
    report.status = newStatus
  } catch (err) {
    console.error('Failed to update report status', err)
  }
}

async function handleDeleteReport(report: any) {
  if (!confirm(locale.value === 'ar' ? 'هل أنت متأكد من حذف هذا التقرير؟' : 'Delete this report?')) return
  try {
    await api.delete(`/projects/${projectId}/reports/${report.id}`)
    if (project.value && project.value.reports) {
      project.value.reports = project.value.reports.filter((r: any) => r.id !== report.id)
    }
  } catch (err) {
    console.error('Failed to delete report', err)
  }
}

// Create Link / Reference
async function handleCreateLink() {
  if (!linkForm.value.title) {
    modalError.value = locale.value === 'ar' ? 'العنوان مطلوب' : 'Title is required'
    return
  }
  submitting.value = true
  modalError.value = ''
  try {
    await api.post(`/projects/${projectId}/links`, linkForm.value)
    showCreateLinkModal.value = false
    const pRes = await api.get(`/projects/${projectId}`)
    project.value = pRes.project || pRes.data?.project || project.value
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Failed to save link'
  } finally {
    submitting.value = false
  }
}

async function handleDeleteLink(link: any) {
  if (!confirm(locale.value === 'ar' ? 'حذف هذا الرابط؟' : 'Delete link?')) return
  try {
    await api.delete(`/projects/${projectId}/links/${link.id}`)
    if (project.value && project.value.links) {
      project.value.links = project.value.links.filter((l: any) => l.id !== link.id)
    }
  } catch (err) {
    console.error('Failed to delete link', err)
  }
}

// Create Milestone
async function handleCreateMilestone() {
  if (!milestoneForm.value.title_ar && !milestoneForm.value.title_en) return
  submitting.value = true
  try {
    await api.post(`/projects/${projectId}/milestones`, milestoneForm.value)
    showCreateMilestoneModal.value = false
    const pRes = await api.get(`/projects/${projectId}`)
    project.value = pRes.project || pRes.data?.project || project.value
  } catch (err) {
    console.error('Failed to add milestone', err)
  } finally {
    submitting.value = false
  }
}

function copyToClipboard(text: string, fieldKey: string) {
  navigator.clipboard.writeText(text)
  copiedField.value = fieldKey
  setTimeout(() => {
    copiedField.value = null
  }, 2000)
}

function togglePassword(noteId: number) {
  visiblePasswords.value[noteId] = !visiblePasswords.value[noteId]
}

// Computed Filtered Tasks
const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    if (taskSearchQuery.value) {
      const q = taskSearchQuery.value.toLowerCase()
      const title = (task.title_ar || task.title_en || '').toLowerCase()
      const code = (task.code || '').toLowerCase()
      if (!title.includes(q) && !code.includes(q)) return false
    }

    if (taskFilter.value === 'mine') {
      return task.assignee_id === auth.user.value?.id
    }
    if (taskFilter.value === 'unassigned') {
      return !task.assignee_id && task.status !== 'done' && task.status !== 'cancelled'
    }
    if (taskFilter.value === 'in_progress') {
      return task.status === 'in_progress'
    }
    if (taskFilter.value === 'done') {
      return task.status === 'done'
    }
    return true
  })
})

const kanbanColumns = [
  { id: 'todo', label_ar: 'قيد الانتظار (To Do)', label_en: 'To Do', color: 'border-slate-300 dark:border-slate-700' },
  { id: 'in_progress', label_ar: 'قيد التنفيذ (In Progress)', label_en: 'In Progress', color: 'border-blue-400' },
  { id: 'in_review', label_ar: 'قيد المراجعة (In Review)', label_en: 'In Review', color: 'border-amber-400' },
  { id: 'done', label_ar: 'مكتملة (Done)', label_en: 'Done', color: 'border-emerald-500' }
]

function getTasksByStatus(status: string) {
  return filteredTasks.value.filter(t => t.status === status)
}

onMounted(fetchProjectData)
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
      <div class="flex items-center gap-2 text-slate-500">
        <button
          type="button"
          class="hover:text-primary-600 dark:hover:text-primary-400 font-medium flex items-center gap-1 transition-colors"
          @click="router.push('/projects')"
        >
          <component :is="isRtl ? ArrowRight : ArrowLeft" class="w-3.5 h-3.5" />
          <span>{{ t('projects.title') }}</span>
        </button>
        <span>/</span>
        <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">
          {{ project?.code || '...' }}
        </span>
      </div>

      <div class="flex items-center gap-2">
        <UiButton
          v-if="!auth.isGuest.value"
          variant="outline"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateNoteModal = true; modalError = ''"
        >
          <Key class="w-3.5 h-3.5 text-primary-500" />
          <span>{{ t('projects.add_card') }}</span>
        </UiButton>
        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateTaskModal = true; modalError = ''"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ t('tasks.create_task') }}</span>
        </UiButton>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-20 text-center text-slate-400 flex flex-col items-center justify-center">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"></div>
      <p class="text-sm">{{ t('app.loading') }}</p>
    </div>

    <!-- Project Details Card (Hero) -->
    <UiCard v-else-if="project" class="p-5 sm:p-6 bg-gradient-to-br from-white via-white to-slate-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm">
      <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
        <!-- Project Title, Badges & Desc -->
        <div class="space-y-3 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
              {{ project.code }}
            </span>
            <UiBadge :variant="project.status === 'active' ? 'success' : project.status === 'completed' ? 'neutral' : 'warning'">
              {{ t(`status.${project.status}`) }}
            </UiBadge>
            <UiBadge :variant="project.priority === 'urgent' || project.priority === 'high' ? 'danger' : 'info'">
              {{ t(`priority.${project.priority}`) }}
            </UiBadge>
            <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
              {{ project.type === 'client_delivery' ? (locale === 'ar' ? 'تسليم عميل' : 'Client Delivery') : project.type === 'product_rd' ? 'R&D' : (locale === 'ar' ? 'تشغيل داخلي' : 'Internal Ops') }}
            </span>
          </div>

          <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-tight">
            {{ locale === 'ar' ? (project.name_ar || project.name_en) : (project.name_en || project.name_ar) }}
          </h1>

          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-3xl leading-relaxed whitespace-pre-line">
            {{ locale === 'ar' ? (project.description_ar || project.description_en) : (project.description_en || project.description_ar) || '—' }}
          </p>

          <!-- Associated Badges (Client & Product) -->
          <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
            <div v-if="project.client" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60">
              <Building2 class="w-3.5 h-3.5 text-blue-600" />
              <span class="font-semibold">{{ locale === 'ar' ? (project.client.name_ar || project.client.name_en) : (project.client.name_en || project.client.name_ar) }}</span>
            </div>
            <div v-if="project.product" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-900/60">
              <Boxes class="w-3.5 h-3.5 text-teal-600" />
              <span class="font-semibold">{{ locale === 'ar' ? (project.product.name_ar || project.product.name_en) : (project.product.name_en || project.product.name_ar) }}</span>
            </div>
            <div class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
              <Calendar class="w-3.5 h-3.5" />
              <span>{{ t('projects.due_date') }}: {{ project.due_date || '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Project Progress & People Box -->
        <div class="w-full lg:w-72 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-4 shrink-0">
          <!-- Deliverables Progress Bar -->
          <div>
            <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              <span class="flex items-center gap-1.5">
                <CheckSquare class="w-4 h-4 text-primary-500" />
                {{ t('projects.progress') }}
              </span>
              <span>{{ project.progress_percentage || 0 }}%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
              <div
                class="bg-gradient-to-r from-primary-600 to-teal-500 h-full rounded-full transition-all duration-300"
                :style="{ width: `${project.progress_percentage || 0}%` }"
              ></div>
            </div>
            <div class="text-[11px] text-slate-400 mt-1 flex justify-between">
              <span>{{ tasks.filter(t => t.status === 'done').length }} / {{ tasks.length }} {{ locale === 'ar' ? 'مهمة منجزة' : 'tasks done' }}</span>
              <span>{{ project.milestones?.length || 0 }} {{ locale === 'ar' ? 'محطات' : 'milestones' }}</span>
            </div>
          </div>

          <!-- Project Lead & Members -->
          <div class="pt-3 border-t border-slate-200 dark:border-slate-700/60 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-500">{{ t('projects.lead') }}:</span>
              <span class="font-medium text-slate-800 dark:text-slate-200">{{ project.lead?.name || '—' }}</span>
            </div>
            <div v-if="project.members && project.members.length > 0" class="flex items-center justify-between text-xs">
              <span class="text-slate-500">{{ t('projects.members') }}:</span>
              <div class="flex items-center -space-x-1.5" :class="isRtl ? 'space-x-reverse' : ''">
                <div
                  v-for="m in project.members.slice(0, 5)"
                  :key="m.id"
                  class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-[10px] ring-2 ring-white dark:ring-slate-900"
                  :title="m.name"
                >
                  {{ m.name.charAt(0) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </UiCard>

    <template v-if="project">
      <!-- Workstation Tabs -->
      <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-2 sm:gap-6 text-xs sm:text-sm font-semibold overflow-x-auto pb-1">
      <!-- Tab 1: Tasks & Deliverables -->
      <button
        type="button"
        class="pb-3 px-2 border-b-2 flex items-center gap-1.5 transition-colors whitespace-nowrap"
        :class="activeTab === 'tasks' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'tasks'"
      >
        <CheckSquare class="w-4 h-4" />
        <span>{{ t('projects.tab_tasks') }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ tasks.length }}
        </span>
      </button>

      <!-- Tab 2: Info Cards & Vault -->
      <button
        type="button"
        class="pb-3 px-2 border-b-2 flex items-center gap-1.5 transition-colors whitespace-nowrap"
        :class="activeTab === 'notes' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'notes'"
      >
        <Key class="w-4 h-4" />
        <span>{{ t('projects.tab_notes') }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ project?.notes?.length || 0 }}
        </span>
      </button>

      <!-- Tab 3: Reports & Suggestions -->
      <button
        type="button"
        class="pb-3 px-2 border-b-2 flex items-center gap-1.5 transition-colors whitespace-nowrap"
        :class="activeTab === 'reports' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'reports'"
      >
        <FileCheck class="w-4 h-4" />
        <span>{{ t('projects.tab_reports') }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ project?.reports?.length || 0 }}
        </span>
      </button>

      <!-- Tab 4: References & Links -->
      <button
        type="button"
        class="pb-3 px-2 border-b-2 flex items-center gap-1.5 transition-colors whitespace-nowrap"
        :class="activeTab === 'links' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'links'"
      >
        <LinkIcon class="w-4 h-4" />
        <span>{{ t('projects.tab_links') }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ project?.links?.length || 0 }}
        </span>
      </button>

      <!-- Tab 5: Milestones -->
      <button
        type="button"
        class="pb-3 px-2 border-b-2 flex items-center gap-1.5 transition-colors whitespace-nowrap"
        :class="activeTab === 'milestones' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'milestones'"
      >
        <Flag class="w-4 h-4" />
        <span>{{ t('projects.tab_milestones') }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ project?.milestones?.length || 0 }}
        </span>
      </button>
    </div>

    <!-- TAB 1: TASKS & TO-DO (Project Workstream) -->
    <div v-if="activeTab === 'tasks'" class="space-y-4">
      <!-- Task Filter Toolbar -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <!-- Search & Filter buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative w-full sm:w-60">
            <Search class="w-3.5 h-3.5 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-2.5' : 'left-2.5'" />
            <input
              v-model="taskSearchQuery"
              type="text"
              :placeholder="locale === 'ar' ? 'بحث في مهام المشروع...' : 'Search project tasks...'"
              class="w-full text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
              :class="isRtl ? 'pr-8 pl-3' : 'pl-8 pr-3'"
            />
          </div>

          <div class="flex items-center gap-1 text-xs">
            <button
              type="button"
              class="px-2.5 py-1 rounded-lg transition-colors"
              :class="taskFilter === 'all' ? 'bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
              @click="taskFilter = 'all'"
            >
              {{ locale === 'ar' ? 'الكل' : 'All' }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-lg transition-colors"
              :class="taskFilter === 'unassigned' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
              @click="taskFilter = 'unassigned'"
            >
              {{ t('projects.unassigned') }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-lg transition-colors"
              :class="taskFilter === 'mine' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
              @click="taskFilter = 'mine'"
            >
              {{ locale === 'ar' ? 'مهامي' : 'My Tasks' }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-lg transition-colors"
              :class="taskFilter === 'done' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-semibold' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
              @click="taskFilter = 'done'"
            >
              {{ locale === 'ar' ? 'المكتملة' : 'Done' }}
            </button>
          </div>
        </div>

        <!-- View mode & Add Task button -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
          <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700">
            <button
              type="button"
              class="p-1 rounded transition-colors"
              :class="tasksViewMode === 'list' ? 'bg-white dark:bg-slate-700 text-primary-600 shadow-xs' : 'text-slate-400'"
              @click="tasksViewMode = 'list'"
            >
              <ListIcon class="w-3.5 h-3.5" />
            </button>
            <button
              type="button"
              class="p-1 rounded transition-colors"
              :class="tasksViewMode === 'kanban' ? 'bg-white dark:bg-slate-700 text-primary-600 shadow-xs' : 'text-slate-400'"
              @click="tasksViewMode = 'kanban'"
            >
              <LayoutGrid class="w-3.5 h-3.5" />
            </button>
          </div>

          <UiButton
            v-if="!auth.isGuest.value"
            variant="primary"
            size="sm"
            class="h-7 text-xs flex items-center gap-1"
            @click="showCreateTaskModal = true; modalError = ''"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>{{ t('tasks.create_task') }}</span>
          </UiButton>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredTasks.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
        <CheckSquare class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
          {{ locale === 'ar' ? 'لا توجد مهام مطابقة للمشروع' : 'No project tasks found' }}
        </h3>
        <p class="text-xs text-slate-500 mb-4">
          {{ locale === 'ar' ? 'قم بإضافة أول مهمة أو مرحلة عمل لهذا المشروع.' : 'Get started by creating a task for this project.' }}
        </p>
        <UiButton v-if="!auth.isGuest.value" variant="primary" size="sm" @click="showCreateTaskModal = true">
          <Plus class="w-3.5 h-3.5 mr-1 inline" />
          {{ t('tasks.create_task') }}
        </UiButton>
      </div>

      <!-- LIST VIEW -->
      <div v-else-if="tasksViewMode === 'list'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <table class="w-full text-left text-xs" :class="isRtl ? 'text-right' : 'text-left'">
          <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-medium text-slate-500 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-3 w-20">{{ t('tasks.code') }}</th>
              <th class="py-3 px-3">{{ t('tasks.task_title') }}</th>
              <th class="py-3 px-3 w-28">{{ t('tasks.status') }}</th>
              <th class="py-3 px-3 w-24">{{ t('tasks.priority') }}</th>
              <th class="py-3 px-3 w-48">{{ locale === 'ar' ? 'المسؤول / من يعمل عليها' : 'Assignee / Taken By' }}</th>
              <th class="py-3 px-3 w-28">{{ t('tasks.due_date') }}</th>
              <th class="py-3 px-3 w-28 text-center">{{ locale === 'ar' ? 'إجراء الاستلام' : 'Claim Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="task in filteredTasks"
              :key="task.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-3 font-mono font-semibold text-slate-600 dark:text-slate-400">
                {{ task.code }}
              </td>
              <td class="py-3 px-3 font-medium text-slate-900 dark:text-white">
                <div class="line-clamp-1 font-semibold">
                  {{ locale === 'ar' ? (task.title_ar || task.title_en) : (task.title_en || task.title_ar) }}
                </div>
                <div v-if="task.description_ar || task.description_en" class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                  {{ locale === 'ar' ? task.description_ar : task.description_en }}
                </div>
              </td>
              <td class="py-3 px-3">
                <UiBadge :variant="task.status === 'done' ? 'success' : task.status === 'in_progress' ? 'info' : task.status === 'in_review' ? 'warning' : 'neutral'">
                  {{ t(`status.${task.status}`) }}
                </UiBadge>
              </td>
              <td class="py-3 px-3">
                <UiBadge :variant="task.priority === 'urgent' || task.priority === 'high' ? 'danger' : 'neutral'">
                  {{ t(`priority.${task.priority}`) }}
                </UiBadge>
              </td>
              <td class="py-3 px-3">
                <!-- Assignee / Taken Status Display -->
                <div v-if="task.assignee" class="flex items-center gap-1.5">
                  <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-[10px] shrink-0">
                    {{ task.assignee.name.charAt(0) }}
                  </div>
                  <div class="truncate">
                    <div class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ task.assignee.name }}</div>
                    <div v-if="task.is_taken" class="text-[10px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                      <Clock class="w-2.5 h-2.5" />
                      <span>{{ t('projects.taken_by') }}</span>
                    </div>
                  </div>
                </div>
                <div v-else class="text-slate-400 italic flex items-center gap-1 text-[11px]">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                  <span>{{ t('projects.unassigned') }}</span>
                </div>
              </td>
              <td class="py-3 px-3 text-slate-500 text-[11px]">
                {{ task.due_date || '—' }}
              </td>
              <td class="py-3 px-3 text-center">
                <!-- Claim / Release Actions -->
                <button
                  v-if="!task.assignee_id && task.status !== 'done'"
                  type="button"
                  class="px-2.5 py-1 rounded bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-semibold border border-emerald-200 dark:border-emerald-800 transition-colors"
                  @click="handleClaimTask(task)"
                >
                  {{ t('projects.claim_task') }}
                </button>
                <button
                  v-else-if="task.assignee_id === auth.user.value?.id && task.status !== 'done'"
                  type="button"
                  class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium text-[11px] transition-colors"
                  @click="handleReleaseTask(task)"
                >
                  {{ t('projects.release_task') }}
                </button>
                <span v-else class="text-slate-400 text-[11px]">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- KANBAN BOARD -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 overflow-x-auto pb-4">
        <div
          v-for="col in kanbanColumns"
          :key="col.id"
          class="bg-slate-50/70 dark:bg-slate-900/60 p-3 rounded-xl border flex flex-col min-w-[240px]"
          :class="col.color"
        >
          <!-- Column Header -->
          <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200">
            <span>{{ locale === 'ar' ? col.label_ar : col.label_en }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
              {{ getTasksByStatus(col.id).length }}
            </span>
          </div>

          <!-- Cards in column -->
          <div class="space-y-3 flex-1 overflow-y-auto max-h-[550px] pr-1">
            <div
              v-if="getTasksByStatus(col.id).length === 0"
              class="py-8 text-center text-[11px] text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-lg"
            >
              {{ locale === 'ar' ? 'لا توجد مهام' : 'No tasks' }}
            </div>

            <UiCard
              v-for="task in getTasksByStatus(col.id)"
              :key="task.id"
              class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between gap-1 mb-1.5 text-[11px]">
                <span class="font-mono font-semibold text-slate-500">{{ task.code }}</span>
                <UiBadge :variant="task.priority === 'urgent' || task.priority === 'high' ? 'danger' : 'neutral'">
                  {{ t(`priority.${task.priority}`) }}
                </UiBadge>
              </div>

              <h4 class="text-xs font-bold text-slate-900 dark:text-white leading-snug mb-2">
                {{ locale === 'ar' ? (task.title_ar || task.title_en) : (task.title_en || task.title_ar) }}
              </h4>

              <!-- Claim / Assignee Info in Card -->
              <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                <div v-if="task.assignee" class="flex items-center gap-1.5">
                  <div class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-[9px]">
                    {{ task.assignee.name.charAt(0) }}
                  </div>
                  <span class="text-slate-600 dark:text-slate-300 truncate max-w-[85px]">{{ task.assignee.name }}</span>
                </div>
                <button
                  v-else-if="task.status !== 'done'"
                  type="button"
                  class="text-[10px] px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold border border-emerald-200 dark:border-emerald-800"
                  @click="handleClaimTask(task)"
                >
                  {{ t('projects.claim_task') }}
                </button>
                <span v-else class="text-slate-400 text-[10px]">—</span>

                <span v-if="task.due_date" class="text-[10px] text-slate-400">
                  {{ task.due_date }}
                </span>
              </div>
            </UiCard>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: INFO CARDS & ACCESS VAULT (Notes, Credentials, Plans, Dashboards) -->
    <div v-else-if="activeTab === 'notes'" class="space-y-4">
      <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <Key class="w-4 h-4 text-primary-600" />
            {{ locale === 'ar' ? 'خزينة البيانات والملاحظات وبطاقات الوصول والمعلومات' : 'Project Info Cards, Plans & Access Vault' }}
          </h3>
          <p class="text-[11px] text-slate-400 mt-0.5">
            {{ locale === 'ar' ? 'حفظ الحسابات، كلمات المرور، روابط لوحات المراقبة، وخطط العمل الخاصة بالمشروع بشكل آمن ومنظم.' : 'Securely organize logins, server credentials, dashboard consoles, and sprint plans.' }}
          </p>
        </div>

        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateNoteModal = true; modalError = ''"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ t('projects.add_card') }}</span>
        </UiButton>
      </div>

      <!-- Empty state -->
      <div v-if="!project?.notes || project.notes.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
        <Key class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
          {{ locale === 'ar' ? 'لا توجد بطاقات معلومات مسجلة لهذا المشروع' : 'No info cards or credentials recorded' }}
        </h4>
        <p class="text-xs text-slate-500 mb-4">
          {{ locale === 'ar' ? 'سجّل بيانات الدخول للوحة التحكم، السيرفرات، خطط العمل، أو الروابط الهامة.' : 'Add login credentials, server access, dashboard URLs, or sprint notes.' }}
        </p>
        <UiButton v-if="!auth.isGuest.value" variant="primary" size="sm" @click="showCreateNoteModal = true">
          <Plus class="w-3.5 h-3.5 mr-1 inline" />
          {{ t('projects.add_card') }}
        </UiButton>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <UiCard
          v-for="note in (project?.notes || [])"
          :key="note.id"
          class="p-4 flex flex-col justify-between border-t-4 transition-shadow hover:shadow-md"
          :class="note.color === 'emerald' ? 'border-t-emerald-500' : note.color === 'blue' ? 'border-t-blue-500' : note.color === 'purple' ? 'border-t-purple-500' : 'border-t-slate-400'"
        >
          <div>
            <!-- Card Header -->
            <div class="flex items-start justify-between gap-2 mb-2">
              <div class="flex items-center gap-1.5">
                <span
                  class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                  :class="note.type === 'credential' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : note.type === 'plan' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300' : note.type === 'dashboard' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600'"
                >
                  {{ note.type === 'credential' ? (locale === 'ar' ? 'بيانات دخول' : 'Credential') : note.type === 'plan' ? (locale === 'ar' ? 'خطة عمل' : 'Plan') : note.type === 'dashboard' ? (locale === 'ar' ? 'لوحة تحكم' : 'Dashboard') : (locale === 'ar' ? 'ملاحظة' : 'Note') }}
                </span>
                <span v-if="note.environment" class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono">
                  {{ note.environment }}
                </span>
              </div>

              <!-- Pin toggle & Delete -->
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-amber-500 transition-colors"
                  :title="note.is_pinned ? (locale === 'ar' ? 'إلغاء التثبيت' : 'Unpin') : (locale === 'ar' ? 'تثبيت في الأعلى' : 'Pin to top')"
                  @click="togglePinNote(note)"
                >
                  <Pin class="w-3.5 h-3.5" :class="note.is_pinned ? 'fill-amber-500 text-amber-500' : ''" />
                </button>
                <button
                  v-if="!auth.isGuest.value"
                  type="button"
                  class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-rose-500 transition-colors"
                  @click="handleDeleteNote(note)"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- Title -->
            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
              {{ note.title }}
            </h4>

            <!-- Credential Fields (If type === 'credential') -->
            <div v-if="note.type === 'credential' || note.username || note.password" class="space-y-2 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 text-xs mb-3 font-mono">
              <div v-if="note.username" class="flex items-center justify-between">
                <span class="text-slate-500 text-[10px] uppercase font-bold">{{ locale === 'ar' ? 'المستخدم' : 'User' }}:</span>
                <div class="flex items-center gap-1 font-semibold text-slate-800 dark:text-slate-200">
                  <span>{{ note.username }}</span>
                  <button
                    type="button"
                    class="p-0.5 text-slate-400 hover:text-primary-600"
                    :title="locale === 'ar' ? 'نسخ' : 'Copy'"
                    @click="copyToClipboard(note.username, `user-${note.id}`)"
                  >
                    <Check v-if="copiedField === `user-${note.id}`" class="w-3 h-3 text-emerald-500" />
                    <Copy v-else class="w-3 h-3" />
                  </button>
                </div>
              </div>

              <div v-if="note.password" class="flex items-center justify-between">
                <span class="text-slate-500 text-[10px] uppercase font-bold">{{ locale === 'ar' ? 'المرور' : 'Pass' }}:</span>
                <div class="flex items-center gap-1">
                  <span class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ visiblePasswords[note.id] ? note.password : '••••••••••••' }}
                  </span>
                  <button
                    type="button"
                    class="p-0.5 text-slate-400 hover:text-primary-600"
                    @click="togglePassword(note.id)"
                  >
                    <EyeOff v-if="visiblePasswords[note.id]" class="w-3 h-3" />
                    <Eye v-else class="w-3 h-3" />
                  </button>
                  <button
                    type="button"
                    class="p-0.5 text-slate-400 hover:text-primary-600"
                    :title="locale === 'ar' ? 'نسخ' : 'Copy'"
                    @click="copyToClipboard(note.password, `pass-${note.id}`)"
                  >
                    <Check v-if="copiedField === `pass-${note.id}`" class="w-3 h-3 text-emerald-500" />
                    <Copy v-else class="w-3 h-3" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Content / Notes -->
            <p v-if="note.content" class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line leading-relaxed mb-3">
              {{ note.content }}
            </p>

            <!-- URL / External Dashboard Link -->
            <div v-if="note.url" class="mb-2">
              <a
                :href="note.url"
                target="_blank"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-teal-400 hover:underline bg-primary-50 dark:bg-primary-950/40 px-2.5 py-1 rounded-md"
              >
                <ExternalLink class="w-3 h-3" />
                <span>{{ locale === 'ar' ? 'فتح لوحة التحكم / الرابط' : 'Open Console URL' }}</span>
              </a>
            </div>
          </div>

          <!-- Footer Author & Date -->
          <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[10px] text-slate-400">
            <span>{{ note.author?.name || 'Team' }}</span>
            <span>{{ note.created_at ? new Date(note.created_at).toLocaleDateString() : '' }}</span>
          </div>
        </UiCard>
      </div>
    </div>

    <!-- TAB 3: REPORTS & SUGGESTIONS -->
    <div v-else-if="activeTab === 'reports'" class="space-y-4">
      <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <FileCheck class="w-4 h-4 text-primary-600" />
            {{ locale === 'ar' ? 'تقارير الإنجاز، المقترحات الفنية، ورصد المعوقات' : 'Project Reports, Suggestions & Risk Logs' }}
          </h3>
          <p class="text-[11px] text-slate-400 mt-0.5">
            {{ locale === 'ar' ? 'توثيق تقارير السبرنت الدورية ومقترحات التطوير مع مراجعتها واعتمادها.' : 'Document sprint updates, technical suggestions, and manage team feedback.' }}
          </p>
        </div>

        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateReportModal = true; modalError = ''"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ t('projects.add_report') }}</span>
        </UiButton>
      </div>

      <!-- Empty state -->
      <div v-if="!project?.reports || project.reports.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
        <FileCheck class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
          {{ locale === 'ar' ? 'لا توجد تقارير أو مقترحات مسجلة حالياً' : 'No reports or suggestions logged yet' }}
        </h4>
        <p class="text-xs text-slate-500 mb-4">
          {{ locale === 'ar' ? 'شارك تقرير إنجاز المرحلة أو مقترحاً لتحسين سير عمل المشروع.' : 'Post sprint updates, progress summaries, or architecture suggestions.' }}
        </p>
        <UiButton v-if="!auth.isGuest.value" variant="primary" size="sm" @click="showCreateReportModal = true">
          <Plus class="w-3.5 h-3.5 mr-1 inline" />
          {{ t('projects.add_report') }}
        </UiButton>
      </div>

      <!-- Reports List -->
      <div v-else class="space-y-3">
        <UiCard
          v-for="rep in (project?.reports || [])"
          :key="rep.id"
          class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow"
        >
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-2">
            <div class="flex items-center gap-2">
              <UiBadge :variant="rep.type === 'progress_report' ? 'success' : rep.type === 'suggestion' ? 'info' : rep.type === 'risk' ? 'danger' : 'purple'">
                {{ rep.type === 'progress_report' ? (locale === 'ar' ? 'تقرير تقدم' : 'Progress Report') : rep.type === 'suggestion' ? (locale === 'ar' ? 'مقترح تطوير' : 'Suggestion') : rep.type === 'risk' ? (locale === 'ar' ? 'معوق / خطر' : 'Risk & Blocker') : (locale === 'ar' ? 'مراجعة فنية' : 'Review') }}
              </UiBadge>

              <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                {{ rep.title }}
              </h4>
            </div>

            <!-- Status switcher for managers/leads -->
            <div class="flex items-center gap-2">
              <select
                v-if="auth.isManager.value || auth.isAdmin.value"
                :value="rep.status"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-slate-700 dark:text-slate-300 focus:outline-none"
                @change="handleUpdateReportStatus(rep, ($event.target as HTMLSelectElement).value)"
              >
                <option value="open">{{ locale === 'ar' ? 'مفتوح (Open)' : 'Open' }}</option>
                <option value="reviewed">{{ locale === 'ar' ? 'تمت المراجعة (Reviewed)' : 'Reviewed' }}</option>
                <option value="approved">{{ locale === 'ar' ? 'تم الاعتماد (Approved)' : 'Approved' }}</option>
                <option value="resolved">{{ locale === 'ar' ? 'تم الإنجاز (Resolved)' : 'Resolved' }}</option>
              </select>
              <UiBadge v-else :variant="rep.status === 'approved' || rep.status === 'resolved' ? 'success' : 'neutral'">
                {{ rep.status }}
              </UiBadge>

              <button
                v-if="!auth.isGuest.value"
                type="button"
                class="p-1 rounded text-slate-400 hover:text-rose-500 transition-colors"
                @click="handleDeleteReport(rep)"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed mb-3">
            {{ rep.content }}
          </p>

          <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
            <span class="flex items-center gap-1.5">
              <UserIcon class="w-3.5 h-3.5" />
              {{ rep.author?.name || 'Novixa Core' }}
            </span>
            <span class="flex items-center gap-1.5">
              <Clock class="w-3.5 h-3.5" />
              {{ rep.created_at ? new Date(rep.created_at).toLocaleDateString() : '' }}
            </span>
          </div>
        </UiCard>
      </div>
    </div>

    <!-- TAB 4: REFERENCES & LINKS -->
    <div v-else-if="activeTab === 'links'" class="space-y-4">
      <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <LinkIcon class="w-4 h-4 text-primary-600" />
            {{ locale === 'ar' ? 'المراجع والمصادر الخارجية وتصاميم ومستودعات المشروع' : 'Project References, Repositories & External Resources' }}
          </h3>
          <p class="text-[11px] text-slate-400 mt-0.5">
            {{ locale === 'ar' ? 'روابط مستودعات GitHub، تصاميم Figma، لوحات المراقبة، ومجلدات الملفات.' : 'Quick links to design boards, GitHub repos, external consoles, and specs.' }}
          </p>
        </div>

        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateLinkModal = true; modalError = ''"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ t('projects.add_link') }}</span>
        </UiButton>
      </div>

      <!-- Empty state -->
      <div v-if="!project?.links || project.links.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
        <LinkIcon class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
          {{ locale === 'ar' ? 'لا توجد مراجع أو روابط مسجلة' : 'No reference links logged' }}
        </h4>
        <p class="text-xs text-slate-500 mb-4">
          {{ locale === 'ar' ? 'أضف رابط مستودع الكود أو ملف تصميم Figma أو لوحة مراقبة الخادم.' : 'Add your GitHub repo, Figma board, or external dashboard links.' }}
        </p>
        <UiButton v-if="!auth.isGuest.value" variant="primary" size="sm" @click="showCreateLinkModal = true">
          <Plus class="w-3.5 h-3.5 mr-1 inline" />
          {{ t('projects.add_link') }}
        </UiButton>
      </div>

      <!-- Links Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <UiCard
          v-for="link in (project?.links || [])"
          :key="link.id"
          class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:shadow-md transition-shadow"
        >
          <div>
            <div class="flex items-center justify-between gap-2 mb-2">
              <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {{ link.category || 'link' }}
              </span>
              <button
                v-if="!auth.isGuest.value"
                type="button"
                class="p-1 rounded text-slate-400 hover:text-rose-500 transition-colors"
                @click="handleDeleteLink(link)"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>

            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1.5 leading-snug">
              {{ link.title }}
            </h4>

            <p v-if="link.note" class="text-xs text-slate-500 dark:text-slate-400 mb-3">
              {{ link.note }}
            </p>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <a
              v-if="link.url"
              :href="link.url"
              target="_blank"
              class="inline-flex items-center gap-1.5 text-primary-600 dark:text-teal-400 font-semibold hover:underline"
            >
              <span>{{ locale === 'ar' ? 'زيارة الرابط الخارجي' : 'Open Link' }}</span>
              <ExternalLink class="w-3 h-3" />
            </a>
            <span class="text-[10px] text-slate-400">{{ link.author?.name || '' }}</span>
          </div>
        </UiCard>
      </div>
    </div>

    <!-- TAB 5: MILESTONES & TIMELINE -->
    <div v-else-if="activeTab === 'milestones'" class="space-y-4">
      <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <Flag class="w-4 h-4 text-primary-600" />
            {{ t('projects.milestones') }}
          </h3>
          <p class="text-[11px] text-slate-400 mt-0.5">
            {{ locale === 'ar' ? 'محطات الإنجاز الكبرى والجدول الزمني لتسليم مخرجات المشروع.' : 'Major delivery milestones and scheduled phase dates.' }}
          </p>
        </div>

        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          size="sm"
          class="h-8 text-xs flex items-center gap-1.5"
          @click="showCreateMilestoneModal = true"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ t('projects.add_milestone') }}</span>
        </UiButton>
      </div>

      <div class="space-y-2">
        <div
          v-for="m in (project?.milestones || [])"
          :key="m.id"
          class="flex items-center justify-between p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
              :class="m.status === 'completed' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
            >
              <CheckSquare class="w-4 h-4" />
            </div>
            <div>
              <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                {{ locale === 'ar' ? (m.title_ar || m.title_en) : (m.title_en || m.title_ar) }}
              </h4>
              <span v-if="m.due_date" class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                <Calendar class="w-3 h-3" />
                {{ m.due_date }}
              </span>
            </div>
          </div>

          <UiBadge :variant="m.status === 'completed' ? 'success' : m.status === 'in_progress' ? 'info' : 'neutral'">
            {{ m.status }}
          </UiBadge>
        </div>
      </div>
    </div>
    </template>

    <!-- MODAL 1: CREATE TASK -->
    <UiModal
      v-model="showCreateTaskModal"
      :title="`${t('tasks.create_task')} — ${project?.code}`"
      max-width="xl"
    >
      <form @submit.prevent="handleCreateTask" class="space-y-4">
        <div v-if="modalError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ modalError }}
        </div>

        <UiInput
          v-model="taskForm.title_ar"
          :label="locale === 'ar' ? 'عنوان المهمة (بالعربية)' : 'Task Title (Arabic)'"
          placeholder="مثال: ربط بوابة الدفع الإلكتروني وتأمين المسارات"
          required
        />

        <UiInput
          v-model="taskForm.title_en"
          :label="locale === 'ar' ? 'عنوان المهمة (بالإنجليزية - اختياري)' : 'Task Title (English)'"
          placeholder="e.g. Integrate payment gateway and secure endpoints"
        />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <UiSelect
            v-model="taskForm.status"
            :label="t('tasks.status')"
            :options="[
              { value: 'todo', label: 'To Do (قيد الانتظار)' },
              { value: 'in_progress', label: 'In Progress (قيد التنفيذ)' },
              { value: 'in_review', label: 'In Review (قيد المراجعة)' },
              { value: 'backlog', label: 'Backlog (صندوق الانتظار)' }
            ]"
          />
          <UiSelect
            v-model="taskForm.priority"
            :label="t('tasks.priority')"
            :options="[
              { value: 'low', label: t('priority.low') },
              { value: 'medium', label: t('priority.medium') },
              { value: 'high', label: t('priority.high') },
              { value: 'urgent', label: t('priority.urgent') }
            ]"
          />
          <UiSelect
            v-model="taskForm.assignee_id"
            :label="t('tasks.assignee')"
            :options="[
              { value: '', label: locale === 'ar' ? 'بدون تعيين (متاحة للاستلام)' : 'Unassigned (Open)' },
              ...usersList.map(u => ({ value: u.id.toString(), label: u.name }))
            ]"
          />
        </div>

        <UiInput
          v-model="taskForm.due_date"
          type="date"
          :label="t('tasks.due_date')"
        />

        <UiTextarea
          v-model="taskForm.description_ar"
          :label="t('tasks.description')"
          :rows="3"
          :placeholder="locale === 'ar' ? 'تفاصيل المهمة، المتطلبات، ومعايير القبول...' : 'Task requirements, details...'"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateTaskModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'إنشاء المهمة' : 'Create Task') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- MODAL 2: CREATE NOTE / INFO CARD / CREDENTIAL -->
    <UiModal
      v-model="showCreateNoteModal"
      :title="locale === 'ar' ? 'إضافة بطاقة معلومات أو بيانات وصول' : 'Add Info Card / Access Vault'"
      max-width="xl"
    >
      <form @submit.prevent="handleCreateNote" class="space-y-4">
        <div v-if="modalError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ modalError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <UiInput
              v-model="noteForm.title"
              :label="locale === 'ar' ? 'عنوان البطاقة' : 'Card Title'"
              placeholder="مثال: بيانات خادم الإنتاج أو خطة الإطلاق"
              required
            />
          </div>
          <UiSelect
            v-model="noteForm.type"
            :label="locale === 'ar' ? 'نوع البطاقة' : 'Card Type'"
            :options="[
              { value: 'credential', label: 'بيانات دخول (Credential)' },
              { value: 'plan', label: 'خطة عمل (Sprint Plan)' },
              { value: 'dashboard', label: 'رابط لوحة تحكم (Dashboard)' },
              { value: 'note', label: 'ملاحظة عامة (Note)' }
            ]"
          />
        </div>

        <!-- Conditional Credential Fields -->
        <div v-if="noteForm.type === 'credential'" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-3">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ locale === 'ar' ? 'بيانات الوصول والحسابات' : 'Login Credentials' }}
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiInput
              v-model="noteForm.username"
              :label="locale === 'ar' ? 'اسم المستخدم أو البريد' : 'Username / Email'"
              placeholder="admin@domain.com"
            />
            <UiInput
              v-model="noteForm.password"
              :label="locale === 'ar' ? 'كلمة المرور' : 'Password / Token'"
              placeholder="••••••••"
            />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiSelect
              v-model="noteForm.environment"
              :label="locale === 'ar' ? 'البيئة التشغيلية' : 'Environment'"
              :options="[
                { value: 'staging', label: 'Staging (تجريبي)' },
                { value: 'production', label: 'Production (إنتاج حي)' },
                { value: 'development', label: 'Development (تطوير)' },
                { value: 'internal', label: 'Internal (داخلي)' }
              ]"
            />
            <UiSelect
              v-model="noteForm.color"
              :label="locale === 'ar' ? 'لون البطاقة' : 'Color Accent'"
              :options="[
                { value: 'emerald', label: 'أخضر (Emerald)' },
                { value: 'blue', label: 'أزرق (Blue)' },
                { value: 'purple', label: 'بنفسجي (Purple)' },
                { value: 'amber', label: 'برتقالي (Amber)' }
              ]"
            />
          </div>
        </div>

        <UiInput
          v-model="noteForm.url"
          :label="locale === 'ar' ? 'الرابط المباشر (لوحة تحكم / صفحة دخول / سيرفر)' : 'Direct URL / Console Link'"
          placeholder="https://console.example.com"
        />

        <UiTextarea
          v-model="noteForm.content"
          :label="locale === 'ar' ? 'المحتوى أو التفاصيل' : 'Details / Notes'"
          :rows="3"
          placeholder="خطوات، ملاحظات، أو تعليمات استخدام..."
        />

        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
          <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
            <input v-model="noteForm.is_pinned" type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
            <span>{{ locale === 'ar' ? 'تثبيت البطاقة في الأعلى' : 'Pin card to top' }}</span>
          </label>

          <div class="flex items-center gap-2">
            <UiButton type="button" variant="outline" @click="showCreateNoteModal = false">
              {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
            </UiButton>
            <UiButton type="submit" variant="primary" :disabled="submitting">
              {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ البطاقة' : 'Save Card') }}
            </UiButton>
          </div>
        </div>
      </form>
    </UiModal>

    <!-- MODAL 3: CREATE REPORT / SUGGESTION -->
    <UiModal
      v-model="showCreateReportModal"
      :title="locale === 'ar' ? 'إضافة تقرير أو مقترح للمشروع' : 'Add Report or Suggestion'"
      max-width="lg"
    >
      <form @submit.prevent="handleCreateReport" class="space-y-4">
        <div v-if="modalError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ modalError }}
        </div>

        <UiInput
          v-model="reportForm.title"
          :label="locale === 'ar' ? 'عنوان التقرير أو الفكرة' : 'Report / Suggestion Title'"
          placeholder="مثال: تقرير تسليم المرحلة الأولى أو مقترح تحسين الأداء"
          required
        />

        <UiSelect
          v-model="reportForm.type"
          :label="locale === 'ar' ? 'التصنيف' : 'Category'"
          :options="[
            { value: 'progress_report', label: 'تقرير تقدم وإنجاز (Progress Report)' },
            { value: 'suggestion', label: 'مقترح تطوير (Suggestion)' },
            { value: 'risk', label: 'رصد خطر أو معوق (Risk / Blocker)' },
            { value: 'review', label: 'مراجعة فنية واعتماد (Review)' }
          ]"
        />

        <UiTextarea
          v-model="reportForm.content"
          :label="locale === 'ar' ? 'تفاصيل التقرير أو المقترح' : 'Report Details'"
          :rows="5"
          required
          placeholder="اكتب خلاصة التقدم، الملاحظات، أو المقترح الهندسي بالتفصيل..."
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateReportModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ ونشر' : 'Submit') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- MODAL 4: CREATE REFERENCE / LINK -->
    <UiModal
      v-model="showCreateLinkModal"
      :title="t('projects.add_link')"
      max-width="md"
    >
      <form @submit.prevent="handleCreateLink" class="space-y-4">
        <div v-if="modalError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ modalError }}
        </div>

        <UiInput
          v-model="linkForm.title"
          :label="locale === 'ar' ? 'اسم المرجع أو الرابط' : 'Resource Title'"
          placeholder="مثال: تصاميم Figma أو مستودع GitHub"
          required
        />

        <UiSelect
          v-model="linkForm.category"
          :label="locale === 'ar' ? 'النوع' : 'Category'"
          :options="[
            { value: 'dashboard', label: 'لوحة تحكم (Dashboard)' },
            { value: 'figma', label: 'تصميم (Figma)' },
            { value: 'repo', label: 'مستودع كود (GitHub/GitLab)' },
            { value: 'docs', label: 'توثيق وملفات (Docs)' },
            { value: 'other', label: 'أخرى (Other)' }
          ]"
        />

        <UiInput
          v-model="linkForm.url"
          type="url"
          :label="locale === 'ar' ? 'الرابط الإلكتروني' : 'URL Link'"
          placeholder="https://..."
          required
        />

        <UiTextarea
          v-model="linkForm.note"
          :label="locale === 'ar' ? 'ملاحظة أو وصف مختصر' : 'Description / Note'"
          :rows="2"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateLinkModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'إضافة الرابط' : 'Add Link') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- MODAL 5: CREATE MILESTONE -->
    <UiModal
      v-model="showCreateMilestoneModal"
      :title="t('projects.add_milestone')"
      max-width="md"
    >
      <form @submit.prevent="handleCreateMilestone" class="space-y-4">
        <UiInput
          v-model="milestoneForm.title_ar"
          :label="locale === 'ar' ? 'عنوان المرحلة الرئيسية (بالعربية)' : 'Milestone Title (Arabic)'"
          placeholder="مثال: إطلاق النسخة التجريبية (Beta)"
          required
        />

        <UiInput
          v-model="milestoneForm.title_en"
          :label="locale === 'ar' ? 'عنوان المرحلة (بالإنجليزية - اختياري)' : 'Milestone Title (English)'"
          placeholder="e.g. Beta Launch & Client Testing"
        />

        <UiInput
          v-model="milestoneForm.due_date"
          type="date"
          :label="t('projects.due_date')"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateMilestoneModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ المرحلة' : 'Save Milestone') }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </div>
</template>
