<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Курсы – ${event?.name}`" />
    <div class="space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="font-brand text-2xl font-bold text-gray-900">Каталог курсов</h1>
        <div class="flex gap-2">
          <button
            v-for="f in filters"
            :key="f.value"
            :class="[
              'rounded-xl px-4 py-2 text-sm font-medium transition',
              filter === f.value
                ? 'bg-rosatom-50 text-rosatom-600'
                : 'bg-white text-gray-500 hover:text-gray-900',
            ]"
            @click="filter = f.value"
          >
            {{ f.label }}
          </button>
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="course in filteredCourses"
          :key="course.id"
          class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300"
        >
          <Link :href="route('lms.courses.show', { event: event?.slug, course: course.id })" class="block">
            <div class="aspect-video overflow-hidden bg-gray-100">
              <img
                v-if="course.image"
                :src="course.image"
                :alt="course.title"
                class="h-full w-full object-cover"
              />
              <div v-else class="flex h-full items-center justify-center">
                <BookOpenIcon class="h-16 w-16 text-gray-400" />
              </div>
            </div>
            <div class="p-5">
              <h3 class="font-semibold text-gray-900">{{ course.title }}</h3>
              <p class="mt-2 line-clamp-3 text-sm text-gray-500">{{ course.description }}</p>
              <div v-if="course.is_enrolled && course.progress_percent != null" class="mt-4">
                <div class="flex justify-between text-sm text-gray-500">
                  <span>Прогресс</span>
                  <span>{{ course.progress_percent }}%</span>
                </div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    class="h-full rounded-full bg-rosatom-500"
                    :style="{ width: `${course.progress_percent}%` }"
                  />
                </div>
              </div>
            </div>
          </Link>
          <div class="border-t border-gray-200 p-4">
            <Link
              v-if="!course.is_enrolled"
              :href="route('lms.courses.enroll', { event: event?.slug, course: course.id })"
              method="post"
              as="button"
              class="w-full rounded-xl bg-rosatom-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            >
              Записаться
            </Link>
            <Link
              v-else
              :href="route('lms.courses.show', { event: event?.slug, course: course.id })"
              class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            >
              Продолжить
            </Link>
          </div>
        </div>
      </div>

      <div v-if="!filteredCourses.length" class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm">
        Курсы не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { BookOpenIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  courses: { type: Object, default: () => ({ data: [] }) },
})

const filter = ref('all')
const filters = [
  { value: 'all', label: 'Все' },
  { value: 'enrolled', label: 'Записаны' },
  { value: 'completed', label: 'Завершённые' },
]

const coursesList = computed(() =>
  Array.isArray(props.courses) ? props.courses : props.courses?.data || []
)

const filteredCourses = computed(() => {
  const list = coursesList.value
  if (filter.value === 'all') return list
  if (filter.value === 'enrolled') return list.filter((c) => c.is_enrolled && (c.progress_percent ?? 0) < 100)
  if (filter.value === 'completed') return list.filter((c) => (c.progress_percent ?? 0) >= 100)
  return list
})
</script>
