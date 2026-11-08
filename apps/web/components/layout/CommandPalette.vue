<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 overflow-y-auto"
      @keydown.esc="close"
      @keydown.down.prevent="navigateDown"
      @keydown.up.prevent="navigateUp"
      @keydown.enter.prevent="selectActive"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="close" />

      <!-- Command Palette Modal -->
      <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl z-10 overflow-hidden">
        <!-- Search Input -->
        <div class="p-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
          <svg class="w-5 h-5 text-slate-400 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            ref="inputRef"
            v-model="query"
            type="text"
            class="w-full text-sm bg-transparent border-0 focus:outline-none focus:ring-0 text-slate-900 dark:text-slate-100 placeholder-slate-400"
            :placeholder="t('app.search_placeholder')"
            @input="handleInput"
          />
          <kbd class="text-[10px] font-mono text-slate-400 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 me-2">
            ESC
          </kbd>
        </div>

        <!-- Results List -->
        <div class="max-h-80 overflow-y-auto p-2">
          <div v-if="loading" class="py-8 text-center text-xs text-slate-400">
            {{ t('app.loading') }}
          </div>

          <div v-else-if="results.length === 0 && query.trim().length >= 2" class="py-8 text-center text-xs text-slate-400">
            {{ t('app.no_results') }}
          </div>

          <div v-else-if="results.length === 0" class="py-6 px-4 text-xs text-slate-400 text-center">
            {{ isRtl ? 'اكتب كلمة للبحث الفوري في المهام والمشاريع والعملاء...' : 'Type to search tasks, projects, products, and clients...' }}
          </div>

          <div v-else class="space-y-1">
            <div
              v-for="(item, idx) in results"
              :key="item.type + item.id"
              :class="[
                'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-colors cursor-pointer',
                activeIndex === idx
                  ? 'bg-brand-blue text-white'
                  : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200',
              ]"
              @click="goTo(item.url)"
              @mouseenter="activeIndex = idx"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <span
                  :class="[
                    'text-[10px] px-1.5 py-0.5 rounded font-mono uppercase',
                    activeIndex === idx ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'
                  ]"
                >
                  {{ item.type }}
                </span>
                <span class="truncate font-medium">{{ item.title }}</span>
                <span v-if="item.subtitle" :class="['text-xs truncate', activeIndex === idx ? 'text-white/80' : 'text-slate-400']">
                  — {{ item.subtitle }}
                </span>
              </div>

              <span
                v-if="item.badge"
                :class="[
                  'text-[11px] px-2 py-0.5 rounded-full capitalize',
                  activeIndex === idx ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'
                ]"
              >
                {{ item.badge }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const { t, isRtl } = useI18n()
const api = useApi()
const router = useRouter()

const query = ref('')
const loading = ref(false)
const results = ref<any[]>([])
const activeIndex = ref(0)
const inputRef = ref<HTMLInputElement | null>(null)

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    query.value = ''
    results.value = []
    activeIndex.value = 0
    nextTick(() => inputRef.value?.focus())
  }
})

let debounceTimer: any = null
function handleInput() {
  clearTimeout(debounceTimer)
  if (query.value.trim().length < 2) {
    results.value = []
    return
  }

  loading.value = true
  debounceTimer = setTimeout(async () => {
    try {
      const res = await api.get<{ results: any[] }>(`/search?q=${encodeURIComponent(query.value)}`)
      results.value = res.results || []
      activeIndex.value = 0
    } catch (e) {
      results.value = []
    } finally {
      loading.value = false
    }
  }, 200)
}

function navigateDown() {
  if (results.value.length === 0) return
  activeIndex.value = (activeIndex.value + 1) % results.value.length
}

function navigateUp() {
  if (results.value.length === 0) return
  activeIndex.value = (activeIndex.value - 1 + results.value.length) % results.value.length
}

function selectActive() {
  if (results.value[activeIndex.value]) {
    goTo(results.value[activeIndex.value].url)
  }
}

function goTo(url: string) {
  close()
  router.push(url)
}

function close() {
  emit('update:modelValue', false)
}
</script>
