<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.roles.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к ролям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ role ? 'Редактировать роль' : 'Новая роль' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.name"
          label="Название *"
          placeholder="Например: Эксперт, Модератор, Спикер"
          required
          :error="form.errors.name"
        />
        <RInput
          v-model="form.slug"
          label="Slug"
          placeholder="Заполнится автоматически"
          :error="form.errors.slug"
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Краткое описание роли (необязательно)"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          />
        </div>
        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.roles.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, role: Object })

const form = useForm({
  name: props.role?.name ?? '',
  slug: props.role?.slug ?? '',
  description: props.role?.description ?? '',
})

function submit() {
  if (props.role) {
    form.put(route('lms.admin.roles.update', [props.event.slug, props.role.id]))
  } else {
    form.post(route('lms.admin.roles.store', props.event.slug))
  }
}
</script>
