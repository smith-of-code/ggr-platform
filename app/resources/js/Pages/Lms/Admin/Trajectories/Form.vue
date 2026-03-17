<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.trajectories.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к траекториям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ trajectory ? 'Редактировать траекторию' : 'Новая траектория' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основное</h2>
        </template>
        <div class="space-y-6 p-8">
          <RInput
            v-model="form.title"
            label="Название *"
            required
            :error="form.errors.title"
          />
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <RCheckbox v-model="form.is_active" label="Активна" />
        </div>
      </RCard>

      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Этапы (курсы)</h2>
        </template>
        <div class="space-y-3 p-8">
          <div v-for="(step, idx) in form.steps" :key="idx" class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
            <select v-model="step.lms_course_id" required class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
              <option value="">— Выберите курс —</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <RCheckbox v-model="step.is_locked" label="Закрыт" class="shrink-0" />
            <RInput v-model="step.opens_at" type="datetime-local" size="sm" />
            <RButton variant="ghost" size="sm" iconOnly @click="form.steps.splice(idx, 1)" class="text-red-600 hover:bg-red-50">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
              </template>
            </RButton>
          </div>
        </div>
        <template #footer>
          <RButton variant="outline" block @click="addStep" class="mt-3">
            + Добавить этап
          </RButton>
        </template>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" :disabled="form.processing" variant="primary">
          Сохранить
        </RButton>
        <Link :href="route('lms.admin.trajectories.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
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
    form.transform(d => ({ ...d, steps })).put(route('lms.admin.trajectories.update', [props.event.slug, props.trajectory.id]))
  } else {
    form.transform(d => ({ ...d, steps })).post(route('lms.admin.trajectories.store', props.event.slug))
  }
}
</script>
