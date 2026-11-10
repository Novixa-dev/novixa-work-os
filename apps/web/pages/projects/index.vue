<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  FolderKanban,
  Plus,
  Search,
  Calendar,
  CheckCircle2,
  Clock,
  Layers,
  Archive,
  RotateCcw,
  Flag,
  ArrowRight,
  ArrowLeft,
  LayoutGrid,
  List as ListIcon,
  CheckSquare
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
const router = useRouter()

const loading = ref(true)
const projects = ref<any[]>([])
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedType = ref('')
const viewMode = ref<'grid' | 'table'>('grid')

// Modals
const showCreateModal = ref(false)
const showMilestoneModal = ref(false)
const selectedProjectForMilestone = ref<any>(null)
const submitting = ref(false)
const formError = ref('')

// Form states
const projectForm = ref({
  code: '',
  name_ar: '',
  name_en: '',
  description_ar: '',
  description_en: '',
  type: 'client_delivery',
  status: 'active',
  priority: 'medium',
  lead_id: '',
  product_id: '',
  client_id: '',
  target_end_date: '',
})

const milestoneForm = ref({
  title_ar: '',
  title_en: '',
  due_date: '',
})

// Aux data for selects
const usersList = ref<any[]>([])
const productsList = ref<any[]>([])
const clientsList = ref<any[]>([])

async function fetchProjects() {
  loading.value = true
  try {
    const params: any = {}
    if (selectedStatus.value) params.status = selectedStatus.value
    if (selectedType.value) params.type = selectedType.value
    if (searchQuery.value) params.search = searchQuery.value

    const res = await api.get('/projects', { params })
    projects.value = (Array.isArray(res.data) ? res.data : [])
  } catch (err) {
    console.error('Failed to load projects', err)
  } finally {
    loading.value = false
  }
}

async function fetchAuxData() {
  try {
    const [uRes, pRes, cRes] = await Promise.all([
      api.get('/users'),
      api.get('/products'),
      api.get('/clients')
    ])
    usersList.value = (Array.isArray(uRes.data) ? uRes.data : [])
    productsList.value = (Array.isArray(pRes.data) ? pRes.data : [])
    clientsList.value = (Array.isArray(cRes.data) ? cRes.data : [])
  } catch (err) {
    console.error('Failed to load aux data', err)
  }
}

function openCreateModal() {
  projectForm.value = {
    code: `PRJ-${Math.floor(100 + Math.random() * 900)}`,
    name_ar: '',
    name_en: '',
    description_ar: '',
    description_en: '',
    type: 'client_delivery',
    status: 'active',
    priority: 'medium',
    lead_id: auth.user.value?.id?.toString() || '',
    product_id: '',
    client_id: '',
    target_end_date: '',
  }
  formError.value = ''
  showCreateModal.value = true
}

async function handleCreateProject() {
  if (!projectForm.value.name_ar && !projectForm.value.name_en) {
    formError.value = locale.value === 'ar' ? 'يرجى إدخال اسم المشروع' : 'Project name is required'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    const payload: any = { ...projectForm.value }
    if (!payload.product_id) delete payload.product_id
    if (!payload.client_id) delete payload.client_id
    if (!payload.lead_id) delete payload.lead_id
    if (!payload.target_end_date) delete payload.target_end_date

    await api.post('/projects', payload)
    showCreateModal.value = false
    await fetchProjects()
  } catch (err: any) {
    formError.value = err?.data?.message || 'Failed to save project'
  } finally {
    submitting.value = false
  }
}

function openAddMilestone(project: any) {
  selectedProjectForMilestone.value = project
  milestoneForm.value = {
    title_ar: '',
    title_en: '',
    due_date: '',
  }
  showMilestoneModal.value = true
}

async function handleAddMilestone() {
  if (!milestoneForm.value.title_ar && !milestoneForm.value.title_en) return
  submitting.value = true
  try {
    await api.post(`/projects/${selectedProjectForMilestone.value.id}/milestones`, milestoneForm.value)
    showMilestoneModal.value = false
    await fetchProjects()
  } catch (err) {
    console.error('Failed to add milestone', err)
  } finally {
    submitting.value = false
  }
}

async function toggleArchiveProject(project: any) {
  try {
    await api.patch(`/projects/${project.id}/archive`)
    await fetchProjects()
  } catch (err) {
    console.error('Failed to toggle archive', err)
  }
}

function navigateToTasks(projectId: number) {
  router.push({ path: '/tasks', query: { project_id: projectId.toString() } })
}

const statusOptions = [
  { value: '', label_ar: 'كل الحالات', label_en: 'All Statuses' },
  { value: 'planning', label_ar: 'تخطيط', label_en: 'Planning' },
  { value: 'active', label_ar: 'نشط', label_en: 'Active' },
  { value: 'on_hold', label_ar: 'معلق', label_en: 'On Hold' },
  { value: 'completed', label_ar: 'مكتمل', label_en: 'Completed' },
  { value: 'archived', label_ar: 'مؤرشف', label_en: 'Archived' },
]

const typeOptions = [
  { value: '', label_ar: 'جميع الأنواع', label_en: 'All Types' },
  { value: 'client_delivery', label_ar: 'تسليم عميل', label_en: 'Client Delivery' },
  { value: 'product_rd', label_ar: 'تطوير منتج (R&D)', label_en: 'Product R&D' },
  { value: 'internal', label_ar: 'داخلي', label_en: 'Internal' },
]

const userSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون تعيين' : 'Unassigned' },
  ...usersList.value.map(u => ({ value: u.id.toString(), label: u.name }))
])

const productSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون منتج مرتبط' : 'No Product' },
  ...productsList.value.map(p => ({
    value: p.id.toString(),
    label: locale.value === 'ar' ? (p.name_ar || p.name_en) : (p.name_en || p.name_ar)
  }))
])

const clientSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون عميل مرتبط' : 'No Client' },
  ...clientsList.value.map(c => ({
    value: c.id.toString(),
    label: locale.value === 'ar' ? (c.name_ar || c.name_en) : (c.name_en || c.name_ar)
  }))
])

onMounted(async () => {
  await Promise.all([fetchProjects(), fetchAuxData()])
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
          <FolderKanban class="w-7 h-7 text-primary-600 dark:text-primary-400" />
          {{ t('projects.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ locale === 'ar' ? 'إدارة مشاريع نوفيكسا، مبادرات التسليم، وتتبع الإنجاز والمحطات الرئيسية' : 'Manage Novixa delivery initiatives, product sprints, and track milestones.' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <!-- View mode switcher -->
        <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700">
          <button
            type="button"
            class="p-1.5 rounded-md transition-colors"
            :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 text-primary-600 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
            :title="locale === 'ar' ? 'عرض شبكي' : 'Grid View'"
            @click="viewMode = 'grid'"
          >
            <LayoutGrid class="w-4 h-4" />
          </button>
          <button
            type="button"
            class="p-1.5 rounded-md transition-colors"
            :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-primary-600 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
            :title="locale === 'ar' ? 'عرض جدول' : 'Table View'"
            @click="viewMode = 'table'"
          >
            <ListIcon class="w-4 h-4" />
          </button>
        </div>

        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          class="flex items-center gap-1.5"
          @click="openCreateModal"
        >
          <Plus class="w-4 h-4" />
          {{ t('projects.create_project') }}
        </UiButton>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
      <div class="relative flex-1">
        <Search class="w-4 h-4 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-3' : 'left-3'" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="locale === 'ar' ? 'بحث بالاسم، الرمز...' : 'Search by name, code...'"
          class="w-full text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
          :class="isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3'"
          @input="fetchProjects"
        />
      </div>

      <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
        <!-- Status Filter -->
        <select
          v-model="selectedStatus"
          class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @change="fetchProjects"
        >
          <option v-for="st in statusOptions" :key="st.value" :value="st.value">
            {{ locale === 'ar' ? st.label_ar : st.label_en }}
          </option>
        </select>

        <!-- Type Filter -->
        <select
          v-model="selectedType"
          class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @change="fetchProjects"
        >
          <option v-for="tp in typeOptions" :key="tp.value" :value="tp.value">
            {{ locale === 'ar' ? tp.label_ar : tp.label_en }}
          </option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"></div>
      <p class="text-sm">{{ t('app.loading') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="projects.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
      <FolderKanban class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-1">
        {{ t('projects.empty_projects') }}
      </h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
        {{ locale === 'ar' ? 'ابدأ بإنشاء أول مشروع لنوفيكسا أو تسليم عميل.' : 'Get started by creating your first Novixa delivery or product project.' }}
      </p>
      <UiButton v-if="!auth.isGuest.value" variant="primary" @click="openCreateModal">
        <Plus class="w-4 h-4 inline mr-1" />
        {{ t('projects.create_project') }}
      </UiButton>
    </div>

    <!-- Grid Cards View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <UiCard
        v-for="project in projects"
        :key="project.id"
        class="flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all hover:shadow-md group"
      >
        <div>
          <!-- Card Header -->
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
              {{ project.code }}
            </span>
            <div class="flex items-center gap-1.5">
              <UiBadge :variant="project.status === 'active' ? 'success' : project.status === 'completed' ? 'neutral' : 'warning'">
                {{ t(`status.${project.status}`) }}
              </UiBadge>
              <UiBadge :variant="project.priority === 'urgent' || project.priority === 'high' ? 'danger' : 'info'">
                {{ t(`priority.${project.priority}`) }}
              </UiBadge>
            </div>
          </div>

          <!-- Title & Description -->
          <h3 class="text-base font-semibold text-slate-900 dark:text-white line-clamp-1 mb-1">
            {{ locale === 'ar' ? (project.name_ar || project.name_en) : (project.name_en || project.name_ar) }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3 min-h-[32px]">
            {{ locale === 'ar' ? (project.description_ar || project.description_en) : (project.description_en || project.description_ar) || '—' }}
          </p>

          <!-- Badges for Product / Client -->
          <div class="flex flex-wrap items-center gap-1.5 mb-4 text-xs">
            <span v-if="project.client" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
              🏢 {{ locale === 'ar' ? (project.client.name_ar || project.client.name_en) : (project.client.name_en || project.client.name_ar) }}
            </span>
            <span v-if="project.product" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
              ⚡ {{ locale === 'ar' ? (project.product.name_ar || project.product.name_en) : (project.product.name_en || project.product.name_ar) }}
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[11px]">
              {{ project.type === 'client_delivery' ? (locale === 'ar' ? 'تسليم عميل' : 'Client Delivery') : project.type === 'product_rd' ? 'R&D' : (locale === 'ar' ? 'داخلي' : 'Internal') }}
            </span>
          </div>

          <!-- Progress Bar -->
          <div class="space-y-1.5 mb-4 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-300 font-medium">
              <span class="flex items-center gap-1">
                <CheckSquare class="w-3.5 h-3.5 text-primary-500" />
                {{ t('projects.progress') }}
              </span>
              <span>{{ project.progress_percentage || 0 }}% ({{ project.completed_tasks_count || 0 }}/{{ project.tasks_count || 0 }})</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
              <div
                class="bg-primary-600 h-full rounded-full transition-all duration-300"
                :style="{ width: `${project.progress_percentage || 0}%` }"
              ></div>
            </div>
          </div>

          <!-- Milestones preview -->
          <div v-if="project.milestones && project.milestones.length > 0" class="mb-4">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center justify-between">
              <span>{{ t('projects.milestones') }}</span>
              <span>{{ project.milestones.filter((m: any) => m.status === 'completed').length }}/{{ project.milestones.length }}</span>
            </div>
            <div class="space-y-1">
              <div
                v-for="m in project.milestones.slice(0, 2)"
                :key="m.id"
                class="flex items-center justify-between text-xs p-1.5 rounded bg-slate-50 dark:bg-slate-800/60"
              >
                <div class="flex items-center gap-1.5 truncate">
                  <CheckCircle2 v-if="m.status === 'completed'" class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                  <Clock v-else class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                  <span class="truncate text-slate-700 dark:text-slate-300 text-[11px]">
                    {{ locale === 'ar' ? (m.title_ar || m.title_en) : (m.title_en || m.title_ar) }}
                  </span>
                </div>
                <span v-if="m.due_date" class="text-[10px] text-slate-400 shrink-0">
                  {{ m.due_date }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Footer -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs mt-2">
          <!-- Lead Info -->
          <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">
              {{ project.lead?.name?.charAt(0) || '—' }}
            </div>
            <span class="text-slate-600 dark:text-slate-400 text-xs truncate max-w-[90px]">
              {{ project.lead?.name || t('tasks.assignee') }}
            </span>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
              :title="locale === 'ar' ? 'إضافة محطة رئيسية' : 'Add Milestone'"
              @click="openAddMilestone(project)"
            >
              <Flag class="w-3.5 h-3.5" />
            </button>
            <button
              v-if="!auth.isGuest.value"
              type="button"
              class="p-1 text-slate-400 hover:text-amber-600 transition-colors"
              :title="project.status === 'archived' ? (locale === 'ar' ? 'استعادة' : 'Restore') : (locale === 'ar' ? 'أرشفة' : 'Archive')"
              @click="toggleArchiveProject(project)"
            >
              <RotateCcw v-if="project.status === 'archived'" class="w-3.5 h-3.5" />
              <Archive v-else class="w-3.5 h-3.5" />
            </button>
            <UiButton
              variant="outline"
              size="sm"
              class="text-xs py-1 px-2.5 h-auto flex items-center gap-1"
              @click="navigateToTasks(project.id)"
            >
              <span>{{ locale === 'ar' ? 'المهام' : 'Tasks' }}</span>
              <component :is="isRtl ? ArrowLeft : ArrowRight" class="w-3 h-3" />
            </UiButton>
          </div>
        </div>
      </UiCard>
    </div>

    <!-- Table View -->
    <div v-else class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" :class="isRtl ? 'text-right' : 'text-left'">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase font-medium text-slate-500 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-4">{{ t('projects.code') }}</th>
              <th class="py-3 px-4">{{ t('projects.name') }}</th>
              <th class="py-3 px-4">{{ t('projects.type') }}</th>
              <th class="py-3 px-4">{{ t('projects.status') }}</th>
              <th class="py-3 px-4">{{ t('projects.lead') }}</th>
              <th class="py-3 px-4">{{ t('projects.progress') }}</th>
              <th class="py-3 px-4">{{ t('projects.due_date') }}</th>
              <th class="py-3 px-4 text-center"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="project in projects"
              :key="project.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-mono font-semibold text-xs text-slate-600 dark:text-slate-300">
                {{ project.code }}
              </td>
              <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">
                <div>{{ locale === 'ar' ? (project.name_ar || project.name_en) : (project.name_en || project.name_ar) }}</div>
                <div v-if="project.client" class="text-xs text-slate-400">🏢 {{ locale === 'ar' ? project.client.name_ar : project.client.name_en }}</div>
              </td>
              <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-400">
                {{ project.type === 'client_delivery' ? (locale === 'ar' ? 'تسليم عميل' : 'Client Delivery') : project.type === 'product_rd' ? 'R&D' : (locale === 'ar' ? 'داخلي' : 'Internal') }}
              </td>
              <td class="py-3 px-4">
                <UiBadge :variant="project.status === 'active' ? 'success' : 'neutral'">
                  {{ t(`status.${project.status}`) }}
                </UiBadge>
              </td>
              <td class="py-3 px-4 text-xs text-slate-700 dark:text-slate-300">
                {{ project.lead?.name || '—' }}
              </td>
              <td class="py-3 px-4 text-xs">
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-primary-600 h-full rounded-full" :style="{ width: `${project.progress_percentage || 0}%` }"></div>
                  </div>
                  <span class="text-slate-500 text-[11px]">{{ project.progress_percentage || 0 }}%</span>
                </div>
              </td>
              <td class="py-3 px-4 text-xs text-slate-500">
                {{ project.target_end_date || '—' }}
              </td>
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-end gap-1">
                  <UiButton variant="ghost" size="sm" class="h-7 px-2 text-xs" @click="navigateToTasks(project.id)">
                    {{ locale === 'ar' ? 'المهام' : 'Tasks' }}
                  </UiButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Project Modal -->
    <UiModal
      v-model="showCreateModal"
      :title="t('projects.create_project')"
      max-width="2xl"
    >
      <form class="space-y-4" @submit.prevent="handleCreateProject">
        <div v-if="formError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ formError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <UiInput
            v-model="projectForm.code"
            :label="t('projects.code')"
            placeholder="PRJ-101"
            required
          />
          <div class="sm:col-span-2">
            <UiInput
              v-model="projectForm.name_ar"
              :label="locale === 'ar' ? 'اسم المشروع (بالعربية)' : 'Project Name (Arabic)'"
              placeholder="مثال: ترقية منصة عقار هضبة حضرموت"
              required
            />
          </div>
        </div>

        <UiInput
          v-model="projectForm.name_en"
          :label="locale === 'ar' ? 'اسم المشروع (بالإنجليزية - اختياري)' : 'Project Name (English)'"
          placeholder="e.g. Hadramout Aqar Platform Upgrade"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiSelect
            v-model="projectForm.type"
            :label="t('projects.type')"
            :options="[
              { value: 'client_delivery', label: locale === 'ar' ? 'تسليم عميل' : 'Client Delivery' },
              { value: 'product_rd', label: locale === 'ar' ? 'تطوير منتج (R&D)' : 'Product R&D' },
              { value: 'internal', label: locale === 'ar' ? 'داخلي للمؤسسة' : 'Internal Ops' }
            ]"
          />
          <UiSelect
            v-model="projectForm.priority"
            :label="t('projects.priority')"
            :options="[
              { value: 'low', label: t('priority.low') },
              { value: 'medium', label: t('priority.medium') },
              { value: 'high', label: t('priority.high') },
              { value: 'urgent', label: t('priority.urgent') }
            ]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <UiSelect
            v-model="projectForm.lead_id"
            :label="t('projects.lead')"
            :options="userSelectOptions"
          />
          <UiSelect
            v-model="projectForm.product_id"
            :label="t('products.title')"
            :options="productSelectOptions"
          />
          <UiSelect
            v-model="projectForm.client_id"
            :label="t('crm.clients_tab')"
            :options="clientSelectOptions"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="projectForm.target_end_date"
            type="date"
            :label="t('projects.due_date')"
          />
          <UiSelect
            v-model="projectForm.status"
            :label="t('projects.status')"
            :options="[
              { value: 'planning', label: t('status.planning') },
              { value: 'active', label: t('status.active') },
              { value: 'on_hold', label: t('status.on_hold') }
            ]"
          />
        </div>

        <UiTextarea
          v-model="projectForm.description_ar"
          :label="t('projects.description')"
          :rows="3"
          :placeholder="locale === 'ar' ? 'أهداف ومخرجات المشروع الرئيسية...' : 'Main objectives and deliverables...'"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'إنشاء المشروع' : 'Create Project') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- Add Milestone Modal -->
    <UiModal
      v-model="showMilestoneModal"
      :title="t('projects.add_milestone')"
      max-width="md"
    >
      <form class="space-y-4" @submit.prevent="handleAddMilestone">
        <p class="text-xs text-slate-500">
          {{ locale === 'ar' ? 'إضافة محطة رئيسية لمشروع:' : 'Add milestone to project:' }}
          <strong class="text-slate-800 dark:text-slate-200">
            {{ selectedProjectForMilestone ? (locale === 'ar' ? selectedProjectForMilestone.name_ar : selectedProjectForMilestone.name_en) : '' }}
          </strong>
        </p>

        <UiInput
          v-model="milestoneForm.title_ar"
          :label="locale === 'ar' ? 'عنوان المحطة (بالعربية)' : 'Milestone Title (Arabic)'"
          placeholder="مثال: إطلاق النسخة التجريبية (Beta Launch)"
          required
        />

        <UiInput
          v-model="milestoneForm.title_en"
          :label="locale === 'ar' ? 'عنوان المحطة (بالإنجليزية)' : 'Milestone Title (English)'"
          placeholder="e.g. Beta Release"
        />

        <UiInput
          v-model="milestoneForm.due_date"
          type="date"
          :label="t('projects.due_date')"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showMilestoneModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ المحطة' : 'Save Milestone') }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </div>
</template>
