<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.materials.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к материалам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ material ? 'Редактировать раздел' : 'Новый раздел' }}</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
        <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Контент</label>
        <textarea v-model="form.content" rows="6" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
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
      <div class="flex gap-4">
        <label class="flex cursor-pointer items-center gap-2">
          <input v-model="form.in_menu" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
          <span class="text-sm text-gray-700">В меню</span>
        </label>
        <div>
          <label class="mb-1 block text-xs text-gray-500">Позиция</label>
          <input v-model.number="form.position" type="number" class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
        </div>
      </div>
      <div class="flex gap-3 border-t border-gray-200 pt-6">
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white hover:bg-rosatom-700 disabled:opacity-50">Сохранить</button>
        <Link :href="route('lms.admin.materials.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, material: Object, groups: Array })

const form = useForm({
  title: props.material?.title ?? '',
  content: props.material?.content ?? '',
  in_menu: props.material?.in_menu ?? false,
  position: props.material?.position ?? 0,
  group_ids: props.material?.groups?.map(g => g.id) ?? [],
})

function submit() {
  if (props.material) {
    form.put(route('lms.admin.materials.update', [props.event.id, props.material.id]))
  } else {
    form.post(route('lms.admin.materials.store', props.event.id))
  }
}
</script>
