<template>
  <div class="antialiased selection:bg-brand-blue selection:text-white">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
const { initLocale } = useI18n()
const { initTheme } = useTheme()

onMounted(() => {
  initLocale()
  initTheme()
})
</script>
