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
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Прогресс по программам</h2>
        <div class="space-y-3">
          <Link
            v-for="cp in (courseProgress || [])"
            :key="cp.course?.id"
            :href="route('lms.courses.show', { event: event?.slug, course: cp.course?.id })"
            class="block"
          >
            <RCard hoverable class="flex items-center justify-between p-4">
              <span class="font-medium text-gray-900">{{ cp.course?.title }}</span>
              <div class="flex items-center gap-3">
                <RProgress :percentage="cp.progress ?? 0" show-label size="sm" />
                <ChevronRightIcon class="h-5 w-5 text-gray-400" />
              </div>
            </RCard>
          </Link>
        </div>
        <p v-if="!(courseProgress?.length)" class="py-8 text-center text-gray-400">
          Нет записей на программы
        </p>
      </div>

      <!-- Test results -->
      <div v-if="(testResults || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Результаты тестов</h2>
        <div class="space-y-2">
          <RCard
            v-for="t in testResults"
            :key="t.id || t.test?.id"
            class="flex items-center justify-between px-6 py-4"
          >
            <span class="font-medium text-gray-900">{{ t.title ?? t.test?.title }}</span>
            <RBadge variant="primary">
              {{ t.best_score ?? t.score ?? 0 }}%
            </RBadge>
          </RCard>
        </div>
      </div>

      <!-- Assignment statuses -->
      <div v-if="(assignmentStatuses || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Задания</h2>
        <div class="flex flex-wrap gap-2">
          <RBadge
            v-for="a in assignmentStatuses"
            :key="a.id"
            :variant="assignmentBadgeVariant(a.status)"
          >
            {{ a.title }}: {{ statusLabel(a.status) }}
          </RBadge>
        </div>
      </div>

      <!-- Trajectory enrollments -->
      <div v-if="(trajectoryEnrollments || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Траектории</h2>
        <div class="space-y-2">
          <RCard
            v-for="te in trajectoryEnrollments"
            :key="te.trajectory?.id"
            class="flex items-center justify-between px-6 py-4"
          >
            <span class="font-medium text-gray-900">{{ te.trajectory?.title }}</span>
            <RBadge :variant="te.enrollment?.status === 'completed' ? 'success' : 'neutral'">
              {{ te.enrollment?.status === 'completed' ? 'Завершено' : 'В процессе' }}
            </RBadge>
          </RCard>
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

function assignmentBadgeVariant(status) {
  const map = {
    not_submitted: 'neutral',
    submitted: 'info',
    revision: 'warning',
    approved: 'success',
    rejected: 'error',
  }
  return map[status] || 'neutral'
}
</script>
