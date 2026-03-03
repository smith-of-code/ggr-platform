<template>
  <LmsLayout :event="event" :user="authUser" :profile="profile">
    <Head :title="`Прогресс: ${displayUser?.name}`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.leader.groups', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к группам
      </Link>

      <div>
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ displayUser?.name }}</h1>
        <p class="text-gray-500">{{ displayUser?.email }}</p>
        <p v-if="totalPoints != null" class="mt-2 text-lg font-semibold text-rosatom-600">
          Всего баллов: {{ totalPoints }}
        </p>
      </div>

      <!-- Course progress -->
      <div>
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Прогресс по курсам</h2>
        <div class="space-y-3">
          <Link
            v-for="cp in (courseProgress || [])"
            :key="cp.course?.id"
            :href="route('lms.courses.show', { event: event?.slug, course: cp.course?.id })"
            class="flex items-center justify-between rounded-xl border border-gray-200 bg-white shadow-sm p-4 transition hover:border-gray-300"
          >
            <span class="font-medium text-gray-900">{{ cp.course?.title }}</span>
            <div class="flex items-center gap-3">
              <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100">
                <div
                  class="h-full rounded-full bg-rosatom-500"
                  :style="{ width: `${cp.progress ?? 0}%` }"
                />
              </div>
              <span class="w-12 text-right text-sm text-gray-500">{{ cp.progress ?? 0 }}%</span>
              <ChevronRightIcon class="h-5 w-5 text-gray-400" />
            </div>
          </Link>
        </div>
        <p v-if="!(courseProgress?.length)" class="py-8 text-center text-gray-400">
          Нет записей на курсы
        </p>
      </div>

      <!-- Test results -->
      <div v-if="(testResults || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Результаты тестов</h2>
        <div class="space-y-2">
          <div
            v-for="t in testResults"
            :key="t.id || t.test?.id"
            class="flex items-center justify-between rounded-xl border border-gray-200 bg-white shadow-sm px-6 py-4"
          >
            <span class="font-medium text-gray-900">{{ t.title ?? t.test?.title }}</span>
            <span class="rounded px-2 py-0.5 text-sm font-medium text-rosatom-600">
              {{ t.best_score ?? t.score ?? 0 }}%
            </span>
          </div>
        </div>
      </div>

      <!-- Assignment statuses -->
      <div v-if="(assignmentStatuses || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Задания</h2>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="a in assignmentStatuses"
            :key="a.id"
            :class="[
              'rounded-lg px-3 py-1.5 text-sm font-medium',
              assignmentBadgeClass(a.status),
            ]"
          >
            {{ a.title }}: {{ statusLabel(a.status) }}
          </span>
        </div>
      </div>

      <!-- Trajectory enrollments -->
      <div v-if="(trajectoryEnrollments || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Траектории</h2>
        <div class="space-y-2">
          <div
            v-for="te in trajectoryEnrollments"
            :key="te.trajectory?.id"
            class="flex items-center justify-between rounded-xl border border-gray-200 bg-white shadow-sm px-6 py-4"
          >
            <span class="font-medium text-gray-900">{{ te.trajectory?.title }}</span>
            <span
              :class="[
                'rounded px-2 py-0.5 text-xs font-medium',
                te.enrollment?.status === 'completed' ? 'bg-accent-green/10 text-accent-green' : 'bg-gray-200 text-gray-700',
              ]"
            >
              {{ te.enrollment?.status === 'completed' ? 'Завершено' : 'В процессе' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  profile: { type: Object, default: () => ({}) },
  user: { type: Object, required: true },
  courseProgress: { type: Array, default: () => [] },
  testResults: { type: Array, default: () => [] },
  assignmentStatuses: { type: Array, default: () => [] },
  trajectoryEnrollments: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: null },
})

const displayUser = computed(() => props.user)
const authUser = computed(() => usePage().props.auth?.user || {})

function statusLabel(status) {
  const map = {
    not_submitted: 'Не сдано',
    submitted: 'Сдано',
    revision: 'На доработке',
    approved: 'Принято',
    rejected: 'Отклонено',
  }
  return map[status] || status
}

function assignmentBadgeClass(status) {
  const map = {
    not_submitted: 'bg-gray-200 text-gray-700',
    submitted: 'bg-rosatom-50 text-rosatom-500',
    revision: 'bg-accent-yellow/10 text-accent-yellow',
    approved: 'bg-accent-green/10 text-accent-green',
    rejected: 'bg-red-100 text-red-600',
  }
  return map[status] || 'bg-gray-200 text-gray-700'
}
</script>
