<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  Users,
  TrendingUp,
  Plus,
  Search,
  Building2,
  Phone,
  Mail,
  MapPin,
  Calendar,
  DollarSign,
  Rocket,
  CheckCircle2,
  MessageSquare,
  ArrowRight,
  ArrowLeft,
  ChevronRight,
  ChevronLeft,
  Briefcase
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

const activeTab = ref<'pipeline' | 'clients'>('pipeline')
const loading = ref(true)

// Data lists
const clients = ref<any[]>([])
const opportunities = ref<any[]>([])
const usersList = ref<any[]>([])
const productsList = ref<any[]>([])
const searchQuery = ref('')

// Modals
const showClientModal = ref(false)
const showOppModal = ref(false)
const showInteractionModal = ref(false)
const showConvertModal = ref(false)
const selectedClient = ref<any>(null)
const selectedOpp = ref<any>(null)
const clientInteractions = ref<any[]>([])
const loadingInteractions = ref(false)

const submitting = ref(false)
const formError = ref('')

// Forms
const clientForm = ref({
  name_ar: '',
  name_en: '',
  industry: '',
  city: '',
  website: '',
  account_manager_id: '',
  contact_name: '',
  contact_email: '',
  contact_phone: '',
  contact_title: '',
})

const oppForm = ref({
  client_id: '',
  product_id: '',
  title_ar: '',
  title_en: '',
  stage: 'lead',
  estimated_value: '',
  currency: 'USD',
  owner_id: '',
  expected_close_date: '',
  next_follow_up_date: '',
  notes: '',
})

const interactionForm = ref({
  channel: 'meeting',
  interaction_date: new Date().toISOString().split('T')[0],
  notes: '',
  next_follow_up_date: '',
})

const convertForm = ref({
  code: '',
  name_ar: '',
  due_date: '',
})

const pipelineStages = [
  { id: 'lead', label_ar: 'عميل محتمل (Lead)', label_en: 'Lead', color: 'border-slate-300 dark:border-slate-700' },
  { id: 'qualified', label_ar: 'مؤهل (Qualified)', label_en: 'Qualified', color: 'border-blue-400' },
  { id: 'proposal', label_ar: 'تقديم العرض (Proposal)', label_en: 'Proposal', color: 'border-amber-400' },
  { id: 'negotiation', label_ar: 'تفاوض (Negotiation)', label_en: 'Negotiation', color: 'border-purple-400' },
  { id: 'won', label_ar: 'صفقة رابحة (Won 🎉)', label_en: 'Won 🎉', color: 'border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10' },
  { id: 'lost', label_ar: 'خسارة (Lost)', label_en: 'Lost', color: 'border-rose-400' },
]

async function fetchData() {
  loading.value = true
  try {
    const [cRes, oRes, uRes, pRes] = await Promise.all([
      api.get('/clients'),
      api.get('/opportunities'),
      api.get('/users'),
      api.get('/products'),
    ])
    clients.value = (Array.isArray(cRes.data) ? cRes.data : [])
    opportunities.value = (Array.isArray(oRes.data) ? oRes.data : [])
    usersList.value = (Array.isArray(uRes.data) ? uRes.data : [])
    productsList.value = (Array.isArray(pRes.data) ? pRes.data : [])
  } catch (err) {
    console.error('Failed to load CRM data', err)
  } finally {
    loading.value = false
  }
}

function openCreateClient() {
  clientForm.value = {
    name_ar: '',
    name_en: '',
    industry: '',
    city: 'المكلا / حضرموت',
    website: '',
    account_manager_id: auth.user.value?.id?.toString() || '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    contact_title: '',
  }
  formError.value = ''
  showClientModal.value = true
}

async function handleCreateClient() {
  if (!clientForm.value.name_ar && !clientForm.value.name_en) {
    formError.value = locale.value === 'ar' ? 'يرجى كتابة اسم العميل / المؤسسة' : 'Client name is required'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    const payload: any = {
      name_ar: clientForm.value.name_ar,
      name_en: clientForm.value.name_en || clientForm.value.name_ar,
      industry: clientForm.value.industry,
      city: clientForm.value.city,
      website: clientForm.value.website,
      account_manager_id: clientForm.value.account_manager_id || null,
    }
    if (clientForm.value.contact_name) {
      payload.primary_contact = {
        name: clientForm.value.contact_name,
        email: clientForm.value.contact_email,
        phone: clientForm.value.contact_phone,
        job_title: clientForm.value.contact_title,
      }
    }
    await api.post('/clients', payload)
    showClientModal.value = false
    await fetchData()
  } catch (err: any) {
    formError.value = err?.data?.message || 'Failed to save client'
  } finally {
    submitting.value = false
  }
}

function openCreateOpp(presetClientId?: number) {
  oppForm.value = {
    client_id: presetClientId ? presetClientId.toString() : (clients.value[0]?.id?.toString() || ''),
    product_id: '',
    title_ar: '',
    title_en: '',
    stage: 'lead',
    estimated_value: '',
    currency: 'USD',
    owner_id: auth.user.value?.id?.toString() || '',
    expected_close_date: '',
    next_follow_up_date: '',
    notes: '',
  }
  formError.value = ''
  showOppModal.value = true
}

async function handleCreateOpp() {
  if (!oppForm.value.title_ar || !oppForm.value.client_id) {
    formError.value = locale.value === 'ar' ? 'عنوان الفرصة والعميل مطلوبان' : 'Title and Client are required'
    return
  }
  submitting.value = true
  formError.value = ''
  try {
    const payload: any = { ...oppForm.value }
    if (!payload.product_id) delete payload.product_id
    if (!payload.owner_id) delete payload.owner_id
    if (!payload.estimated_value) delete payload.estimated_value
    if (!payload.expected_close_date) delete payload.expected_close_date
    if (!payload.next_follow_up_date) delete payload.next_follow_up_date

    await api.post('/opportunities', payload)
    showOppModal.value = false
    await fetchData()
  } catch (err: any) {
    formError.value = err?.data?.message || 'Failed to create opportunity'
  } finally {
    submitting.value = false
  }
}

async function updateOppStage(opp: any, newStage: string) {
  try {
    await api.patch(`/opportunities/${opp.id}/stage`, { stage: newStage })
    opp.stage = newStage
    await fetchData()
  } catch (err) {
    console.error('Failed to change stage', err)
  }
}

function openConvertModal(opp: any) {
  selectedOpp.value = opp
  convertForm.value = {
    code: `PRJ-${Math.floor(100 + Math.random() * 900)}`,
    name_ar: `مشروع تسليم: ${opp.title_ar}`,
    due_date: opp.expected_close_date || '',
  }
  formError.value = ''
  showConvertModal.value = true
}

async function handleConvertWonDeal() {
  submitting.value = true
  formError.value = ''
  try {
    const res = await api.post(`/opportunities/${selectedOpp.value.id}/convert-to-project`, convertForm.value)
    showConvertModal.value = false
    await fetchData()
    if (res.project?.id) {
      router.push({ path: '/projects' })
    }
  } catch (err: any) {
    formError.value = err?.data?.message || 'Failed to convert deal to project'
  } finally {
    submitting.value = false
  }
}

async function openInteractionHistory(client: any) {
  selectedClient.value = client
  showInteractionModal.value = true
  loadingInteractions.value = true
  interactionForm.value = {
    channel: 'meeting',
    interaction_date: new Date().toISOString().split('T')[0],
    notes: '',
    next_follow_up_date: '',
  }
  try {
    const res = await api.get(`/clients/${client.id}/interactions`)
    clientInteractions.value = (Array.isArray(res.data) ? res.data : [])
  } catch (err) {
    console.error('Failed to load interactions', err)
  } finally {
    loadingInteractions.value = false
  }
}

async function handleAddInteraction() {
  if (!interactionForm.value.notes) return
  submitting.value = true
  try {
    await api.post(`/clients/${selectedClient.value.id}/interactions`, interactionForm.value)
    interactionForm.value.notes = ''
    const res = await api.get(`/clients/${selectedClient.value.id}/interactions`)
    clientInteractions.value = (Array.isArray(res.data) ? res.data : [])
    await fetchData()
  } catch (err) {
    console.error('Failed to add interaction', err)
  } finally {
    submitting.value = false
  }
}

// Stage summaries
function getOppsByStage(stageId: string) {
  return opportunities.value.filter(o => o.stage === stageId)
}

function getStageTotal(stageId: string) {
  const opps = getOppsByStage(stageId)
  const sum = opps.reduce((acc, curr) => acc + Number(curr.estimated_value || 0), 0)
  return sum.toLocaleString()
}

const clientSelectOptions = computed(() => [
  ...clients.value.map(c => ({
    value: c.id.toString(),
    label: locale.value === 'ar' ? (c.name_ar || c.name_en) : (c.name_en || c.name_ar)
  }))
])

const productSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون منتج محدد' : 'No Product' },
  ...productsList.value.map(p => ({
    value: p.id.toString(),
    label: locale.value === 'ar' ? (p.name_ar || p.name_en) : (p.name_en || p.name_ar)
  }))
])

const userSelectOptions = computed(() => [
  { value: '', label: locale.value === 'ar' ? 'بدون مسؤول' : 'Unassigned' },
  ...usersList.value.map(u => ({ value: u.id.toString(), label: u.name }))
])

const filteredClients = computed(() => {
  if (!searchQuery.value) return clients.value
  const q = searchQuery.value.toLowerCase()
  return clients.value.filter(c =>
    (c.name_ar && c.name_ar.toLowerCase().includes(q)) ||
    (c.name_en && c.name_en.toLowerCase().includes(q)) ||
    (c.industry && c.industry.toLowerCase().includes(q)) ||
    (c.city && c.city.toLowerCase().includes(q))
  )
})

onMounted(fetchData)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
          <Briefcase class="w-7 h-7 text-primary-600 dark:text-primary-400" />
          {{ t('crm.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          {{ locale === 'ar' ? 'إدارة العملاء والشركات في حضرموت والخليج، وتتبع خط أنابيب الصفقات والفرص' : 'Manage clients and track the deals and opportunities pipeline.' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <UiButton
          v-if="!auth.isGuest.value"
          variant="outline"
          class="flex items-center gap-1.5"
          @click="openCreateClient"
        >
          <Building2 class="w-4 h-4" />
          {{ t('crm.create_client') }}
        </UiButton>
        <UiButton
          v-if="!auth.isGuest.value"
          variant="primary"
          class="flex items-center gap-1.5"
          @click="openCreateOpp()"
        >
          <Plus class="w-4 h-4" />
          {{ t('crm.create_opportunity') }}
        </UiButton>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-4 text-sm font-medium">
      <button
        type="button"
        class="pb-3 px-1 border-b-2 flex items-center gap-2 transition-colors"
        :class="activeTab === 'pipeline' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'pipeline'"
      >
        <TrendingUp class="w-4 h-4" />
        <span>{{ t('crm.pipeline_tab') }}</span>
        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ opportunities.length }}
        </span>
      </button>

      <button
        type="button"
        class="pb-3 px-1 border-b-2 flex items-center gap-2 transition-colors"
        :class="activeTab === 'clients' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'clients'"
      >
        <Users class="w-4 h-4" />
        <span>{{ t('crm.clients_tab') }}</span>
        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          {{ clients.length }}
        </span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-16 text-center text-slate-400 flex flex-col items-center justify-center">
      <div class="w-8 h-8 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"></div>
      <p class="text-sm">{{ t('app.loading') }}</p>
    </div>

    <!-- TAB 1: DEAL PIPELINE KANBAN -->
    <div v-else-if="activeTab === 'pipeline'" class="overflow-x-auto pb-4">
      <div class="flex gap-4 min-w-[1200px]">
        <div
          v-for="st in pipelineStages"
          :key="st.id"
          class="flex-1 bg-slate-50/70 dark:bg-slate-900/60 rounded-xl border p-3 flex flex-col min-w-[260px] max-w-[340px]"
          :class="st.color"
        >
          <!-- Stage Header -->
          <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800 text-xs">
            <span class="font-bold text-slate-800 dark:text-slate-200">
              {{ locale === 'ar' ? st.label_ar : st.label_en }}
            </span>
            <div class="flex items-center gap-2">
              <span class="text-[11px] font-mono font-bold text-slate-600 dark:text-slate-300">
                ${{ getStageTotal(st.id) }}
              </span>
              <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-[10px] font-medium text-slate-600 dark:text-slate-400">
                {{ getOppsByStage(st.id).length }}
              </span>
            </div>
          </div>

          <!-- Stage Opportunity Cards -->
          <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-280px)] pr-1">
            <div
              v-if="getOppsByStage(st.id).length === 0"
              class="py-8 text-center text-[11px] text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-lg"
            >
              {{ locale === 'ar' ? 'لا توجد صفقات هنا' : 'No deals' }}
            </div>

            <UiCard
              v-for="opp in getOppsByStage(st.id)"
              :key="opp.id"
              class="p-3 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md transition-shadow"
            >
              <!-- Deal Client -->
              <div class="text-[11px] font-medium text-primary-600 dark:text-teal-400 mb-1 flex items-center justify-between">
                <span class="truncate">🏢 {{ locale === 'ar' ? (opp.client?.name_ar || opp.client?.name_en) : (opp.client?.name_en || opp.client?.name_ar) }}</span>
                <span v-if="opp.product" class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                  {{ opp.product.slug }}
                </span>
              </div>

              <!-- Deal Title -->
              <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                {{ locale === 'ar' ? (opp.title_ar || opp.title_en) : (opp.title_en || opp.title_ar) }}
              </h4>

              <!-- Value & Currency -->
              <div class="flex items-center justify-between text-xs font-mono font-bold text-slate-800 dark:text-slate-200 mb-3 bg-slate-50 dark:bg-slate-800/60 p-1.5 rounded">
                <span class="flex items-center gap-1">
                  <DollarSign class="w-3.5 h-3.5 text-emerald-600" />
                  {{ opp.estimated_value ? Number(opp.estimated_value).toLocaleString() : '0' }}
                </span>
                <span class="text-[10px] text-slate-500">{{ opp.currency || 'USD' }}</span>
              </div>

              <!-- Won Delivery Project Conversion -->
              <div v-if="opp.stage === 'won'" class="mb-3">
                <div v-if="opp.won_delivery_project_id" class="p-2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] flex items-center gap-1.5">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>{{ locale === 'ar' ? 'تم إنشاء مشروع التسليم بنجاح' : 'Delivery project active' }}</span>
                </div>
                <UiButton
                  v-else-if="!auth.isGuest.value"
                  variant="primary"
                  size="sm"
                  class="w-full text-xs py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 flex items-center justify-center gap-1.5"
                  @click="openConvertModal(opp)"
                >
                  <Rocket class="w-3.5 h-3.5" />
                  <span>{{ t('crm.convert_to_project') }}</span>
                </UiButton>
              </div>

              <!-- Footer with owner & stage shifters -->
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px]">
                <div class="flex items-center gap-1">
                  <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold flex items-center justify-center text-[9px]">
                    {{ opp.owner?.name?.charAt(0) || 'U' }}
                  </div>
                  <span class="text-slate-500 text-[10px] truncate max-w-[80px]">
                    {{ opp.owner?.name }}
                  </span>
                </div>

                <!-- Shift Stage Buttons -->
                <div class="flex items-center gap-1">
                  <!-- Previous stage -->
                  <button
                    v-if="pipelineStages.findIndex(s => s.id === opp.stage) > 0"
                    type="button"
                    class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700"
                    :title="locale === 'ar' ? 'المرحلة السابقة' : 'Previous Stage'"
                    @click="updateOppStage(opp, pipelineStages[pipelineStages.findIndex(s => s.id === opp.stage) - 1].id)"
                  >
                    <component :is="isRtl ? ChevronRight : ChevronLeft" class="w-3 h-3" />
                  </button>
                  <!-- Next stage -->
                  <button
                    v-if="pipelineStages.findIndex(s => s.id === opp.stage) < pipelineStages.length - 1"
                    type="button"
                    class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700"
                    :title="locale === 'ar' ? 'المرحلة التالية' : 'Next Stage'"
                    @click="updateOppStage(opp, pipelineStages[pipelineStages.findIndex(s => s.id === opp.stage) + 1].id)"
                  >
                    <component :is="isRtl ? ChevronLeft : ChevronRight" class="w-3 h-3" />
                  </button>
                </div>
              </div>
            </UiCard>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: CLIENTS DIRECTORY -->
    <div v-else class="space-y-4">
      <!-- Search -->
      <div class="relative max-w-md">
        <Search class="w-4 h-4 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-3' : 'left-3'" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="locale === 'ar' ? 'بحث في العملاء والمؤسسات...' : 'Search clients...'"
          class="w-full text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
          :class="isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3'"
        />
      </div>

      <!-- Clients Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <UiCard
          v-for="client in filteredClients"
          :key="client.id"
          class="flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all hover:shadow-md"
        >
          <div>
            <div class="flex items-start justify-between gap-2 mb-2">
              <h3 class="text-base font-bold text-slate-900 dark:text-white">
                {{ locale === 'ar' ? (client.name_ar || client.name_en) : (client.name_en || client.name_ar) }}
              </h3>
              <span v-if="client.city" class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center gap-1 shrink-0">
                <MapPin class="w-2.5 h-2.5 text-slate-400" />
                {{ client.city }}
              </span>
            </div>

            <p v-if="client.industry" class="text-xs text-primary-600 dark:text-teal-400 font-medium mb-3">
              {{ client.industry }}
            </p>

            <!-- Contacts info -->
            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 mb-4">
              <div v-if="client.primary_contact?.name" class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <Users class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ client.primary_contact.name }} ({{ client.primary_contact.job_title || 'جهة الاتصال' }})</span>
              </div>
              <div v-if="client.primary_contact?.phone" class="flex items-center gap-1.5 text-[11px]">
                <Phone class="w-3.5 h-3.5 text-slate-400" />
                <span dir="ltr">{{ client.primary_contact.phone }}</span>
              </div>
              <div v-if="client.primary_contact?.email" class="flex items-center gap-1.5 text-[11px]">
                <Mail class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ client.primary_contact.email }}</span>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-500 text-[11px]">
              {{ t('crm.account_manager') }}: {{ client.account_manager?.name || '—' }}
            </span>

            <div class="flex items-center gap-1.5">
              <UiButton
                variant="outline"
                size="sm"
                class="text-xs py-1 px-2 h-auto flex items-center gap-1"
                @click="openInteractionHistory(client)"
              >
                <MessageSquare class="w-3 h-3" />
                <span>{{ t('crm.interactions') }}</span>
              </UiButton>
              <UiButton
                v-if="!auth.isGuest.value"
                variant="primary"
                size="sm"
                class="text-xs py-1 px-2 h-auto"
                @click="openCreateOpp(client.id)"
              >
                <Plus class="w-3 h-3" />
                <span>{{ locale === 'ar' ? 'صفقة' : 'Deal' }}</span>
              </UiButton>
            </div>
          </div>
        </UiCard>
      </div>
    </div>

    <!-- Create Client Modal -->
    <UiModal
      v-model="showClientModal"
      :title="t('crm.create_client')"
      max-width="xl"
    >
      <form class="space-y-4" @submit.prevent="handleCreateClient">
        <div v-if="formError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ formError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="clientForm.name_ar"
            :label="locale === 'ar' ? 'اسم المؤسسة / العميل (بالعربية)' : 'Company Name (Arabic)'"
            placeholder="مثال: شركة دار حضرموت للتطوير العقاري"
            required
          />
          <UiInput
            v-model="clientForm.name_en"
            :label="locale === 'ar' ? 'اسم العميل (بالإنجليزية)' : 'Company Name (English)'"
            placeholder="e.g. Dar Hadramout Real Estate"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="clientForm.industry"
            :label="t('crm.industry')"
            placeholder="عقارات، لوجستيات، تجارة..."
          />
          <UiInput
            v-model="clientForm.city"
            :label="t('crm.city')"
            placeholder="المكلا، الرياض، دبي..."
          />
        </div>

        <UiSelect
          v-model="clientForm.account_manager_id"
          :label="t('crm.account_manager')"
          :options="userSelectOptions"
        />

        <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-lg space-y-3 border border-slate-100 dark:border-slate-800">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ locale === 'ar' ? 'بيانات جهة الاتصال الرئيسية' : 'Primary Contact' }}
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiInput
              v-model="clientForm.contact_name"
              :label="locale === 'ar' ? 'اسم الشخص المسؤول' : 'Contact Person'"
              placeholder="مثال: م. عمر الكثيري"
            />
            <UiInput
              v-model="clientForm.contact_title"
              :label="locale === 'ar' ? 'المسمى الوظيفي' : 'Job Title'"
              placeholder="المدير العام"
            />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiInput
              v-model="clientForm.contact_phone"
              :label="locale === 'ar' ? 'رقم الهاتف / واتساب' : 'Phone / WhatsApp'"
              placeholder="+967 770 000 000"
            />
            <UiInput
              v-model="clientForm.contact_email"
              type="email"
              :label="locale === 'ar' ? 'البريد الإلكتروني' : 'Email Address'"
              placeholder="contact@company.com"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showClientModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'حفظ العميل' : 'Save Client') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- Create Opportunity Modal -->
    <UiModal
      v-model="showOppModal"
      :title="t('crm.create_opportunity')"
      max-width="xl"
    >
      <form class="space-y-4" @submit.prevent="handleCreateOpp">
        <div v-if="formError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ formError }}
        </div>

        <UiSelect
          v-model="oppForm.client_id"
          :label="t('crm.clients_tab')"
          :options="clientSelectOptions"
          required
        />

        <UiInput
          v-model="oppForm.title_ar"
          :label="locale === 'ar' ? 'عنوان الصفقة / الفرصة (بالعربية)' : 'Deal Title (Arabic)'"
          placeholder="مثال: ترقية منصة عقار وتسليم لوحة التحكم"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiSelect
            v-model="oppForm.product_id"
            :label="t('products.title')"
            :options="productSelectOptions"
          />
          <UiSelect
            v-model="oppForm.stage"
            :label="t('crm.stage')"
            :options="[
              { value: 'lead', label: 'عميل محتمل (Lead)' },
              { value: 'qualified', label: 'مؤهل (Qualified)' },
              { value: 'proposal', label: 'تقديم العرض (Proposal)' },
              { value: 'negotiation', label: 'تفاوض (Negotiation)' },
              { value: 'won', label: 'صفقة رابحة (Won 🎉)' },
              { value: 'lost', label: 'خسارة (Lost)' }
            ]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="oppForm.estimated_value"
            type="number"
            :label="t('crm.estimated_value')"
            placeholder="15000"
          />
          <UiSelect
            v-model="oppForm.currency"
            label="العملة / Currency"
            :options="[
              { value: 'USD', label: 'USD ($)' },
              { value: 'SAR', label: 'SAR (ريال سعودي)' },
              { value: 'YER', label: 'YER (ريال يمني)' }
            ]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiSelect
            v-model="oppForm.owner_id"
            :label="t('crm.account_manager')"
            :options="userSelectOptions"
          />
          <UiInput
            v-model="oppForm.expected_close_date"
            type="date"
            :label="locale === 'ar' ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date'"
          />
        </div>

        <UiTextarea
          v-model="oppForm.notes"
          :label="locale === 'ar' ? 'ملاحظات وتفاصيل الصفقة' : 'Deal Notes'"
          :rows="3"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showOppModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'إنشاء الصفقة' : 'Create Deal') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- Convert Won Deal to Delivery Project Modal -->
    <UiModal
      v-model="showConvertModal"
      :title="t('crm.won_dialog_title')"
      max-width="md"
    >
      <form class="space-y-4" @submit.prevent="handleConvertWonDeal">
        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg text-emerald-800 dark:text-emerald-200 text-xs">
          {{ t('crm.won_dialog_desc') }}
        </div>

        <UiInput
          v-model="convertForm.code"
          :label="t('projects.code')"
          placeholder="PRJ-102"
          required
        />

        <UiInput
          v-model="convertForm.name_ar"
          :label="t('projects.name')"
          required
        />

        <UiInput
          v-model="convertForm.due_date"
          type="date"
          :label="t('projects.due_date')"
        />

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showConvertModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submitting" class="bg-emerald-600 hover:bg-emerald-700">
            {{ submitting ? t('app.loading') : (locale === 'ar' ? 'بدء مشروع التسليم الآن 🚀' : 'Start Delivery Project 🚀') }}
          </UiButton>
        </div>
      </form>
    </UiModal>

    <!-- Client Interactions Drawer / Modal -->
    <UiModal
      v-model="showInteractionModal"
      :title="`${t('crm.interactions')} — ${selectedClient ? (locale === 'ar' ? selectedClient.name_ar : selectedClient.name_en) : ''}`"
      max-width="2xl"
    >
      <div class="space-y-6">
        <!-- Log new interaction form -->
        <form class="bg-slate-50 dark:bg-slate-800/40 p-3 rounded-lg border border-slate-100 dark:border-slate-800 space-y-3" @submit.prevent="handleAddInteraction">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
            <Plus class="w-3.5 h-3.5 text-primary-600" />
            {{ t('crm.add_interaction') }}
          </h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiSelect
              v-model="interactionForm.channel"
              :label="t('crm.channel')"
              :options="[
                { value: 'meeting', label: 'اجتماع حضوري / زووم' },
                { value: 'call', label: 'مكالمة هاتفية' },
                { value: 'whatsapp', label: 'مراسلة واتساب' },
                { value: 'email', label: 'بريد إلكتروني' },
                { value: 'site_visit', label: 'زيارة موقع ميدانية' }
              ]"
            />
            <UiInput
              v-model="interactionForm.interaction_date"
              type="date"
              :label="locale === 'ar' ? 'تاريخ التفاعل' : 'Date'"
            />
          </div>
          <UiTextarea
            v-model="interactionForm.notes"
            :label="locale === 'ar' ? 'ملخص ما تم الاتفاق عليه ومخرجات التواصل' : 'Meeting Notes & Agreement'"
            :rows="2"
            required
          />
          <div class="flex items-center justify-between">
            <UiInput
              v-model="interactionForm.next_follow_up_date"
              type="date"
              :label="t('crm.next_follow_up')"
              class="w-48 text-xs"
            />
            <UiButton type="submit" variant="primary" size="sm" :disabled="submitting">
              {{ submitting ? t('app.loading') : (locale === 'ar' ? 'تسجيل الملاحظة' : 'Log Note') }}
            </UiButton>
          </div>
        </form>

        <!-- Timeline of past interactions -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ locale === 'ar' ? 'سجل التواصل والمتابعات السابقة' : 'Past Interactions Log' }}
          </h4>
          <div v-if="loadingInteractions" class="py-6 text-center text-slate-400">
            <div class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto mb-1"></div>
            <p class="text-xs">{{ t('app.loading') }}</p>
          </div>
          <div v-else-if="clientInteractions.length === 0" class="py-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40 rounded-lg">
            {{ locale === 'ar' ? 'لا توجد تسجيلات تواصل سابقة لهذا العميل.' : 'No interactions recorded yet.' }}
          </div>
          <div v-else class="space-y-2 max-h-72 overflow-y-auto pr-1">
            <div
              v-for="item in clientInteractions"
              :key="item.id"
              class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs"
            >
              <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                <span class="font-bold text-primary-600 dark:text-teal-400 uppercase">{{ item.channel }}</span>
                <span>{{ item.interaction_date }}</span>
              </div>
              <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ item.notes }}</p>
              <div v-if="item.next_follow_up_date" class="mt-2 text-[10px] text-amber-600 dark:text-amber-400 flex items-center gap-1">
                <Calendar class="w-3 h-3" />
                <span>{{ t('crm.next_follow_up') }}: {{ item.next_follow_up_date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </UiModal>
  </div>
</template>
