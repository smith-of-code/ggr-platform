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
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Группы</label>
          <div class="space-y-2">
            <div v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-50" :class="form.group_ids.includes(g.id) ? 'bg-rosatom-50' : ''">
              <RCheckbox
                :model-value="form.group_ids.includes(g.id)"
                @update:model-value="(v) => { if (v) form.group_ids.push(g.id); else form.group_ids = form.group_ids.filter(id => id !== g.id) }"
                :label="g.title"
              />
            </div>
          </div>
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
    form.put(route('lms.admin.videos.update', [props.event.slug, props.video.id]))
  } else {
    form.post(route('lms.admin.videos.store', props.event.slug))
  }
}
</script>
