<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.directions.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к направлениям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ direction ? 'Редактировать направление' : 'Новое направление' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Основная информация -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Основная информация</h2>
          <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <RInput v-model="form.title" label="Название *" :error="form.errors.title" required />
            </div>
            <RInput v-model="form.slug" label="Slug" placeholder="auto-generated" />
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Проект (project_key)</label>
              <select v-model="form.project_key" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                <option value="">—</option>
                <option v-for="(label, key) in projectKeys" :key="key" :value="key">{{ label }}</option>
              </select>
            </div>
            <ImageUploadCrop v-model="form.image" label="Изображение" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="directions" :entity-type="mediaEntityType" :entity-id="mediaEntityId" :skip-crop="true" preview-class="h-32 w-full object-cover" />
            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
              <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <RInput v-model.number="form.position" label="Позиция" type="number" />
            <div class="flex items-center gap-3 pt-6">
              <RCheckbox v-model:checked="form.is_active" />
              <span class="text-sm font-medium text-gray-700">Активно</span>
            </div>
          </div>
        </div>
      </RCard>

      <!-- Поднаправления -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Поднаправления</h2>
          <div class="grid gap-5 sm:grid-cols-2">
            <RInput v-model="form.sub_directions_title" label="Заголовок секции" />
            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-gray-700">Описание секции</label>
              <textarea v-model="form.sub_directions_description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
          </div>
          <div v-for="(sd, i) in form.sub_directions" :key="i" class="rounded-xl border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-500">Поднаправление {{ i + 1 }}</span>
              <button type="button" @click="form.sub_directions.splice(i, 1)" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="sd.name" label="Название" />
              <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                <textarea v-model="sd.description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
              </div>
            </div>
          </div>
          <button type="button" @click="form.sub_directions.push({ name: '', description: '' })" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">+ Добавить поднаправление</button>
        </div>
      </RCard>

      <!-- Целевые аудитории -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Целевые аудитории</h2>
          <div v-for="(ta, i) in form.target_audiences" :key="i" class="rounded-xl border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-500">Аудитория {{ i + 1 }}</span>
              <button type="button" @click="form.target_audiences.splice(i, 1)" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
              <RInput v-model.number="ta.number" label="Номер" type="number" />
              <div class="sm:col-span-2">
                <RInput v-model="ta.title" label="Заголовок" />
              </div>
              <div class="sm:col-span-3">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                <textarea v-model="ta.description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
              </div>
            </div>
          </div>
          <button type="button" @click="form.target_audiences.push({ number: form.target_audiences.length + 1, title: '', description: '' })" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">+ Добавить аудиторию</button>
          <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Примечание о целевой аудитории</label>
            <textarea v-model="form.target_audience_note" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
          </div>
        </div>
      </RCard>

      <!-- Бесплатное участие -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Бесплатное участие (конкурс)</h2>
          <div v-for="(step, i) in form.free_participation_steps" :key="i" class="rounded-xl border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-500">Шаг {{ i + 1 }}</span>
              <button type="button" @click="form.free_participation_steps.splice(i, 1)" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="step.title" label="Заголовок" />
              <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                <textarea v-model="step.description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
              </div>
            </div>
          </div>
          <button type="button" @click="form.free_participation_steps.push({ title: '', description: '' })" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">+ Добавить шаг</button>

          <h3 class="pt-4 text-sm font-bold text-gray-900">Конкурсные детали</h3>
          <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Вопросы (по одному на строку)</label>
            <textarea v-model="questionsText" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Каждый вопрос на отдельной строке" />
          </div>
          <RInput v-model="form.free_participation_details.challenge_title" label="Заголовок проверочного задания" />
          <div>
            <label class="mb-2 block text-sm font-semibold text-gray-700">Описание проверочного задания</label>
            <textarea v-model="form.free_participation_details.challenge_description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
          </div>
        </div>
      </RCard>

      <!-- Платное участие -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Платное участие</h2>
          <div v-for="(step, i) in form.paid_participation_steps" :key="i" class="rounded-xl border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-500">Шаг {{ i + 1 }}</span>
              <button type="button" @click="form.paid_participation_steps.splice(i, 1)" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput v-model="step.title" label="Заголовок" />
              <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                <textarea v-model="step.description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
              </div>
            </div>
          </div>
          <button type="button" @click="form.paid_participation_steps.push({ title: '', description: '' })" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">+ Добавить шаг</button>

          <div v-if="lmsForms.length" class="pt-2">
            <label class="mb-2 block text-sm font-semibold text-gray-700">Форма для кнопки «Оставить заявку»</label>
            <select v-model="form.paid_form_slug" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
              <option value="">— Нет (скролл к турам)</option>
              <option v-for="f in lmsForms" :key="f.slug" :value="f.slug">{{ f.title }}</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">Если выбрана форма, кнопка «Оставить заявку» откроет её в модальном окне</p>
          </div>
        </div>
      </RCard>

      <!-- Туры -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Туры для слайдшоу</h2>
          <p class="text-sm text-gray-500">Выберите туры, которые будут отображаться на странице направления</p>
          <div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-gray-200 p-3">
            <label v-for="t in tours" :key="t.id" class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-gray-50">
              <input type="checkbox" :value="t.id" v-model="form.featured_tour_ids" class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]/20" />
              <span class="text-sm text-gray-700">{{ t.title }}</span>
              <RBadge v-if="t.project" variant="primary" size="sm" class="ml-auto">{{ projectLabel(t.project) }}</RBadge>
            </label>
          </div>
        </div>
      </RCard>

      <!-- Кнопки -->
      <div class="flex items-center justify-end gap-4 pb-8">
        <Link :href="route('admin.directions.index')" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
        <RButton type="submit" variant="primary" size="md" :disabled="form.processing">
          {{ direction ? 'Сохранить' : 'Создать' }}
        </RButton>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'

const props = defineProps({
  direction: { type: Object, default: null },
  tours: { type: Array, default: () => [] },
  projectKeys: { type: Object, default: () => ({}) },
  lmsForms: { type: Array, default: () => [] },
})

const mediaEntityType = 'App\\Models\\Direction'
const mediaEntityId = props.direction?.id || null

const form = useForm({
  title: props.direction?.title ?? '',
  slug: props.direction?.slug ?? '',
  description: props.direction?.description ?? '',
  image: props.direction?.image ?? '',
  project_key: props.direction?.project_key ?? '',
  sub_directions_title: props.direction?.sub_directions_title ?? '',
  sub_directions_description: props.direction?.sub_directions_description ?? '',
  sub_directions: props.direction?.sub_directions ?? [],
  target_audiences: props.direction?.target_audiences ?? [],
  target_audience_note: props.direction?.target_audience_note ?? '',
  free_participation_steps: props.direction?.free_participation_steps ?? [],
  free_participation_details: props.direction?.free_participation_details ?? { questions: [], challenge_title: '', challenge_description: '' },
  paid_participation_steps: props.direction?.paid_participation_steps ?? [],
  paid_form_slug: props.direction?.paid_form_slug ?? '',
  featured_tour_ids: props.direction?.featured_tour_ids ?? [],
  is_active: props.direction?.is_active ?? true,
  position: props.direction?.position ?? 0,
})

const questionsText = computed({
  get: () => (form.free_participation_details.questions ?? []).join('\n'),
  set: (val) => {
    form.free_participation_details.questions = val.split('\n').filter(q => q.trim())
  },
})

function projectLabel(k) {
  return { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }[k] || k || '—'
}

function submit() {
  if (props.direction) {
    form.put(route('admin.directions.update', props.direction.id))
  } else {
    form.post(route('admin.directions.store'))
  }
}
</script>
