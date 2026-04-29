<template>
  <div class="space-y-4">
    <p
      v-if="locked"
      class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
    >
      <template v-if="currentStage <= 1">
        Этот этап станет доступен после нажатия кнопки «Перейти к этапу 2 →» на этапе 1.
      </template>
      <template v-else>
        Анкета этапа 2 уже отправлена. Редактирование недоступно.
      </template>
    </p>

    <template v-if="!locked">
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
          v-if="hasCityForm"
          :href="route('tour-cabinet.commerce-tours.stage-2.form')"
        >
          <RButton type="button" variant="primary">
            Открыть анкету
          </RButton>
        </a>

        <form @submit.prevent="reopen">
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
    </template>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  cityName: { type: String, default: '' },
  tourName: { type: String, default: '' },
  hasCityForm: { type: Boolean, default: false },
  locked: { type: Boolean, default: false },
  currentStage: { type: Number, default: 1 },
})

const reopenForm = useForm({})

function reopen() {
  reopenForm.post(route('tour-cabinet.commerce-tours.reopen-selection'), {
    preserveScroll: true,
  })
}
</script>
