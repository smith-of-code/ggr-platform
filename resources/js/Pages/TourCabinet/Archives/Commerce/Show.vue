<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 font-sans text-slate-900">
    <Head :title="`Архивная заявка №${archive.id} — коммерческий тур`" />
    <TourCabinetHeader max-width-class="max-w-4xl">
      <template #title>
        <h1 class="text-xl font-bold leading-tight tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">Архивная заявка №{{ archive.id }}</h1>
      </template>
      <template #subtitle>
        <p class="text-sm leading-relaxed text-slate-600">
          Коммерческий тур. Отправлено: {{ formatDateTime(archive.submitted_at) }}.
        </p>
      </template>
      <template #toolbar>
        <Link :href="route('tour-cabinet.archives.commerce.index')" class="w-full sm:w-auto">
          <RButton type="button" variant="outline" size="sm" class="w-full min-h-[2.75rem] sm:min-h-0 sm:w-auto">
            ← К списку
          </RButton>
        </Link>
      </template>
    </TourCabinetHeader>

    <div class="mx-auto max-w-4xl px-3 pb-10 pt-4 sm:px-4 lg:px-6 sm:pt-6">
      <div class="mb-4 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 ring-1 ring-emerald-200">
        <CheckCircleIcon class="h-3.5 w-3.5 text-emerald-700" aria-hidden="true" />
        Отправлено
      </div>

      <section class="space-y-5">
        <RCard elevation="raised" class="p-5">
          <h2 class="text-base font-semibold text-slate-900">Выбор</h2>
          <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
            <div>
              <dt class="text-xs font-medium text-slate-500">Город</dt>
              <dd class="text-slate-900">{{ payload.city?.name || '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-slate-500">Тур</dt>
              <dd class="text-slate-900">{{ payload.tour?.title || '—' }}</dd>
            </div>
          </dl>
        </RCard>

        <RCard elevation="raised" class="p-5">
          <h2 class="text-base font-semibold text-slate-900">Этап 2 — анкета</h2>
          <p v-if="payload.lms_form?.title" class="mt-1 text-sm text-slate-600">{{ payload.lms_form.title }}</p>
          <dl v-if="payload.lms_form?.responses?.length" class="mt-4 space-y-3 text-sm">
            <div v-for="(r, idx) in payload.lms_form.responses" :key="idx">
              <dt class="text-xs font-medium text-slate-500">{{ r.label }}</dt>
              <dd class="mt-0.5 whitespace-pre-wrap text-slate-900">{{ r.value || '—' }}</dd>
            </div>
          </dl>
          <p v-else class="mt-3 text-sm text-slate-400">Нет полей ответа.</p>
        </RCard>

        <RCard v-if="payload.stage3_notification" elevation="raised" class="p-5">
          <h2 class="text-base font-semibold text-slate-900">Этап 3 — уведомление</h2>
          <p class="mt-2 text-sm font-medium text-slate-900">{{ payload.stage3_notification.subject || '—' }}</p>
          <p v-if="payload.stage3_notification.body" class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ payload.stage3_notification.body }}</p>
        </RCard>
      </section>
    </div>
  </div>
</template>

<script setup>
import { CheckCircleIcon } from '@heroicons/vue/24/outline'
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import TourCabinetHeader from '@/Components/TourCabinet/TourCabinetHeader.vue'

const props = defineProps({
  archive: { type: Object, required: true },
})

const payload = computed(() => props.archive?.payload ?? {})

function formatDateTime(iso) {
  if (!iso) return '—'
  try {
    const d = new Date(iso)
    return d.toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return iso
  }
}
</script>
