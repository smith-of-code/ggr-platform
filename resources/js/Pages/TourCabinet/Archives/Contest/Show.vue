<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 font-sans text-slate-900">
    <Head :title="`Архивная заявка №${archive.id} — конкурс`" />
    <TourCabinetHeader max-width-class="max-w-4xl">
      <template #title>
        <h1 class="text-xl font-bold leading-tight tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">Архивная заявка №{{ archive.id }}</h1>
      </template>
      <template #subtitle>
        <p class="text-sm leading-relaxed text-slate-600">
          Конкурс. Отправлено: {{ formatDateTime(archive.submitted_at) }}.
        </p>
      </template>
      <template #toolbar>
        <Link :href="route('tour-cabinet.archives.contest.index')" class="w-full sm:w-auto">
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
        <RCard v-if="payload.progress" elevation="raised" class="p-5">
          <h2 class="text-base font-semibold text-slate-900">Прогресс и направление</h2>
          <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
            <div>
              <dt class="text-xs font-medium text-slate-500">Направление</dt>
              <dd class="text-slate-900">{{ payload.progress.direction_label || '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-slate-500">Текущий этап (на момент архивации)</dt>
              <dd class="text-slate-900">{{ payload.progress.current_stage }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-xs font-medium text-slate-500">Города в выборе (этап 1)</dt>
              <dd class="text-slate-900">
                <span v-if="payload.progress.selected_cities?.length">{{ payload.progress.selected_cities.map((c) => c.name).join(', ') }}</span>
                <span v-else class="text-slate-400">—</span>
              </dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-slate-500">Этап 2 отправлен</dt>
              <dd class="text-slate-900">{{ formatDateTime(payload.progress.stage2_submitted_at) }}</dd>
            </div>
          </dl>
        </RCard>

        <div>
          <h3 class="mb-3 text-sm font-semibold text-slate-800">Этап 1 — анкеты по городам</h3>
          <div v-if="payload.stage1_city_forms?.length" class="space-y-4">
            <RCard v-for="(block, idx) in payload.stage1_city_forms" :key="idx" elevation="raised" class="p-5">
              <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-100 pb-3">
                <p class="text-base font-semibold text-slate-900">{{ block.city_name }}</p>
                <p class="text-xs text-slate-500">Ответ формы: {{ formatDateTime(block.submitted_at) }}</p>
              </div>
              <dl v-if="block.responses?.length" class="mt-4 space-y-3">
                <div v-for="(r, j) in block.responses" :key="j">
                  <dt class="text-xs font-medium text-slate-500">{{ r.label }}</dt>
                  <dd class="mt-0.5 whitespace-pre-wrap text-sm text-slate-900">{{ r.value || '—' }}</dd>
                </div>
              </dl>
              <p v-else class="mt-3 text-sm text-slate-400">Нет полей ответа.</p>
            </RCard>
          </div>
          <RCard v-else elevation="raised" class="p-4 text-sm text-slate-500">Нет отправленных анкет по городам.</RCard>
        </div>

        <div>
          <h3 class="mb-3 text-sm font-semibold text-slate-800">Этап 2 — ответы на вопросы</h3>
          <RCard v-if="payload.stage2_qa?.length" elevation="raised" flush class="divide-y divide-slate-100">
            <div v-for="(qa, idx) in payload.stage2_qa" :key="idx" class="p-5">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Вопрос</p>
              <p class="mt-1 whitespace-pre-wrap text-sm font-medium text-slate-900">{{ qa.question_body }}</p>
              <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-500">Ответ</p>
              <p class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ qa.answer_text || '—' }}</p>
            </div>
          </RCard>
          <RCard v-else elevation="raised" class="p-4 text-sm text-slate-500">Нет ответов этапа 2.</RCard>
        </div>

        <div>
          <h3 class="mb-3 text-sm font-semibold text-slate-800">Этап 3 — проверочное задание</h3>
          <RCard v-if="payload.stage3" elevation="raised" class="p-5">
            <p class="text-sm font-semibold text-slate-900">{{ payload.stage3.assignment_title || 'Проверочное задание' }}</p>
            <p v-if="payload.stage3.task_body" class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ payload.stage3.task_body }}</p>
            <div class="mt-4 space-y-3 text-sm">
              <div v-if="payload.stage3.text">
                <p class="text-xs font-medium text-slate-500">Текст ответа</p>
                <p class="mt-1 whitespace-pre-wrap text-slate-900">{{ payload.stage3.text }}</p>
              </div>
              <div v-if="payload.stage3.video_url">
                <p class="text-xs font-medium text-slate-500">Видео</p>
                <a :href="payload.stage3.video_url" target="_blank" rel="noopener" class="text-rosatom-700 underline hover:text-rosatom-900">
                  {{ payload.stage3.video_url }}
                </a>
              </div>
              <div v-if="payload.stage3.attachment_original_name || payload.meta?.stage3_attachment_original_name">
                <p class="text-xs font-medium text-slate-500">Файл</p>
                <p class="mt-1 text-slate-900">{{ payload.stage3.attachment_original_name || payload.meta?.stage3_attachment_original_name }}</p>
              </div>
            </div>
          </RCard>
          <RCard v-else elevation="raised" class="p-4 text-sm text-slate-500">Нет данных этапа 3.</RCard>
        </div>
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
