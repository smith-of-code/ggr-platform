<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.videos.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к видео
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ video ? 'Редактировать видео' : 'Новое видео' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.title"
          label="Название *"
          required
          :error="form.errors.title"
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        </div>
        <RInput
          v-model="form.url"
          label="URL"
          type="url"
          placeholder="https://..."
        />

        <!-- Длительность видео -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Длительность видео</label>
          <p class="mb-2 text-xs text-gray-400">Используется для контроля просмотра в программах</p>
          <div class="flex items-center gap-2">
            <div class="w-24">
              <RInput
                v-model="durationMinutes"
                type="number"
                placeholder="мин"
                :min="0"
              />
            </div>
            <span class="text-sm text-gray-500">мин</span>
            <div class="w-24">
              <RInput
                v-model="durationSecs"
                type="number"
                placeholder="сек"
                :min="0"
                :max="59"
              />
            </div>
            <span class="text-sm text-gray-500">сек</span>
          </div>
          <div v-if="form.errors.duration_seconds" class="mt-1 text-sm text-red-600">{{ form.errors.duration_seconds }}</div>
        </div>

        <!-- Обложка -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Обложка</label>

          <div v-if="thumbnailPreview || currentThumbnail" class="mb-3">
            <div class="relative inline-block">
              <img
                :src="thumbnailPreview || currentThumbnail"
                alt="Обложка"
                class="h-40 w-auto rounded-xl border border-gray-200 object-cover shadow-sm"
              />
              <button
                type="button"
                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md transition hover:bg-red-600"
                @click="removeThumbnail"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>
          </div>

          <div
            v-if="!thumbnailPreview && !currentThumbnail"
            class="relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-8 text-center transition hover:border-rosatom-400 hover:bg-gray-50"
            :class="{ 'border-rosatom-500 bg-rosatom-50': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
          >
            <svg class="mx-auto mb-3 h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
            </svg>
            <p class="text-sm font-medium text-gray-700">Перетащите изображение или нажмите для выбора</p>
            <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP — до 5 МБ</p>
            <input
              ref="fileInput"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              class="absolute inset-0 cursor-pointer opacity-0"
              @change="handleFileSelect"
            />
          </div>

          <div v-if="!thumbnailPreview && !currentThumbnail" class="mt-2 flex flex-wrap items-center gap-3">
            <button
              type="button"
              class="text-xs font-medium text-rosatom-600 hover:underline"
              @click="showUrlInput = !showUrlInput"
            >
              {{ showUrlInput ? 'Скрыть URL' : 'Вставить URL изображения' }}
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-[#003274]/30 hover:text-[#003274]"
              @click="showMediaPicker = true"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
              </svg>
              Из библиотеки
            </button>
            <div v-if="showUrlInput" class="w-full mt-2">
              <RInput
                v-model="thumbnailUrl"
                placeholder="https://example.com/image.jpg"
                type="url"
                @blur="applyThumbnailUrl"
                @keydown.enter.prevent="applyThumbnailUrl"
              />
            </div>
          </div>

          <MediaPickerModal
            :show="showMediaPicker"
            :api-url="route('lms.admin.media.index', event.slug)"
            @close="showMediaPicker = false"
            @select="onMediaSelect"
          />

          <div v-if="form.errors.thumbnail_file" class="mt-1 text-sm text-red-600">{{ form.errors.thumbnail_file }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-5">
          <p class="mb-3 text-sm font-medium text-gray-800">Кому показывать</p>
          <RCheckbox
            v-model="form.visible_to_all"
            label="Всем пользователям (включая не записанных ни в одну программу)"
          />
          <p class="mb-3 mt-2 text-xs text-gray-500">Если снять галочку, отметьте хотя бы одну программу.</p>
          <p class="mb-2 text-sm font-medium text-gray-700">Программы</p>
          <div class="space-y-2" :class="form.visible_to_all ? 'pointer-events-none opacity-50' : ''">
            <div v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-white" :class="form.group_ids.includes(g.id) ? 'bg-white ring-1 ring-rosatom-200' : ''">
              <RCheckbox
                :model-value="form.group_ids.includes(g.id)"
                @update:model-value="(v) => { if (v) { if (!form.group_ids.includes(g.id)) form.group_ids.push(g.id) } else { form.group_ids = form.group_ids.filter(id => id !== g.id) } }"
                :label="g.title"
              />
            </div>
          </div>
          <div v-if="form.errors.group_ids" class="mt-2 text-sm text-red-600">{{ form.errors.group_ids }}</div>
        </div>
        <RCheckbox v-model="form.is_active" label="Активно" />
        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.videos.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const props = defineProps({ event: Object, video: Object, groups: Array })

const isDragging = ref(false)
const showUrlInput = ref(false)
const showMediaPicker = ref(false)
const thumbnailUrl = ref('')
const thumbnailPreview = ref(null)
const currentThumbnail = ref(props.video?.thumbnail ?? null)

const initialDuration = props.video?.duration_seconds ?? 0
const durationMinutes = ref(Math.floor(initialDuration / 60))
const durationSecs = ref(initialDuration % 60)

const form = useForm({
  title: props.video?.title ?? '',
  description: props.video?.description ?? '',
  source: props.video?.source ?? '',
  url: props.video?.url ?? '',
  visible_to_all: props.video?.visible_to_all ?? true,
  group_ids: props.video?.groups?.map(g => g.id) ?? [],
  is_active: props.video?.is_active ?? true,
  duration_seconds: props.video?.duration_seconds ?? null,
  thumbnail_file: null,
  remove_thumbnail: false,
})

watch(() => form.visible_to_all, (v) => {
  if (v) {
    form.group_ids = []
  }
})

function handleFileSelect(e) {
  const file = e.target.files?.[0]
  if (file) applyFile(file)
}

function handleDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file && file.type.startsWith('image/')) applyFile(file)
}

function applyFile(file) {
  form.thumbnail_file = file
  form.remove_thumbnail = false
  thumbnailPreview.value = URL.createObjectURL(file)
  currentThumbnail.value = null
}

function applyThumbnailUrl() {
  if (!thumbnailUrl.value) return
  currentThumbnail.value = thumbnailUrl.value
  thumbnailPreview.value = null
  form.thumbnail_file = null
  form.remove_thumbnail = false
  showUrlInput.value = false
}

function onMediaSelect(url) {
  currentThumbnail.value = url
  thumbnailPreview.value = null
  form.thumbnail_file = null
  form.remove_thumbnail = false
}

function removeThumbnail() {
  thumbnailPreview.value = null
  currentThumbnail.value = null
  form.thumbnail_file = null
  form.remove_thumbnail = true
  thumbnailUrl.value = ''
}

function submit() {
  const totalSeconds = (parseInt(durationMinutes.value) || 0) * 60 + (parseInt(durationSecs.value) || 0)
  form.duration_seconds = totalSeconds > 0 ? totalSeconds : null

  const options = {
    forceFormData: true,
  }

  if (props.video) {
    router.post(route('lms.admin.videos.update', [props.event.slug, props.video.id]), {
      ...form.data(),
      _method: 'PUT',
    }, options)
  } else {
    form.post(route('lms.admin.videos.store', props.event.slug), options)
  }
}
</script>
