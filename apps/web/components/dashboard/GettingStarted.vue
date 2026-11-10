<template>
  <section
    v-if="visible"
    class="rounded-2xl border border-primary-200 dark:border-primary-900 bg-primary-50/60 dark:bg-primary-950/40 p-5 sm:p-6"
    aria-labelledby="getting-started-title"
  >
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 id="getting-started-title" class="text-base font-bold text-slate-900 dark:text-slate-100">
          {{ isRtl ? 'ابدأ من هنا: جهّز مساحة عملك' : 'Get started: set up your workspace' }}
        </h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          {{ isRtl ? 'مساحة العمل فارغة حالياً. هذه الخطوات تجعلها جاهزة للفريق في دقائق.' : 'Your workspace is empty. These steps get it ready for the team in minutes.' }}
        </p>
      </div>
      <button
        type="button"
        class="shrink-0 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue"
        @click="dismiss"
      >
        {{ isRtl ? 'إخفاء' : 'Dismiss' }}
      </button>
    </div>

    <ol class="mt-4 grid gap-2.5 sm:grid-cols-2">
      <li v-for="(step, i) in steps" :key="step.to">
        <NuxtLink
          :to="step.to"
          class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 text-sm hover:border-primary-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-blue transition-colors"
        >
          <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-primary-600 text-[11px] font-bold text-white">{{ i + 1 }}</span>
          <span class="flex-1">
            <span class="block font-semibold text-slate-800 dark:text-slate-100">{{ isRtl ? step.ar : step.en }}</span>
            <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ isRtl ? step.hintAr : step.hintEn }}</span>
          </span>
        </NuxtLink>
      </li>
    </ol>

    <p class="mt-4 text-[11px] text-slate-500 dark:text-slate-400">
      {{ isRtl ? 'نصيحة: اضغط' : 'Tip: press' }}
      <kbd class="rounded border border-slate-300 dark:border-slate-700 px-1.5 py-0.5 font-mono">Ctrl+K</kbd>
      {{ isRtl ? 'للبحث السريع، و' : 'to search, and' }}
      <kbd class="rounded border border-slate-300 dark:border-slate-700 px-1.5 py-0.5 font-mono">C</kbd>
      {{ isRtl ? 'للإنشاء السريع من أي صفحة.' : 'to quick-create from any page.' }}
    </p>
  </section>
</template>

<script setup lang="ts">
const STORAGE_KEY = 'novixa_getting_started_dismissed'
const { isRtl } = useI18n()
const { isAdmin } = useAuth()

const dismissed = ref(false)
onMounted(() => {
  try {
    dismissed.value = localStorage.getItem(STORAGE_KEY) === '1'
  } catch {
    /* storage unavailable: keep showing */
  }
})

function dismiss() {
  dismissed.value = true
  try {
    localStorage.setItem(STORAGE_KEY, '1')
  } catch {
    /* ignore */
  }
}

const allSteps = [
  { to: '/settings', ar: 'أكمل بيانات الشركة', en: 'Complete company details', hintAr: 'الاسم والتواصل والعملة والمنطقة الزمنية', hintEn: 'Name, contact, currency and timezone', adminOnly: true },
  { to: '/settings', ar: 'ادعُ أعضاء الفريق', en: 'Invite your team', hintAr: 'أضف المستخدمين وحدد أدوارهم', hintEn: 'Add users and assign roles', adminOnly: true },
  { to: '/products', ar: 'أضف منتجاتك', en: 'Add your products', hintAr: 'عقار، سيرف، وغيرها من المنتجات', hintEn: 'Aqar, Serve and your other products', adminOnly: false },
  { to: '/crm', ar: 'أضف أول عميل', en: 'Add your first client', hintAr: 'سجّل العملاء والفرص والمتابعات', hintEn: 'Track clients, deals and follow-ups', adminOnly: false },
  { to: '/projects', ar: 'أنشئ أول مشروع', en: 'Create your first project', hintAr: 'حدد المسؤول والمراحل والموعد', hintEn: 'Set an owner, milestones and a deadline', adminOnly: false },
  { to: '/tasks', ar: 'أنشئ أول مهمة', en: 'Create your first task', hintAr: 'وزّع العمل وتابع الإنجاز', hintEn: 'Assign work and track progress', adminOnly: false },
]

const steps = computed(() => allSteps.filter((s) => !s.adminOnly || isAdmin.value))
const visible = computed(() => !dismissed.value)
</script>
