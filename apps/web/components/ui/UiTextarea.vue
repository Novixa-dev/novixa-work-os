<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <textarea
      :id="id"
      :value="modelValue"
      :rows="rows"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :class="[
        'block w-full text-sm rounded-lg px-3.5 py-2 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
        error
          ? 'border-rose-500 text-rose-900 dark:text-rose-200 placeholder-rose-300 focus:ring-rose-500/30 border'
          : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-brand-blue/30 focus:border-brand-blue border',
      ]"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />

    <p v-if="error" class="mt-1 text-xs text-rose-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue?: string
  label?: string
  id?: string
  rows?: number
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
}

withDefaults(defineProps<Props>(), {
  rows: 3,
  disabled: false,
  required: false,
})

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>
