<template>
  <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Top Bar: Language Toggle -->
    <div class="absolute top-6 end-6">
      <button
        type="button"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 border border-slate-700 transition-colors"
        @click="toggleLocale"
      >
        {{ locale === 'ar' ? 'English' : 'عربي' }}
      </button>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Logo -->
      <div class="flex justify-center mb-4">
        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-brand-blue to-brand-teal flex items-center justify-center text-white font-extrabold text-2xl shadow-xl shadow-brand-blue/20">
          N
        </div>
      </div>

      <h2 class="text-center text-2xl font-bold tracking-tight text-white">
        {{ isRtl ? 'تسجيل الدخول إلى نوڤيكسا WorkOS' : 'Sign in to Novixa WorkOS' }}
      </h2>
      <p class="mt-1.5 text-center text-xs text-slate-400">
        {{ isRtl ? 'المنظومة الرقمية الموحدة لإدارة العمليات والمشاريع والعملاء' : 'Unified enterprise management workspace for Novixa' }}
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-slate-950/80 border border-slate-800/90 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
        <form class="space-y-4" @submit.prevent="handleLogin">
          <!-- Error alert -->
          <div
            v-if="errorMessage"
            role="alert"
            class="p-3 rounded-xl bg-rose-950/40 border border-rose-900/60 text-rose-300 text-xs flex items-center gap-2"
          >
            <svg class="w-4 h-4 shrink-0 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
              {{ isRtl ? 'البريد الإلكتروني المهني' : 'Work Email' }}
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              autocomplete="username"
              autofocus
              inputmode="email"
              class="block w-full text-sm rounded-lg px-3.5 py-2.5 bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-blue/40 focus:border-brand-blue"
              placeholder="name@novixa.dev"
            />
          </div>

          <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
              {{ isRtl ? 'كلمة المرور' : 'Password' }}
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                class="block w-full text-sm rounded-lg ps-3.5 pe-16 py-2.5 bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-blue/40 focus:border-brand-blue"
              />
              <button
                type="button"
                class="absolute inset-y-0 end-0 px-3 text-[11px] font-semibold text-slate-400 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue rounded-e-lg"
                :aria-pressed="showPassword"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? (isRtl ? 'إخفاء' : 'Hide') : (isRtl ? 'إظهار' : 'Show') }}
              </button>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-brand-blue hover:bg-brand-blue-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue transition-all disabled:opacity-50"
            >
              <svg v-if="loading" class="animate-spin -ms-1 me-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              {{ isRtl ? 'تسجيل الدخول' : 'Sign In' }}
            </button>
          </div>
        </form>

        <!-- Quick demo logins: development builds only, never shipped to production -->
        <!-- Quick Demo Switcher -->
        <div v-if="isDev" class="mt-6 pt-6 border-t border-slate-800">
          <p class="text-[11px] font-semibold text-slate-400 mb-2.5 text-center uppercase tracking-wider">
            {{ isRtl ? 'حسابات التجربة السريعة (اختر للتعبئة التلقائية):' : 'Quick Demo Logins:' }}
          </p>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              class="p-2 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-start transition-colors"
              @click="setCredentials('ahmed@novixa.dev', 'password123')"
            >
              <span class="block text-xs font-semibold text-white">أحمد الحضرمي</span>
              <span class="block text-[10px] text-brand-blue font-medium">Owner (المالك)</span>
            </button>

            <button
              type="button"
              class="p-2 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-start transition-colors"
              @click="setCredentials('tariq@novixa.dev', 'password123')"
            >
              <span class="block text-xs font-semibold text-white">طارق باوزير</span>
              <span class="block text-[10px] text-brand-teal font-medium">Tech Lead (مدير مشاريع)</span>
            </button>

            <button
              type="button"
              class="p-2 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-start transition-colors"
              @click="setCredentials('omar@novixa.dev', 'password123')"
            >
              <span class="block text-xs font-semibold text-white">عمر بن محفوظ</span>
              <span class="block text-[10px] text-amber-400 font-medium">Engineer (مهندس)</span>
            </button>

            <button
              type="button"
              class="p-2 rounded-lg bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-start transition-colors"
              @click="setCredentials('saeed@darhadramout.com', 'password123')"
            >
              <span class="block text-xs font-semibold text-white">سعيد الكندي</span>
              <span class="block text-[10px] text-purple-400 font-medium">Guest (عميل/مراقب)</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

const { isRtl, locale, setLocale } = useI18n()
const { login } = useAuth()
const router = useRouter()
const route = useRoute()

const isDev = import.meta.dev
const email = ref(isDev ? 'ahmed@novixa.dev' : '')
const password = ref(isDev ? 'password123' : '')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')

function toggleLocale() {
  setLocale(locale.value === 'ar' ? 'en' : 'ar')
}

function setCredentials(e: string, p: string) {
  email.value = e
  password.value = p
  errorMessage.value = ''
}

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''

  try {
    await login(email.value, password.value)
    const redirect = route.query.redirect
    router.push(typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//') ? redirect : '/dashboard')
  } catch (err: any) {
    if (err?.data?.message) {
      errorMessage.value = pickLocalizedMessage(err.data.message, isRtl.value)
    } else if (err?.data?.errors?.email) {
      errorMessage.value = pickLocalizedMessage(err.data.errors.email[0], isRtl.value)
    } else {
      errorMessage.value = isRtl.value
        ? 'تعذر تسجيل الدخول. يرجى التحقق من صحة البيانات.'
        : 'Failed to sign in. Please verify your credentials.'
    }
  } finally {
    loading.value = false
  }
}
</script>
