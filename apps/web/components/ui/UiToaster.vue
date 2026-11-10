<template>
  <Teleport to="body">
    <div
      class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:end-6 sm:bottom-6 z-[60] flex flex-col gap-2 sm:w-96 pointer-events-none"
      aria-live="polite"
      aria-atomic="false"
    >
      <TransitionGroup
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-for="t in toasts"
          :key="t.id"
          :role="t.kind === 'error' ? 'alert' : 'status'"
          class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-lg bg-white dark:bg-slate-900"
          :class="styles[t.kind]"
        >
          <span class="mt-0.5 text-base leading-none" aria-hidden="true">{{ icons[t.kind] }}</span>
          <p class="flex-1 text-slate-800 dark:text-slate-100">{{ t.message }}</p>
          <button
            type="button"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue rounded"
            :aria-label="isRtl ? 'إغلاق' : 'Dismiss'"
            @click="dismiss(t.id)"
          >
            ✕
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const { toasts, dismiss } = useToast()
const { isRtl } = useI18n()

const styles = {
  success: 'border-emerald-200 dark:border-emerald-900',
  error: 'border-rose-200 dark:border-rose-900',
  info: 'border-slate-200 dark:border-slate-700',
}
const icons = { success: '✓', error: '!', info: 'i' }
</script>
