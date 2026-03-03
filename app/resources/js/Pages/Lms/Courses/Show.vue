<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${course?.title} – ${event?.name}`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.courses.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к курсам
      </Link>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left: course info -->
        <div class="lg:col-span-1">
          <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="aspect-video bg-gray-100">
              <img
                v-if="course?.image"
                :src="course.image"
                :alt="course.title"
                class="h-full w-full object-cover"
              />
              <div v-else class="flex h-full items-center justify-center">
                <BookOpenIcon class="h-20 w-20 text-gray-400" />
              </div>
            </div>
            <div class="p-6">
              <h1 class="font-brand text-xl font-bold text-gray-900">{{ course?.title }}</h1>
              <p class="mt-3 text-sm text-gray-500">{{ course?.description }}</p>
              <div v-if="course?.is_enrolled && overallProgress != null" class="mt-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Общий прогресс</span>
                  <span class="font-medium text-gray-900">{{ overallProgress }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    class="h-full rounded-full bg-rosatom-500"
                    :style="{ width: `${overallProgress}%` }"
                  />
                </div>
              </div>
              <Link
                v-if="!course?.is_enrolled"
                :href="route('lms.courses.enroll', { event: event?.slug, course: course?.id })"
                method="post"
                as="button"
                class="mt-6 w-full rounded-xl bg-rosatom-600 py-3 font-semibold text-white transition hover:bg-rosatom-700"
              >
                Записаться на курс
              </Link>
            </div>
          </div>
        </div>

        <!-- Right: stages -->
        <div class="lg:col-span-2">
          <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Программа курса</h2>
          <div class="space-y-0 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <Link
              v-for="(stage, idx) in (course?.stages || [])"
              :key="stage.id"
              :href="canAccessStage(stage) ? route('lms.courses.stage', { event: event?.slug, course: course?.id, stage: stage.id }) : '#'"
              :class="[
                'flex items-center gap-4 border-gray-200 p-4 transition',
                canAccessStage(stage)
                  ? 'hover:bg-gray-50 cursor-pointer'
                  : 'cursor-not-allowed opacity-75',
                idx > 0 ? 'border-t' : '',
              ]"
            >
              <div
                :class="[
                  'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                  statusClass(stage),
                ]"
              >
                {{ idx + 1 }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900">{{ stage.title }}</p>
                <span
                  :class="[
                    'mt-1 inline-block rounded px-2 py-0.5 text-xs font-medium',
                    typeBadgeClass(stage.type),
                  ]"
                >
                  {{ typeLabel(stage.type) }}
                </span>
              </div>
              <LockClosedIcon v-if="stageStatus(stage) === 'locked'" class="h-5 w-5 shrink-0 text-gray-400" />
              <MinusCircleIcon v-else-if="stageStatus(stage) === 'not_started'" class="h-5 w-5 shrink-0 text-gray-500" />
              <PlayIcon v-else-if="stageStatus(stage) === 'in_progress'" class="h-5 w-5 shrink-0 text-rosatom-500" />
              <CheckCircleIcon v-else class="h-5 w-5 shrink-0 text-rosatom-600" />
            </Link>
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
import { ArrowLeftIcon, BookOpenIcon } from '@heroicons/vue/24/outline'
import { LockClosedIcon, PlayIcon, CheckCircleIcon, MinusCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  course: { type: Object, required: true },
})

const progressByStage = computed(() => {
  const p = props.course?.user_progress_by_stage || {}
  return p
})

const overallProgress = computed(() => props.course?.progress_percent ?? null)

function stageStatus(stage) {
  const progress = progressByStage.value[stage?.id]
  if (progress?.is_completed) return 'completed'
  if (progress?.is_started) return 'in_progress'
  if (canAccessStage(stage)) return 'not_started'
  return 'locked'
}

function canAccessStage(stage) {
  const stages = props.course?.stages || []
  const idx = stages.findIndex((s) => s.id === stage?.id)
  if (idx <= 0) return true
  const prev = stages[idx - 1]
  const prevProgress = progressByStage.value[prev?.id]
  return prevProgress?.is_completed || prevProgress?.is_started
}

function statusClass(stage) {
  const s = stageStatus(stage)
  if (s === 'completed') return 'bg-rosatom-50 text-rosatom-600'
  if (s === 'in_progress') return 'bg-rosatom-50 text-rosatom-500'
  if (s === 'not_started') return 'bg-gray-200 text-gray-700'
  return 'bg-gray-100 text-gray-400'
}

function typeLabel(type) {
  const map = { content: 'Контент', scorm: 'SCORM', test: 'Тест', assignment: 'Задание', video: 'Видео' }
  return map[type] || type
}

function typeBadgeClass(type) {
  const map = {
    content: 'bg-gray-200 text-gray-700',
    scorm: 'bg-rosatom-50 text-rosatom-500',
    test: 'bg-accent-yellow/10 text-accent-yellow',
    assignment: 'bg-accent-magenta/10 text-accent-magenta',
    video: 'bg-rose-100 text-rose-500',
  }
  return map[type] || 'bg-gray-200 text-gray-700'
}

</script>
