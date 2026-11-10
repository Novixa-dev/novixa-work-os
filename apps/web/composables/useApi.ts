interface ApiOptions extends NonNullable<Parameters<typeof $fetch>[1]> {
  /** Suppress the automatic success/error toast (the caller shows its own feedback). */
  silent?: boolean
}

export function useApi() {
  const config = useRuntimeConfig()
  const toast = useToast()
  const { isRtl } = useI18n()
  const configuredBase = config.public.apiBase || 'http://127.0.0.1:8000/api/v1'
  // A root-relative base ("/api/v1") must not be resolved against the SPA's baseURL ("/app/"),
  // so anchor it to the current origin in the browser.
  const apiBase = import.meta.client && configuredBase.startsWith('/')
    ? `${location.origin}${configuredBase}`
    : configuredBase

  async function client<T = any>(endpoint: string, { silent = false, ...options }: ApiOptions = {}): Promise<T> {
    const token = import.meta.client ? localStorage.getItem('novixa_token') : null

    const defaultHeaders: Record<string, string> = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }

    if (token) {
      defaultHeaders['Authorization'] = `Bearer ${token}`
    }

    const url = endpoint.startsWith('http') ? endpoint : `${apiBase}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`

    const method = String(options.method || 'GET').toUpperCase()
    const isMutation = method !== 'GET'

    try {
      const result = await $fetch<T>(url, {
        credentials: 'include',
        ...options,
        headers: {
          ...defaultHeaders,
          ...((options.headers as Record<string, string>) || {}),
        },
      })

      const message = (result as { message?: unknown } | null)?.message
      if (isMutation && !silent && typeof message === 'string' && message) {
        toast.success(pickLocalizedMessage(message, isRtl.value))
      }
      return result
    } catch (err: any) {
      const status: number | undefined = err?.response?.status
      if (status === 401 && import.meta.client) {
        localStorage.removeItem('novixa_token')
        localStorage.removeItem('novixa_user')
        const router = useRouter()
        if (router.currentRoute.value.path !== '/auth/login') {
          router.push('/auth/login')
        }
      }
      if (!silent && status !== 401) {
        toast.error(describeError(err, isRtl.value))
      }
      throw err
    }
  }

  return {
    client,
    get: <T = any>(url: string, options?: any) => client<T>(url, { method: 'GET', ...options }),
    post: <T = any>(url: string, body?: any, options?: ApiOptions) => client<T>(url, { method: 'POST', body, ...options }),
    patch: <T = any>(url: string, body?: any, options?: any) => client<T>(url, { method: 'PATCH', body, ...options }),
    put: <T = any>(url: string, body?: any, options?: any) => client<T>(url, { method: 'PUT', body, ...options }),
    delete: <T = any>(url: string, options?: any) => client<T>(url, { method: 'DELETE', ...options }),
  }
}

function describeError(err: any, arabic: boolean): string {
  const data = err?.data
  const first = data?.errors ? (Object.values(data.errors)[0] as string[] | undefined)?.[0] : undefined
  const raw: unknown = first || data?.message
  if (typeof raw === 'string' && raw) return pickLocalizedMessage(raw, arabic)

  switch (err?.response?.status) {
    case 403: return arabic ? 'ليست لديك صلاحية لتنفيذ هذا الإجراء.' : 'You do not have permission to do that.'
    case 404: return arabic ? 'العنصر المطلوب غير موجود.' : 'The requested item was not found.'
    case 429: return arabic ? 'محاولات كثيرة. حاول بعد قليل.' : 'Too many attempts. Please try again shortly.'
    case undefined: return arabic ? 'تعذّر الاتصال بالخادم. تحقّق من الإنترنت.' : 'Cannot reach the server. Check your connection.'
    default: return arabic ? 'حدث خطأ غير متوقع. حاول مرة أخرى.' : 'Something went wrong. Please try again.'
  }
}
