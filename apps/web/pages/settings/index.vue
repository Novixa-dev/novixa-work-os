<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  Settings as SettingsIcon,
  User,
  Users,
  Building,
  Shield,
  Moon,
  Sun,
  Globe,
  Plus,
  Search,
  Check,
  CheckCircle2,
  Lock,
  Mail,
  Phone,
  Briefcase
} from 'lucide-vue-next'
import { useI18n } from '~/composables/useI18n'
import { useTheme } from '~/composables/useTheme'
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import UiCard from '~/components/ui/UiCard.vue'
import UiBadge from '~/components/ui/UiBadge.vue'
import UiButton from '~/components/ui/UiButton.vue'
import UiInput from '~/components/ui/UiInput.vue'
import UiSelect from '~/components/ui/UiSelect.vue'
import UiModal from '~/components/ui/UiModal.vue'

const { t, locale, setLocale, isRtl } = useI18n()
const { theme, setTheme } = useTheme()
const api = useApi()
const { confirm } = useConfirm()
const auth = useAuth()

const activeTab = ref<'profile' | 'team' | 'workspace'>('profile')
const loading = ref(false)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// User Profile Preferences form
const profileForm = ref({
  locale: 'ar',
  theme: 'dark',
  timezone: 'Asia/Riyadh',
})

// Team Members state
const users = ref<any[]>([])
const searchQuery = ref('')
const selectedRole = ref('')
const showAddUserModal = ref(false)
const submittingUser = ref(false)
const userFormError = ref('')

const newUserForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'member',
  phone: '',
  job_title_ar: '',
  job_title_en: '',
})

// Workspace Settings state
const workspaceForm = ref({
  name_ar: '',
  name_en: '',
  tagline_ar: '',
  tagline_en: '',
  website: '',
  email: '',
  phone: '',
  default_currency: 'USD',
  default_timezone: 'Asia/Riyadh',
})

async function loadProfile() {
  if (auth.user.value) {
    profileForm.value = {
      locale: auth.user.value.locale || locale.value,
      theme: auth.user.value.theme || theme.value,
      timezone: auth.user.value.timezone || 'Asia/Riyadh',
    }
  }
}

async function savePreferences() {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    // update local state
    setLocale(profileForm.value.locale as any)
    setTheme(profileForm.value.theme as any)

    await api.patch('/auth/preferences', profileForm.value)
    successMessage.value = locale.value === 'ar' ? 'تم حفظ التفضيلات بنجاح' : 'Preferences saved successfully'
  } catch (err: any) {
    errorMessage.value = err?.data?.message || 'Failed to save preferences'
  } finally {
    saving.value = false
  }
}

async function loadTeam() {
  if (auth.isGuest.value) return
  loading.value = true
  try {
    const res = await api.get('/users?per_page=100')
    users.value = (Array.isArray(res.data) ? res.data : [])
  } catch (err) {
    console.error('Failed to load team', err)
  } finally {
    loading.value = false
  }
}

async function toggleUserActive(u: any) {
  if (u.is_active) {
    const ok = await confirm({
      title: isRtl.value ? 'تعطيل هذا الحساب؟' : 'Deactivate this account?',
      message: isRtl.value ? 'لن يتمكن المستخدم من تسجيل الدخول حتى تعيد تفعيله.' : 'The user will not be able to sign in until you reactivate the account.',
      confirmLabel: isRtl.value ? 'تعطيل' : 'Deactivate',
      danger: true,
    })
    if (!ok) return
  }
  // The API toasts success/failure itself; only flip the local state when it succeeded.
  try {
    await api.patch(`/users/${u.id}/toggle-active`)
    u.is_active = !u.is_active
  } catch {
    /* error toast already shown by useApi */
  }
}

function openAddUser() {
  newUserForm.value = {
    name: '',
    email: '',
    password: 'Password123!',
    role: 'member',
    phone: '',
    job_title_ar: '',
    job_title_en: '',
  }
  userFormError.value = ''
  showAddUserModal.value = true
}

async function handleAddUser() {
  if (!newUserForm.value.name || !newUserForm.value.email || !newUserForm.value.password) {
    userFormError.value = locale.value === 'ar' ? 'يرجى إكمال الحقول الإلزامية' : 'Please complete required fields'
    return
  }
  submittingUser.value = true
  userFormError.value = ''
  try {
    await api.post('/users', newUserForm.value)
    showAddUserModal.value = false
    await loadTeam()
  } catch (err: any) {
    userFormError.value = err?.data?.message || 'Failed to create user'
  } finally {
    submittingUser.value = false
  }
}

async function loadWorkspaceInfo() {
  try {
    const res = await api.get('/settings/workspace')
    const s = res.settings || {}
    workspaceForm.value = {
      name_ar: s.name_ar || '',
      name_en: s.name_en || '',
      tagline_ar: s.tagline_ar || '',
      tagline_en: s.tagline_en || '',
      website: s.website || '',
      email: s.email || '',
      phone: s.phone || '',
      default_currency: s.default_currency || 'USD',
      default_timezone: s.default_timezone || 'Asia/Riyadh',
    }
  } catch (err) {
    console.error('Failed to load workspace info', err)
  }
}

async function saveWorkspaceInfo() {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    await api.put('/settings/workspace', workspaceForm.value)
    successMessage.value = locale.value === 'ar' ? 'تم تحديث هوية بيئة العمل بنجاح' : 'Workspace settings updated successfully'
  } catch (err: any) {
    errorMessage.value = err?.data?.message || 'Failed to update workspace settings'
  } finally {
    saving.value = false
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    if (selectedRole.value && u.role !== selectedRole.value) return false
    if (!searchQuery.value) return true
    const q = searchQuery.value.toLowerCase()
    return (
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      (u.job_title && u.job_title.toLowerCase().includes(q))
    )
  })
})

onMounted(async () => {
  await loadProfile()
  if (auth.isAdmin.value) {
    await Promise.all([loadTeam(), loadWorkspaceInfo()])
  } else if (!auth.isGuest.value) {
    await loadTeam()
  }
})
</script>

<template>
  <div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
        <SettingsIcon class="w-7 h-7 text-primary-600 dark:text-primary-400" />
        {{ t('settings.title') }}
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        {{ locale === 'ar' ? 'إدارة التفضيلات الشخصية، أعضاء الفريق، وصلاحيات المنظومة' : 'Manage your preferences, team members, and workspace settings.' }}
      </p>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center border-b border-slate-200 dark:border-slate-800 gap-4 text-sm font-medium">
      <button
        type="button"
        class="pb-3 px-1 border-b-2 flex items-center gap-2 transition-colors"
        :class="activeTab === 'profile' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'profile'"
      >
        <User class="w-4 h-4" />
        <span>{{ t('settings.profile_tab') }}</span>
      </button>

      <button
        v-if="!auth.isGuest.value"
        type="button"
        class="pb-3 px-1 border-b-2 flex items-center gap-2 transition-colors"
        :class="activeTab === 'team' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'team'; loadTeam()"
      >
        <Users class="w-4 h-4" />
        <span>{{ t('settings.team_tab') }}</span>
      </button>

      <button
        v-if="auth.isAdmin.value"
        type="button"
        class="pb-3 px-1 border-b-2 flex items-center gap-2 transition-colors"
        :class="activeTab === 'workspace' ? 'border-primary-600 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
        @click="activeTab = 'workspace'; loadWorkspaceInfo()"
      >
        <Building class="w-4 h-4" />
        <span>{{ t('settings.workspace_tab') }}</span>
      </button>
    </div>

    <!-- Alert Notices -->
    <div v-if="successMessage" class="p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 text-xs flex items-center gap-2">
      <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
      <span>{{ successMessage }}</span>
    </div>
    <div v-if="errorMessage" class="p-3.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-200 text-xs">
      {{ errorMessage }}
    </div>

    <!-- TAB 1: PROFILE & PREFERENCES -->
    <div v-if="activeTab === 'profile'" class="space-y-6">
      <!-- Profile Card -->
      <UiCard class="p-5">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-teal-500 text-white font-bold text-xl flex items-center justify-center shadow-md">
            {{ auth.user.value?.name?.charAt(0) || 'U' }}
          </div>
          <div>
            <div class="flex items-center gap-2 mb-1">
              <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ auth.user.value?.name }}</h2>
              <UiBadge variant="primary">{{ t(`roles.${auth.user.value?.role}`) }}</UiBadge>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
              <Mail class="w-3.5 h-3.5" />
              <span>{{ auth.user.value?.email }}</span>
              <span v-if="auth.user.value?.job_title" class="inline-block px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px]">
                {{ auth.user.value?.job_title }}
              </span>
            </p>
          </div>
        </div>

        <form class="space-y-5" @submit.prevent="savePreferences">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">
            {{ locale === 'ar' ? 'تخصيص الواجهة وبيئة العمل' : 'Interface & Localization Preferences' }}
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Language Selector -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                <Globe class="w-4 h-4 text-primary-500" />
                {{ t('settings.language') }}
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="p-2.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                  :class="profileForm.locale === 'ar' ? 'bg-primary-50 dark:bg-primary-950/40 border-primary-500 text-primary-700 dark:text-primary-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'"
                  @click="profileForm.locale = 'ar'"
                >
                  <Check v-if="profileForm.locale === 'ar'" class="w-3.5 h-3.5 text-primary-600" />
                  <span>العربية (RTL)</span>
                </button>
                <button
                  type="button"
                  class="p-2.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                  :class="profileForm.locale === 'en' ? 'bg-primary-50 dark:bg-primary-950/40 border-primary-500 text-primary-700 dark:text-primary-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'"
                  @click="profileForm.locale = 'en'"
                >
                  <Check v-if="profileForm.locale === 'en'" class="w-3.5 h-3.5 text-primary-600" />
                  <span>English (LTR)</span>
                </button>
              </div>
            </div>

            <!-- Theme Selector -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                <Sun class="w-4 h-4 text-amber-500" />
                {{ t('settings.theme') }}
              </label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  class="p-2.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                  :class="profileForm.theme === 'dark' ? 'bg-slate-800 border-primary-500 text-white font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'"
                  @click="profileForm.theme = 'dark'"
                >
                  <Moon class="w-3.5 h-3.5 text-indigo-400" />
                  <span>{{ t('settings.theme_dark') }}</span>
                </button>
                <button
                  type="button"
                  class="p-2.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                  :class="profileForm.theme === 'light' ? 'bg-primary-50 border-primary-500 text-primary-800 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'"
                  @click="profileForm.theme = 'light'"
                >
                  <Sun class="w-3.5 h-3.5 text-amber-500" />
                  <span>{{ t('settings.theme_light') }}</span>
                </button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <UiSelect
              v-model="profileForm.timezone"
              :label="t('settings.timezone')"
              :options="[
                { value: 'Asia/Riyadh', label: 'Asia/Riyadh / مكة المكرمة (GMT+3)' },
                { value: 'Asia/Aden', label: 'Asia/Aden / عدن والمكلا (GMT+3)' },
                { value: 'Asia/Dubai', label: 'Asia/Dubai / دبي (GMT+4)' },
                { value: 'UTC', label: 'UTC (توقيت غرينتش)' }
              ]"
            />
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <UiButton type="submit" variant="primary" :disabled="saving">
              {{ saving ? t('app.loading') : t('settings.save_changes') }}
            </UiButton>
          </div>
        </form>
      </UiCard>
    </div>

    <!-- TAB 2: TEAM MEMBERS & ACCESS CONTROL -->
    <div v-else-if="activeTab === 'team'" class="space-y-4">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-sm">
          <Search class="w-4 h-4 text-slate-400 absolute top-1/2 -translate-y-1/2" :class="isRtl ? 'right-3' : 'left-3'" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="locale === 'ar' ? 'بحث بالاسم، البريد...' : 'Search members...'"
            class="w-full text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg py-1.5 focus:ring-2 focus:ring-primary-500 focus:outline-none text-slate-900 dark:text-white"
            :class="isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3'"
          />
        </div>

        <div class="flex items-center gap-2">
          <select
            v-model="selectedRole"
            class="text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1.5 text-slate-700 dark:text-slate-300 focus:outline-none"
          >
            <option value="">{{ locale === 'ar' ? 'جميع الصلاحيات' : 'All Roles' }}</option>
            <option value="owner">{{ t('roles.owner') }}</option>
            <option value="admin">{{ t('roles.admin') }}</option>
            <option value="manager">{{ t('roles.manager') }}</option>
            <option value="member">{{ t('roles.member') }}</option>
            <option value="guest">{{ t('roles.guest') }}</option>
          </select>

          <UiButton
            v-if="auth.isAdmin.value"
            variant="primary"
            size="sm"
            class="flex items-center gap-1.5"
            @click="openAddUser"
          >
            <Plus class="w-4 h-4" />
            <span>{{ locale === 'ar' ? 'إضافة عضو جديد' : 'Add Member' }}</span>
          </UiButton>
        </div>
      </div>

      <!-- Team Members Table -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm" :class="isRtl ? 'text-right' : 'text-left'">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs uppercase font-medium text-slate-500 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-4">{{ locale === 'ar' ? 'العضو' : 'Member' }}</th>
              <th class="py-3 px-4">{{ locale === 'ar' ? 'الدور والصلاحية' : 'Role' }}</th>
              <th class="py-3 px-4">{{ locale === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</th>
              <th class="py-3 px-4">{{ locale === 'ar' ? 'الحالة' : 'Status' }}</th>
              <th class="py-3 px-4 text-center"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            <tr
              v-for="u in filteredUsers"
              :key="u.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center text-xs">
                    {{ u.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-white">{{ u.name }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ u.email }}</div>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <UiBadge :variant="u.role === 'owner' ? 'danger' : u.role === 'admin' ? 'purple' : u.role === 'manager' ? 'info' : 'neutral'">
                  {{ t(`roles.${u.role}`) }}
                </UiBadge>
              </td>
              <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                {{ u.job_title || '—' }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="u.is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="u.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                  {{ u.is_active ? (locale === 'ar' ? 'نشط' : 'Active') : (locale === 'ar' ? 'معطل' : 'Disabled') }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  v-if="auth.isAdmin.value && u.role !== 'owner' && u.id !== auth.user.value?.id"
                  type="button"
                  class="text-xs font-medium text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                  @click="toggleUserActive(u)"
                >
                  {{ u.is_active ? (locale === 'ar' ? 'تعطيل الحساب' : 'Deactivate') : (locale === 'ar' ? 'تنشيط' : 'Activate') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: WORKSPACE IDENTITY (Admin/Owner) -->
    <div v-else-if="activeTab === 'workspace' && auth.isAdmin.value" class="space-y-6">
      <UiCard class="p-5">
        <form class="space-y-4" @submit.prevent="saveWorkspaceInfo">
          <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <Shield class="w-5 h-5 text-primary-600" />
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
              {{ locale === 'ar' ? 'هوية المنظومة وبيانات الشركة الرسمية' : 'Workspace Identity & Legal Info' }}
            </h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiInput
              v-model="workspaceForm.name_ar"
              :label="locale === 'ar' ? 'اسم الشركة (بالعربية)' : 'Company Name (Arabic)'"
              required
            />
            <UiInput
              v-model="workspaceForm.name_en"
              :label="locale === 'ar' ? 'اسم الشركة (بالإنجليزية)' : 'Company Name (English)'"
              required
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiInput
              v-model="workspaceForm.tagline_ar"
              :label="locale === 'ar' ? 'الشعار التسويقي (بالعربية)' : 'Tagline (Arabic)'"
            />
            <UiInput
              v-model="workspaceForm.tagline_en"
              :label="locale === 'ar' ? 'الشعار التسويقي (بالإنجليزية)' : 'Tagline (English)'"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <UiInput
              v-model="workspaceForm.website"
              :label="t('products.website_url')"
            />
            <UiInput
              v-model="workspaceForm.email"
              :label="locale === 'ar' ? 'البريد الرسمي للمؤسسة' : 'Company Email'"
            />
            <UiInput
              v-model="workspaceForm.phone"
              :label="locale === 'ar' ? 'رقم الهاتف المعتمد' : 'Company Phone'"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <UiSelect
              v-model="workspaceForm.default_currency"
              label="العملة الافتراضية / Default Currency"
              :options="[
                { value: 'USD', label: 'USD ($)' },
                { value: 'SAR', label: 'SAR (ريال سعودي)' },
                { value: 'YER', label: 'YER (ريال يمني)' }
              ]"
            />
            <UiSelect
              v-model="workspaceForm.default_timezone"
              :label="t('settings.timezone')"
              :options="[
                { value: 'Asia/Riyadh', label: 'Asia/Riyadh (GMT+3)' },
                { value: 'Asia/Aden', label: 'Asia/Aden (GMT+3)' },
                { value: 'UTC', label: 'UTC' }
              ]"
            />
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <UiButton type="submit" variant="primary" :disabled="saving">
              {{ saving ? t('app.loading') : (locale === 'ar' ? 'حفظ إعدادات المنظومة' : 'Save Workspace Info') }}
            </UiButton>
          </div>
        </form>
      </UiCard>
    </div>

    <!-- Add Team Member Modal -->
    <UiModal
      v-model="showAddUserModal"
      :title="locale === 'ar' ? 'إضافة عضو جديد للفريق' : 'Add Team Member'"
      max-width="lg"
    >
      <form class="space-y-4" @submit.prevent="handleAddUser">
        <div v-if="userFormError" class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs">
          {{ userFormError }}
        </div>

        <UiInput
          v-model="newUserForm.name"
          :label="locale === 'ar' ? 'الاسم الكامل' : 'Full Name'"
          placeholder="مثال: أحمد بانعيم"
          required
        />

        <UiInput
          v-model="newUserForm.email"
          type="email"
          :label="locale === 'ar' ? 'البريد الإلكتروني المهني' : 'Work Email'"
          placeholder="ahmed@novixa.dev"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="newUserForm.password"
            type="password"
            :label="locale === 'ar' ? 'كلمة المرور المؤقتة' : 'Temporary Password'"
            required
          />
          <UiSelect
            v-model="newUserForm.role"
            :label="locale === 'ar' ? 'الدور والصلاحية' : 'Role'"
            :options="[
              { value: 'admin', label: t('roles.admin') },
              { value: 'manager', label: t('roles.manager') },
              { value: 'member', label: t('roles.member') },
              { value: 'guest', label: t('roles.guest') }
            ]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <UiInput
            v-model="newUserForm.job_title_ar"
            :label="locale === 'ar' ? 'المسمى الوظيفي (بالعربية)' : 'Job Title (Arabic)'"
            placeholder="مهندس نظم وحلول سحابية"
          />
          <UiInput
            v-model="newUserForm.phone"
            :label="locale === 'ar' ? 'رقم الهاتف' : 'Phone'"
            placeholder="+967 770 000 000"
          />
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <UiButton type="button" variant="outline" @click="showAddUserModal = false">
            {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
          </UiButton>
          <UiButton type="submit" variant="primary" :disabled="submittingUser">
            {{ submittingUser ? t('app.loading') : (locale === 'ar' ? 'إنشاء الحساب' : 'Create Account') }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </div>
</template>
