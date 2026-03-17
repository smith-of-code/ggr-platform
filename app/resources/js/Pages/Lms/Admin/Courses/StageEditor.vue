<template>
  <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
    <div class="mb-3 flex items-center justify-between">
      <span class="text-sm font-medium text-gray-500">Этап {{ index + 1 }}</span>
      <div class="flex gap-2">
        <RButton v-if="index > 0" variant="ghost" size="sm" icon-only type="button" @click="$emit('move', -1)">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
          </template>
        </RButton>
        <RButton v-if="index < total - 1" variant="ghost" size="sm" icon-only type="button" @click="$emit('move', 1)">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
          </template>
        </RButton>
        <RButton variant="danger" size="sm" icon-only type="button" @click="$emit('remove')">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
          </template>
        </RButton>
      </div>
    </div>
    <div class="space-y-3">
      <div>
        <RInput v-model="stage.title" placeholder="Название этапа" required />
      </div>
      <div>
        <select v-model="stage.type" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
          <option value="content">Контент</option>
          <option value="scorm">SCORM</option>
          <option value="test">Тест</option>
          <option value="assignment">Задание</option>
          <option value="video">Видео</option>
        </select>
      </div>
      <div v-if="stage.type === 'content'">
        <textarea v-model="stage.content" rows="3" placeholder="Текст контента" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
      </div>
      <div v-else-if="stage.type === 'scorm'" class="space-y-3">
        <div
          class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center transition hover:border-rosatom-400"
          :class="{ 'border-rosatom-500 bg-rosatom-50': stage.uploading }"
        >
          <template v-if="stage.uploading">
            <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
            <p class="text-sm font-medium text-rosatom-600">Загрузка SCORM-пакета...</p>
          </template>
          <template v-else-if="stage.scormFilename">
            <svg class="mx-auto mb-2 h-8 w-8 text-accent-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <p class="text-sm font-medium text-gray-900">{{ stage.scormFilename }}</p>
            <p class="mt-1 text-xs text-gray-500">SCORM-пакет загружен</p>
            <button type="button" class="mt-2 text-xs font-medium text-rosatom-600 hover:underline" @click="clearScorm">Заменить файл</button>
          </template>
          <template v-else>
            <svg class="mx-auto mb-2 h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            <p class="text-sm font-medium text-gray-700">Загрузить SCORM-пакет (.zip)</p>
            <p class="mt-1 text-xs text-gray-400">до 100 МБ, ZIP с imsmanifest.xml</p>
            <input
              type="file"
              accept=".zip"
              class="absolute inset-0 cursor-pointer opacity-0"
              @change="handleScormUpload($event)"
            />
          </template>
        </div>
        <div v-if="stage.scormError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ stage.scormError }}</div>
        <RInput v-if="stage.content" v-model="stage.content" label="SCORM URL (авто)" disabled />
      </div>
      <div v-else-if="stage.type === 'test'" class="space-y-2">
        <label class="block text-xs text-gray-500">Тест</label>
        <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
          <option value="">— Выберите тест —</option>
          <option v-for="t in tests" :key="t.id" :value="String(t.id)">{{ t.title }}</option>
        </select>
      </div>
      <div v-else-if="stage.type === 'assignment'" class="space-y-2">
        <label class="block text-xs text-gray-500">Задание</label>
        <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
          <option value="">— Выберите задание —</option>
          <option v-for="a in assignments" :key="a.id" :value="String(a.id)">{{ a.title }}</option>
        </select>
      </div>
      <div v-else-if="stage.type === 'video'" class="space-y-2">
        <label class="block text-xs text-gray-500">Видео</label>
        <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
          <option value="">— Выберите видео —</option>
          <option v-for="v in videos" :key="v.id" :value="String(v.id)">{{ v.title }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'

const props = defineProps({
  stage: Object,
  index: Number,
  total: Number,
  tests: Array,
  assignments: Array,
  videos: Array,
  eventSlug: String,
})

defineEmits(['move', 'remove'])

async function handleScormUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return

  props.stage.uploading = true
  props.stage.scormError = null

  const fd = new FormData()
  fd.append('scorm_file', file)

  try {
    const { data } = await axios.post(
      route('lms.admin.scorm.upload', props.eventSlug),
      fd,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    props.stage.content = data.url
    props.stage.scormFilename = data.filename
  } catch (err) {
    props.stage.scormError = err.response?.data?.error || err.response?.data?.message || 'Ошибка загрузки'
  } finally {
    props.stage.uploading = false
  }
}

function clearScorm() {
  props.stage.content = ''
  props.stage.scormFilename = null
  props.stage.scormError = null
}
</script>
