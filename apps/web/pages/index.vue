<template>
  <div class="p-8 text-center text-xs text-slate-400">
    Redirecting to dashboard...
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: [
    () => {
      return navigateTo('/dashboard')
    },
  ],
})
</script>
