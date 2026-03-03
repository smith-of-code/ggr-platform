<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.trajectories.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к траекториям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ trajectory ? 'Редактировать траекторию' : 'Новая траектория' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">Основное</h2>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
          <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        </div>
        <label class="flex cursor-pointer items-center gap-3">
          <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-gray-300 bg-white text-rosatom-600" />
          <span class="text-sm font-medium text-gray-700">Активна</span>
        </label>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="mb-4 text-base font-bold text-gray-900">Этапы (курсы)</h2>
        <div class="space-y-3">
          <div v-for="(step, idx) in form.steps" :key="idx" class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
            <select v-model="step.lms_course_id" required class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
              <option value="">— Выберите курс —</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <label class="flex shrink-0 items-center gap-2">
              <input v-model="step.is_locked" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
              <span class="text-sm text-gray-500">Закрыт</span>
            </label>
            <input v-model="step.opens_at" type="datetime-local" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
            <button type="button" @click="form.steps.splice(idx, 1)" class="rounded p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
          </div>
        </div>
        <button type="button" @click="addStep" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 py-3 text-sm text-gray-500 hover:border-rosatom-500 hover:text-rosatom-600">
          + Добавить этап
        </button>
      </div>

      <div class="flex gap-3">
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white hover:bg-rosatom-700 disabled:opacity-50">
          Сохранить
        </button>
        <Link :href="route('lms.admin.trajectories.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, trajectory: Object, courses: Array })

const buildSteps = () => {
  if (props.trajectory?.steps?.length) {
    return props.trajectory.steps.map(s => ({
      lms_course_id: s.lms_course_id ?? s.course?.id,
      is_locked: s.is_locked ?? false,
      opens_at: s.opens_at ? s.opens_at.slice(0, 16) : '',
    }))
  }
  return [{ lms_course_id: '', is_locked: false, opens_at: '' }]
}

const form = useForm({
  title: props.trajectory?.title ?? '',
  description: props.trajectory?.description ?? '',
  is_active: props.trajectory?.is_active ?? true,
  steps: buildSteps(),
})

function addStep() {
  form.steps.push({ lms_course_id: '', is_locked: false, opens_at: '' })
}

function submit() {
  const steps = form.steps.filter(s => s.lms_course_id).map((s, i) => ({ ...s, position: i }))
  if (props.trajectory) {
    form.transform(d => ({ ...d, steps })).put(route('lms.admin.trajectories.update', [props.event.id, props.trajectory.id]))
  } else {
    form.transform(d => ({ ...d, steps })).post(route('lms.admin.trajectories.store', props.event.id))
  }
}
</script>
