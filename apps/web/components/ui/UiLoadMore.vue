<template>
  <div v-if="total > shown" class="flex flex-col items-center gap-2 py-4">
    <p class="text-xs text-slate-500 dark:text-slate-400">
      {{ isRtl ? `عرض ${shown} من ${total}` : `Showing ${shown} of ${total}` }}
    </p>
    <UiButton variant="outline" size="sm" :loading="loading" @click="$emit('more')">
      {{ isRtl ? 'عرض المزيد' : 'Load more' }}
    </UiButton>
  </div>
</template>

<script setup lang="ts">
defineProps<{ shown: number; total: number; loading?: boolean }>()
defineEmits<{ (e: 'more'): void }>()
const { isRtl } = useI18n()
</script>
