<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Туры возможностей</h1>
      <p class="mt-1 text-sm text-gray-500">Редактирование блоков страницы «Туры возможностей»</p>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Hero -->
      <RCard elevation="raised">
        <SectionHeader title="Заголовок и описание" />
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <RInput v-model="form.hero_title" label="Заголовок *" :error="form.errors.hero_title" />
          <RInput v-model="form.hero_description" label="Описание *" :error="form.errors.hero_description" />
        </div>
      </RCard>

      <!-- Stats -->
      <RCard elevation="raised">
        <SectionHeader title="Цифры проекта" />
        <DynamicList
          v-model="form.stats"
          :fields="[
            { key: 'value', label: 'Значение', placeholder: '47' },
            { key: 'label', label: 'Подпись', placeholder: 'Туров реализовано' },
          ]"
          add-label="Добавить счётчик"
          :new-item="{ value: '', label: '' }"
        />
      </RCard>

      <!-- Projects -->
      <RCard elevation="raised">
        <SectionHeader title="Проекты программы" />
        <p class="mb-3 text-xs text-gray-500">Выберите направление из списка или добавьте кастомный элемент</p>
        <DynamicList
          v-model="form.projects"
          :fields="[
            { key: 'type', label: 'Тип', type: 'select', options: projectTypeOptions, column: 'left' },
            { key: 'direction_id', label: 'Направление', type: 'select', options: directionOptions, visibleWhen: { field: 'type', value: 'direction' }, column: 'left' },
            { key: 'title', label: 'Название', placeholder: 'Название проекта', visibleWhen: { field: 'type', value: 'custom' }, column: 'left' },
            { key: 'description', label: 'Описание', placeholder: 'Описание проекта', type: 'textarea', visibleWhen: { field: 'type', value: 'custom' }, column: 'left' },
            { key: 'link', label: 'Ссылка', placeholder: '/some-page', visibleWhen: { field: 'type', value: 'custom' }, column: 'left' },
            { key: 'image', label: 'Изображение', placeholder: 'URL или загрузить', type: 'image-upload', preview: 'logo-card', visibleWhen: { field: 'type', value: 'custom' }, column: 'right' },
          ]"
          add-label="Добавить проект"
          :new-item="{ type: 'direction', direction_id: '', title: '', description: '', image: '', link: '' }"
          @lightbox="openLightbox"
        />
      </RCard>

      <!-- Videos -->
      <RCard elevation="raised">
        <SectionHeader title="Видео туров" />
        <p class="mb-3 text-xs text-gray-500">Загрузите обложку и выберите источник видео. Нажмите Play для предпросмотра.</p>
        <DynamicList
          v-model="form.videos"
          :fields="[
            { key: 'title', label: 'Название', placeholder: 'Тур в Саров', column: 'left' },
            { key: 'thumbnail', label: 'Обложка', placeholder: 'URL обложки', type: 'image-upload', preview: 'video-card', column: 'left' },
            { key: 'sourceType', label: 'Источник видео', type: 'select', options: videoSourceOptions, column: 'right' },
            { key: 'embedUrl', label: 'Embed URL (ВК/Рутьюб)', placeholder: 'Вставьте ссылку или <iframe> код', parseIframe: true, visibleWhen: { field: 'sourceType', value: 'embed' }, column: 'right' },
            { key: 'videoFile', label: 'Видеофайл', placeholder: 'Прямая ссылка или загрузить файл', type: 'file-upload', accept: 'video/*', visibleWhen: { field: 'sourceType', value: 'file' }, column: 'right' },
          ]"
          add-label="Добавить видео"
          :new-item="{ title: '', thumbnail: '', sourceType: 'embed', embedUrl: '', videoFile: '' }"
          @preview="openVideoPreview"
          @lightbox="openLightbox"
        />
      </RCard>

      <!-- Participation Steps -->
      <RCard elevation="raised">
        <SectionHeader title="Как принять участие" />
        <DynamicList
          v-model="form.participation_steps"
          :fields="[
            { key: 'title', label: 'Заголовок шага', placeholder: 'Зарегистрируйтесь' },
            { key: 'description', label: 'Описание', placeholder: 'Создайте личный кабинет...', type: 'textarea' },
          ]"
          add-label="Добавить шаг"
          :new-item="{ title: '', description: '' }"
        />
      </RCard>

      <!-- Emotions -->
      <RCard elevation="raised">
        <SectionHeader title="Счётчик эмоций" />
        <DynamicList
          v-model="form.emotions"
          :fields="[
            { key: 'icon', label: 'Иконка', type: 'icon-select', options: emotionIconOptions, iconMap: emotionIconMap },
            { key: 'count', label: 'Число', placeholder: '8 540' },
            { key: 'label', label: 'Подпись', placeholder: 'Нравится' },
          ]"
          add-label="Добавить эмоцию"
          :new-item="{ icon: 'heart', count: '', label: '' }"
        />
      </RCard>

      <!-- Featured Tours -->
      <RCard elevation="raised">
        <SectionHeader title="Популярные туры" />
        <p class="mb-3 text-xs text-gray-500">Выберите туры, которые будут отображаться в блоке «Популярные туры»</p>
        <MultiSelect
          v-model="form.featured_tour_ids"
          :options="allTours"
          value-key="id"
          label-key="title"
          label="Туры"
          placeholder="Выберите туры из каталога..."
        />
      </RCard>

      <!-- Socials -->
      <RCard elevation="raised">
        <SectionHeader title="Социальные сети" />
        <DynamicList
          v-model="form.socials"
          :fields="[
            { key: 'name', label: 'Название', placeholder: 'ВКонтакте' },
            { key: 'url', label: 'Ссылка', placeholder: 'https://vk.com/...' },
            { key: 'icon', label: 'Иконка', type: 'icon-select', options: socialIconOptions, iconMap: socialIconMap },
          ]"
          add-label="Добавить соцсеть"
          :new-item="{ name: '', url: '', icon: 'vk' }"
        />
      </RCard>

      <!-- FAQ -->
      <RCard elevation="raised">
        <SectionHeader title="FAQ (ответы на вопросы)" />
        <p class="mb-3 text-xs text-gray-500">В поле «Ответ» можно использовать HTML-ссылки: &lt;a href=&quot;...&quot;&gt;текст&lt;/a&gt;</p>
        <DynamicList
          v-model="form.faq"
          :fields="[
            { key: 'question', label: 'Вопрос', placeholder: 'Кто может принять участие?' },
            { key: 'answer', label: 'Ответ (поддерживает HTML)', placeholder: 'Участие открыто для всех...', type: 'textarea' },
          ]"
          add-label="Добавить вопрос"
          :new-item="{ question: '', answer: '' }"
        />
      </RCard>

      <!-- Partners -->
      <RCard elevation="raised">
        <SectionHeader title="Партнёры" />
        <DynamicList
          v-model="form.partners"
          :fields="[
            { key: 'name', label: 'Название', placeholder: 'Росатом', column: 'left' },
            { key: 'url', label: 'Ссылка на сайт', placeholder: 'https://rosatom.ru', column: 'left' },
            { key: 'logo', label: 'Логотип', placeholder: '/images/partners/logo.png', type: 'image-upload', preview: 'logo-card', column: 'right' },
          ]"
          add-label="Добавить партнёра"
          :new-item="{ name: '', url: '', logo: '' }"
          @lightbox="openLightbox"
        />
      </RCard>

      <!-- Submit -->
      <div class="flex items-center gap-4">
        <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить все изменения
        </RButton>
        <Transition
          enter-active-class="transition duration-200"
          enter-from-class="opacity-0"
          leave-active-class="transition duration-200"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Сохранено</p>
        </Transition>
      </div>
    </form>

    <!-- Video preview modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-200"
        leave-to-class="opacity-0"
      >
        <div
          v-if="previewVideo"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
          @click.self="previewVideo = null"
        >
          <div class="relative w-full max-w-4xl">
            <button
              type="button"
              @click="previewVideo = null"
              class="absolute -right-2 -top-10 rounded-full p-1.5 text-white/80 transition hover:text-white"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
            <div class="overflow-hidden rounded-2xl bg-black">
              <div class="aspect-video">
                <video
                  v-if="previewVideo.videoFile"
                  :src="previewVideo.videoFile"
                  controls
                  autoplay
                  class="h-full w-full"
                  :poster="previewVideo.thumbnail || undefined"
                />
                <iframe
                  v-else-if="previewVideo.embedUrl"
                  :src="previewVideo.embedUrl"
                  class="h-full w-full"
                  frameborder="0"
                  allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                  allowfullscreen
                />
              </div>
            </div>
            <p v-if="previewVideo.title" class="mt-3 text-center text-lg font-semibold text-white">{{ previewVideo.title }}</p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Image lightbox -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-200"
        leave-to-class="opacity-0"
      >
        <div
          v-if="lightboxImage"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
          @click.self="lightboxImage = null"
        >
          <div class="relative max-h-[90vh] max-w-4xl">
            <button
              type="button"
              @click="lightboxImage = null"
              class="absolute -right-2 -top-10 rounded-full p-1.5 text-white/80 transition hover:text-white"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
            <img :src="lightboxImage" class="max-h-[85vh] rounded-lg object-contain" />
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import SectionHeader from './SectionHeader.vue'
import DynamicList from './DynamicList.vue'
import { emotionIcon, socialIcon, emotionIconKeys, socialIconKeys } from '@/utils/opportunityToursIcons'

const props = defineProps({
  pageData: { type: Object, default: () => ({}) },
  allTours: { type: Array, default: () => [] },
  allDirections: { type: Array, default: () => [] },
})

const emotionIconOptions = [
  { value: 'heart', label: 'Сердце' },
  { value: 'eye', label: 'Глаз' },
  { value: 'fire', label: 'Огонь' },
  { value: 'thumbs-up', label: 'Палец вверх' },
  { value: 'star', label: 'Звезда' },
]

const emotionIconMap = Object.fromEntries(
  emotionIconKeys.map(k => [k, emotionIcon(k, 'h-5 w-5')])
)

const socialIconOptions = [
  { value: 'vk', label: 'ВКонтакте' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'rutube', label: 'Рутьюб' },
  { value: 'ok', label: 'Одноклассники' },
  { value: 'dzen', label: 'Дзен' },
]

const socialIconMap = Object.fromEntries(
  socialIconKeys.map(k => [k, socialIcon(k, 'h-5 w-5')])
)

const videoSourceOptions = [
  { value: 'embed', label: 'Ссылка VK или RUTUBE' },
  { value: 'file', label: 'Прямая ссылка на файл' },
]

const projectTypeOptions = [
  { value: 'direction', label: 'Из направлений' },
  { value: 'custom', label: 'Кастомный' },
]

const directionOptions = props.allDirections.map(d => ({
  value: d.id,
  label: d.title,
}))

const previewVideo = ref(null)
const lightboxImage = ref(null)

function openVideoPreview(video) {
  if (!video.videoFile && !video.embedUrl) return
  previewVideo.value = video
}

function openLightbox(src) {
  if (src) lightboxImage.value = src
}

const d = props.pageData

const form = useForm({
  hero_title: d.hero_title ?? 'Туры возможностей',
  hero_description: d.hero_description ?? '',
  stats: d.stats ?? [{ value: '', label: '' }],
  emotions: d.emotions ?? [{ icon: 'heart', count: '', label: '' }],
  partners: d.partners ?? [{ name: '', url: '', logo: '' }],
  socials: d.socials ?? [{ name: '', url: '', icon: 'vk' }],
  faq: d.faq ?? [{ question: '', answer: '' }],
  videos: (d.videos?.length ? d.videos : [{ title: '', thumbnail: '', sourceType: 'embed', embedUrl: '', videoFile: '' }]).map(v => ({
    title: v.title ?? '',
    thumbnail: v.thumbnail ?? '',
    sourceType: v.sourceType ?? (v.videoFile ? 'file' : 'embed'),
    embedUrl: v.embedUrl ?? '',
    videoFile: v.videoFile ?? '',
  })),
  projects: (d.projects?.length ? d.projects : []).map(p => ({
    type: p.type ?? 'direction',
    direction_id: p.direction_id ?? '',
    title: p.title ?? '',
    description: p.description ?? '',
    image: p.image ?? '',
    link: p.link ?? '',
  })),
  participation_steps: d.participation_steps ?? [{ title: '', description: '' }],
  featured_tour_ids: d.featured_tour_ids ?? [],
})

function submit() {
  form.put(route('admin.opportunity-tours-page.update'), {
    preserveScroll: true,
  })
}
</script>
