<template>
  <div
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm transition-shadow hover:shadow-md/50 overflow-hidden"
  >
    <div
      v-if="$slots.header || title"
      class="px-5 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between"
    >
      <slot name="header">
        <div>
          <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ title }}</h3>
          <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ subtitle }}</p>
        </div>
      </slot>
      <div v-if="$slots.action" class="ms-auto flex items-center gap-2">
        <slot name="action" />
      </div>
    </div>

    <div :class="['px-5 py-4', bodyClass]">
      <slot />
    </div>

    <div
      v-if="$slots.footer"
      class="px-5 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 flex items-center justify-between"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  subtitle?: string
  bodyClass?: string
}>()
</script>
