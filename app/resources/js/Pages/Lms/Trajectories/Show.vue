<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${trajectory?.title} – Траектории`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.trajectories.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к траекториям
      </Link>

      <div>
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ trajectory?.title }}</h1>
        <p v-if="trajectory?.description" class="mt-2 text-gray-500">{{ trajectory.description }}</p>
      </div>

      <!-- Enroll button if not enrolled -->
      <Link
        v-if="!enrollment"
        :href="route('lms.trajectories.enroll', { event: event?.slug, trajectory: trajectory?.id })"
        method="post"
        as="button"
        class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700"
      >
        Записаться на траекторию
      </Link>

      <!-- Timeline of steps -->
      <div v-if="(steps || []).length > 0" class="space-y-4">
        <h2 class="font-brand text-lg font-semibold text-gray-900">Этапы траектории</h2>
        <div class="space-y-4">
          <div
            v-for="(s, idx) in steps"
            :key="s.step?.id || idx"
            class="flex gap-4"
          >
            <div class="flex shrink-0 flex-col items-center">
              <div
                :class="[
                  'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                  stepStatusClass(s),
                ]"
              >
                <LockClosedIcon v-if="s.step?.is_locked" class="h-5 w-5" />
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <div
                v-if="idx < steps.length - 1"
                class="mt-1 h-full min-h-[2rem] w-0.5 flex-1 bg-gray-200"
              />
            </div>
            <div class="min-w-0 flex-1 pb-4">
              <Link
                v-if="canAccessStep(s)"
                :href="route('lms.courses.show', { event: event?.slug, course: s.course?.id })"
                class="block rounded-xl border border-gray-200 bg-white shadow-sm p-4 transition hover:border-gray-300"
              >
                <div class="flex items-center justify-between">
                  <h3 class="font-medium text-gray-900">{{ s.course?.title }}</h3>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                </div>
                <p v-if="s.step?.opens_at" class="mt-1 text-sm text-gray-400">
                  Открывается: {{ formatDate(s.step.opens_at) }}
                </p>
                <div class="mt-3">
                  <div class="flex justify-between text-xs text-gray-500">
                    <span>Прогресс</span>
                    <span>{{ s.progress ?? 0 }}%</span>
                  </div>
                  <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full bg-rosatom-500"
                      :style="{ width: `${s.progress ?? 0}%` }"
                    />
                  </div>
                </div>
              </Link>
              <div
                v-else
                class="block rounded-xl border border-gray-200 bg-white p-4 opacity-75"
              >
                <div class="flex items-center justify-between">
                  <h3 class="font-medium text-gray-400">{{ s.course?.title }}</h3>
                  <LockClosedIcon class="h-5 w-5 text-gray-400" />
                </div>
                <p v-if="s.step?.opens_at" class="mt-1 text-sm text-gray-400">
                  Открывается: {{ formatDate(s.step.opens_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!(steps?.length) && enrollment"
        class="rounded-xl border border-gray-200 bg-white py-12 text-center text-gray-400 shadow-sm"
      >
        В траектории пока нет этапов
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, ChevronRightIcon, LockClosedIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  trajectory: { type: Object, required: true },
  enrollment: { type: Object, default: null },
  steps: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function canAccessStep(s) {
  if (s.step?.is_locked) return false
  if (s.step?.opens_at) {
    const opensAt = new Date(s.step.opens_at)
    if (opensAt > new Date()) return false
  }
  return true
}

function stepStatusClass(s) {
  if (s.step?.is_locked) return 'bg-gray-100 text-gray-400'
  const p = s.progress ?? 0
  if (p >= 100) return 'bg-rosatom-50 text-rosatom-600'
  if (p > 0) return 'bg-rosatom-50 text-rosatom-500'
  return 'bg-gray-200 text-gray-700'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
