<template>
  <div class="space-y-4">
    <div class="rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-700">
      <p>
        <span class="font-semibold">Город:</span> {{ cityName || '—' }}
      </p>
      <p class="mt-1">
        <span class="font-semibold">Тур:</span> {{ tourName || '—' }}
      </p>
    </div>

    <div v-if="!hasCityForm" class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-900">
      Анкета для выбранного города пока не настроена. Обратитесь в поддержку или вернитесь к выбору города.
    </div>
    <p v-else class="text-sm text-slate-700">
      Откройте анкету доп. данных по выбранному городу — после успешной отправки конкурс перейдёт к экрану ожидания.
    </p>

    <div class="flex flex-wrap gap-3">
      <a
        v-if="hasCityForm && !locked"
        :href="route('tour-cabinet.commerce-tours.stage-2.form')"
      >
        <RButton type="button" variant="primary">
          Открыть анкету
        </RButton>
      </a>

      <form v-if="!locked" @submit.prevent="reopen">
        <RButton
          type="submit"
          variant="outline"
          :loading="reopenForm.processing"
          :disabled="reopenForm.processing"
        >
          Изменить выбор
        </RButton>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  cityName: { type: String, default: '' },
  tourName: { type: String, default: '' },
  hasCityForm: { type: Boolean, default: false },
  locked: { type: Boolean, default: false },
})

const reopenForm = useForm({})

function reopen() {
  reopenForm.post(route('tour-cabinet.commerce-tours.reopen-selection'), {
    preserveScroll: true,
  })
}
</script>
