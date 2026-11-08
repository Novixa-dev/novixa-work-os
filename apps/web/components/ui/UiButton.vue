<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none',
      sizeClasses[size],
      variantClasses[variant],
      rounded ? 'rounded-full' : 'rounded-lg',
    ]"
    @click="$emit('click', $event)"
  >
    <svg
      v-if="loading"
      class="animate-spin -ms-1 me-2 h-4 w-4 text-current"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>

    <slot name="prefix" />
    <span :class="{ 'opacity-90': loading }"><slot /></span>
    <slot name="suffix" />
  </button>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  loading?: boolean
  rounded?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  rounded: false,
})

defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()

const sizeClasses = {
  sm: 'px-2.5 py-1.5 text-xs gap-1.5',
  md: 'px-3.5 py-2 text-sm gap-2',
  lg: 'px-5 py-2.5 text-base gap-2.5',
}

const variantClasses = {
  primary: 'bg-brand-blue hover:bg-brand-blue-dark text-white shadow-sm focus:ring-brand-blue/50 dark:focus:ring-offset-slate-900',
  secondary: 'bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-100 focus:ring-slate-400',
  outline: 'border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-slate-400',
  ghost: 'hover:bg-slate-100 dark:hover:bg-slate-800/60 text-slate-600 dark:text-slate-300 focus:ring-slate-400',
  danger: 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500/50',
}
</script>
