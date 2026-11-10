<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  Boxes,
  Plus,
  Search,
  ExternalLink,
  Globe,
  Code2,
  BookOpen,
  FolderKanban,
  CheckSquare,
  Sparkles,
  ArrowRight,
  ArrowLeft
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
const products = ref<any[]>([])
const searchQuery = ref('')
const selectedStatus = ref('')

// Modals
const showCreateModal = ref(false)
const showWorkModal = ref(false)
const selectedProduct = ref<any>(null)
const productWork = ref<{ projects: any[], tasks: any[] }>({ projects: [], tasks: [] })
const loadingWork = ref(false)
const submitting = ref(false)
const formError = ref('')

// Product Form
const productForm = ref({
  name_ar: '',
  name_en: '',
  slug: '',
  tagline_ar: '',
  tagline_en: '',
  description_ar: '',
  description_en: '',
  lifecycle_status: 'active',
  owner_id: '',
  repository_url: '',
  website_url: '',
  demo_url: '',
  documentation_url: '',
})

const usersList = ref<any[]>([])

async function fetchProducts() {
  loading.value = true
  try {
    const params: any = {}
    if (selectedStatus.value) params.status = selectedStatus.value
    if (searchQuery.value) params.search = searchQuery.value

    const res = await api.get('/products', { params })
    products.value = (Array.isArray(res.data) ? res.data : [])
  } catch (err) {
    console.error('Failed to load products', err)
  } finally {
    loading.value = false
  }
}

async function fetchUsers() {
  try {
    const res = await api.get('/users')
    usersList.value = (Array.isArray(res.data) ? res.data : [])
  } catch (err) {
    console.error('Failed to load users', err)
  }
}

function openCreateModal() {
  productForm.value = {
    name_ar: '',
    name_en: '',
    slug: '',
    tagline_ar: '',
    tagline_en: '',
    description_ar: '',
    description_en: '',
    lifecycle_status: 'active',
    owner_id: auth.user.value?.id?.toString() || '',
    repository_url: '',
    website_url: '',
    demo_url: '',
    documentation_url: '',
  }
  formError.value = ''
  showCreateModal.value = true
}

async function handleCreateProduct() {
  if (!productForm.value.name_ar || !productForm.value.name_en || !productForm.value.slug) {
    formError.value = locale.value === 'ar' ? 'يرجى ملء الحقول الإلزامية (الاسم والرمز التعريفي)' : 'Please fill required fields (Name & Slug)'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    const payload: any = { ...productForm.value }
    if (!payload.owner_id) delete payload.owner_id
    if (!payload.repository_url) delete payload.repository_url
    if (!payload.website_url) delete payload.website_url
    if (!payload.demo_url) delete payload.demo_url
    if (!payload.documentation_url) delete payload.documentation_url

    await api.post('/products', payload)
    showCreateModal.value = false
    await fetchProducts()
  } catch (err: any) {
    formError.value = err?.data?.message || 'Failed to create product'
  } finally {
    submitting.value = false
  }
}

async function viewLinkedWork(product: any) {
  selectedProduct.value = product
  showWorkModal.value = true
  loadingWork.value = true
  try {
    const res = await api.get(`/products/${product.id}/work`)
    productWork.value = {
      projects: res.projects || [],
      tasks: res.tasks || []
    }
  } catch (err) {
    console.error('Failed to load product work', err)
  } finally {
    loadingWork.value = false
  }
}

function navigateToTasksForProduct(productId: number) {
  router.push({ path: '/tasks', query: { product_id: productId.toString() } })
}

const statusOptions = [
  { value: '', label_ar: 'كل الحالات', label_en: 'All Statuses' },
  { value: 'active', label_ar: 'نشط في السوق', label_en: 'Active' },
  { value: 'in_development', label_ar: 'قيد التطوير', label_en: 'In Development' },
  { value: 'beta', label_ar: 'نسخة تجريبية (بيتا)', label_en: 'Beta' },
  { value: 'ideation', label_ar: 'فكرة وتخطيط', label_en: 'Ideation' },
  { value: 'sunset', label_ar: 'متقاعد (Sunset)', label_en: 'Sunset' },
]

const userSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون مالك' : 'No Owner' },
  ...usersList.value.map(u => ({ value: u.id.toString(), label: u.name }))
])

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchUsers()])
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
          <Boxes class="w-7 h-7 text-primary-600 dark:text-primary-400" />
          {{ t('products.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ t('products.subtitle') }}
        </p>
      </div>

      <UiButton
        v-if="!auth.isGuest.value"
        variant="primary"
        class="flex items-center gap-1.5"
        @click="openCreateModal"
      >
        <Plus class="w-4 h-4" />
        {{ t('products.create_product') }}
      </UiButton>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
      <div class="relative flex-1">
        <Search class="w-4 h-4 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-3' : 'left-3'" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="locale === 'ar' ? 'بحث في المنتجات...' : 'Search products...'"
          class="w-full text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
          :class="isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3'"
          @input="fetchProducts"
        />
      </div>

      <select
        v-model="selectedStatus"
        class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
        @change="fetchProducts"
      >
        <option v-for="st in statusOptions" :key="st.value" :value="st.value">
          {{ locale === 'ar' ? st.label_ar : st.label_en }}
        </option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"></div>
      <p class="text-sm">{{ t('app.loading') }}</p>
    </div>

    <!-- Products Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <UiCard
        v-for="product in products"
        :key="product.id"
        class="flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all hover:shadow-md"
      >
        <div>
          <!-- Card Top Bar -->
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-600 to-teal-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
              {{ (product.name_ar || product.name_en).charAt(0) }}
            </div>
            <div class="flex items-center gap-1.5">
              <UiBadge :variant="product.lifecycle_status === 'active' ? 'success' : product.lifecycle_status === 'beta' ? 'warning' : 'neutral'">
                {{ product.lifecycle_status }}
              </UiBadge>
            </div>
          </div>

          <!-- Titles -->
          <div class="mb-2">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
              {{ locale === 'ar' ? (product.name_ar || product.name_en) : (product.name_en || product.name_ar) }}
            </h3>
            <span class="text-xs font-mono text-slate-400">#{{ product.slug }}</span>
          </div>

          <p class="text-xs text-primary-600 dark:text-teal-400 font-medium mb-2">
            {{ locale === 'ar' ? (product.tagline_ar || product.tagline_en) : (product.tagline_en || product.tagline_ar) }}
          </p>

          <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 mb-4 min-h-[48px]">
            {{ locale === 'ar' ? (product.description_ar || product.description_en) : (product.description_en || product.description_ar) || '—' }}
          </p>

          <!-- External Links -->
          <div class="flex flex-wrap items-center gap-2 mb-4">
            <a
              v-if="product.demo_url"
              :href="product.demo_url"
              target="_blank"
              class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 hover:underline"
            >
              <Sparkles class="w-3 h-3" />
              {{ t('products.demo_url') }}
              <ExternalLink class="w-2.5 h-2.5 opacity-70" />
            </a>
            <a
              v-if="product.website_url"
              :href="product.website_url"
              target="_blank"
              class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:underline"
            >
              <Globe class="w-3 h-3" />
              {{ t('products.website_url') }}
              <ExternalLink class="w-2.5 h-2.5 opacity-70" />
            </a>
            <a
              v-if="product.repository_url"
              :href="product.repository_url"
              target="_blank"
              class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:underline"
            >
              <Code2 class="w-3 h-3" />
              {{ t('products.repo_url') }}
              <ExternalLink class="w-2.5 h-2.5 opacity-70" />
            </a>
            <a
              v-if="product.documentation_url"
              :href="product.documentation_url"
              target="_blank"
              class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:underline"
            >
              <BookOpen class="w-3 h-3" />
              {{ t('products.docs_url') }}
              <ExternalLink class="w-2.5 h-2.5 opacity-70" />
            </a>
          </div>

          <!-- Stats Counters -->
          <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 text-xs mb-3">
            <div class="flex items-center gap-2">
              <FolderKanban class="w-4 h-4 text-primary-500 shrink-0" />
              <div>
                <div class="text-[10px] text-slate-400 uppercase font-medium">{{ t('products.linked_projects') }}</div>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ product.projects_count || 0 }}</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <CheckSquare class="w-4 h-4 text-teal-500 shrink-0" />
              <div>
                <div class="text-[10px] text-slate-400 uppercase font-medium">{{ t('products.linked_tasks') }}</div>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ product.tasks_count || 0 }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs mt-2">
          <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold flex items-center justify-center text-[10px]">
              {{ product.owner?.name?.charAt(0) || '—' }}
            </div>
            <span class="text-slate-600 dark:text-slate-400 text-xs truncate max-w-[100px]">
              {{ product.owner?.name || t('products.owner') }}
            </span>
          </div>

          <div class="flex items-center gap-1.5">
            <UiButton
              variant="outline"
              size="sm"
              class="text-xs py-1 px-2.5 h-auto flex items-center gap-1"
              @click="viewLinkedWork(product)"
            >
              <span>{{ locale === 'ar' ? 'الأعمال والمشاريع' : 'Work & Sprints' }}</span>
              <component :is="isRtl ? ArrowLeft : ArrowRight" class="w-3 h-3" />
            </UiButton>
          </div>
        </div>
      </UiCard>
    </div>

    <!-- Create Product Modal -->
    <UiModal
      v-model="showCreateModal"
      :title="t('products.create_product')"
      max-width="2xl"
    >
      <form class="space-y-4" @submit.prevent="handleCreateProduct">
        <div v-if="formError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ formError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <UiInput
              v-model="productForm.name_ar"
              :label="locale === 'ar' ? 'اسم المنتج (بالعربية)' : 'Product Name (Arabic)'"
              placeholder="مثال: نوفيكسا عقار"
              required
            />
          </div>
          <UiInput
            v-model="productForm.slug"
            label="Slug / الرمز"
            placeholder="novixa-aqar"
            required
          />
        </div>

        <UiInput
          v-model="productForm.name_en"
          :label="locale === 'ar' ? 'اسم المنتج (بالإنجليزية)' : 'Product Name (English)'"
          placeholder="e.g. Novixa Aqar"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="productForm.tagline_ar"
            :label="locale === 'ar' ? 'الشعار التسويقي (بالعربية)' : 'Tagline (Arabic)'"
            placeholder="منصة التسويق وإدارة العقارات المتكاملة"
          />
          <UiInput
            v-model="productForm.tagline_en"
            :label="locale === 'ar' ? 'الشعار التسويقي (بالإنجليزية)' : 'Tagline (English)'"
            placeholder="Integrated real estate ERP and marketing"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiSelect
            v-model="productForm.lifecycle_status"
            :label="t('products.lifecycle')"
            :options="[
              { value: 'ideation', label: 'تخطيط وفكرة (Ideation)' },
              { value: 'in_development', label: 'قيد التطوير (In Development)' },
              { value: 'beta', label: 'إطلاق تجريبي (Beta)' },
              { value: 'active', label: 'نشط في السوق (Active)' },
              { value: 'sunset', label: 'متقاعد (Sunset)' }
            ]"
          />
          <UiSelect
            v-model="productForm.owner_id"
            :label="t('products.owner')"
            :options="userSelectOptions"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="productForm.demo_url"
            :label="t('products.demo_url')"
            placeholder="https://demo.aqar.novixa.io"
          />
          <UiInput
            v-model="productForm.website_url"
            :label="t('products.website_url')"
            placeholder="https://aqar.novixa.io"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="productForm.repository_url"
            :label="t('products.repo_url')"
            placeholder="https://github.com/novixa/aqar"
          />
          <UiInput
            v-model="productForm.documentation_url"
            :label="t('products.docs_url')"
            placeholder="https://docs.novixa.io/aqar"
          />
        </div>

        <UiTextarea
          v-model="productForm.description_ar"
          :label="locale === 'ar' ? 'وصف المنتج وقيمته السوقية' : 'Description & Value Prop'"
          :rows="3"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showCreateModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ المنتج' : 'Save Product') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- Product Linked Work Modal -->
    <UiModal
      v-model="showWorkModal"
      :title="`${locale === 'ar' ? 'المشاريع والمهام المرتبطة بـ' : 'Linked Work for'} ${selectedProduct ? (locale === 'ar' ? selectedProduct.name_ar : selectedProduct.name_en) : ''}`"
      max-width="3xl"
    >
      <div v-if="loadingWork" class="py-12 text-center text-slate-400">
        <div class="w-7 h-7 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
        <p class="text-xs">{{ t('app.loading') }}</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Linked Projects -->
        <div>
          <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-1.5">
            <FolderKanban class="w-4 h-4 text-primary-600" />
            {{ t('products.linked_projects') }} ({{ productWork.projects.length }})
          </h4>
          <div v-if="productWork.projects.length === 0" class="text-xs text-slate-400 py-3 bg-slate-50 dark:bg-slate-800/40 rounded-lg text-center">
            {{ locale === 'ar' ? 'لا توجد مشاريع مخصصة لهذا المنتج حالياً.' : 'No active projects linked.' }}
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="proj in productWork.projects"
              :key="proj.id"
              class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs"
            >
              <div>
                <span class="font-mono font-bold text-slate-600 dark:text-slate-300 mr-2">{{ proj.code }}</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">
                  {{ locale === 'ar' ? (proj.name_ar || proj.name_en) : (proj.name_en || proj.name_ar) }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <UiBadge :variant="proj.status === 'active' ? 'success' : 'neutral'">
                  {{ t(`status.${proj.status}`) }}
                </UiBadge>
                <span class="text-slate-400">{{ proj.progress_percentage || 0 }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Linked Tasks -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
              <CheckSquare class="w-4 h-4 text-teal-600" />
              {{ t('products.linked_tasks') }} ({{ productWork.tasks.length }})
            </h4>
            <UiButton
              v-if="selectedProduct"
              variant="outline"
              size="sm"
              class="text-xs py-1 px-2 h-auto"
              @click="navigateToTasksForProduct(selectedProduct.id)"
            >
              {{ locale === 'ar' ? 'فتح لوحة المهام' : 'Open Tasks Board' }}
            </UiButton>
          </div>

          <div v-if="productWork.tasks.length === 0" class="text-xs text-slate-400 py-3 bg-slate-50 dark:bg-slate-800/40 rounded-lg text-center">
            {{ locale === 'ar' ? 'لا توجد مهام نشطة مرتبطة بهذا المنتج.' : 'No active tasks linked.' }}
          </div>
          <div v-else class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
            <div
              v-for="task in productWork.tasks"
              :key="task.id"
              class="flex items-center justify-between p-2 rounded bg-slate-50 dark:bg-slate-800/40 text-xs"
            >
              <div class="flex items-center gap-2 truncate">
                <span class="font-mono text-[11px] text-slate-500">{{ task.code }}</span>
                <span class="truncate text-slate-700 dark:text-slate-300">
                  {{ locale === 'ar' ? (task.title_ar || task.title_en) : (task.title_en || task.title_ar) }}
                </span>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <UiBadge :variant="task.status === 'done' ? 'success' : 'neutral'">
                  {{ t(`status.${task.status}`) }}
                </UiBadge>
                <span v-if="task.assignee" class="text-slate-400 text-[11px]">
                  {{ task.assignee.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </UiModal>
  </div>
</template>
