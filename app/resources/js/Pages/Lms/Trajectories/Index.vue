<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Траектории – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Образовательные траектории</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="t in (trajectories || [])"
          :key="t.trajectory?.id"
          class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        >
          <Link
            :href="route('lms.trajectories.show', { event: event?.slug, trajectory: t.trajectory?.id })"
            class="flex-1 p-6"
          >
            <h3 class="font-semibold text-gray-900 hover:text-rosatom-600">{{ t.trajectory?.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ t.trajectory?.description || '–' }}</p>
            <div class="mt-4">
              <div class="flex justify-between text-sm text-gray-500">
                <span>Шагов: {{ stepsCount(t) }}</span>
                <span>{{ progressPercent(t) }}%</span>
              </div>
              <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100">
                <div
                  class="h-full rounded-full bg-rosatom-500 transition-all"
                  :style="{ width: `${progressPercent(t)}%` }"
                />
              </div>
            </div>
          </Link>
          <div class="border-t border-gray-200 p-4">
            <Link
              v-if="!t.enrolled"
              :href="route('lms.trajectories.enroll', { event: event?.slug, trajectory: t.trajectory?.id })"
              method="post"
              as="button"
              class="w-full rounded-xl bg-rosatom-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            >
              Записаться
            </Link>
            <Link
              v-else
              :href="route('lms.trajectories.show', { event: event?.slug, trajectory: t.trajectory?.id })"
              class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            >
              Подробнее
            </Link>
          </div>
        </div>
      </div>

      <div
        v-if="!(trajectories?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        Траектории не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  trajectories: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function stepsCount(t) {
  return t.steps_count ?? t.trajectory?.steps_count ?? 0
}

function progressPercent(t) {
  const completed = t.completed_steps ?? 0
  const total = stepsCount(t) || 1
  return Math.round((completed / total) * 100)
}
</script>
