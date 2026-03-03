<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Задания – ${event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Задания</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="a in (assignments?.data || assignments || [])"
          :key="a.id"
          :href="route('lms.assignments.show', { event: event?.slug, assignment: a.id })"
          class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300"
        >
          <div class="p-6">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ a.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ a.description }}</p>
            <div class="mt-4 flex items-center justify-between">
              <span class="text-sm text-gray-400">{{ countdown(a.deadline) }}</span>
              <span
                :class="[
                  'rounded px-2 py-0.5 text-xs font-medium',
                  statusBadgeClass(a.submission_status || a.status),
                ]"
              >
                {{ statusLabel(a.submission_status || a.status) }}
              </span>
            </div>
          </div>
        </Link>
      </div>

      <div v-if="!(assignments?.data?.length || assignments?.length)" class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm">
        Задания не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'

defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  assignments: { type: [Object, Array], default: () => [] },
})

function countdown(deadline) {
  if (!deadline) return 'Без дедлайна'
  const d = new Date(deadline)
  const now = new Date()
  const diff = d - now
  if (diff < 0) return 'Истёк'
  const days = Math.floor(diff / (24 * 60 * 60 * 1000))
  if (days > 0) return `Осталось ${days} дн.`
  const hours = Math.floor(diff / (60 * 60 * 1000))
  return `Осталось ${hours} ч.`
}

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

function statusBadgeClass(status) {
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
