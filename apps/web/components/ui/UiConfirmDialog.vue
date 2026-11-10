<template>
  <UiModal
    :model-value="state.open"
    :title="state.title"
    max-width="sm"
    @update:model-value="(v: boolean) => !v && answer(false)"
  >
    <p v-if="state.message" class="text-sm text-slate-600 dark:text-slate-300">{{ state.message }}</p>
    <template #footer>
      <UiButton variant="outline" size="sm" @click="answer(false)">
        {{ state.cancelLabel || (isRtl ? 'إلغاء' : 'Cancel') }}
      </UiButton>
      <UiButton :variant="state.danger ? 'danger' : 'primary'" size="sm" @click="answer(true)">
        {{ state.confirmLabel || (isRtl ? 'تأكيد' : 'Confirm') }}
      </UiButton>
    </template>
  </UiModal>
</template>

<script setup lang="ts">
const { state, answer } = useConfirm()
const { isRtl } = useI18n()
</script>
