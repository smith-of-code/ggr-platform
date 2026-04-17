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
        <button
          v-if="!stage.title"
          type="button"
          class="mb-1.5 inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-rosatom-600 transition hover:bg-rosatom-50 hover:text-rosatom-700"
          @click="$emit('search')"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
          Найти этап из другой программы
        </button>
        <RInput v-model="stage.title" placeholder="Название этапа" required />
      </div>

      <!-- Blocks list -->
      <div class="space-y-3">
        <div
          v-for="(block, bIdx) in blocks"
          :key="bIdx"
          class="rounded-lg border border-gray-200 bg-white p-3"
        >
          <div class="mb-2 flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Блок {{ bIdx + 1 }}</span>
            <div class="flex items-center gap-1">
              <button v-if="bIdx > 0" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" @click="moveBlock(bIdx, -1)">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
              </button>
              <button v-if="bIdx < blocks.length - 1" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" @click="moveBlock(bIdx, 1)">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
              </button>
              <button v-if="blocks.length > 1" type="button" class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500" @click="removeBlock(bIdx)">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>
          </div>

          <SearchSelect
            :model-value="block.type"
            @update:model-value="v => block.type = v"
            :options="blockTypes"
            value-key="value"
            label-key="label"
            placeholder="Тип блока"
            :clearable="false"
            :searchable="false"
          />

          <div class="mt-2">
            <div v-if="block.type === 'content'">
              <RichTextEditor v-model="block.content" label="Контент" :upload-url="route('lms.admin.upload.image', eventSlug)" :media-picker-url="route('lms.admin.media.index', eventSlug)" collection="lms_courses" />
            </div>

            <div v-else-if="block.type === 'scorm'" class="space-y-3">
              <div
                class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center transition hover:border-rosatom-400"
                :class="{ 'border-rosatom-500 bg-rosatom-50': block._uploading }"
              >
                <template v-if="block._uploading">
                  <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                  <p class="text-sm font-medium text-rosatom-600">Загрузка SCORM-пакета...</p>
                </template>
                <template v-else-if="block._scormFilename">
                  <svg class="mx-auto mb-2 h-8 w-8 text-accent-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                  <p class="text-sm font-medium text-gray-900">{{ block._scormFilename }}</p>
                  <p class="mt-1 text-xs text-gray-500">SCORM-пакет загружен</p>
                  <button type="button" class="mt-2 text-xs font-medium text-rosatom-600 hover:underline" @click="clearBlockScorm(block)">Заменить файл</button>
                </template>
                <template v-else>
                  <svg class="mx-auto mb-2 h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                  <p class="text-sm font-medium text-gray-700">Загрузить SCORM-пакет (.zip)</p>
                  <p class="mt-1 text-xs text-gray-400">до 100 МБ, ZIP с imsmanifest.xml</p>
                  <input type="file" accept=".zip" class="absolute inset-0 cursor-pointer opacity-0" @change="handleBlockScormUpload($event, block)" />
                </template>
              </div>
              <div v-if="block._scormError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ block._scormError }}</div>
            </div>

            <div v-else-if="block.type === 'test'">
              <SearchSelect
                :model-value="block.content ? Number(block.content) : null"
                @update:model-value="v => block.content = v != null ? String(v) : ''"
                :options="tests"
                value-key="id"
                label-key="title"
                label="Тест"
                placeholder="Выберите тест"
                search-placeholder="Поиск по названию..."
              />
            </div>

            <div v-else-if="block.type === 'assignment'">
              <SearchSelect
                :model-value="block.content ? Number(block.content) : null"
                @update:model-value="v => block.content = v != null ? String(v) : ''"
                :options="assignments"
                value-key="id"
                label-key="title"
                label="Задание"
                placeholder="Выберите задание"
                search-placeholder="Поиск по названию..."
              />
            </div>

            <div v-else-if="block.type === 'video'">
              <SearchSelect
                :model-value="block.content ? Number(block.content) : null"
                @update:model-value="v => block.content = v != null ? String(v) : ''"
                :options="videos"
                value-key="id"
                label-key="title"
                label="Лекция"
                placeholder="Выберите лекцию"
                search-placeholder="Поиск по названию..."
              />
            </div>

            <div v-else-if="block.type === 'file'" class="space-y-3">
              <div
                class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center transition hover:border-rosatom-400"
                :class="{ 'border-rosatom-500 bg-rosatom-50': block._fileUploading }"
              >
                <template v-if="block._fileUploading">
                  <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                  <p class="text-sm font-medium text-rosatom-600">Загрузка файла…</p>
                </template>
                <template v-else-if="block.content">
                  <svg class="mx-auto mb-2 h-8 w-8 text-accent-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                  <p class="text-sm font-medium text-gray-900">{{ block._fileFilename || fileDisplayName(block) }}</p>
                  <p class="mt-1 text-xs text-gray-500">Участник сможет скачать файл на этапе курса</p>
                  <button type="button" class="mt-2 text-xs font-medium text-rosatom-600 hover:underline" @click="clearBlockFile(block)">Заменить файл</button>
                </template>
                <template v-else>
                  <svg class="mx-auto mb-2 h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                  <p class="text-sm font-medium text-gray-700">Загрузить файл</p>
                  <p class="mt-1 text-xs text-gray-400">до 20 МБ: PDF, Office, изображения, ZIP и др.</p>
                  <input type="file" class="absolute inset-0 cursor-pointer opacity-0" @change="handleBlockFileUpload($event, block)" />
                </template>
              </div>
              <div v-if="block._fileError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ block._fileError }}</div>
            </div>

            <div v-else-if="['workshop', 'city_meeting', 'curator_meeting'].includes(block.type)" class="space-y-3">
              <div class="flex items-center gap-2 rounded-lg bg-gradient-to-r px-3 py-2 text-sm font-medium"
                :class="{
                  'from-purple-50 to-purple-100/50 text-purple-700': block.type === 'workshop',
                  'from-teal-50 to-teal-100/50 text-teal-700': block.type === 'city_meeting',
                  'from-amber-50 to-amber-100/50 text-amber-700': block.type === 'curator_meeting',
                }"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                {{ block.type === 'workshop' ? 'Воркшоп' : block.type === 'city_meeting' ? 'Встреча города' : 'Встреча с куратором' }}
              </div>
              <div class="grid gap-3 sm:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-500">Дата проведения</label>
                  <input
                    type="date"
                    :value="scheduledDate(block)"
                    @input="e => setScheduledDate(block, e.target.value)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-500">Время начала (необязательно)</label>
                  <input
                    type="time"
                    :value="scheduledTime(block)"
                    @input="e => setScheduledTime(block, e.target.value)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-500">Время окончания (необязательно)</label>
                  <input
                    type="time"
                    :value="scheduledTimeEnd(block)"
                    @input="e => setScheduledTimeEnd(block, e.target.value)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500"
                  />
                </div>
              </div>
              <RichTextEditor v-model="block.content" label="Описание" :upload-url="route('lms.admin.upload.image', eventSlug)" />
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-2">
        <button
          type="button"
          class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-xs font-medium text-gray-500 transition hover:border-rosatom-400 hover:bg-rosatom-50 hover:text-rosatom-600"
          @click="addBlock"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Добавить блок
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-blue-300 px-3 py-2 text-xs font-medium text-blue-500 transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
          @click="$emit('searchBlock')"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
          Скопировать блок
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import axios from 'axios'
import SearchSelect from '@/Components/SearchSelect.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'

const blockTypes = [
  { value: 'content', label: 'Контент (текст)' },
  { value: 'scorm', label: 'SCORM' },
  { value: 'test', label: 'Тест' },
  { value: 'assignment', label: 'Задание' },
  { value: 'video', label: 'Лекция' },
  { value: 'file', label: 'Файл' },
  { value: 'workshop', label: 'Воркшоп' },
  { value: 'city_meeting', label: 'Встреча города' },
  { value: 'curator_meeting', label: 'Встреча с куратором' },
]

const props = defineProps({
  stage: Object,
  index: Number,
  total: Number,
  tests: Array,
  assignments: Array,
  videos: Array,
  eventSlug: String,
})

defineEmits(['move', 'remove', 'search', 'searchBlock'])

const blocks = computed(() => {
  if (!props.stage.blocks || props.stage.blocks.length === 0) {
    props.stage.blocks = [{ type: props.stage.type || 'content', content: props.stage.content || '', position: 0 }]
  }
  return props.stage.blocks
})

function addBlock() {
  props.stage.blocks.push({ type: 'content', content: '', position: props.stage.blocks.length })
}

function removeBlock(idx) {
  props.stage.blocks.splice(idx, 1)
  syncPrimaryType()
}

function moveBlock(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= props.stage.blocks.length) return
  const temp = props.stage.blocks[idx]
  props.stage.blocks[idx] = props.stage.blocks[newIdx]
  props.stage.blocks[newIdx] = temp
  syncPrimaryType()
}

function syncPrimaryType() {
  if (props.stage.blocks.length > 0) {
    props.stage.type = props.stage.blocks[0].type
    props.stage.content = props.stage.blocks[0].content
  }
}

async function handleBlockScormUpload(event, block) {
  const file = event.target.files?.[0]
  if (!file) return

  block._uploading = true
  block._scormError = null

  const fd = new FormData()
  fd.append('scorm_file', file)

  try {
    const { data } = await axios.post(
      route('lms.admin.scorm.upload', props.eventSlug),
      fd,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    block.content = data.url
    block._scormFilename = data.filename
  } catch (err) {
    block._scormError = err.response?.data?.error || err.response?.data?.message || 'Ошибка загрузки'
  } finally {
    block._uploading = false
  }
}

function clearBlockScorm(block) {
  block.content = ''
  block._scormFilename = null
  block._scormError = null
}

async function handleBlockFileUpload(event, block) {
  const file = event.target.files?.[0]
  if (!file) return
  event.target.value = ''

  block._fileUploading = true
  block._fileError = null

  const fd = new FormData()
  fd.append('file', file)

  try {
    const { data } = await axios.post(
      route('lms.admin.stage-block-file.upload', props.eventSlug),
      fd,
      { headers: { 'Content-Type': 'multipart/form-data' } },
    )
    block.content = data.url
    block._fileFilename = data.filename
  } catch (err) {
    block._fileError = err.response?.data?.message || err.response?.data?.error || 'Ошибка загрузки'
  } finally {
    block._fileUploading = false
  }
}

function clearBlockFile(block) {
  block.content = ''
  block._fileFilename = null
  block._fileError = null
}

function fileDisplayName(block) {
  const u = block?.content
  if (!u || typeof u !== 'string') return 'Файл загружен'
  try {
    const path = u.split('?')[0]
    const seg = decodeURIComponent(path.split('/').pop() || '')
    const i = seg.indexOf('_')
    if (i > 0 && i < seg.length - 1) return seg.slice(i + 1)
    return seg || 'Файл загружен'
  } catch {
    return 'Файл загружен'
  }
}

function scheduledDate(block) {
  if (!block.scheduled_at) return ''
  return block.scheduled_at.slice(0, 10)
}

function scheduledTime(block) {
  if (!block.scheduled_at || block.scheduled_at.length <= 10) return ''
  return block.scheduled_at.slice(11, 16)
}

function setScheduledDate(block, date) {
  if (!date) {
    block.scheduled_at = ''
    block.scheduled_ends_at = ''
    return
  }
  const time = scheduledTime(block)
  block.scheduled_at = time ? `${date}T${time}` : date
  const timeEnd = scheduledTimeEnd(block)
  block.scheduled_ends_at = timeEnd ? `${date}T${timeEnd}` : ''
}

function setScheduledTime(block, time) {
  const date = scheduledDate(block)
  if (!date) return
  block.scheduled_at = time ? `${date}T${time}` : date
}

function scheduledTimeEnd(block) {
  if (!block.scheduled_ends_at) return ''
  return block.scheduled_ends_at.slice(11, 16)
}

function setScheduledTimeEnd(block, time) {
  const date = scheduledDate(block)
  if (!date) return
  block.scheduled_ends_at = time ? `${date}T${time}` : ''
}
</script>
