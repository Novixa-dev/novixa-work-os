export type ToastKind = 'success' | 'error' | 'info'

export interface ToastItem {
  id: number
  kind: ToastKind
  message: string
}

const toasts = ref<ToastItem[]>([])
let nextId = 1

export function useToast() {
  function dismiss(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function push(kind: ToastKind, message: string, durationMs = kind === 'error' ? 6000 : 3500) {
    // De-duplicate identical messages already on screen (e.g. repeated failures).
    if (toasts.value.some((t) => t.kind === kind && t.message === message)) return
    const id = nextId++
    toasts.value = [...toasts.value.slice(-3), { id, kind, message }]
    if (import.meta.client) setTimeout(() => dismiss(id), durationMs)
  }

  return {
    toasts: readonly(toasts),
    dismiss,
    success: (message: string) => push('success', message),
    error: (message: string) => push('error', message),
    info: (message: string) => push('info', message),
  }
}
