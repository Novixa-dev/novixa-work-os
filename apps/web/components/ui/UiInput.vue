<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <div class="relative rounded-lg shadow-sm">
      <div v-if="$slots.prefix" class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-slate-400">
        <slot name="prefix" />
      </div>

      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'block w-full text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
          $slots.prefix ? 'ps-9' : 'ps-3.5',
          $slots.suffix ? 'pe-9' : 'pe-3.5',
          'py-2',
          error
            ? 'border-rose-500 text-rose-900 dark:text-rose-200 placeholder-rose-300 focus:ring-rose-500/30 border'
            : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-brand-blue/30 focus:border-brand-blue border',
        ]"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @blur="$emit('blur', $event)"
      />

      <div v-if="$slots.suffix" class="absolute inset-y-0 end-0 pe-3 flex items-center text-slate-400">
        <slot name="suffix" />
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-rose-500">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue?: string | number
  label?: string
  id?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
  hint?: string
}

withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
  required: false,
})

defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'blur', event: FocusEvent): void
}>()
</script>
