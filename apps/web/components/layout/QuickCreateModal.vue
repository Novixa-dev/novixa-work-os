<template>
  <UiModal
    :model-value="modelValue"
    :title="isRtl ? 'إنشاء عنصر جديد' : 'Quick Create'"
    :subtitle="isRtl ? 'إضافة سريعة لمهمة أو مشروع أو عميل جديد' : 'Quickly record a new task, project, or client'"
    max-width="lg"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <!-- Tab Selector -->
    <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl mb-5">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        :class="[
          'flex-1 py-1.5 text-xs font-semibold rounded-lg transition-all',
          activeTab === tab.id
            ? 'bg-white dark:bg-slate-900 text-brand-blue shadow-sm'
            : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300',
        ]"
        @click="activeTab = tab.id"
      >
        {{ isRtl ? tab.nameAr : tab.nameEn }}
      </button>
    </div>

    <!-- Tab 1: Task -->
    <form v-if="activeTab === 'task'" class="space-y-4" @submit.prevent="submitTask">
      <UiInput
        v-model="taskForm.title_ar"
        :label="isRtl ? 'عنوان المهمة' : 'Task Title'"
        :placeholder="isRtl ? 'مثال: ربط بوابة الدفع، تدريب العميل...' : 'e.g. Integrate API, Train client...'"
        required
      />

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <UiSelect
          v-model="taskForm.project_id"
          :label="isRtl ? 'المشروع المرتبط' : 'Linked Project'"
          :options="projectOptions"
          :placeholder="isRtl ? 'بدون مشروع' : 'No Project'"
        />

        <UiSelect
          v-model="taskForm.priority"
          :label="isRtl ? 'الأولوية' : 'Priority'"
          :options="priorityOptions"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <UiSelect
          v-model="taskForm.assignee_id"
          :label="isRtl ? 'المسؤول' : 'Assignee'"
          :options="userOptions"
          :placeholder="isRtl ? 'تعيين لاحقاً' : 'Unassigned'"
        />

        <UiInput
          v-model="taskForm.due_date"
          type="date"
          :label="isRtl ? 'موعد الاستحقاق' : 'Due Date'"
        />
      </div>

      <UiTextarea
        v-model="taskForm.description_ar"
        :label="isRtl ? 'الوصف والتفاصيل (اختياري)' : 'Description (Optional)'"
        :rows="2"
      />

      <div class="pt-2 flex justify-end gap-2.5">
        <UiButton variant="outline" type="button" @click="$emit('update:modelValue', false)">
          {{ isRtl ? 'إلغاء' : 'Cancel' }}
        </UiButton>
        <UiButton variant="primary" type="submit" :loading="submitting">
          {{ isRtl ? 'إنشاء المهمة' : 'Create Task' }}
        </UiButton>
      </div>
    </form>

    <!-- Tab 2: Project -->
    <form v-else-if="activeTab === 'project'" class="space-y-4" @submit.prevent="submitProject">
      <div class="grid grid-cols-3 gap-3">
        <div class="col-span-1">
          <UiInput
            v-model="projectForm.code"
            :label="isRtl ? 'الرمز' : 'Code'"
            placeholder="PRJ-XX"
            required
          />
        </div>
        <div class="col-span-2">
          <UiInput
            v-model="projectForm.name_ar"
            :label="isRtl ? 'اسم المشروع' : 'Project Name'"
            required
          />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <UiSelect
          v-model="projectForm.type"
          :label="isRtl ? 'نوع المشروع' : 'Type'"
          :options="projectTypeOptions"
        />
        <UiSelect
          v-model="projectForm.priority"
          :label="isRtl ? 'الأولوية' : 'Priority'"
          :options="priorityOptions"
        />
      </div>

      <div class="pt-2 flex justify-end gap-2.5">
        <UiButton variant="outline" type="button" @click="$emit('update:modelValue', false)">
          {{ isRtl ? 'إلغاء' : 'Cancel' }}
        </UiButton>
        <UiButton variant="primary" type="submit" :loading="submitting">
          {{ isRtl ? 'إنشاء المشروع' : 'Create Project' }}
        </UiButton>
      </div>
    </form>

    <!-- Tab 3: Client -->
    <form v-else class="space-y-4" @submit.prevent="submitClient">
      <UiInput
        v-model="clientForm.name_ar"
        :label="isRtl ? 'اسم العميل / الشركة' : 'Client Name'"
        placeholder="مثال: شركة الريان اللوجستية"
        required
      />

      <div class="grid grid-cols-2 gap-3">
        <UiInput
          v-model="clientForm.city"
          :label="isRtl ? 'المدينة' : 'City'"
          placeholder="المكلا، سيئون، الرياض..."
        />
        <UiInput
          v-model="clientForm.phone"
          :label="isRtl ? 'الهاتف / واتساب' : 'Phone'"
          placeholder="+967..."
        />
      </div>

      <div class="pt-2 flex justify-end gap-2.5">
        <UiButton variant="outline" type="button" @click="$emit('update:modelValue', false)">
          {{ isRtl ? 'إلغاء' : 'Cancel' }}
        </UiButton>
        <UiButton variant="primary" type="submit" :loading="submitting">
          {{ isRtl ? 'إنشاء العميل' : 'Create Client' }}
        </UiButton>
      </div>
    </form>
  </UiModal>
</template>

<script setup lang="ts">
import UiButton from '~/components/ui/UiButton.vue'
import UiInput from '~/components/ui/UiInput.vue'
import UiModal from '~/components/ui/UiModal.vue'
import UiSelect from '~/components/ui/UiSelect.vue'
import UiTextarea from '~/components/ui/UiTextarea.vue'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'created', entityType: string): void
}>()

const { isRtl, t } = useI18n()
const api = useApi()

const activeTab = ref<'task' | 'project' | 'client'>('task')
const submitting = ref(false)

const tabs = [
  { id: 'task', nameAr: 'مهمة جديدة', nameEn: 'Task' },
  { id: 'project', nameAr: 'مشروع جديد', nameEn: 'Project' },
  { id: 'client', nameAr: 'عميل جديد', nameEn: 'Client' },
]

const projectOptions = ref<{ label: string; value: number }[]>([])
const userOptions = ref<{ label: string; value: number }[]>([])

const priorityOptions = computed(() => [
  { label: t('priority.low'), value: 'low' },
  { label: t('priority.medium'), value: 'medium' },
  { label: t('priority.high'), value: 'high' },
  { label: t('priority.urgent'), value: 'urgent' },
])

const projectTypeOptions = computed(() => [
  { label: isRtl.value ? 'منتج داخلي' : 'Internal Product', value: 'internal_product' },
  { label: isRtl.value ? 'تسليم عميل' : 'Client Delivery', value: 'client_delivery' },
  { label: isRtl.value ? 'عمليات' : 'Operational', value: 'operational' },
])

const taskForm = ref({
  title_ar: '',
  project_id: '',
  priority: 'medium',
  assignee_id: '',
  due_date: '',
  description_ar: '',
})

const projectForm = ref({
  code: '',
  name_ar: '',
  type: 'internal_product',
  priority: 'medium',
})

const clientForm = ref({
  name_ar: '',
  city: '',
  phone: '',
  type: 'company',
  status: 'lead',
})

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    loadLookups()
  }
})

async function loadLookups() {
  try {
    const [projRes, userRes] = await Promise.all([
      api.get<{ data: any[] }>('/projects'),
      api.get<{ data: any[] }>('/users'),
    ])

    projectOptions.value = (projRes.data || []).map(p => ({
      label: `${p.code} - ${p.name_ar}`,
      value: p.id,
    }))

    userOptions.value = (userRes.data || []).map(u => ({
      label: u.name,
      value: u.id,
    }))
  } catch (e) {}
}

async function submitTask() {
  submitting.value = true
  try {
    await api.post('/tasks', {
      title_ar: taskForm.value.title_ar,
      project_id: taskForm.value.project_id || null,
      priority: taskForm.value.priority,
      assignee_id: taskForm.value.assignee_id || null,
      due_date: taskForm.value.due_date || null,
      description_ar: taskForm.value.description_ar || null,
      status: 'todo',
    })

    taskForm.value.title_ar = ''
    taskForm.value.description_ar = ''
    emit('created', 'task')
    emit('update:modelValue', false)
  } catch (e) {
  } finally {
    submitting.value = false
  }
}

async function submitProject() {
  submitting.value = true
  try {
    await api.post('/projects', {
      code: projectForm.value.code,
      name_ar: projectForm.value.name_ar,
      type: projectForm.value.type,
      priority: projectForm.value.priority,
      status: 'active',
    })

    projectForm.value.code = ''
    projectForm.value.name_ar = ''
    emit('created', 'project')
    emit('update:modelValue', false)
  } catch (e) {
  } finally {
    submitting.value = false
  }
}

async function submitClient() {
  submitting.value = true
  try {
    await api.post('/clients', clientForm.value)
    clientForm.value.name_ar = ''
    emit('created', 'client')
    emit('update:modelValue', false)
  } catch (e) {
  } finally {
    submitting.value = false
  }
}
</script>
