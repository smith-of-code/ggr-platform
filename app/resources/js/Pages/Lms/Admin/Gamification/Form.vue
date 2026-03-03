<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.gamification.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к геймификации
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ rule ? 'Редактировать правило' : 'Новое правило' }}</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
        <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        <p v-if="form.errors.title" class="mt-1.5 text-xs text-red-600">{{ form.errors.title }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Действие (код)</label>
        <input v-model="form.action" type="text" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="course_completed, test_passed, ..." />
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Баллы *</label>
        <input v-model.number="form.points" type="number" required min="0" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        <p v-if="form.errors.points" class="mt-1.5 text-xs text-red-600">{{ form.errors.points }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Макс. раз (0 = без ограничений)</label>
        <input v-model.number="form.max_times" type="number" min="0" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
      </div>
      <div class="flex flex-wrap gap-3">
        <label class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-300 px-4 py-2.5 transition hover:bg-gray-50" :class="form.is_auto ? 'border-rosatom-500 bg-rosatom-50' : ''">
          <input v-model="form.is_auto" type="checkbox" class="h-4 w-4 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
          <span class="text-sm font-medium text-gray-700">Автоматическое</span>
        </label>
        <label class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-300 px-4 py-2.5 transition hover:bg-gray-50" :class="form.is_active ? 'border-rosatom-500 bg-rosatom-50' : ''">
          <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
          <span class="text-sm font-medium text-gray-700">Активно</span>
        </label>
      </div>

      <div class="flex gap-3 border-t border-gray-200 pt-6">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Сохранить
        </button>
        <Link :href="route('lms.admin.gamification.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
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
    form.put(route('lms.admin.gamification.update', [props.event.id, props.rule.id]))
  } else {
    form.post(route('lms.admin.gamification.store', props.event.id))
  }
}
</script>
