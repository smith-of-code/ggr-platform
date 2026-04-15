<template>
  <div>
    <p v-if="locked" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      <template v-if="lockNotice === 'saved'">Просмотр задания этапа 3. Редактирование недоступно после сохранения.</template>
      <template v-else>Этот этап станет доступен для заполнения после завершения этапа 2.</template>
    </p>

    <form v-if="!locked" class="mt-8 space-y-5" @submit.prevent="submit">
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">Текст задания / ответ *</label>
        <textarea
          v-model="form.stage3_text"
          rows="8"
          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
          required
        />
        <p v-if="form.errors.stage3_text" class="mt-1 text-xs text-red-600">{{ form.errors.stage3_text }}</p>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-600">Ссылка на видео (необязательно)</label>
        <input
          v-model="form.stage3_video_url"
          type="url"
          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
          placeholder="https://…"
        />
        <p v-if="form.errors.stage3_video_url" class="mt-1 text-xs text-red-600">{{ form.errors.stage3_video_url }}</p>
      </div>
      <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">Сохранить</RButton>
    </form>

    <div v-else class="mt-8 space-y-3 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">
      <div>
        <p class="text-xs font-medium text-gray-500">Текст</p>
        <p class="mt-1 whitespace-pre-wrap">{{ progress.stage3_text || '—' }}</p>
      </div>
      <div v-if="progress.stage3_video_url">
        <p class="text-xs font-medium text-gray-500">Видео</p>
        <a :href="progress.stage3_video_url" class="mt-1 break-all text-rosatom-600 hover:text-rosatom-800" target="_blank" rel="noopener noreferrer">
          {{ progress.stage3_video_url }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  progress: { type: Object, required: true },
  locked: { type: Boolean, default: false },
  /** `early` — этап ещё не открыт; `saved` — ответ уже сохранён (только просмотр). */
  lockNotice: {
    type: String,
    default: null,
    validator: (v) => v === null || v === 'early' || v === 'saved',
  },
})

const form = useForm({
  stage3_text: props.progress.stage3_text ?? '',
  stage3_video_url: props.progress.stage3_video_url ?? '',
})

watch(
  () => props.progress,
  (p) => {
    form.stage3_text = p.stage3_text ?? ''
    form.stage3_video_url = p.stage3_video_url ?? ''
  },
  { deep: true },
)

function submit() {
  form.post(route('tour-cabinet.contest.stage3.store'), { preserveScroll: true })
}
</script>
