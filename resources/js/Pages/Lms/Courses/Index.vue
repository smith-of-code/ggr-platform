<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`Курсы – ${event?.title}`" />
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="font-brand text-2xl font-bold text-gray-900">Каталог курсов</h1>
        <div class="w-full sm:w-72">
          <input
            :value="filters?.search ?? ''"
            @input="debouncedSearch"
            type="text"
            placeholder="Поиск курсов..."
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
          />
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <CourseCard
          v-for="item in coursesList"
          :key="item.course.id"
          :title="item.course.title"
          :description="stripTags(item.course.description)"
          :image="item.course.image"
          :progress="item.enrolled && item.progress > 0 ? item.progress : undefined"
          :stages-count="item.stages_count || 0"
          :stages-label="stageWord(item.stages_count || 0)"
          :duration="formatDateRange(item.course)"
          :badge="enrollmentBadge(item)"
          @click="router.visit(route('lms.courses.show', { event: event?.slug, course: item.course.id }))"
        />
      </div>

      <div v-if="!coursesList.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <BookOpenIcon class="mx-auto h-10 w-10 text-gray-300" />
        <p class="mt-3 text-sm text-gray-400">Курсы не найдены</p>
      </div>

      <!-- Pagination -->
      <div v-if="courses.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ courses.from }}–{{ courses.to }} из {{ courses.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in courses.links"
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
import { computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { BookOpenIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  courses: Object,
  filters: Object,
})

const coursesList = computed(() => {
  const raw = props.courses?.data || props.courses || []
  return Array.isArray(raw) ? raw : []
})

let searchTimeout = null
function debouncedSearch(e) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('lms.courses.index', props.event.slug), { search: e.target.value || undefined }, { preserveState: true })
  }, 400)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

function formatDateRange(course) {
  if (!course.starts_at && !course.ends_at) return undefined
  const parts = []
  if (course.starts_at) parts.push(formatDate(course.starts_at))
  if (course.ends_at) parts.push(formatDate(course.ends_at))
  return parts.join(' – ')
}

function enrollmentBadge(item) {
  const status = item.enrollment?.status
  if (status === 'pending') return { text: 'Ожидает одобрения', variant: 'warning' }
  if (status === 'rejected') return { text: 'Отклонена', variant: 'error' }
  if (item.enrolled) return { text: 'Вы записаны', variant: 'info' }
  return undefined
}

function stripTags(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '')
}

function stageWord(n) {
  if (n === 1) return 'урок'
  if (n >= 2 && n <= 4) return 'урока'
  return 'уроков'
}
</script>
