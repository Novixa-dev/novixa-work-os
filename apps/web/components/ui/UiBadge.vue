<template>
  <span
    :class="[
      'inline-flex items-center font-medium select-none',
      size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs',
      rounded ? 'rounded-full' : 'rounded-md',
      colorClasses[resolvedColor],
    ]"
  >
    <span v-if="dot" class="me-1.5 h-1.5 w-1.5 rounded-full" :class="dotClasses[resolvedColor]" />
    <slot />
  </span>
</template>

<script setup lang="ts">
type BadgeColor = 'slate' | 'blue' | 'teal' | 'amber' | 'rose' | 'emerald' | 'purple'

interface Props {
  color?: BadgeColor
  status?: string
  priority?: string
  size?: 'sm' | 'md'
  rounded?: boolean
  dot?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  color: 'slate',
  size: 'sm',
  rounded: true,
  dot: false,
})

const resolvedColor = computed<BadgeColor>(() => {
  if (props.priority) {
    switch (props.priority) {
      case 'urgent': return 'rose'
      case 'high': return 'amber'
      case 'medium': return 'blue'
      case 'low': return 'slate'
    }
  }

  if (props.status) {
    switch (props.status) {
      case 'done':
      case 'completed':
      case 'won':
        return 'emerald'
      case 'in_progress':
      case 'active':
        return 'blue'
      case 'in_review':
      case 'proposal':
      case 'negotiation':
        return 'amber'
      case 'todo':
      case 'planning':
      case 'qualified':
        return 'teal'
      case 'cancelled':
      case 'lost':
        return 'rose'
      case 'backlog':
      case 'archived':
      case 'lead':
        return 'slate'
    }
  }

  return props.color
})

const colorClasses: Record<BadgeColor, string> = {
  slate: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
  blue: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-900',
  teal: 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200/60 dark:border-teal-900',
  amber: 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900',
  rose: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900',
  emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900',
  purple: 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/60 dark:border-purple-900',
}

const dotClasses: Record<BadgeColor, string> = {
  slate: 'bg-slate-400 dark:bg-slate-500',
  blue: 'bg-blue-500',
  teal: 'bg-teal-500',
  amber: 'bg-amber-500',
  rose: 'bg-rose-500',
  emerald: 'bg-emerald-500',
  purple: 'bg-purple-500',
}
</script>
