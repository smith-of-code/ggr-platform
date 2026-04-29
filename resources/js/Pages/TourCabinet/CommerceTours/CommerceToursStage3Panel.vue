<template>
  <div class="space-y-3">
    <p
      v-if="locked"
      class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
    >
      Этот этап станет доступен после успешной отправки анкеты на этапе 2.
    </p>

    <template v-else>
      <h4 class="text-base font-semibold text-slate-900">{{ subject || 'Заявка принята' }}</h4>
      <p class="whitespace-pre-line text-sm text-slate-700">{{ body }}</p>
    </template>
  </div>
</template>

<script setup>
defineProps({
  subject: { type: String, default: '' },
  body: { type: String, default: '' },
  locked: { type: Boolean, default: false },
})
</script>
