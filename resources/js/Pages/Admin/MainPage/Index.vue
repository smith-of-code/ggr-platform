<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Главная страница</h1>
      <p class="mt-1 text-sm text-gray-500">Перетаскивайте блоки для изменения порядка. Нажмите на блок, чтобы развернуть редактирование.</p>
    </div>

    <form @submit.prevent="submit" class="space-y-3">
      <div
        v-for="(block, idx) in form.block_order"
        :key="block.id"
        class="rounded-2xl border bg-white shadow-sm transition-all"
        :class="dragOverIdx === idx ? 'border-[#003274] ring-2 ring-[#003274]/20' : 'border-gray-200'"
      >
        <!-- Block header: drag + name + toggle + expand -->
        <div
          draggable="true"
          class="flex cursor-grab items-center gap-3 px-5 py-3.5 active:cursor-grabbing"
          @dragstart="onDragStart(idx)"
          @dragover.prevent="onDragOver(idx)"
          @dragleave="onDragLeave"
          @drop="onDrop(idx)"
          @dragend="onDragEnd"
        >
          <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>

          <button
            type="button"
            class="flex min-w-0 flex-1 items-center gap-2 text-left"
            @click.stop="toggleExpand(block.id)"
          >
            <span class="text-sm font-semibold text-gray-800">{{ blockLabels[block.id] || block.id }}</span>
            <span v-if="!block.enabled" class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-400">скрыт</span>
            <span v-if="isDynamic(block.id)" class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-500">только заголовок</span>
          </button>

          <label class="relative inline-flex shrink-0 cursor-pointer items-center" @click.stop>
            <input v-model="block.enabled" type="checkbox" class="peer sr-only" />
            <div class="h-5 w-9 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[#003274] peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
          </label>

          <button
            type="button"
            class="shrink-0 rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
            @click.stop="toggleExpand(block.id)"
          >
            <svg
              class="h-4 w-4 transition-transform duration-200"
              :class="expanded[block.id] ? 'rotate-180' : ''"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
        </div>

        <!-- Block body (expandable) -->
        <div v-if="expanded[block.id]" class="border-t border-gray-100 px-5 pb-5 pt-4 space-y-4">
          <!-- Section title fields (if this block has them) -->
          <div v-if="hasSectionTitle(block.id)" class="rounded-lg border border-gray-100 bg-gray-50/60 p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Заголовок секции</p>
            <div class="grid gap-3 sm:grid-cols-2">
              <RInput v-model="form.section_titles[block.id].title" label="Заголовок" :placeholder="blockLabels[block.id]" />
              <RInput v-model="form.section_titles[block.id].subtitle" label="Подзаголовок" placeholder="Краткое описание секции" />
            </div>
          </div>

          <!-- Block-specific content -->
          <template v-if="block.id === 'hero'">
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="form.hero_title" label="Заголовок *" :error="form.errors.hero_title" />
              <RInput v-model="form.hero_bg_image" label="Фоновое изображение (URL)" :error="form.errors.hero_bg_image" />
            </div>
            <RInput v-model="form.hero_description" label="Описание *" :error="form.errors.hero_description" />
            <p class="text-xs text-gray-500">Градиент фона: начало, середина (необязательно), конец.</p>
            <div class="grid gap-4 sm:grid-cols-3">
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
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Цвет текста</label>
                <div class="flex items-center gap-3">
                  <input type="color" v-model="form.hero_text_color" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200" />
                  <RInput v-model="form.hero_text_color" placeholder="#ffffff" class="flex-1" />
                </div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <RCheckbox v-model="form.hero_bg_color_enabled" />
              <span class="text-sm font-medium text-gray-700">Использовать свой градиент (вместо стандартного)</span>
            </div>
          </template>

          <template v-else-if="block.id === 'program_stages'">
            <DynamicList
              v-model="form.program_stages"
              :fields="[
                { key: 'step', label: 'Номер этапа', placeholder: 'Этап 1' },
                { key: 'title', label: 'Заголовок', placeholder: 'Название этапа' },
                { key: 'description', label: 'Описание', placeholder: 'Описание этапа...', type: 'textarea' },
                { key: 'image', label: 'Изображение (URL)', placeholder: 'https://...' },
                { key: 'buttonLabel', label: 'Текст кнопки', placeholder: 'Перейти' },
                { key: 'href', label: 'Ссылка кнопки', placeholder: '/research' },
              ]"
              add-label="Добавить этап"
              :new-item="{ step: '', title: '', description: '', image: '', buttonLabel: 'Скоро', href: '' }"
            />
          </template>

          <template v-else-if="block.id === 'program_cities'">
            <div v-for="(yearGroup, yIdx) in form.program_cities" :key="yIdx" class="mb-4 rounded-xl border border-gray-200 p-4">
              <div class="mb-3 flex items-center gap-3">
                <RInput v-model.number="yearGroup.year" label="Год" type="number" class="w-32" />
                <button
                  v-if="form.program_cities.length > 1"
                  type="button"
                  @click="form.program_cities.splice(yIdx, 1)"
                  class="mt-5 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <DynamicList
                v-model="yearGroup.cities"
                :fields="[
                  { key: 'name', label: 'Город', placeholder: 'Саров' },
                  { key: 'region', label: 'Регион', placeholder: 'Нижегородская область' },
                  { key: 'image', label: 'Изображение (URL)', placeholder: 'https://...' },
                ]"
                add-label="Добавить город"
                :new-item="{ name: '', region: '', image: '' }"
              />
            </div>
            <button type="button" @click="form.program_cities.push({ year: new Date().getFullYear(), cities: [{ name: '', region: '', image: '' }] })" class="rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:border-[#003274] hover:text-[#003274]">
              + Добавить год
            </button>
          </template>

          <template v-else-if="block.id === 'program_results'">
            <RInput v-model="form.program_results_image" label="Изображение результатов (URL)" :error="form.errors.program_results_image" />
            <div v-for="(yearGroup, yIdx) in form.program_results" :key="yIdx" class="mb-4 rounded-xl border border-gray-200 p-4">
              <div class="mb-3 flex items-center gap-3">
                <RInput v-model.number="yearGroup.year" label="Год" type="number" class="w-32" />
                <button
                  v-if="form.program_results.length > 1"
                  type="button"
                  @click="form.program_results.splice(yIdx, 1)"
                  class="mt-5 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <DynamicList
                v-model="yearGroup.results"
                :fields="[
                  { key: 'value', label: 'Значение', placeholder: '13 млн руб.' },
                  { key: 'description', label: 'Описание', placeholder: 'Грантовой поддержки...', type: 'textarea' },
                ]"
                add-label="Добавить результат"
                :new-item="{ value: '', description: '' }"
              />
            </div>
            <button type="button" @click="form.program_results.push({ year: new Date().getFullYear(), results: [{ value: '', description: '' }] })" class="rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:border-[#003274] hover:text-[#003274]">
              + Добавить год
            </button>
          </template>

          <template v-else-if="block.id === 'city_benefits'">
            <DynamicList
              v-model="form.city_benefits"
              :fields="[
                { key: 'title', label: 'Описание', placeholder: 'Комплексное исследование...' },
                { key: 'image', label: 'Изображение (URL)', placeholder: 'https://...' },
              ]"
              add-label="Добавить преимущество"
              :new-item="{ title: '', image: '' }"
            />
          </template>

          <template v-else-if="block.id === 'additional_initiatives'">
            <DynamicList
              v-model="form.additional_initiatives"
              :fields="[
                { key: 'title', label: 'Название', placeholder: 'Гастротуризм' },
                { key: 'image', label: 'Изображение (URL)', placeholder: 'https://...' },
              ]"
              add-label="Добавить инициативу"
              :new-item="{ title: '', image: '' }"
            />
          </template>

          <template v-else-if="block.id === 'videos'">
            <DynamicList
              v-model="form.videos"
              :fields="[
                { key: 'title', label: 'Название', placeholder: 'Гостеприимные города — о программе' },
                { key: 'thumbnail', label: 'Обложка (URL)', placeholder: 'https://...' },
                { key: 'embedUrl', label: 'Embed URL', placeholder: 'https://vk.com/video_ext.php?...' },
                { key: 'videoFile', label: 'Видеофайл (URL)', placeholder: 'https://...' },
              ]"
              add-label="Добавить видео"
              :new-item="{ title: '', thumbnail: '', embedUrl: '', videoFile: '' }"
            />
          </template>

          <template v-else-if="block.id === 'moving'">
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="form.moving_title" label="Заголовок *" :error="form.errors.moving_title" />
              <RInput v-model="form.moving_description" label="Описание *" :error="form.errors.moving_description" />
            </div>
          </template>

          <template v-else-if="block.id === 'stats'">
            <div
              v-for="(card, ci) in form.stats_cards"
              :key="ci"
              class="rounded-lg border border-gray-100 bg-gray-50/60 p-4"
            >
              <div class="mb-3 flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Карточка {{ ci + 1 }}</span>
                <button
                  v-if="form.stats_cards.length > 1"
                  type="button"
                  class="text-xs text-red-500 hover:text-red-700"
                  @click="form.stats_cards.splice(ci, 1)"
                >Удалить</button>
              </div>
              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-600">Иконка</label>
                  <IconPicker v-model="card.icon" />
                </div>
                <RInput v-model="card.label" label="Подпись *" placeholder="Атомных городов" />
                <div>
                  <label class="mb-1 block text-xs font-medium text-gray-600">Источник</label>
                  <select
                    v-model="card.source"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
                  >
                    <option value="cities">Города (авто)</option>
                    <option value="tours">Туры (авто)</option>
                    <option value="events">Хронология (авто)</option>
                    <option value="custom">Своё значение</option>
                  </select>
                </div>
                <RInput v-if="card.source === 'custom'" v-model="card.value" label="Значение *" placeholder="3000+" />
              </div>
            </div>
            <button
              type="button"
              class="mt-2 rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm text-gray-500 transition hover:border-gray-400 hover:text-gray-700"
              @click="form.stats_cards.push({ icon: 'star', label: '', source: 'custom', value: '' })"
            >+ Добавить карточку</button>
          </template>

          <template v-else-if="block.id === 'cta'">
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="form.cta_title" label="Заголовок *" :error="form.errors.cta_title" />
              <RInput v-model="form.cta_description" label="Описание *" :error="form.errors.cta_description" />
            </div>
          </template>

          <template v-else-if="block.id === 'contact_form'">
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="form.contact_title" label="Заголовок *" :error="form.errors.contact_title" />
              <RInput v-model="form.contact_description" label="Описание *" :error="form.errors.contact_description" />
            </div>
            <RInput v-model="form.contact_left_text" label="Текст слева *" :error="form.errors.contact_left_text" />
            <div>
              <p class="mb-2 text-xs font-semibold text-gray-500">Буллиты (преимущества)</p>
              <DynamicList
                v-model="form.contact_bullets"
                :fields="[{ key: 'text', label: 'Текст', placeholder: 'Ответ в рабочие дни...' }]"
                add-label="Добавить пункт"
                :new-item="{ text: '' }"
              />
            </div>
          </template>

          <template v-else-if="block.id === 'contacts'">
            <DynamicList
              v-model="form.contacts"
              :fields="[
                { key: 'label', label: 'Метка', placeholder: 'Телефон' },
                { key: 'value', label: 'Значение', placeholder: '+7 (495) 668-28-83' },
                { key: 'href', label: 'Ссылка', placeholder: 'tel:+74956682883' },
              ]"
              add-label="Добавить контакт"
              :new-item="{ label: '', value: '', href: '' }"
            />
            <div class="mt-2">
              <p class="mb-2 text-xs font-semibold text-gray-500">Социальные сети</p>
              <DynamicList
                v-model="form.socials"
                :fields="[
                  { key: 'label', label: 'Название', placeholder: 'VK' },
                  { key: 'href', label: 'Ссылка', placeholder: 'https://vk.com/gostepr' },
                  { key: 'icon', label: 'Иконка', type: 'icon-select', options: socialIconOptions, iconMap: socialIconMap },
                ]"
                add-label="Добавить соцсеть"
                :new-item="{ label: '', href: '', icon: 'vk' }"
              />
            </div>
          </template>

          <!-- Dynamic-only blocks show only section titles (already rendered above) -->
          <p v-else-if="isDynamic(block.id)" class="text-xs text-gray-400">
            Контент этого блока формируется автоматически из данных сайта.
          </p>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex items-center gap-4 pt-4">
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
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SectionHeader from '@/Pages/Admin/OpportunityToursPage/SectionHeader.vue'
import DynamicList from '@/Pages/Admin/OpportunityToursPage/DynamicList.vue'
import IconPicker from '@/Components/IconPicker.vue'
import { socialIcon, socialIconKeys } from '@/utils/opportunityToursIcons'

const props = defineProps({
  pageData: { type: Object, default: () => ({}) },
})

const socialIconOptions = [
  { value: 'vk', label: 'ВКонтакте' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'max', label: 'Max' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'rutube', label: 'Рутьюб' },
  { value: 'ok', label: 'Одноклассники' },
  { value: 'dzen', label: 'Дзен' },
]

const socialIconMap = Object.fromEntries(
  socialIconKeys.map(k => [k, socialIcon(k, 'h-5 w-5')])
)

const blockLabels = {
  hero: 'Hero-блок',
  program_stages: 'Этапы программы',
  program_cities: 'Города программы',
  program_results: 'Результаты программы',
  city_benefits: 'Что получает город',
  additional_initiatives: 'Дополнительные инициативы',
  videos: 'Видеоролики',
  news: 'Новости',
  moving: 'Переезжаем',
  stats: 'Статистика',
  featured_tours: 'Популярные туры',
  cities: 'Атомные города',
  map: 'География проекта',
  recipes: 'Книга рецептов',
  timeline: 'Хронология событий',
  cta: 'Призыв к действию',
  contact_form: 'Форма обратной связи',
  contacts: 'Контакты',
}

const sectionTitleKeys = new Set([
  'program_stages', 'program_cities', 'program_results',
  'city_benefits', 'additional_initiatives', 'videos',
  'news', 'featured_tours', 'cities', 'map',
  'recipes', 'timeline', 'contacts',
])

const dynamicBlocks = new Set([
  'news', 'featured_tours', 'cities', 'map', 'recipes', 'timeline',
])

function hasSectionTitle(id) {
  return sectionTitleKeys.has(id)
}

function isDynamic(id) {
  return dynamicBlocks.has(id)
}

const defaultBlockOrder = [
  { id: 'hero', enabled: true },
  { id: 'program_stages', enabled: true },
  { id: 'program_cities', enabled: true },
  { id: 'program_results', enabled: true },
  { id: 'city_benefits', enabled: true },
  { id: 'additional_initiatives', enabled: true },
  { id: 'videos', enabled: true },
  { id: 'news', enabled: true },
  { id: 'moving', enabled: true },
  { id: 'stats', enabled: true },
  { id: 'featured_tours', enabled: true },
  { id: 'cities', enabled: true },
  { id: 'map', enabled: true },
  { id: 'recipes', enabled: true },
  { id: 'timeline', enabled: true },
  { id: 'cta', enabled: true },
  { id: 'contact_form', enabled: true },
  { id: 'contacts', enabled: true },
]

const defaultSectionTitles = {}
sectionTitleKeys.forEach(id => {
  defaultSectionTitles[id] = { title: '', subtitle: '' }
})

const d = props.pageData

const form = useForm({
  block_order: d.block_order?.length ? d.block_order : defaultBlockOrder,

  hero_title: d.hero_title ?? 'Гостеприимные города Росатома',
  hero_description: d.hero_description ?? '',
  hero_bg_image: d.hero_bg_image ?? '',
  hero_bg_color_from: d.hero_bg_color_from ?? '',
  hero_bg_color_via: d.hero_bg_color_via ?? '',
  hero_bg_color_to: d.hero_bg_color_to ?? '',
  hero_text_color: d.hero_text_color ?? '',
  hero_bg_color_enabled: Boolean(Number(d.hero_bg_color_enabled ?? 0)),

  program_stages: d.program_stages ?? [{ step: '', title: '', description: '', image: '', buttonLabel: 'Скоро', href: '' }],
  program_cities: d.program_cities ?? [{ year: new Date().getFullYear(), cities: [{ name: '', region: '', image: '' }] }],
  program_results: d.program_results ?? [{ year: new Date().getFullYear(), results: [{ value: '', description: '' }] }],
  program_results_image: d.program_results_image ?? '',

  city_benefits: d.city_benefits ?? [{ title: '', image: '' }],
  additional_initiatives: d.additional_initiatives ?? [{ title: '', image: '' }],
  videos: d.videos ?? [{ title: '', thumbnail: '', embedUrl: '', videoFile: '' }],

  moving_title: d.moving_title ?? 'Переезжаем',
  moving_description: d.moving_description ?? '',

  stats_cards: d.stats_cards?.length ? d.stats_cards : [
    { icon: 'building', label: 'Атомных городов', source: 'cities', value: '' },
    { icon: 'map', label: 'Туров возможностей', source: 'tours', value: '' },
    { icon: 'calendar', label: 'Событий в хронологии', source: 'events', value: '' },
    { icon: 'users', label: 'Гостей', source: 'custom', value: '3000+' },
  ],

  cta_title: d.cta_title ?? '',
  cta_description: d.cta_description ?? '',

  contact_title: d.contact_title ?? '',
  contact_description: d.contact_description ?? '',
  contact_left_text: d.contact_left_text ?? '',
  contact_bullets: d.contact_bullets ?? [{ text: '' }],

  contacts: d.contacts ?? [{ label: '', value: '', href: '' }],
  socials: d.socials ?? [{ label: '', href: '', icon: 'vk' }],

  section_titles: { ...defaultSectionTitles, ...(d.section_titles ?? {}) },
})

const expanded = reactive({})

function toggleExpand(id) {
  expanded[id] = !expanded[id]
}

const dragIdx = ref(null)
const dragOverIdx = ref(null)

function onDragStart(idx) {
  dragIdx.value = idx
}

function onDragOver(idx) {
  dragOverIdx.value = idx
}

function onDragLeave() {
  dragOverIdx.value = null
}

function onDrop(targetIdx) {
  if (dragIdx.value !== null && dragIdx.value !== targetIdx) {
    const items = [...form.block_order]
    const [moved] = items.splice(dragIdx.value, 1)
    items.splice(targetIdx, 0, moved)
    form.block_order = items
  }
  dragIdx.value = null
  dragOverIdx.value = null
}

function onDragEnd() {
  dragIdx.value = null
  dragOverIdx.value = null
}

function submit() {
  form.put(route('admin.main-page.update'), {
    preserveScroll: true,
  })
}
</script>
