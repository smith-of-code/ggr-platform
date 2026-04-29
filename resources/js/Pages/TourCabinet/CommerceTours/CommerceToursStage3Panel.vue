<template>
  <div class="space-y-3">
    <h4 class="text-base font-semibold text-slate-900">{{ subject || 'Заявка принята' }}</h4>
    <p class="whitespace-pre-line text-sm text-slate-700">{{ body }}</p>
  </div>
</template>

<script setup>
defineProps({
  subject: { type: String, default: '' },
  body: { type: String, default: '' },
})
</script>
