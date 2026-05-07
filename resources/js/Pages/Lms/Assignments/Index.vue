<template>
  <LmsLayout :event="event" :user="page.props.user" :profile="page.props.profile">
    <Head :title="`Задания – ${event?.title}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Практические задания</h1>

      <div class="mb-4 space-y-3">
        <input
          :value="filters?.search ?? ''"
          @input="debouncedSearch"
          type="text"
          placeholder="Поиск..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
        />
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            :class="[
              'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition',
              filters?.overdue
                ? 'border-red-300 bg-red-50 text-red-700'
                : 'border-gray-200 bg-white text-gray-500 hover:border-red-200 hover:text-red-700',
            ]"
            @click="toggleOverdueFilter"
          >
            <ExclamationTriangleIcon class="h-3.5 w-3.5" />
            Просрочено: {{ assignmentCounts?.overdue ?? 0 }}
          </button>
          <button
            v-if="filters?.overdue"
            type="button"
            class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-rosatom-200 hover:text-rosatom-700"
            @click="clearOverdueFilter"
          >
            Показать все
          </button>
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="item in assignmentsList"
          :key="item.assignment.id"
          :class="[
            'relative rounded-2xl transition',
            isOverdueUnsubmitted(item)
              ? 'bg-red-50/80 p-1 ring-2 ring-red-200'
              : '',
          ]"
        >
          <div
            v-if="isOverdueUnsubmitted(item)"
            class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 shadow-sm"
          >
            <ExclamationTriangleIcon class="h-3.5 w-3.5" />
            Просрочено
          </div>
          <AssignmentCard
            :title="item.assignment.title"
            :description="stripHtml(item.assignment.description)"
            :status="mapStatus(item.submission?.status)"
            :deadline="formatDeadline(item.assignment.deadline)"
            :attachments="item.submission?.files?.length || 0"
            @click="router.visit(route('lms.assignments.show', { event: event?.slug, assignment: item.assignment.id }))"
          />
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
import { ExclamationTriangleIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'
import { formatLmsAssignmentDeadline } from '@/utils/lmsAssignmentDeadline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  assignments: { type: [Object, Array], default: () => [] },
  assignmentCounts: { type: Object, default: () => ({}) },
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
    router.get(route('lms.assignments.index', { event: props.event.slug }), {
      search: e.target.value || undefined,
      overdue: props.filters?.overdue ? 1 : undefined,
    }, { preserveState: true })
  }, 400)
}

function toggleOverdueFilter() {
  router.get(route('lms.assignments.index', { event: props.event.slug }), {
    search: props.filters?.search || undefined,
    overdue: props.filters?.overdue ? undefined : 1,
  }, { preserveState: true, replace: true })
}

function clearOverdueFilter() {
  router.get(route('lms.assignments.index', { event: props.event.slug }), {
    search: props.filters?.search || undefined,
  }, { preserveState: true, replace: true })
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
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return (doc.body.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 120)
}

function formatDeadline(dateStr) {
  if (!dateStr) return ''
  return formatLmsAssignmentDeadline(dateStr, 'long')
}

function mapStatus(status) {
  return { not_submitted: 'pending', submitted: 'review', revision: 'revision', approved: 'approved', rejected: 'revision' }[status] || 'pending'
}

function isOverdueUnsubmitted(item) {
  if (!item?.assignment?.deadline) return false
  if (new Date(item.assignment.deadline) >= new Date()) return false

  return !['submitted', 'approved'].includes(item.submission?.status)
}
</script>
