<template>
  <div class="space-y-8">
    <RCard v-for="c in configs" :key="c.direction_id" elevation="raised">
      <div class="flex flex-col gap-2 border-b border-gray-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-base font-semibold text-gray-900">{{ c.direction_label }}</h3>
          <p class="text-xs text-gray-500">
            <span v-if="c.is_saved" class="text-emerald-700">настройки сохранены</span>
            <span v-else class="text-amber-700">ещё не сохраняли</span>
          </p>
        </div>
      </div>
      <form class="mt-4 space-y-4" @submit.prevent="save(c.direction_id)">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Сколько этапов конкурса доступно в ЛК</label>
          <select
            v-model.number="drafts[c.direction_id].max_contest_stages"
            class="mt-1 w-full max-w-md rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm sm:w-auto"
          >
            <option :value="1">Только этап 1</option>
            <option :value="2">Этапы 1 и 2</option>
            <option :value="3">Все три этапа</option>
          </select>
          <p class="mt-1 text-xs text-gray-500">Вкладки и переходы в личном кабинете ограничиваются этим числом.</p>
          <p v-if="errorsFor(c.direction_id).max_contest_stages" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).max_contest_stages }}</p>
        </div>

        <div v-if="drafts[c.direction_id].max_contest_stages < 3" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
          Этап 3 в ЛК не используется — название и описание задания не обязательны. При выборе «Все три этапа» поля задания станут обязательными.
        </div>

        <template v-else>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Название задания (этап 3)</label>
            <input
              v-model="drafts[c.direction_id].title"
              type="text"
              maxlength="500"
              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
              required
            />
            <p v-if="errorsFor(c.direction_id).title" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).title }}</p>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Описание задания</label>
            <textarea
              v-model="drafts[c.direction_id].task_body"
              rows="6"
              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
              placeholder="Что нужно сделать участнику…"
            />
            <p v-if="errorsFor(c.direction_id).task_body" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).task_body }}</p>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Формат ответа в ЛК участника</label>
            <select
              v-model="drafts[c.direction_id].response_format"
              class="mt-1 w-full max-w-md rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm sm:w-auto"
            >
              <option value="video_link">Текст + ссылка на видео</option>
              <option value="file_upload">Текст + загрузка файла</option>
            </select>
            <p v-if="errorsFor(c.direction_id).response_format" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).response_format }}</p>
          </div>
          <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-3">
            <p class="text-xs font-medium text-gray-700">Лимиты длины текста ответа</p>
            <p class="mt-1 text-[11px] text-gray-500">Пустое значение или 0 — ограничения нет.</p>
            <div class="mt-2 flex flex-wrap gap-3">
              <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-600">Мин. символов</label>
                <input
                  v-model.number="drafts[c.direction_id].text_min_length"
                  type="number"
                  min="0"
                  max="100000"
                  class="w-32 rounded-lg border border-gray-200 px-3 py-2 text-sm"
                  placeholder="нет"
                />
                <p v-if="errorsFor(c.direction_id).text_min_length" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).text_min_length }}</p>
              </div>
              <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-600">Макс. символов</label>
                <input
                  v-model.number="drafts[c.direction_id].text_max_length"
                  type="number"
                  min="0"
                  max="100000"
                  class="w-32 rounded-lg border border-gray-200 px-3 py-2 text-sm"
                  placeholder="нет"
                />
                <p v-if="errorsFor(c.direction_id).text_max_length" class="mt-1 text-xs text-red-600">{{ errorsFor(c.direction_id).text_max_length }}</p>
              </div>
            </div>
          </div>
        </template>
        <RButton type="submit" variant="primary" size="sm" :loading="savingKey === c.direction_id" :disabled="savingKey !== null">
          Сохранить для этого направления
        </RButton>
      </form>
    </RCard>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  configs: { type: Array, default: () => [] },
})

const drafts = reactive({})
const savingKey = ref(null)
const fieldErrors = ref({})

watch(
  () => props.configs,
  (list) => {
    for (const c of list) {
      if (!drafts[c.direction_id]) {
        drafts[c.direction_id] = {
          title: c.title ?? '',
          task_body: c.task_body ?? '',
          response_format: c.response_format ?? 'video_link',
          max_contest_stages: Number.isFinite(Number(c.max_contest_stages)) ? Number(c.max_contest_stages) : 3,
          text_min_length: c.text_min_length ?? null,
          text_max_length: c.text_max_length ?? null,
        }
      } else {
        drafts[c.direction_id].title = c.title ?? ''
        drafts[c.direction_id].task_body = c.task_body ?? ''
        drafts[c.direction_id].response_format = c.response_format ?? 'video_link'
        drafts[c.direction_id].max_contest_stages = Number.isFinite(Number(c.max_contest_stages)) ? Number(c.max_contest_stages) : 3
        drafts[c.direction_id].text_min_length = c.text_min_length ?? null
        drafts[c.direction_id].text_max_length = c.text_max_length ?? null
      }
    }
  },
  { immediate: true, deep: true },
)

function errorsFor(directionId) {
  return fieldErrors.value[directionId] || {}
}

function save(directionId) {
  savingKey.value = directionId
  fieldErrors.value = { ...fieldErrors.value, [directionId]: {} }
  const payload = { ...drafts[directionId] }
  router.put(route('admin.tour-cabinet.stage3-config.update', { direction: directionId }), payload, {
    preserveScroll: true,
    onSuccess: () => {
      fieldErrors.value = { ...fieldErrors.value, [directionId]: {} }
    },
    onError: (errs) => {
      fieldErrors.value = { ...fieldErrors.value, [directionId]: errs }
    },
    onFinish: () => {
      savingKey.value = null
    },
  })
}
</script>
