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
        <div
          v-for="item in coursesList"
          :key="item.course.id"
          class="group cursor-pointer overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          @click="router.visit(route('lms.courses.show', { event: event?.slug, course: item.course.id }))"
        >
          <div v-if="item.course.image" class="aspect-[16/9] overflow-hidden bg-gray-100">
            <img :src="item.course.image" :alt="item.course.title" class="h-full w-full object-cover transition group-hover:scale-105" />
          </div>
          <div v-else class="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-rosatom-50 to-rosatom-100">
            <BookOpenIcon class="h-12 w-12 text-rosatom-300" />
          </div>

          <div class="p-5">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ item.course.title }}</h3>
            <p v-if="item.course.description" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ item.course.description }}</p>

            <div v-if="item.course.starts_at || item.course.ends_at" class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
              <CalendarIcon class="h-3.5 w-3.5" />
              <span v-if="item.course.starts_at">{{ formatDate(item.course.starts_at) }}</span>
              <span v-if="item.course.starts_at && item.course.ends_at">–</span>
              <span v-if="item.course.ends_at">{{ formatDate(item.course.ends_at) }}</span>
            </div>

            <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
              <span>{{ item.stages_count || 0 }} {{ stageWord(item.stages_count || 0) }}</span>
              <span v-if="item.enrolled" class="font-medium text-rosatom-600">Вы записаны</span>
            </div>

            <div v-if="item.enrolled && item.progress > 0" class="mt-3">
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">Прогресс</span>
                <span class="font-semibold text-rosatom-600">{{ item.progress }}%</span>
              </div>
              <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-rosatom-500 transition-all" :style="{ width: item.progress + '%' }" />
              </div>
            </div>
          </div>
        </div>
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
import { BookOpenIcon, CalendarIcon } from '@heroicons/vue/24/outline'

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

function stageWord(n) {
  if (n === 1) return 'урок'
  if (n >= 2 && n <= 4) return 'урока'
  return 'уроков'
}
</script>
