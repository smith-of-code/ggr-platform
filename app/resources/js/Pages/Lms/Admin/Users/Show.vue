<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.users.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к участникам
      </Link>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
          <h2 class="mb-6 text-base font-bold text-gray-900">Профиль</h2>
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-rosatom-50 text-2xl font-bold text-rosatom-700">
              {{ profile.user?.name?.charAt(0) || '?' }}
            </div>
            <div>
              <p class="text-lg font-semibold text-gray-900">{{ profile.user?.name }}</p>
              <p class="text-sm text-gray-500">{{ profile.user?.email }}</p>
              <span class="mt-2 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                {{ roleLabel(profile.role) }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="courseProgress?.length" class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
          <h2 class="mb-4 text-base font-bold text-gray-900">Прогресс по курсам</h2>
          <div class="space-y-4">
            <div v-for="cp in courseProgress" :key="cp.course_id" class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-700">{{ cp.title }}</span>
                <span class="text-gray-500">{{ cp.progress ?? 0 }}%</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-rosatom-600" :style="{ width: (cp.progress ?? 0) + '%' }" />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
          <h2 class="mb-4 text-base font-bold text-gray-900">Прогресс по курсам</h2>
          <p class="text-sm text-gray-500">Нет данных</p>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-bold text-gray-900">Результаты тестов</h2>
          <div v-if="testResults?.length" class="space-y-2">
            <div v-for="tr in testResults" :key="tr.id" class="flex justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
              <span class="text-gray-700">{{ tr.title }}</span>
              <span :class="tr.passed ? 'text-accent-green' : 'text-gray-500'">{{ tr.score ?? 0 }}%</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Нет данных</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-bold text-gray-900">Задания</h2>
          <div v-if="assignmentStatuses?.length" class="space-y-2">
            <div v-for="as in assignmentStatuses" :key="as.id" class="flex justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
              <span class="text-gray-700">{{ as.title }}</span>
              <span :class="statusColor(as.status)" class="font-medium">{{ statusLabel(as.status) }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">Нет данных</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="mb-4 text-base font-bold text-gray-900">Баллы</h2>
          <p class="text-2xl font-bold text-gray-900">{{ totalPoints ?? 0 }}</p>
        </div>
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

defineProps({
  event: Object,
  profile: Object,
  courseProgress: { type: Array, default: () => [] },
  testResults: { type: Array, default: () => [] },
  assignmentStatuses: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
})

function roleLabel(role) {
  const map = { participant: 'Участник', curator: 'Куратор', admin: 'Админ' }
  return map[role] ?? role
}

function statusLabel(status) {
  const map = { approve: 'Принято', revision: 'На доработку', reject: 'Отклонено', pending: 'На проверке' }
  return map[status] ?? status
}

function statusColor(status) {
  const map = { approve: 'text-accent-green', revision: 'text-accent-yellow', reject: 'text-red-600', pending: 'text-gray-500' }
  return map[status] ?? 'text-gray-500'
}
</script>
