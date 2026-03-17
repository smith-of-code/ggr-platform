<template>
  <LmsLayout :event="event" :user="page.props.user" :profile="page.props.profile">
    <Head :title="`Задания – ${event?.title}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Практические задания</h1>

      <div class="mb-4">
        <input
          :value="filters?.search ?? ''"
          @input="debouncedSearch"
          type="text"
          placeholder="Поиск..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
        />
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="item in assignmentsList"
          :key="item.assignment.id"
          class="group cursor-pointer overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          @click="router.visit(route('lms.assignments.show', { event: event?.slug, assignment: item.assignment.id }))"
        >
          <!-- Header with color based on status -->
          <div :class="['px-5 py-4', statusHeaderClass(item.submission?.status)]">
            <div class="flex items-center justify-between">
              <span :class="['rounded-full px-2.5 py-1 text-xs font-bold', statusBadgeClass(item.submission?.status || 'not_submitted')]">
                {{ statusLabel(item.submission?.status || 'not_submitted') }}
              </span>
              <ClockIcon v-if="item.assignment.deadline" class="h-4 w-4 text-current opacity-50" />
            </div>
          </div>

          <!-- Content -->
          <div class="p-5">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ item.assignment.title }}</h3>
            <p v-if="item.assignment.description" class="mt-1 line-clamp-2 text-sm text-gray-500">
              {{ stripHtml(item.assignment.description) }}
            </p>
            <div v-if="item.assignment.deadline" class="mt-3 text-xs text-gray-400">
              {{ countdown(item.assignment.deadline) }}
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t border-gray-100 px-5 py-3">
            <span class="text-sm font-medium text-rosatom-600 group-hover:underline">
              {{ item.submission?.status === 'submitted' ? 'Посмотреть' : 'Открыть задание' }} →
            </span>
          </div>
        </div>
      </div>

      <div v-if="!assignmentsList.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <PencilSquareIcon class="mx-auto h-10 w-10 text-gray-300" />
        <p class="mt-3 text-sm text-gray-400">Задания не найдены</p>
      </div>

      <div v-if="items.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ items.from }}–{{ items.to }} из {{ items.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in items.links"
            :key="link.label"
            @click="link.url && router.visit(link.url, { preserveState: true })"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100 disabled:opacity-30'"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ClockIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  assignments: { type: [Object, Array], default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const items = computed(() => {
  const a = props.assignments
  if (a?.data && typeof a.last_page === 'number') return a
  return { last_page: 1, from: 0, to: 0, total: 0, links: [] }
})

let searchTimeout = null
function debouncedSearch(e) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('lms.assignments.index', { event: props.event.slug }), { search: e.target.value || undefined }, { preserveState: true })
  }, 400)
}

const assignmentsList = computed(() => {
  const raw = Array.isArray(props.assignments) ? props.assignments : props.assignments?.data || []
  return raw.map(item => {
    if (item.assignment && typeof item.assignment === 'object') return item
    return { assignment: item, submission: null }
  })
})

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 120)
}

function countdown(deadline) {
  if (!deadline) return 'Без дедлайна'
  const d = new Date(deadline)
  const now = new Date()
  const diff = d - now
  if (diff < 0) return 'Дедлайн истёк'
  const days = Math.floor(diff / (24 * 60 * 60 * 1000))
  if (days > 0) return `Осталось ${days} дн.`
  const hours = Math.floor(diff / (60 * 60 * 1000))
  return `Осталось ${hours} ч.`
}

function statusLabel(status) {
  return { not_submitted: 'Не сдано', submitted: 'На проверке', revision: 'На доработке', approved: 'Принято', rejected: 'Отклонено' }[status] || 'Не сдано'
}

function statusBadgeClass(status) {
  return {
    not_submitted: 'bg-gray-100 text-gray-600',
    submitted: 'bg-blue-100 text-blue-700',
    revision: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }[status] || 'bg-gray-100 text-gray-600'
}

function statusHeaderClass(status) {
  return {
    submitted: 'bg-blue-50',
    revision: 'bg-amber-50',
    approved: 'bg-green-50',
    rejected: 'bg-red-50',
  }[status] || 'bg-gray-50'
}
</script>
