export function useApi() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase || 'http://127.0.0.1:8000/api/v1'

  async function client<T = any>(endpoint: string, options: Parameters<typeof $fetch>[1] = {}): Promise<T> {
    const token = import.meta.client ? localStorage.getItem('novixa_token') : null

    const defaultHeaders: Record<string, string> = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }

    if (token) {
      defaultHeaders['Authorization'] = `Bearer ${token}`
    }

    const url = endpoint.startsWith('http') ? endpoint : `${apiBase}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`

    try {
      return await $fetch<T>(url, {
        credentials: 'include',
        ...options,
        headers: {
          ...defaultHeaders,
          ...((options.headers as Record<string, string>) || {}),
        },
      })
    } catch (err: any) {
      if (err?.response?.status === 401 && import.meta.client) {
        localStorage.removeItem('novixa_token')
        localStorage.removeItem('novixa_user')
        const router = useRouter()
        if (router.currentRoute.value.path !== '/auth/login') {
          router.push('/auth/login')
        }
      }
      throw err
    }
  }

  return {
    client,
    get: <T = any>(url: string, options?: any) => client<T>(url, { method: 'GET', ...options }),
    post: <T = any>(url: string, body?: any, options?: any) => client<T>(url, { method: 'POST', body, ...options }),
    patch: <T = any>(url: string, body?: any, options?: any) => client<T>(url, { method: 'PATCH', body, ...options }),
    put: <T = any>(url: string, body?: any, options?: any) => client<T>(url, { method: 'PUT', body, ...options }),
    delete: <T = any>(url: string, options?: any) => client<T>(url, { method: 'DELETE', ...options }),
  }
}
