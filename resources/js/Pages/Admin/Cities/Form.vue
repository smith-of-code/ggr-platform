<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <Link :href="route('admin.cities.index')" class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
          Назад к городам
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">{{ city ? 'Редактировать город' : 'Новый город' }}</h1>
      </div>
      <div class="flex gap-3">
        <button type="button" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="showPreview = true">Предпросмотр</button>
        <Link :href="route('admin.cities.index')" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left column -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Basic info -->
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <RInput v-model="form.name" label="Название *" placeholder="Название города" :error="form.errors.name" required />
              <RInput v-model="form.slug" label="Slug" placeholder="Автоматически из названия" :error="form.errors.slug" />
              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="form.region" label="Регион" placeholder="Ленинградская область" :error="form.errors.region" />
                <RInput v-model="form.population" label="Население" type="number" placeholder="68000" :error="form.errors.population" />
              </div>
              <div class="grid gap-4 sm:grid-cols-3">
                <RInput v-model="form.founded_year" label="Год основания" type="number" placeholder="1954" :error="form.errors.founded_year" />
                <RInput v-model="form.population_year" label="Население на год" type="number" placeholder="2021" :error="form.errors.population_year" />
                <RInput v-model="form.timezone" label="Часовой пояс" placeholder="UTC+7" :error="form.errors.timezone" />
              </div>
              <RichTextEditor v-model="form.description" label="Описание города" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>

          <!-- Infrastructure scores -->
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <div>
                <h2 class="text-base font-bold text-gray-900">Инфраструктура</h2>
                <p class="mt-1 text-sm text-gray-500">Оценка развития ключевых сфер (0–100)</p>
              </div>
              <div class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div v-for="inf in infraFields" :key="inf.key">
                  <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-sm font-semibold text-gray-700">{{ inf.label }}</label>
                    <span class="text-sm font-bold tabular-nums text-[#003274]">{{ form.infrastructure[inf.key] ?? 0 }}%</span>
                  </div>
                  <input
                    v-model.number="form.infrastructure[inf.key]"
                    type="range"
                    min="0"
                    max="100"
                    step="1"
                    class="h-2 w-full cursor-pointer appearance-none rounded-full bg-gray-200 accent-[#003274]"
                  />
                </div>
              </div>
            </div>
          </RCard>

          <!-- Facts -->
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-base font-bold text-gray-900">Факты о городе</h2>
                  <p class="mt-1 text-sm text-gray-500">На сайте выводится <strong>описание</strong> (если заполнено), иначе — <strong>заголовок</strong>. Ссылка делает факт кликабельным.</p>
                </div>
                <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#003274]/90" @click="addFact">+ Добавить</button>
              </div>
              <div v-for="(fact, fi) in form.facts" :key="fi" class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                <div class="mb-3 flex items-start justify-between">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ fi + 1 }}</span>
                  <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500" @click="form.facts.splice(fi, 1)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div class="space-y-3">
                  <input v-model="fact.title" type="text" placeholder="Заголовок факта *" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10" />
                  <input v-model="fact.url" type="text" placeholder="Ссылка (необязательно)" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10" />
                  <RichTextEditor v-model="fact.description" label="Описание (необязательно)" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
                </div>
              </div>
              <p v-if="!form.facts.length" class="text-sm text-gray-400">Фактов пока нет</p>
            </div>
          </RCard>

          <!-- Attractions -->
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-base font-bold text-gray-900">Достопримечательности</h2>
                  <p class="mt-1 text-sm text-gray-500">Объекты для туристов</p>
                </div>
                <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#003274]/90" @click="addAttraction">+ Добавить</button>
              </div>
              <div v-for="(attr, ai) in form.attractions" :key="ai" class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                <div class="mb-3 flex items-start justify-between">
                  <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ ai + 1 }}</span>
                  <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500" @click="form.attractions.splice(ai, 1)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div class="space-y-3">
                  <input v-model="attr.title" type="text" placeholder="Название" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10" />
                  <textarea v-model="attr.description" placeholder="Описание (необязательно)" rows="2" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10" />
                  <ImageUploadCrop v-model="attr.image" label="Фото" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
                </div>
              </div>
              <p v-if="!form.attractions.length" class="text-sm text-gray-400">Достопримечательностей пока нет</p>
            </div>
          </RCard>

          <!-- Social objects -->
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <div>
                <h2 class="text-base font-bold text-gray-900">Социальная сфера</h2>
                <p class="mt-1 text-sm text-gray-500">Списки объектов по категориям</p>
              </div>
              <div v-for="cat in socialCategories" :key="cat.key" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                <div class="mb-3 flex items-center justify-between">
                  <h3 class="text-sm font-bold uppercase tracking-wide text-[#003274]">{{ cat.label }}</h3>
                  <button type="button" class="rounded-lg bg-[#003274] px-2.5 py-1 text-xs font-medium text-white transition hover:bg-[#003274]/90" @click="addSocialItem(cat.key)">+ Добавить</button>
                </div>
                <div class="space-y-2">
                  <div v-for="(_, si) in form.social_objects[cat.key]" :key="si" class="flex gap-2">
                    <input
                      v-model="form.social_objects[cat.key][si]"
                      type="text"
                      :placeholder="cat.placeholder"
                      class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10"
                    />
                    <button type="button" class="shrink-0 rounded-lg border border-gray-200 p-2 text-gray-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-500" @click="form.social_objects[cat.key].splice(si, 1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                  <p v-if="!form.social_objects[cat.key].length" class="text-xs text-gray-400">Пока пусто</p>
                </div>
              </div>
            </div>
          </RCard>

          <!-- Coordinates -->
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <h2 class="text-base font-bold text-gray-900">Координаты</h2>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Широта (lat)</label>
                  <input v-model.number="form.lat" type="number" step="any" min="-90" max="90" placeholder="59.9" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10" />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Долгота (lng)</label>
                  <input v-model.number="form.lng" type="number" step="any" min="-180" max="180" placeholder="29.1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10" />
                </div>
              </div>
            </div>
          </RCard>

          <!-- Energy Cities block -->
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <div>
                <h2 class="text-base font-bold text-gray-900">Город в объективе «Энергии городов»</h2>
                <p class="mt-1 text-sm text-gray-500">Блок с видео и описанием проекта «Энергия городов»</p>
              </div>
              <RInput v-model="form.energy_cities_block.video_url" label="Ссылка на видео" placeholder="https://youtube.com/watch?v=..." :error="form.errors['energy_cities_block.video_url']" />
              <RInput v-model="form.energy_cities_block.video_title" label="Заголовок видео" placeholder="«Энергия городов». Железногорск" :error="form.errors['energy_cities_block.video_title']" />
              <RInput v-model="form.energy_cities_block.video_subtitle" label="Подзаголовок видео" placeholder="Проект Госкорпорации «Росатом» о гостеприимных атомных городах" :error="form.errors['energy_cities_block.video_subtitle']" />
              <RichTextEditor v-model="form.energy_cities_block.description" label="Текст описания" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="form.energy_cities_block.button_text" label="Текст кнопки" placeholder="Все серии «Энергии городов»" :error="form.errors['energy_cities_block.button_text']" />
                <RInput v-model="form.energy_cities_block.button_url" label="Ссылка кнопки" placeholder="https://..." :error="form.errors['energy_cities_block.button_url']" />
              </div>
            </div>
          </RCard>
        </div>

        <!-- Right column -->
        <div class="space-y-6">
          <!-- Image -->
          <RCard elevation="raised">
            <ImageUploadCrop v-model="form.image" label="Фото города" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
          </RCard>

          <!-- Coat of arms -->
          <RCard elevation="raised">
            <ImageUploadCrop v-model="form.coat_of_arms" label="Герб города" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="cities" :entity-type="mediaEntityType" :entity-id="mediaEntityId" skip-crop preview-class="mx-auto h-48 w-auto object-contain p-4" />
            <p class="px-6 pb-4 text-xs text-gray-400">PNG без фона — без рамки; JPEG — с декоративной окантовкой</p>
          </RCard>

          <!-- Status & position -->
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Позиция</label>
                <input v-model.number="form.position" type="number" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10" />
              </div>
              <RCheckbox v-model="form.is_active" label="Активен" />
            </div>
          </RCard>

          <!-- Block visibility -->
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <div>
                <h2 class="text-base font-bold text-gray-900">Видимость блоков</h2>
                <p class="mt-1 text-sm text-gray-500">Управление отображением секций на странице города</p>
              </div>
              <RCheckbox v-model="form.block_visibility.facts" label="Факты о городе" />
              <RCheckbox v-model="form.block_visibility.infrastructure" label="Инфраструктура" />
              <RCheckbox v-model="form.block_visibility.video" label="Видео" />
              <RCheckbox v-model="form.block_visibility.attractions" label="Достопримечательности" />
              <RCheckbox v-model="form.block_visibility.social_objects" label="Социальная сфера" />
              <RCheckbox v-model="form.block_visibility.energy_cities_block" label="Город в объективе «Энергии городов»" />
            </div>
          </RCard>

          <!-- Gallery -->
          <RCard elevation="raised">
            <div class="space-y-3 p-6">
              <h2 class="text-base font-bold text-gray-900">Галерея</h2>
              <div class="grid grid-cols-2 gap-2">
                <div
                  v-for="(url, gi) in form.gallery"
                  :key="gi"
                  class="group relative aspect-video overflow-hidden rounded-lg border border-gray-200 bg-gray-50"
                >
                  <img :src="url" alt="" class="h-full w-full object-cover" />
                  <div class="absolute inset-0 flex items-center justify-center gap-1 bg-black/40 opacity-0 transition group-hover:opacity-100">
                    <button type="button" class="rounded-lg bg-white/90 p-1.5 text-gray-700 transition hover:bg-white" title="Влево" :disabled="gi === 0" @click="moveGalleryItem(gi, -1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>
                    <button type="button" class="rounded-lg bg-white/90 p-1.5 text-gray-700 transition hover:bg-white" title="Вправо" :disabled="gi === form.gallery.length - 1" @click="moveGalleryItem(gi, 1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                    <button type="button" class="rounded-lg bg-red-500/90 p-1.5 text-white transition hover:bg-red-600" title="Удалить" @click="form.gallery.splice(gi, 1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <div
                  class="flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03]"
                  @click="$refs.galleryInput.click()"
                >
                  <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18a1.5 1.5 0 0 1 1.5 1.5v15a1.5 1.5 0 0 1-1.5 1.5H3a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 3 3Z" />
                  </svg>
                  <span v-if="!galleryUploading" class="text-sm font-medium text-gray-500">Загрузить фото</span>
                  <span v-else class="text-sm font-medium text-[#003274]">Загрузка…</span>
                </div>
                <button
                  type="button"
                  class="flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03]"
                  @click="showGalleryPicker = true"
                >
                  <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                  </svg>
                  <span class="text-sm font-medium text-gray-500">Из библиотеки</span>
                </button>
              </div>
              <input ref="galleryInput" type="file" accept="image/*" multiple class="hidden" @change="uploadGalleryFiles" />
            </div>
          </RCard>

          <!-- Video -->
          <RCard elevation="raised">
            <div class="space-y-3 p-6">
              <h2 class="text-base font-bold text-gray-900">Видео</h2>
              <RInput v-model="form.video_url" label="" placeholder="https://youtube.com/watch?v=..." :error="form.errors.video_url" />
              <p class="text-xs text-gray-400">YouTube или RuTube ссылка</p>
            </div>
          </RCard>

        </div>
      </div>

      <!-- Sticky save bar -->
      <div class="sticky bottom-0 z-10 -mx-4 mt-6 border-t border-gray-200 bg-white/95 px-4 py-4 backdrop-blur-sm sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex items-center gap-3">
          <RButton variant="primary" :loading="form.processing" :disabled="form.processing" class="px-12">
            Сохранить
          </RButton>
          <span v-if="form.isDirty" class="text-sm text-amber-600">Есть несохранённые изменения</span>
          <span v-if="form.recentlySuccessful" class="text-sm text-green-600">Сохранено</span>
        </div>
      </div>
    </form>

    <ContentPreview
      :open="showPreview"
      :title="form.name"
      :content="form.description"
      :image="form.image"
      :meta="[
        form.is_active ? { label: 'Активен', class: 'bg-green-50 text-green-700' } : { label: 'Скрыт', class: 'bg-gray-100 text-gray-500' },
      ]"
      @close="showPreview = false"
    />

    <MediaPickerModal
      :show="showGalleryPicker"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.image')"
      collection="cities"
      :entity-type="mediaEntityType"
      :entity-id="mediaEntityId"
      multiple
      @close="showGalleryPicker = false"
      @select="onGalleryMediaSelect"
    />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const props = defineProps({ city: Object })
const showPreview = ref(false)
const mediaEntityType = 'App\\Models\\City'
const mediaEntityId = props.city?.id || null
const galleryUploading = ref(false)
const showGalleryPicker = ref(false)

const infraFields = [
  { key: 'work', label: 'Работа' },
  { key: 'housing', label: 'Жильё' },
  { key: 'leisure', label: 'Досуг' },
  { key: 'education', label: 'Образование' },
  { key: 'medicine', label: 'Медицина' },
]

function defaultInfrastructure() {
  return { work: 0, housing: 0, leisure: 0, education: 0, medicine: 0 }
}

const socialCategories = [
  { key: 'education', label: 'Образование', placeholder: 'Филиал МИФИ' },
  { key: 'medicine', label: 'Медицина', placeholder: 'ЦМСЧ-38 ФМБА' },
  { key: 'culture', label: 'Культура', placeholder: 'ДК «Строитель»' },
]

function defaultSocialObjects() {
  return { education: [], medicine: [], culture: [] }
}

const form = useForm({
  name: props.city?.name ?? '',
  slug: props.city?.slug ?? '',
  description: props.city?.description ?? '',
  image: props.city?.image ?? '',
  coat_of_arms: props.city?.coat_of_arms ?? '',
  position: props.city?.position ?? 0,
  is_active: props.city?.is_active ?? true,
  region: props.city?.region ?? '',
  population: props.city?.population ?? null,
  founded_year: props.city?.founded_year ?? null,
  population_year: props.city?.population_year ?? null,
  timezone: props.city?.timezone ?? '',
  lat: props.city?.lat ?? null,
  lng: props.city?.lng ?? null,
  infrastructure: { ...defaultInfrastructure(), ...(props.city?.infrastructure ?? {}) },
  facts: (props.city?.facts ?? []).map(f =>
    typeof f === 'string'
      ? { title: f, url: '', description: '' }
      : { title: f.title ?? '', url: f.url ?? '', description: f.description ?? '' },
  ),
  attractions: props.city?.attractions ?? [],
  social_objects: { ...defaultSocialObjects(), ...(props.city?.social_objects ?? {}) },
  gallery: props.city?.gallery ?? [],
  video_url: props.city?.video_url ?? '',
  energy_cities_block: {
    video_url: props.city?.energy_cities_block?.video_url ?? '',
    video_title: props.city?.energy_cities_block?.video_title ?? '',
    video_subtitle: props.city?.energy_cities_block?.video_subtitle ?? '',
    description: props.city?.energy_cities_block?.description ?? '',
    button_text: props.city?.energy_cities_block?.button_text ?? '',
    button_url: props.city?.energy_cities_block?.button_url ?? '',
  },
  block_visibility: {
    facts: props.city?.block_visibility?.facts ?? true,
    infrastructure: props.city?.block_visibility?.infrastructure ?? true,
    video: props.city?.block_visibility?.video ?? true,
    attractions: props.city?.block_visibility?.attractions ?? true,
    social_objects: props.city?.block_visibility?.social_objects ?? true,
    energy_cities_block: props.city?.block_visibility?.energy_cities_block ?? true,
  },
})

function addFact() {
  form.facts.push({ title: '', url: '', description: '' })
}

function addAttraction() {
  form.attractions.push({ title: '', description: '', image: '' })
}

function addSocialItem(category) {
  form.social_objects[category].push('')
}

async function uploadGalleryFiles(e) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  galleryUploading.value = true
  for (const file of files) {
    try {
      const fd = new FormData()
      fd.append('image', file)
      const { data } = await axios.post(route('admin.upload.image'), fd)
      if (data.url) {
        form.gallery.push(data.url)
      }
    } catch {
      // skip failed uploads
    }
  }
  galleryUploading.value = false
  e.target.value = ''
}

function onGalleryMediaSelect(urls) {
  const list = Array.isArray(urls) ? urls : [urls]
  form.gallery = [...form.gallery, ...list]
  showGalleryPicker.value = false
}

function moveGalleryItem(index, direction) {
  const newIndex = index + direction
  if (newIndex < 0 || newIndex >= form.gallery.length) return
  const arr = [...form.gallery]
  const tmp = arr[index]
  arr[index] = arr[newIndex]
  arr[newIndex] = tmp
  form.gallery = arr
}

function submit() {
  props.city
    ? form.put(route('admin.cities.update', props.city.id))
    : form.post(route('admin.cities.store'))
}
</script>
