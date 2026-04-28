<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Исследования</h1>
      <p class="mt-1 text-sm text-gray-500">Редактирование блоков страницы «Исследования»</p>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Hero -->
      <RCard elevation="raised">
        <SectionHeader title="Hero-блок" />
        <div class="mt-4 space-y-4">
          <RInput v-model="form.hero_title" label="Заголовок *" :error="form.errors.hero_title" />
          <RInput v-model="form.hero_subtitle" label="Подзаголовок *" :error="form.errors.hero_subtitle" />
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Описание *</label>
            <textarea
              v-model="form.hero_description"
              rows="4"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              :class="{ 'border-red-500': form.errors.hero_description }"
            />
            <p v-if="form.errors.hero_description" class="mt-1 text-xs text-red-600">{{ form.errors.hero_description }}</p>
          </div>
          <ImageUploadCrop
            v-model="form.hero_bg_image"
            label="Фоновое изображение (выбор/загрузка)"
            :error="form.errors.hero_bg_image"
            :upload-url="route('admin.upload.image')"
            :media-picker-url="route('admin.media.index')"
            collection="research-page"
            preview-class="h-44 w-full object-cover"
            :skip-crop="true"
          />
          <p class="text-xs text-gray-500">Градиент фона: начало, середина (необязательно), конец.</p>
          <div class="mt-2 grid gap-4 sm:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Цвет (from)</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.hero_bg_color_from" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200" />
                <RInput v-model="form.hero_bg_color_from" placeholder="#003274" class="flex-1" />
              </div>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Цвет (via)</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.hero_bg_color_via" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200" />
                <RInput v-model="form.hero_bg_color_via" placeholder="#025ea1" class="flex-1" />
              </div>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Цвет (to)</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.hero_bg_color_to" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200" />
                <RInput v-model="form.hero_bg_color_to" placeholder="#0277bd" class="flex-1" />
              </div>
            </div>
          </div>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Цвет текста</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.hero_text_color" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200" />
                <RInput v-model="form.hero_text_color" placeholder="#ffffff" class="flex-1" />
              </div>
            </div>
            <div class="flex items-center gap-3 pt-6">
              <RCheckbox v-model="form.hero_bg_color_enabled" />
              <span class="text-sm font-medium text-gray-700">Использовать свой градиент (вместо стандартного)</span>
            </div>
          </div>
        </div>
      </RCard>

      <!-- Tasks -->
      <RCard elevation="raised">
        <SectionHeader title="Общие задачи" />
        <div class="mt-4 space-y-4">
          <p class="text-xs text-gray-500">Ключевые выводы / задачи исследования</p>
          <RInput v-model="form.tasks_title" label="Заголовок секции *" :error="form.errors.tasks_title" />
          <DynamicList
            v-model="form.tasks"
            :fields="[
              { key: 'title', label: 'Заголовок', placeholder: 'Краткое название задачи' },
              { key: 'text', label: 'Описание', type: 'textarea', placeholder: 'Подробное описание задачи...' },
            ]"
            add-label="Добавить задачу"
            :new-item="{ title: '', text: '' }"
          />
        </div>
      </RCard>

      <!-- Pilot Cities -->
      <RCard elevation="raised">
        <SectionHeader title="Пилотные города" />
        <div class="mt-4 space-y-4">
          <p class="text-xs text-gray-500">Выберите города из справочника. Изображение и герб подтянутся автоматически.</p>
          <RInput v-model="form.pilot_cities_title" label="Заголовок секции *" :error="form.errors.pilot_cities_title" />
          <CityPicker v-model="form.pilot_cities" :cities="cities" placeholder="Найти пилотный город..." with-description />
        </div>
      </RCard>

      <!-- Stats -->
      <RCard elevation="raised">
        <SectionHeader title="Основа исследования (статистика)" />
        <div class="mt-4 space-y-4">
          <RInput v-model="form.stats_title" label="Заголовок секции *" :error="form.errors.stats_title" />
          <DynamicList
            v-model="form.stats"
            :fields="[
              { key: 'value', label: 'Число', placeholder: '642' },
              { key: 'label', label: 'Подпись', placeholder: 'анкеты собрано и проанализировано' },
            ]"
            add-label="Добавить счётчик"
            :new-item="{ value: '', label: '' }"
          />
        </div>
      </RCard>

      <!-- Results -->
      <RCard elevation="raised">
        <SectionHeader title="Результаты" />
        <div class="mt-4 space-y-4">
          <RInput v-model="form.results_title" label="Заголовок *" :error="form.errors.results_title" />
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Описание *</label>
            <textarea
              v-model="form.results_description"
              rows="3"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              :class="{ 'border-red-500': form.errors.results_description }"
            />
            <p v-if="form.errors.results_description" class="mt-1 text-xs text-red-600">{{ form.errors.results_description }}</p>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <RInput v-model="form.results_button_text" label="Текст кнопки" placeholder="Скачать альбом" />
            <RInput v-model="form.results_button_url" label="URL файла" placeholder="https://disk.yandex.ru/..." />
          </div>
          <ImageUploadCrop
            v-model="form.results_image"
            label="Изображение"
            :upload-url="route('admin.upload.image')"
            :media-picker-url="route('admin.media.index')"
            collection="research_page"
            :skip-crop="true"
            preview-class="h-32 w-full object-contain"
            :error="form.errors.results_image"
          />
        </div>
      </RCard>

      <!-- Program Cities -->
      <RCard elevation="raised">
        <SectionHeader title="Города программы" />
        <div class="mt-4 space-y-4">
          <p class="text-xs text-gray-500">Выберите города из справочника. Герб города отобразится автоматически.</p>
          <RInput v-model="form.program_cities_title" label="Заголовок секции *" :error="form.errors.program_cities_title" />
          <CityPicker v-model="form.program_cities" :cities="cities" placeholder="Найти город программы..." />
        </div>
      </RCard>

      <!-- Submit -->
      <div class="flex justify-start">
        <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
          {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
        </RButton>
      </div>
    </form>

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
import SectionHeader from '../OpportunityToursPage/SectionHeader.vue'
import DynamicList from '../OpportunityToursPage/DynamicList.vue'
import CityPicker from './CityPicker.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'

const props = defineProps({
  pageData: { type: Object, default: () => ({}) },
  cities: { type: Array, default: () => [] },
})

const lightboxImage = ref(null)

function openLightbox(src) {
  if (src) lightboxImage.value = src
}

const d = props.pageData

const form = useForm({
  hero_title: d.hero_title ?? 'ИССЛЕДОВАНИЯ',
  hero_subtitle: d.hero_subtitle ?? 'ОЦЕНКА ТУРИСТИЧЕСКОГО ПОТЕНЦИАЛА',
  hero_description: d.hero_description ?? '',
  hero_bg_image: d.hero_bg_image ?? '',
  hero_bg_color_from: d.hero_bg_color_from ?? '',
  hero_bg_color_via: d.hero_bg_color_via ?? '',
  hero_bg_color_to: d.hero_bg_color_to ?? '',
  hero_text_color: d.hero_text_color ?? '',
  hero_bg_color_enabled: Boolean(Number(d.hero_bg_color_enabled ?? 0)),

  tasks_title: d.tasks_title ?? 'ОБЩИЕ ЗАДАЧИ',
  tasks: d.tasks?.length ? d.tasks.map(t => ({ title: t.title ?? '', text: t.text ?? '' })) : [{ title: '', text: '' }],

  pilot_cities_title: d.pilot_cities_title ?? 'ПИЛОТНЫЕ ГОРОДА 2024',
  pilot_cities: Array.isArray(d.pilot_cities)
    ? d.pilot_cities.filter(v => v && typeof v === 'object' && v.city_id).map(v => ({ city_id: v.city_id, description: v.description ?? '' }))
    : [],

  stats_title: d.stats_title ?? 'ОСНОВА ИССЛЕДОВАНИЯ',
  stats: d.stats?.length ? d.stats : [{ value: '', label: '' }],

  results_title: d.results_title ?? 'РЕЗУЛЬТАТЫ 2024 ГОДА',
  results_description: d.results_description ?? '',
  results_button_text: d.results_button_text ?? 'Скачать альбом',
  results_button_url: d.results_button_url ?? '',
  results_image: d.results_image ?? '',

  program_cities_title: d.program_cities_title ?? 'ГОРОДА ПРОГРАММЫ 2025',
  program_cities: Array.isArray(d.program_cities) ? d.program_cities.filter(v => typeof v === 'number') : [],
})

function submit() {
  form.put(route('admin.research-page.update'), {
    preserveScroll: true,
  })
}
</script>
