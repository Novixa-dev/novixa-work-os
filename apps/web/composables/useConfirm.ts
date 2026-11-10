export interface ConfirmOptions {
  title: string
  message?: string
  confirmLabel?: string
  cancelLabel?: string
  /** Style the confirm button as destructive. */
  danger?: boolean
}

interface ConfirmState extends ConfirmOptions {
  open: boolean
  resolve?: (value: boolean) => void
}

const state = ref<ConfirmState>({ open: false, title: '' })

/** Promise-based confirmation dialog: `if (await confirm({ title })) { ... }`. */
export function useConfirm() {
  function confirm(options: ConfirmOptions): Promise<boolean> {
    return new Promise((resolve) => {
      state.value = { ...options, open: true, resolve }
    })
  }

  function answer(value: boolean) {
    state.value.resolve?.(value)
    state.value = { open: false, title: '' }
  }

  return { state, confirm, answer }
}
