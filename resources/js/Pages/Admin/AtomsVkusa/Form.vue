<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Атомы вкуса — управление контентом</h1>
      <p class="mt-1 text-sm text-gray-500">Редактирование всех блоков страницы проекта «Атомы вкуса»</p>
    </div>

    <!-- Tabs -->
    <div class="mb-6 overflow-x-auto border-b border-gray-200">
      <div class="flex gap-0">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          class="cursor-pointer whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition"
          :class="activeTab === tab.id ? 'border-[#003274] text-[#003274]' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <form @submit.prevent="submit">
      <!-- Hero -->
      <div v-show="activeTab === 'hero'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Описание программы (Hero)</h2>
          <div class="space-y-4">
            <RInput v-model="form.hero_title" label="Заголовок" :error="form.errors.hero_title" />
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Описание</label>
              <textarea v-model="form.hero_description" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
            <RInput v-model="form.hero_image" label="Фоновое изображение (URL)" />
          </div>
        </div>
      </div>

      <!-- Этапы конкурса -->
      <div v-show="activeTab === 'stages'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Этапы конкурса</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('competition_stages', {title:'', description:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.competition_stages" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Этап {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('competition_stages', i)">Удалить</button>
            </div>
            <RInput v-model="item.title" label="Заголовок" class="mb-2" />
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-600">Описание</label>
              <textarea v-model="item.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
          </div>
          <p v-if="!form.competition_stages.length" class="text-sm text-gray-400">Нет этапов</p>
        </div>
      </div>

      <!-- Условия участия -->
      <div v-show="activeTab === 'conditions'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Условия участия</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('participation_conditions', {title:'', description:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.participation_conditions" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Условие {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('participation_conditions', i)">Удалить</button>
            </div>
            <RInput v-model="item.title" label="Заголовок" class="mb-2" />
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-600">Описание</label>
              <textarea v-model="item.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
          </div>
          <p v-if="!form.participation_conditions.length" class="text-sm text-gray-400">Нет условий</p>
        </div>
      </div>

      <!-- Критерии отбора -->
      <div v-show="activeTab === 'criteria'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Критерии отбора</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('selection_criteria', {title:'', description:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.selection_criteria" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Критерий {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('selection_criteria', i)">Удалить</button>
            </div>
            <RInput v-model="item.title" label="Заголовок" class="mb-2" />
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-600">Описание</label>
              <textarea v-model="item.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
          </div>
          <p v-if="!form.selection_criteria.length" class="text-sm text-gray-400">Нет критериев</p>
        </div>
      </div>

      <!-- Итоги года -->
      <div v-show="activeTab === 'results'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Итоги года</h2>
          <div class="space-y-4">
            <RInput v-model="form.results_year" label="Год (например 2025)" />
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Контент / Релиз</label>
              <textarea v-model="form.results_content" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
          </div>
        </div>

        <!-- Галерея -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Фотографии</h3>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('results_gallery', {url:'', caption:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.results_gallery" :key="i" class="mb-3 flex items-start gap-3">
            <div class="flex-1 space-y-2">
              <RInput v-model="item.url" label="URL изображения" />
              <RInput v-model="item.caption" label="Подпись" />
            </div>
            <button type="button" class="mt-6 text-red-500 hover:text-red-700" @click="removeItem('results_gallery', i)">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
          </div>
        </div>

        <!-- Видео -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Видео</h3>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('results_videos', {url:'', title:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.results_videos" :key="i" class="mb-3 flex items-start gap-3">
            <div class="flex-1 space-y-2">
              <RInput v-model="item.url" label="URL видео" />
              <RInput v-model="item.title" label="Название" />
            </div>
            <button type="button" class="mt-6 text-red-500 hover:text-red-700" @click="removeItem('results_videos', i)">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
          </div>
        </div>

        <!-- Кейсы победителей -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Кейсы победителей</h3>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('results_cases', {name:'', city:'', text:'', image:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.results_cases" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Кейс {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('results_cases', i)">Удалить</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <RInput v-model="item.name" label="Имя победителя" />
              <RInput v-model="item.city" label="Город" />
            </div>
            <div class="mt-2">
              <label class="mb-1 block text-xs font-medium text-gray-600">Описание</label>
              <textarea v-model="item.text" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
            <RInput v-model="item.image" label="Фото (URL)" class="mt-2" />
          </div>
        </div>
      </div>

      <!-- Почему это важно -->
      <div v-show="activeTab === 'why'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Почему это важно</h2>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Текст</label>
            <textarea v-model="form.why_important_content" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Статистика</h3>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('why_important_stats', {value:'', label:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.why_important_stats" :key="i" class="mb-3 flex items-start gap-3">
            <RInput v-model="item.value" label="Значение (например 500+)" class="w-1/3" />
            <RInput v-model="item.label" label="Подпись (например Участников)" class="flex-1" />
            <button type="button" class="mt-6 text-red-500 hover:text-red-700" @click="removeItem('why_important_stats', i)">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Карта городов -->
      <div v-show="activeTab === 'map'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Города на карте</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('map_cities', {name:'', lat:0, lng:0, recipe_title:'', recipe_image:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.map_cities" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">{{ item.name || `Город ${i + 1}` }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('map_cities', i)">Удалить</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <RInput v-model="item.name" label="Название" />
              <RInput v-model.number="item.lat" label="Широта" type="number" step="any" />
              <RInput v-model.number="item.lng" label="Долгота" type="number" step="any" />
            </div>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
              <RInput v-model="item.recipe_title" label="Рецепт победителя" />
              <RInput v-model="item.recipe_image" label="Фото рецепта (URL)" />
            </div>
          </div>
          <p v-if="!form.map_cities.length" class="text-sm text-gray-400">Нет городов</p>
        </div>
      </div>

      <!-- Форма заявки -->
      <div v-show="activeTab === 'application'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Форма подачи заявки</h2>
          <div class="space-y-4">
            <RInput v-model="form.application_form_title" label="Заголовок формы" />
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Текст над формой</label>
              <textarea v-model="form.application_form_text" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
          </div>
        </div>
      </div>

      <!-- Партнёры -->
      <div v-show="activeTab === 'partners'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Партнёры и спонсоры</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('partners', {name:'', logo:'', url:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.partners" :key="i" class="mb-3 flex items-start gap-3">
            <div class="flex-1 grid gap-3 sm:grid-cols-3">
              <RInput v-model="item.name" label="Название" />
              <RInput v-model="item.logo" label="Логотип (URL)" />
              <RInput v-model="item.url" label="Ссылка" />
            </div>
            <button type="button" class="mt-6 text-red-500 hover:text-red-700" @click="removeItem('partners', i)">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
          </div>
          <p v-if="!form.partners.length" class="text-sm text-gray-400">Нет партнёров</p>
        </div>
      </div>

      <!-- Отзывы -->
      <div v-show="activeTab === 'reviews'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Отзывы участников и экспертов</h2>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('reviews', {name:'', role:'', text:'', rating:5, avatar:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.reviews" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Отзыв {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('reviews', i)">Удалить</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
              <RInput v-model="item.name" label="Имя" />
              <RInput v-model="item.role" label="Роль / Должность" />
              <RInput v-model.number="item.rating" label="Рейтинг (1-5)" type="number" min="1" max="5" />
            </div>
            <div class="mt-2">
              <label class="mb-1 block text-xs font-medium text-gray-600">Текст отзыва</label>
              <textarea v-model="item.text" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
            <RInput v-model="item.avatar" label="Аватар (URL)" class="mt-2" />
          </div>
          <p v-if="!form.reviews.length" class="text-sm text-gray-400">Нет отзывов</p>
        </div>
      </div>

      <!-- Помощь туризму -->
      <div v-show="activeTab === 'tourism'" class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Как конкурс помогает туризму</h2>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.tourism_help_content" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Продукты / Элементы</h3>
            <button type="button" class="rounded-lg bg-[#003274] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#004090]" @click="addItem('tourism_help_items', {title:'', description:'', image:''})">+ Добавить</button>
          </div>
          <div v-for="(item, i) in form.tourism_help_items" :key="i" class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-500">Элемент {{ i + 1 }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeItem('tourism_help_items', i)">Удалить</button>
            </div>
            <RInput v-model="item.title" label="Заголовок" class="mb-2" />
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-600">Описание</label>
              <textarea v-model="item.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]" />
            </div>
            <RInput v-model="item.image" label="Изображение (URL)" class="mt-2" />
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="mt-8 flex justify-end border-t border-gray-200 pt-6">
        <RButton type="submit" variant="primary" :disabled="form.processing">Сохранить</RButton>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  content: { type: Object, required: true },
})

const activeTab = ref('hero')

const tabs = [
  { id: 'hero', label: 'Описание' },
  { id: 'stages', label: 'Этапы конкурса' },
  { id: 'conditions', label: 'Условия' },
  { id: 'criteria', label: 'Критерии' },
  { id: 'results', label: 'Итоги года' },
  { id: 'why', label: 'Почему важно' },
  { id: 'map', label: 'Карта городов' },
  { id: 'application', label: 'Форма заявки' },
  { id: 'partners', label: 'Партнёры' },
  { id: 'reviews', label: 'Отзывы' },
  { id: 'tourism', label: 'Помощь туризму' },
]

const form = useForm({
  hero_title: props.content.hero_title ?? '',
  hero_description: props.content.hero_description ?? '',
  hero_image: props.content.hero_image ?? '',
  competition_stages: props.content.competition_stages ?? [],
  participation_conditions: props.content.participation_conditions ?? [],
  selection_criteria: props.content.selection_criteria ?? [],
  results_year: props.content.results_year ?? '',
  results_content: props.content.results_content ?? '',
  results_gallery: props.content.results_gallery ?? [],
  results_videos: props.content.results_videos ?? [],
  results_cases: props.content.results_cases ?? [],
  why_important_content: props.content.why_important_content ?? '',
  why_important_stats: props.content.why_important_stats ?? [],
  map_cities: props.content.map_cities ?? [],
  application_form_title: props.content.application_form_title ?? '',
  application_form_text: props.content.application_form_text ?? '',
  partners: props.content.partners ?? [],
  reviews: props.content.reviews ?? [],
  tourism_help_content: props.content.tourism_help_content ?? '',
  tourism_help_items: props.content.tourism_help_items ?? [],
})

function addItem(field, template) {
  form[field].push({ ...template })
}

function removeItem(field, index) {
  form[field].splice(index, 1)
}

function submit() {
  form.put(route('admin.atoms-vkusa.update'))
}
</script>
