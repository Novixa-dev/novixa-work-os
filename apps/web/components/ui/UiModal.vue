<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
        @keydown.esc="close"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
          @click="close"
        />

        <!-- Dialog Container -->
        <div
          class="relative w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl z-10 overflow-hidden transform transition-all my-8"
          :class="maxWidthClasses[maxWidth]"
        >
          <!-- Header -->
          <div
            v-if="title || $slots.header"
            class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between"
          >
            <slot name="header">
              <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ title }}</h3>
                <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ subtitle }}</p>
              </div>
            </slot>
            <button
              type="button"
              class="rounded-lg p-1 text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none"
              @click="close"
            >
              <span class="sr-only">Close</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-5 max-h-[calc(85vh-130px)] overflow-y-auto">
            <slot />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5"
          >
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
interface Props {
  modelValue: boolean
  title?: string
  subtitle?: string
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl'
}

const props = withDefaults(defineProps<Props>(), {
  maxWidth: 'lg',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
}>()

function close() {
  emit('update:modelValue', false)
  emit('close')
}

const maxWidthClasses = {
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-lg',
  xl: 'max-w-xl',
  '2xl': 'max-w-2xl',
  '3xl': 'max-w-3xl',
}
</script>
