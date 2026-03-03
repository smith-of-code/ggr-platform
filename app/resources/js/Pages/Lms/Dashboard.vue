<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Главная – ${event?.title || event?.name}`" />
    <div class="space-y-8">
      <!-- Welcome banner -->
      <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-rosatom-700 to-rosatom-600 p-6 text-white shadow-lg lg:p-8">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="font-brand text-2xl font-bold lg:text-3xl">
              Здравствуйте, {{ user?.name }}!
            </h1>
            <p class="mt-2 max-w-xl text-rosatom-200">
              Добро пожаловать в образовательную платформу {{ event?.title || event?.name }}
            </p>
          </div>
          <div class="hidden rounded-xl bg-white/10 p-4 lg:block">
            <TrophyIcon class="h-10 w-10 text-accent-yellow" />
          </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-6">
          <div>
            <p class="text-sm text-rosatom-300">Всего баллов</p>
            <p class="text-3xl font-bold">{{ totalPoints ?? 0 }}</p>
          </div>
          <div class="h-12 w-px bg-white/20" />
          <div>
            <p class="text-sm text-rosatom-300">Курсов пройдено</p>
            <p class="text-3xl font-bold">{{ completedCoursesCount }} <span class="text-base font-normal text-rosatom-300">/ {{ courses?.length || 0 }}</span></p>
          </div>
          <div class="h-12 w-px bg-white/20" />
          <div>
            <p class="text-sm text-rosatom-300">Ранг</p>
            <p class="text-3xl font-bold">{{ profile?.rank ?? '–' }}</p>
          </div>
        </div>
      </div>

      <!-- Stats cards -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-rosatom-50 p-2.5">
              <BookOpenIcon class="h-5 w-5 text-rosatom-600" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Записанных курсов</p>
              <p class="text-2xl font-bold text-gray-900">{{ courses?.length || 0 }}</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-accent-green/10 p-2.5">
              <CheckCircleIcon class="h-5 w-5 text-accent-green" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Завершённых курсов</p>
              <p class="text-2xl font-bold text-gray-900">{{ completedCoursesCount }}</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-accent-yellow/10 p-2.5">
              <StarIcon class="h-5 w-5 text-accent-yellow" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Всего баллов</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalPoints ?? 0 }}</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-accent-magenta/10 p-2.5">
              <TrophyIcon class="h-5 w-5 text-accent-magenta" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Ранг</p>
              <p class="text-2xl font-bold text-gray-900">{{ profile?.rank ?? '–' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- My Courses -->
      <div>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">Мои курсы</h2>
          <Link
            :href="route('lms.courses.index', { event: event?.slug })"
            class="text-sm font-semibold text-rosatom-600 hover:text-rosatom-700"
          >
            Все курсы →
          </Link>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <Link
            v-for="course in (courses || []).slice(0, 6)"
            :key="course.id"
            :href="route('lms.courses.show', { event: event?.slug, course: course.id })"
            class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
          >
            <div class="aspect-video overflow-hidden bg-rosatom-50">
              <img
                v-if="course.image"
                :src="course.image"
                :alt="course.title"
                class="h-full w-full object-cover transition group-hover:scale-105"
              />
              <div v-else class="flex h-full items-center justify-center">
                <BookOpenIcon class="h-12 w-12 text-rosatom-300" />
              </div>
            </div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-rosatom-600">{{ course.title }}</h3>
              <div class="mt-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Прогресс</span>
                  <span class="font-medium text-rosatom-600">{{ course.progress_percent ?? 0 }}%</span>
                </div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    class="h-full rounded-full bg-rosatom-500 transition-all"
                    :style="{ width: `${course.progress_percent ?? 0}%` }"
                  />
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-2">
        <!-- Upcoming Deadlines -->
        <div>
          <h2 class="mb-4 text-lg font-bold text-gray-900">Ближайшие дедлайны</h2>
          <div class="space-y-3">
            <div
              v-for="a in (upcomingAssignments || []).slice(0, 5)"
              :key="a.id"
              class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
            >
              <div>
                <p class="font-medium text-gray-900">{{ a.title }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ countdown(a.deadline) }}</p>
              </div>
              <Link
                :href="route('lms.assignments.show', { event: event?.slug, assignment: a.id })"
                class="rounded-lg bg-rosatom-50 px-3 py-1.5 text-sm font-medium text-rosatom-600 transition hover:bg-rosatom-100"
              >
                Открыть
              </Link>
            </div>
            <p v-if="!upcomingAssignments?.length" class="py-8 text-center text-gray-400">
              Нет предстоящих дедлайнов
            </p>
          </div>
        </div>

        <!-- Recent Activity -->
        <div>
          <h2 class="mb-4 text-lg font-bold text-gray-900">Последняя активность</h2>
          <div class="space-y-3">
            <div
              v-for="p in (recentPoints || []).slice(0, 5)"
              :key="p.id"
              class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-green/10">
                <PlusIcon class="h-5 w-5 text-accent-green" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900">{{ p.description || 'Начислены баллы' }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(p.created_at) }}</p>
              </div>
              <span class="shrink-0 font-bold text-accent-green">+{{ p.points ?? p.amount ?? 0 }}</span>
            </div>
            <p v-if="!recentPoints?.length" class="py-8 text-center text-gray-400">
              Пока нет активности
            </p>
          </div>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  BookOpenIcon,
  CheckCircleIcon,
  StarIcon,
  TrophyIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  courses: { type: Array, default: () => [] },
  trajectories: { type: Array, default: () => [] },
  upcomingAssignments: { type: Array, default: () => [] },
  recentPoints: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
})

const user = computed(() => props.user || props.event?.user || props.profile?.user || {})

const completedCoursesCount = computed(() =>
  (props.courses || []).filter((c) => (c.progress_percent ?? 0) >= 100).length
)

function countdown(deadline) {
  if (!deadline) return '–'
  const d = new Date(deadline)
  const now = new Date()
  const diff = d - now
  if (diff < 0) return 'Истёк'
  const days = Math.floor(diff / (24 * 60 * 60 * 1000))
  const hours = Math.floor((diff % (24 * 60 * 60 * 1000)) / (60 * 60 * 1000))
  if (days > 0) return `Осталось ${days} дн. ${hours} ч.`
  if (hours > 0) return `Осталось ${hours} ч.`
  return 'Меньше часа'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
