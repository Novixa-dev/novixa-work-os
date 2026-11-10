export interface AuthUser {
  id: number
  name: string
  email: string
  role: 'owner' | 'admin' | 'manager' | 'member' | 'guest'
  job_title?: string
  locale: 'ar' | 'en'
  timezone: string
  theme: 'dark' | 'light' | 'system'
  avatar_url?: string
  phone?: string
  is_active: boolean
}

const currentUser = ref<AuthUser | null>(null)
const isAuthLoaded = ref(false)

export function useAuth() {
  const api = useApi()
  const router = useRouter()
  const { setLocale } = useI18n()
  const { setTheme } = useTheme()

  const isAuthenticated = computed(() => currentUser.value !== null)
  const isOwner = computed(() => currentUser.value?.role === 'owner')
  const isAdmin = computed(() => ['owner', 'admin'].includes(currentUser.value?.role || ''))
  const isManager = computed(() => ['owner', 'admin', 'manager'].includes(currentUser.value?.role || ''))
  const isGuest = computed(() => currentUser.value?.role === 'guest')

  async function initAuth() {
    if (!import.meta.client || isAuthLoaded.value) return

    const cachedUser = localStorage.getItem('novixa_user')
    const token = localStorage.getItem('novixa_token')

    if (cachedUser && token) {
      try {
        currentUser.value = JSON.parse(cachedUser)
      } catch (e) {
        localStorage.removeItem('novixa_user')
      }
    }

    if (token) {
      try {
        await fetchUser()
      } catch (e: any) {
        // Only an explicit 401 means the token is invalid; keep the cached session on network errors.
        if (e?.response?.status === 401 || e?.statusCode === 401) {
          currentUser.value = null
          localStorage.removeItem('novixa_token')
          localStorage.removeItem('novixa_user')
        }
      }
    }

    isAuthLoaded.value = true
  }

  async function login(email: string, password: string):Promise<AuthUser> {
    const res = await api.post<{ user: AuthUser; token: string }>('/auth/login', { email, password }, { silent: true })

    currentUser.value = res.user
    if (import.meta.client) {
      localStorage.setItem('novixa_token', res.token)
      localStorage.setItem('novixa_user', JSON.stringify(res.user))

      if (res.user.locale) {
        setLocale(res.user.locale)
      }
      if (res.user.theme) {
        setTheme(res.user.theme)
      }
    }

    return res.user
  }

  async function fetchUser(): Promise<AuthUser | null> {
    const res = await api.get<{ user: AuthUser }>('/auth/me')
    currentUser.value = res.user
    if (import.meta.client) {
      localStorage.setItem('novixa_user', JSON.stringify(res.user))
    }
    return res.user
  }

  async function logout() {
    try {
      await api.post('/auth/logout', undefined, { silent: true })
    } catch (e) {
      // Ignore network failure on logout
    } finally {
      currentUser.value = null
      if (import.meta.client) {
        localStorage.removeItem('novixa_token')
        localStorage.removeItem('novixa_user')
      }
      router.push('/auth/login')
    }
  }

  async function updatePreferences(prefs: Partial<AuthUser> & { current_password?: string; new_password?: string }) {
    const res = await api.patch<{ user: AuthUser; message: string }>('/auth/preferences', prefs)
    currentUser.value = res.user
    if (import.meta.client) {
      localStorage.setItem('novixa_user', JSON.stringify(res.user))
      if (res.user.locale) setLocale(res.user.locale)
      if (res.user.theme) setTheme(res.user.theme)
    }
    return res.user
  }

  return {
    user: readonly(currentUser),
    isAuthenticated,
    isOwner,
    isAdmin,
    isManager,
    isGuest,
    initAuth,
    login,
    logout,
    fetchUser,
    updatePreferences,
  }
}
