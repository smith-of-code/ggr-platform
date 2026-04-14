<template>
  <LmsAdminLayout>
    <div class="mb-8">
      <Link :href="route('lms.admin.events.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к событиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">Новое событие</h1>
    </div>

    <RCard class="max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6">
        <RInput
          v-model="form.title"
          label="Название *"
          placeholder="Название события"
          required
          :error="form.errors.title"
          @blur="generateSlug"
        />
        <RInput
          v-model="form.slug"
          label="Slug"
          placeholder="Автоматически из названия"
          :error="form.errors.slug"
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Описание события" />
        </div>
        <RCheckbox v-model="form.is_active" label="Активно" />

        <div>
          <label class="mb-3 block text-sm font-medium text-gray-700">Разделы меню</label>
          <p class="mb-4 text-xs text-gray-400">Выберите, какие разделы будут доступны участникам этого события</p>
          <div class="grid gap-3 sm:grid-cols-2">
            <RCheckbox v-for="s in menuSections" :key="s.key" v-model="form.menu_config[s.key]" :label="s.label" />
          </div>
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
            Создать
          </RButton>
          <Link :href="route('lms.admin.events.index')" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const menuSections = [
  { key: 'courses', label: 'Программы' },
  { key: 'trajectories', label: 'Траектории' },
  { key: 'tests', label: 'Тестирование' },
  { key: 'assignments', label: 'Задания' },
  { key: 'leaderboard', label: 'Рейтинг' },
  { key: 'videos', label: 'Видеоматериалы' },
  { key: 'kb', label: 'База знаний' },
  { key: 'materials', label: 'Материалы' },
]

const form = useForm({
  title: '',
  slug: '',
  description: '',
  is_active: true,
  menu_config: Object.fromEntries(menuSections.map(s => [s.key, true])),
})

function slugify(text) {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
}

function generateSlug() {
  if (form.title && !form.slug) {
    form.slug = slugify(form.title)
  }
}

function submit() {
  form.post(route('lms.admin.events.store'))
}
</script>
