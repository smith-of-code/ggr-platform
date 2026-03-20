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

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Действие (триггер)</label>
          <select
            v-model="form.action"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          >
            <option value="">— Без автоматического триггера —</option>
            <option v-for="(label, key) in actions" :key="key" :value="key">
              {{ label }} ({{ key }})
            </option>
          </select>
          <p class="mt-1.5 text-xs text-gray-400">Выберите действие для автоматического начисления баллов</p>
        </div>

        <RInput
          v-model.number="form.points"
          label="Баллы *"
          type="number"
          required
          :error="form.errors.points"
        />
        <RInput
          v-model.number="form.max_times"
          label="Макс. раз (пусто = без ограничений)"
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

const props = defineProps({ event: Object, rule: Object, actions: { type: Object, default: () => ({}) } })

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
