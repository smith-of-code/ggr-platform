<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.assignments.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к заданиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ assignment ? 'Редактировать задание' : 'Новое задание' }}</h1>
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
          <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        </div>
        <RInput
          v-model="form.template_file"
          label="Шаблон (путь или URL)"
          placeholder="/storage/... или https://..."
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Режим выполнения</label>
          <select v-model="form.completion_mode" class="w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
            <option value="on_review">По рецензии</option>
            <option value="on_submit">При отправке</option>
          </select>
        </div>
        <RInput
          v-model="form.deadline"
          label="Дедлайн"
          type="datetime-local"
        />
        <RCheckbox v-model="form.is_active" label="Активно" />

        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" :loading="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.assignments.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, assignment: Object })

const form = useForm({
  title: props.assignment?.title ?? '',
  description: props.assignment?.description ?? '',
  template_file: props.assignment?.template_file ?? '',
  completion_mode: props.assignment?.completion_mode ?? 'on_review',
  deadline: props.assignment?.deadline ? props.assignment.deadline.slice(0, 16) : '',
  is_active: props.assignment?.is_active ?? true,
})

function submit() {
  if (props.assignment) {
    form.put(route('lms.admin.assignments.update', [props.event.slug, props.assignment.id]))
  } else {
    form.post(route('lms.admin.assignments.store', props.event.slug))
  }
}
</script>
