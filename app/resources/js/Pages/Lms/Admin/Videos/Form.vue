<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.videos.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к видео
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ video ? 'Редактировать видео' : 'Новое видео' }}</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
        <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
        <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">URL</label>
        <input v-model="form.url" type="url" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="https://..." />
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Группы</label>
        <div class="space-y-2">
          <label v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-50" :class="form.group_ids.includes(g.id) ? 'bg-rosatom-50' : ''">
            <input v-model="form.group_ids" type="checkbox" :value="g.id" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
            <span class="text-sm text-gray-900">{{ g.title }}</span>
          </label>
        </div>
      </div>
      <label class="flex cursor-pointer items-center gap-3">
        <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-gray-300 bg-white text-rosatom-600" />
        <span class="text-sm font-medium text-gray-700">Активно</span>
      </label>
      <div class="flex gap-3 border-t border-gray-200 pt-6">
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white hover:bg-rosatom-700 disabled:opacity-50">Сохранить</button>
        <Link :href="route('lms.admin.videos.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, video: Object, groups: Array })

const form = useForm({
  title: props.video?.title ?? '',
  description: props.video?.description ?? '',
  source: props.video?.source ?? '',
  url: props.video?.url ?? '',
  group_ids: props.video?.groups?.map(g => g.id) ?? [],
  is_active: props.video?.is_active ?? true,
})

function submit() {
  if (props.video) {
    form.put(route('lms.admin.videos.update', [props.event.id, props.video.id]))
  } else {
    form.post(route('lms.admin.videos.store', props.event.id))
  }
}
</script>
