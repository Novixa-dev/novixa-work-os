<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  BookOpen,
  Plus,
  Search,
  FileText,
  Bookmark,
  Calendar,
  User as UserIcon,
  Layers,
  ChevronRight,
  ChevronLeft,
  Share2,
  Printer
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

const loading = ref(true)
const pages = ref<any[]>([])
const selectedCategory = ref('')
const searchQuery = ref('')
const selectedPage = ref<any>(null)

// Modal
const showCreateModal = ref(false)
const submitting = ref(false)
const formError = ref('')

const pageForm = ref({
  title_ar: '',
  title_en: '',
  slug: '',
  category: 'sop',
  content_ar: '',
  content_en: '',
})

const categories = [
  { id: '', label_ar: 'جميع الوثائق', label_en: 'All Documents' },
  { id: 'sop', label_ar: 'إجراءات تشغيل قياسية (SOP)', label_en: 'Standard Operating Procedures' },
  { id: 'architecture', label_ar: 'قرارات هندسية (ADR)', label_en: 'Architecture Decisions' },
  { id: 'playbook', label_ar: 'أدلة إرشادية (Playbooks)', label_en: 'Operational Playbooks' },
  { id: 'onboarding', label_ar: 'تأهيل الفريق (Onboarding)', label_en: 'Team Onboarding' },
]

async function fetchPages() {
  loading.value = true
  try {
    const params: any = {}
    if (selectedCategory.value) params.category = selectedCategory.value
    if (searchQuery.value) params.search = searchQuery.value

    const res = await api.get('/knowledge', { params })
    pages.value = res.data?.data || []

    if (pages.value.length > 0 && !selectedPage.value) {
      selectedPage.value = pages.value[0]
    }
  } catch (err) {
    console.error('Failed to load knowledge pages', err)
  } finally {
    loading.value = false
  }
}

function selectDoc(page: any) {
  selectedPage.value = page
}

function openCreateModal() {
  pageForm.value = {
    title_ar: '',
    title_en: '',
    slug: `sop-${Math.floor(100 + Math.random() * 900)}`,
    category: 'sop',
    content_ar: '',
    content_en: '',
  }
  formError.value = ''
  showCreateModal.value = true
}

async function handleCreatePage() {
  if (!pageForm.value.title_ar || !pageForm.value.content_ar || !pageForm.value.slug) {
    formError.value = locale.value === 'ar' ? 'العنوان والمحتوى والرمز مطلوبان' : 'Title, Content and Slug are required'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    const res = await api.post('/knowledge', pageForm.value)
    showCreateModal.value = false
    await fetchPages()
    if (res.data?.page) {
      selectedPage.value = res.data.page
    }
  } catch (err: any) {
    formError.value = err.response?.data?.message || 'Failed to create page'
  } finally {
    submitting.value = false
  }
}

onMounted(fetchPages)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
          <BookOpen class="w-7 h-7 text-primary-600 dark:text-primary-400" />
          {{ t('knowledge.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ locale === 'ar' ? 'المستودع المعرفي الداخلي، إجراءات التشغيل القياسية، التوثيق الهندسي، وأدلة العمل' : 'Internal knowledge repository, standard operating procedures, and engineering ADRs.' }}
        </p>
      </div>

      <UiButton
        v-if="!auth.isGuest.value"
        variant="primary"
        @click="openCreateModal"
        class="flex items-center gap-1.5"
      >
        <Plus class="w-4 h-4" />
        {{ t('knowledge.create_page') }}
      </UiButton>
    </div>

    <!-- Category Pills & Search -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
          :class="selectedCategory === cat.id ? 'bg-primary-600 text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
          @click="selectedCategory = cat.id; fetchPages()"
        >
          {{ locale === 'ar' ? cat.label_ar : cat.label_en }}
        </button>
      </div>

      <div class="relative w-full md:w-64">
        <Search class="w-4 h-4 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-3' : 'left-3'" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="locale === 'ar' ? 'بحث في الوثائق...' : 'Search docs...'"
          class="w-full text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
          :class="isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3'"
          @input="fetchPages"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"></div>
      <p class="text-sm">{{ t('app.loading') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="pages.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-8">
      <FileText class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-1">
        {{ locale === 'ar' ? 'لا توجد وثائق متطابقة' : 'No documents found' }}
      </h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">
        {{ locale === 'ar' ? 'ابدأ بتوثيق أول إجراء تشغيل قياسي أو قرار هندسي للمؤسسة.' : 'Start by documenting your first SOP or ADR.' }}
      </p>
      <UiButton v-if="!auth.isGuest.value" variant="primary" @click="openCreateModal">
        <Plus class="w-4 h-4 inline mr-1" />
        {{ t('knowledge.create_page') }}
      </UiButton>
    </div>

    <!-- Master-Detail Layout -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Documents Sidebar List (4 cols) -->
      <div class="lg:col-span-4 space-y-2">
        <div
          v-for="doc in pages"
          :key="doc.id"
          class="p-3.5 rounded-xl border cursor-pointer transition-all"
          :class="selectedPage?.id === doc.id ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-300 dark:border-primary-800 shadow-xs' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
          @click="selectDoc(doc)"
        >
          <div class="flex items-center justify-between text-[11px] mb-1.5">
            <span class="font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
              #{{ doc.slug }}
            </span>
            <UiBadge :variant="doc.category === 'sop' ? 'info' : doc.category === 'architecture' ? 'purple' : 'warning'">
              {{ doc.category.toUpperCase() }}
            </UiBadge>
          </div>

          <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-1 mb-1">
            {{ locale === 'ar' ? (doc.title_ar || doc.title_en) : (doc.title_en || doc.title_ar) }}
          </h3>

          <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-2">
            {{ (locale === 'ar' ? doc.content_ar : doc.content_en) || doc.content_ar }}
          </p>

          <div class="flex items-center justify-between text-[10px] text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800/80">
            <span class="flex items-center gap-1">
              <UserIcon class="w-3 h-3" />
              {{ doc.author?.name || 'Novixa Core' }}
            </span>
            <span class="flex items-center gap-1">
              <Calendar class="w-3 h-3" />
              {{ doc.created_at ? new Date(doc.created_at).toLocaleDateString() : '' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Document Reader Main Pane (8 cols) -->
      <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm min-h-[500px]">
        <div v-if="selectedPage">
          <!-- Doc Header -->
          <div class="pb-5 mb-5 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between gap-2 mb-3">
              <div class="flex items-center gap-2">
                <UiBadge variant="primary">
                  {{ selectedPage.category.toUpperCase() }}
                </UiBadge>
                <span class="text-xs font-mono text-slate-400">ID: {{ selectedPage.slug }}</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-xs text-slate-400 flex items-center gap-1">
                  <UserIcon class="w-3.5 h-3.5" />
                  {{ selectedPage.author?.name || 'Novixa' }}
                </span>
              </div>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2 leading-tight">
              {{ locale === 'ar' ? (selectedPage.title_ar || selectedPage.title_en) : (selectedPage.title_en || selectedPage.title_ar) }}
            </h2>

            <div v-if="locale === 'ar' && selectedPage.title_en" class="text-xs font-medium text-slate-400">
              {{ selectedPage.title_en }}
            </div>
          </div>

          <!-- Doc Content (Markdown-like formatting with pre-wrap) -->
          <div class="prose dark:prose-invert max-w-none text-slate-800 dark:text-slate-200 text-sm leading-relaxed whitespace-pre-wrap font-sans">
            {{ (locale === 'ar' ? selectedPage.content_ar : selectedPage.content_en) || selectedPage.content_ar }}
          </div>
        </div>
      </div>
    </div>

    <!-- Create Page Modal -->
    <UiModal
      v-model="showCreateModal"
      :title="t('knowledge.create_page')"
      max-width="2xl"
    >
      <form @submit.prevent="handleCreatePage" class="space-y-4">
        <div v-if="formError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ formError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <UiInput
              v-model="pageForm.title_ar"
              :label="locale === 'ar' ? 'عنوان الوثيقة (بالعربية)' : 'Document Title (Arabic)'"
              placeholder="مثال: إجراءات نشر وتحديث خوادم الإنتاج"
              required
            />
          </div>
          <UiInput
            v-model="pageForm.slug"
            label="Slug / المعرف الفريد"
            placeholder="deploy-vps-sop"
            required
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="pageForm.title_en"
            :label="locale === 'ar' ? 'عنوان الوثيقة (بالإنجليزية)' : 'Document Title (English)'"
            placeholder="e.g. Production Deployment SOP"
          />
          <UiSelect
            v-model="pageForm.category"
            :label="t('knowledge.category')"
            :options="[
              { value: 'sop', label: 'إجراء تشغيل قياسي (SOP)' },
              { value: 'architecture', label: 'قرار معماري / تقني (ADR)' },
              { value: 'playbook', label: 'دليل تشغيلي (Playbook)' },
              { value: 'onboarding', label: 'دليل تأهيل الفريق (Onboarding)' }
            ]"
          />
        </div>

        <UiTextarea
          v-model="pageForm.content_ar"
          :label="locale === 'ar' ? 'محتوى الوثيقة بالتفصيل (يدعم التنسيق والفقرات)' : 'Content (Arabic)'"
          :rows="8"
          required
          placeholder="اكتب تفاصيل الإجراء، الخطوات، والمسؤوليات..."
        />

        <UiTextarea
          v-model="pageForm.content_en"
          :label="locale === 'ar' ? 'المحتوى (بالإنجليزية - اختياري)' : 'Content (English)'"
          :rows="4"
          placeholder="English content version if available..."
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'نشر الوثيقة' : 'Publish Document') }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </div>
</template>
