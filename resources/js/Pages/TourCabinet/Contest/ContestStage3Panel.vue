<template>
  <div>
    <p v-if="locked" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      <template v-if="lockNotice === 'saved'">Просмотр задания этапа 3. Редактирование недоступно после сохранения.</template>
      <template v-else>Этот этап станет доступен для заполнения после завершения этапа 2.</template>
    </p>

    <div v-if="!locked" class="mt-8 space-y-6">
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <h3 class="text-base font-semibold text-gray-900">{{ assignment.title }}</h3>
        <div v-if="assignment.task_body" class="mt-3 whitespace-pre-wrap text-sm text-gray-700">{{ assignment.task_body }}</div>
        <p v-else class="mt-2 text-sm text-gray-500">Описание задания уточнит организатор в личном кабинете (раздел настроек конкурса).</p>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Текст ответа *</label>
          <textarea
            v-model="form.stage3_text"
            rows="8"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            required
          />
          <p v-if="form.errors.stage3_text" class="mt-1 text-xs text-red-600">{{ form.errors.stage3_text }}</p>
        </div>

        <div v-if="assignment.response_format === 'video_link'">
          <label class="mb-1 block text-xs font-medium text-gray-600">
            Ссылка на видео{{ assignment.from_config ? ' *' : ' (необязательно)' }}
          </label>
          <input
            v-model="form.stage3_video_url"
            type="url"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            placeholder="https://…"
            :required="assignment.from_config"
          />
          <p v-if="form.errors.stage3_video_url" class="mt-1 text-xs text-red-600">{{ form.errors.stage3_video_url }}</p>
        </div>

        <div v-else>
          <label class="mb-1 block text-xs font-medium text-gray-600">Файл (документ или презентация и т.п.) *</label>
          <input
            type="file"
            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-rosatom-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-rosatom-800 hover:file:bg-rosatom-100"
            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.odt,.odp,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            @change="onFile"
          />
          <p class="mt-1 text-xs text-gray-500">До 50 МБ: PDF, Word, Excel, PowerPoint, ODT, ODP, ZIP.</p>
          <p v-if="form.errors.stage3_attachment" class="mt-1 text-xs text-red-600">{{ form.errors.stage3_attachment }}</p>
        </div>

        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">Сохранить ответ</RButton>
      </form>
    </div>

    <div v-else class="mt-8 space-y-4 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">
      <div>
        <p class="text-xs font-medium text-gray-500">Задание</p>
        <p class="mt-1 font-medium text-gray-900">{{ assignment.title }}</p>
        <div v-if="assignment.task_body" class="mt-2 whitespace-pre-wrap text-gray-700">{{ assignment.task_body }}</div>
      </div>
      <div>
        <p class="text-xs font-medium text-gray-500">Текст ответа</p>
        <p class="mt-1 whitespace-pre-wrap">{{ progress.stage3_text || '—' }}</p>
      </div>
      <div v-if="progress.stage3_video_url">
        <p class="text-xs font-medium text-gray-500">Видео</p>
        <a
          :href="progress.stage3_video_url"
          class="mt-1 break-all text-rosatom-600 hover:text-rosatom-800"
          target="_blank"
          rel="noopener noreferrer"
        >
          {{ progress.stage3_video_url }}
        </a>
      </div>
      <div v-if="progress.stage3_has_attachment">
        <p class="text-xs font-medium text-gray-500">Файл</p>
        <a
          :href="route('tour-cabinet.contest.stage3.attachment')"
          class="mt-1 inline-flex font-medium text-rosatom-600 hover:text-rosatom-800"
        >
          {{ progress.stage3_attachment_original_name || 'Скачать файл' }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
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

const assignment = computed(() => {
  const a = props.progress?.assignment
  if (a && typeof a === 'object') {
    return {
      title: a.title ?? 'Проверочное задание',
      task_body: a.task_body ?? '',
      response_format: a.response_format === 'file_upload' ? 'file_upload' : 'video_link',
      from_config: !!a.from_config,
    }
  }
  return {
    title: 'Проверочное задание',
    task_body: '',
    response_format: 'video_link',
    from_config: false,
  }
})

const form = useForm({
  stage3_text: props.progress.stage3_text ?? '',
  stage3_video_url: props.progress.stage3_video_url ?? '',
  stage3_attachment: null,
})

watch(
  () => props.progress,
  (p) => {
    form.stage3_text = p.stage3_text ?? ''
    form.stage3_video_url = p.stage3_video_url ?? ''
    form.stage3_attachment = null
  },
  { deep: true },
)

function onFile(e) {
  const f = e.target.files?.[0]
  form.stage3_attachment = f || null
}

function submit() {
  const opts = { preserveScroll: true }
  if (assignment.value.response_format === 'file_upload') {
    opts.forceFormData = true
  }
  form.post(route('tour-cabinet.contest.stage3.store'), opts)
}
</script>
