<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.gamification.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к геймификации
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ rule ? 'Редактировать правило' : 'Новое правило' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.title"
          label="Название *"
          required
          :error="form.errors.title"
        />
        <RInput
          v-model="form.action"
          label="Действие (код)"
          placeholder="course_completed, test_passed, ..."
        />
        <RInput
          v-model.number="form.points"
          label="Баллы *"
          type="number"
          required
          :error="form.errors.points"
        />
        <RInput
          v-model.number="form.max_times"
          label="Макс. раз (0 = без ограничений)"
          type="number"
        />
        <div class="flex flex-wrap gap-3">
          <RCheckbox v-model="form.is_auto" label="Автоматическое" />
          <RCheckbox v-model="form.is_active" label="Активно" />
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" :loading="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.gamification.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, rule: Object })

const form = useForm({
  title: props.rule?.title ?? '',
  action: props.rule?.action ?? '',
  points: props.rule?.points ?? 0,
  max_times: props.rule?.max_times ?? null,
  is_auto: props.rule?.is_auto ?? true,
  is_active: props.rule?.is_active ?? true,
})

function submit() {
  if (props.rule) {
    form.put(route('lms.admin.gamification.update', [props.event.slug, props.rule.id]))
  } else {
    form.post(route('lms.admin.gamification.store', props.event.slug))
  }
}
</script>
