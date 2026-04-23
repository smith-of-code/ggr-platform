<template>
  <div class="space-y-8">
    <RCard v-for="c in configs" :key="c.project_key" elevation="raised">
      <div class="flex flex-col gap-2 border-b border-gray-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 class="text-base font-semibold text-gray-900">{{ c.direction_label }}</h3>
          <p class="text-xs text-gray-500">
            Направление: <span class="font-mono">{{ c.project_key }}</span>
            <span v-if="c.is_saved" class="ml-2 text-emerald-700">настройки сохранены</span>
            <span v-else class="ml-2 text-amber-700">ещё не сохраняли</span>
          </p>
        </div>
      </div>
      <form class="mt-4 space-y-4" @submit.prevent="save(c.project_key)">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Название задания</label>
          <input
            v-model="drafts[c.project_key].title"
            type="text"
            maxlength="500"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            required
          />
          <p v-if="errorsFor(c.project_key).title" class="mt-1 text-xs text-red-600">{{ errorsFor(c.project_key).title }}</p>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Описание задания</label>
          <textarea
            v-model="drafts[c.project_key].task_body"
            rows="6"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            placeholder="Что нужно сделать участнику…"
          />
          <p v-if="errorsFor(c.project_key).task_body" class="mt-1 text-xs text-red-600">{{ errorsFor(c.project_key).task_body }}</p>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Сколько этапов конкурса доступно в ЛК</label>
          <select
            v-model.number="drafts[c.project_key].max_contest_stages"
            class="mt-1 w-full max-w-md rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm sm:w-auto"
          >
            <option :value="1">Только этап 1</option>
            <option :value="2">Этапы 1 и 2</option>
            <option :value="3">Все три этапа</option>
          </select>
          <p class="mt-1 text-xs text-gray-500">Вкладки и переходы в личном кабинете ограничиваются этим числом.</p>
          <p v-if="errorsFor(c.project_key).max_contest_stages" class="mt-1 text-xs text-red-600">{{ errorsFor(c.project_key).max_contest_stages }}</p>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Формат ответа в ЛК участника</label>
          <select
            v-model="drafts[c.project_key].response_format"
            class="mt-1 w-full max-w-md rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm sm:w-auto"
          >
            <option value="video_link">Текст + ссылка на видео</option>
            <option value="file_upload">Текст + загрузка файла</option>
          </select>
          <p v-if="errorsFor(c.project_key).response_format" class="mt-1 text-xs text-red-600">{{ errorsFor(c.project_key).response_format }}</p>
        </div>
        <RButton type="submit" variant="primary" size="sm" :loading="savingKey === c.project_key" :disabled="savingKey !== null">
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
      if (!drafts[c.project_key]) {
        drafts[c.project_key] = {
          title: c.title ?? '',
          task_body: c.task_body ?? '',
          response_format: c.response_format ?? 'video_link',
          max_contest_stages: Number.isFinite(Number(c.max_contest_stages)) ? Number(c.max_contest_stages) : 3,
        }
      } else {
        drafts[c.project_key].title = c.title ?? ''
        drafts[c.project_key].task_body = c.task_body ?? ''
        drafts[c.project_key].response_format = c.response_format ?? 'video_link'
        drafts[c.project_key].max_contest_stages = Number.isFinite(Number(c.max_contest_stages)) ? Number(c.max_contest_stages) : 3
      }
    }
  },
  { immediate: true, deep: true },
)

function errorsFor(projectKey) {
  return fieldErrors.value[projectKey] || {}
}

function save(projectKey) {
  savingKey.value = projectKey
  fieldErrors.value = { ...fieldErrors.value, [projectKey]: {} }
  const payload = { ...drafts[projectKey] }
  router.put(route('admin.tour-cabinet.stage3-config.update', { project_key: projectKey }), payload, {
    preserveScroll: true,
    onSuccess: () => {
      fieldErrors.value = { ...fieldErrors.value, [projectKey]: {} }
    },
    onError: (errs) => {
      fieldErrors.value = { ...fieldErrors.value, [projectKey]: errs }
    },
    onFinish: () => {
      savingKey.value = null
    },
  })
}
</script>
