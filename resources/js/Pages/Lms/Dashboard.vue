<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Главная – ${event?.title || event?.name}`" />
    <div class="space-y-8">
      <!-- Welcome banner -->
      <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-rosatom-700 via-rosatom-600 to-rosatom-500 p-6 text-white shadow-lg lg:p-8">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <h1 class="font-brand text-2xl font-bold lg:text-3xl">
              Здравствуйте, {{ user?.name }}!
            </h1>
            <p class="mt-2 max-w-xl text-sm text-white/70 lg:text-base">
              Добро пожаловать в образовательную платформу Высшей школы гостеприимства Росатома 2026
            </p>
          </div>
          <div v-if="gamificationEnabled" class="hidden shrink-0 rounded-2xl bg-white/10 p-4 lg:block">
            <TrophyIcon class="h-10 w-10 text-accent-yellow" />
          </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-6 lg:gap-10">
          <template v-if="gamificationEnabled">
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-white/50">Всего баллов</p>
              <p class="mt-1 text-3xl font-bold">{{ totalPoints ?? 0 }}</p>
            </div>
            <div class="h-12 w-px bg-white/20" />
          </template>
          <div>
            <p class="text-xs font-medium uppercase tracking-wider text-white/50">Курсов пройдено</p>
            <p class="mt-1 text-3xl font-bold">
              {{ completedCoursesCount }}
              <span class="text-base font-normal text-white/50">/ {{ courses?.length || 0 }}</span>
            </p>
          </div>
          <template v-if="gamificationEnabled">
            <div class="h-12 w-px bg-white/20" />
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-white/50">Ваш ранг</p>
              <p class="mt-1 text-3xl font-bold">#{{ userRank ?? '–' }}</p>
            </div>
            <template v-if="cityName && cityRank">
              <div class="h-12 w-px bg-white/20" />
              <Link :href="route('lms.gamification.leaderboard', { event: event?.slug }) + '?tab=cities'" class="group cursor-pointer">
                <p class="text-xs font-medium uppercase tracking-wider text-white/50">Рейтинг города</p>
                <p class="mt-1 text-3xl font-bold group-hover:text-accent-yellow">
                  #{{ cityRank }}
                  <span class="text-base font-normal text-white/50">{{ cityName }}</span>
                </p>
              </Link>
            </template>
          </template>
        </div>
      </div>

      <!-- Profile incomplete banner -->
      <div
        v-if="!isProfileComplete"
        class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div>
          <p class="font-semibold text-amber-800">Заполните профиль</p>
          <p class="mt-1 text-sm text-amber-700">
            Только при заполненном личном кабинете участник может записаться на курс.
          </p>
          <Link :href="route('lms.profile.edit', { event: event?.slug })" class="mt-2 inline-block text-sm font-medium text-rosatom-600 hover:underline">
            Перейти в личный кабинет
          </Link>
        </div>
      </div>

      <!-- Stats cards -->
      <div class="grid gap-4 sm:grid-cols-2" :class="gamificationEnabled ? 'lg:grid-cols-4' : 'lg:grid-cols-2'">
        <RCard elevation="raised">
          <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rosatom-50">
              <BookOpenIcon class="h-5 w-5 text-rosatom-600" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Записанных курсов</p>
              <p class="text-2xl font-bold text-gray-900">{{ courses?.length || 0 }}</p>
            </div>
          </div>
        </RCard>
        <RCard elevation="raised">
          <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-green/10">
              <CheckCircleIcon class="h-5 w-5 text-accent-green" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Завершённых курсов</p>
              <p class="text-2xl font-bold text-gray-900">{{ completedCoursesCount }}</p>
            </div>
          </div>
        </RCard>
        <RCard v-if="gamificationEnabled" elevation="raised">
          <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-yellow/10">
              <StarIcon class="h-5 w-5 text-accent-yellow" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Всего баллов</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalPoints ?? 0 }}</p>
            </div>
          </div>
        </RCard>
        <RCard v-if="gamificationEnabled" elevation="raised">
          <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-magenta/10">
              <TrophyIcon class="h-5 w-5 text-accent-magenta" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Ваш ранг</p>
              <p class="text-2xl font-bold text-gray-900">#{{ userRank ?? '–' }}</p>
            </div>
          </div>
        </RCard>
      </div>

      <!-- City rank card -->
      <Link
        v-if="gamificationEnabled && cityName && cityRank"
        :href="route('lms.gamification.leaderboard', { event: event?.slug }) + '?tab=cities'"
        class="block"
      >
        <RCard elevation="raised" class="cursor-pointer transition hover:shadow-md">
          <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
              <MapPinIcon class="h-5 w-5 text-blue-600" />
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-gray-500">Рейтинг моего города</p>
              <p class="truncate text-lg font-bold text-gray-900">{{ cityName }}</p>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-blue-600">#{{ cityRank }}</p>
              <p class="text-xs text-gray-400">место</p>
            </div>
            <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </div>
        </RCard>
      </Link>

      <!-- My Courses -->
      <section>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">Мои курсы</h2>
          <Link
            :href="route('lms.courses.index', { event: event?.slug })"
            class="text-sm font-semibold text-rosatom-600 hover:text-rosatom-700"
          >
            Все курсы →
          </Link>
        </div>
        <div v-if="courses?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <CourseCard
            v-for="course in courses.slice(0, 6)"
            :key="course.id"
            :title="course.title"
            :description="stripTags(course.description)"
            :image="course.image"
            :progress="course.progress_percent ?? 0"
            :badge="(course.progress_percent ?? 0) >= 100 ? { text: 'Завершён', variant: 'success' } : undefined"
            @click="router.visit(route('lms.courses.show', { event: event?.slug, course: course.id }))"
          />
        </div>
        <div v-else class="rounded-xl border border-dashed border-gray-200 bg-white py-12 text-center">
          <BookOpenIcon class="mx-auto h-10 w-10 text-gray-300" />
          <p class="mt-3 text-sm text-gray-400">Курсы пока не назначены</p>
        </div>
      </section>

      <div class="grid gap-8" :class="gamificationEnabled ? 'lg:grid-cols-2' : ''">
        <!-- Upcoming Deadlines -->
        <section>
          <h2 class="mb-4 text-lg font-bold text-gray-900">Ближайшие дедлайны</h2>
          <div class="space-y-3">
            <RCard
              v-for="a in (upcomingAssignments || []).slice(0, 5)"
              :key="a.id"
              elevation="raised"
            >
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900">{{ a.title }}</p>
                <p class="mt-0.5 text-sm text-gray-500">{{ countdown(a.deadline) }}</p>
              </div>
              <Link
                :href="route('lms.assignments.show', { event: event?.slug, assignment: a.id })"
                class="ml-4 shrink-0 rounded-lg bg-rosatom-50 px-3 py-1.5 text-sm font-medium text-rosatom-600 transition hover:bg-rosatom-100"
              >
                Открыть
              </Link>
            </RCard>
            <div v-if="!upcomingAssignments?.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center">
              <p class="text-sm text-gray-400">Нет предстоящих дедлайнов</p>
            </div>
          </div>
        </section>

        <!-- Recent Activity -->
        <section v-if="gamificationEnabled">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Последняя активность</h2>
          <div class="space-y-3">
            <RCard
              v-for="p in (recentPoints || []).slice(0, 5)"
              :key="p.id"
              elevation="raised"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-green/10">
                <PlusIcon class="h-5 w-5 text-accent-green" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900">{{ p.description || 'Начислены баллы' }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(p.created_at) }}</p>
              </div>
              <span class="shrink-0 text-sm font-bold text-accent-green">+{{ p.points ?? p.amount ?? 0 }}</span>
            </RCard>
            <div v-if="!recentPoints?.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center">
              <p class="text-sm text-gray-400">Пока нет активности</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  BookOpenIcon,
  CheckCircleIcon,
  MapPinIcon,
  StarIcon,
  TrophyIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  isProfileComplete: { type: Boolean, default: false },
  courses: { type: Array, default: () => [] },
  trajectories: { type: Array, default: () => [] },
  upcomingAssignments: { type: Array, default: () => [] },
  recentPoints: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
  userRank: { type: Number, default: null },
  cityRank: { type: Number, default: null },
  cityName: { type: String, default: null },
})

const gamificationEnabled = computed(() => usePage().props.gamificationEnabled ?? false)

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

function stripTags(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '')
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
