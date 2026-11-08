<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <div class="relative rounded-lg shadow-sm">
      <select
        :id="id"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="[
          'block w-full text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-slate-50 disabled:dark:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed',
          'ps-3.5 pe-9 py-2',
          error
            ? 'border-rose-500 text-rose-900 dark:text-rose-200 focus:ring-rose-500/30 border'
            : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-brand-blue/30 focus:border-brand-blue border',
        ]"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      >
        <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
        <option
          v-for="opt in normalizedOptions"
          :key="opt.value"
          :value="opt.value"
        >
          {{ opt.label }}
        </option>
      </select>

      <div class="absolute inset-y-0 end-0 pe-2.5 flex items-center pointer-events-none text-slate-400">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-xs text-rose-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
interface Option {
  label: string
  value: string | number
}

interface Props {
  modelValue?: string | number
  label?: string
  id?: string
  options: (Option | string | number)[]
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  required: false,
})

defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const normalizedOptions = computed<Option[]>(() => {
  return props.options.map(opt => {
    if (typeof opt === 'object' && opt !== null) {
      return opt
    }
    return { label: String(opt), value: opt }
  })
})
</script>
