<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${stage?.title} – ${course?.title}`" />
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Link
          :href="route('lms.courses.show', { event: event?.slug, course: course?.id })"
          class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          {{ course?.title }}
        </Link>
        <div class="flex gap-3">
          <Link
            v-if="prevStage"
            :href="route('lms.courses.stage', { event: event?.slug, course: course?.id, stage: prevStage.id })"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            <ChevronLeftIcon class="h-4 w-4" />
            Назад
          </Link>
          <Link
            v-if="nextStage"
            :href="route('lms.courses.stage', { event: event?.slug, course: course?.id, stage: nextStage.id })"
            class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rosatom-700"
          >
            Далее
            <ChevronRightIcon class="h-4 w-4" />
          </Link>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ stage?.title }}</h1>
        <span
          :class="[
            'mt-2 inline-block rounded px-2 py-0.5 text-xs font-medium',
            typeBadgeClass(stage?.type),
          ]"
        >
          {{ typeLabel(stage?.type) }}
        </span>

        <!-- Content by type -->
        <div class="mt-6">
          <!-- content: render HTML -->
          <div
            v-if="stage?.type === 'content' && stage?.content"
            class="prose max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600"
            v-html="stage.content"
          />

          <!-- scorm: iframe -->
          <div v-else-if="stage?.type === 'scorm' && stage?.scorm_url" class="aspect-video w-full overflow-hidden rounded-lg">
            <iframe
              :src="stage.scorm_url"
              class="h-full w-full"
              title="SCORM content"
              allowfullscreen
            />
          </div>

          <!-- test: redirect -->
          <div v-else-if="stage?.type === 'test' && stage?.test_id">
            <Link
              :href="route('lms.tests.take', { event: event?.slug, test: stage.test_id })"
              class="inline-flex items-center gap-2 rounded-xl bg-accent-yellow/10 px-4 py-3 font-medium text-accent-yellow hover:bg-accent-yellow/20"
            >
              <ClipboardDocumentListIcon class="h-5 w-5" />
              Перейти к тесту
            </Link>
          </div>

          <!-- assignment: redirect -->
          <div v-else-if="stage?.type === 'assignment' && stage?.assignment_id">
            <Link
              :href="route('lms.assignments.show', { event: event?.slug, assignment: stage.assignment_id })"
              class="inline-flex items-center gap-2 rounded-xl bg-accent-magenta/10 px-4 py-3 font-medium text-accent-magenta hover:bg-accent-magenta/20"
            >
              <PencilSquareIcon class="h-5 w-5" />
              Перейти к заданию
            </Link>
          </div>

          <!-- video: embed -->
          <div v-else-if="stage?.type === 'video'" class="mt-4">
            <div v-if="stage?.video_url" class="aspect-video w-full overflow-hidden rounded-lg">
              <iframe
                :src="embedVideoUrl(stage.video_url)"
                class="h-full w-full"
                title="Video"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              />
            </div>
            <p v-else class="text-gray-400">Видео недоступно</p>
          </div>

          <div v-else class="py-12 text-center text-gray-400">
            Контент не найден
          </div>
        </div>

        <!-- Mark as Complete -->
        <div v-if="stage?.type !== 'test' && stage?.type !== 'assignment' && !progress?.is_completed" class="mt-8">
          <Link
            :href="route('lms.courses.stage.complete', { event: event?.slug, course: course?.id, stage: stage?.id })"
            method="post"
            as="button"
            class="rounded-xl bg-rosatom-600 px-6 py-2.5 font-semibold text-white transition hover:bg-rosatom-700"
          >
            Отметить как пройденное
          </Link>
        </div>
        <div v-else-if="progress?.is_completed" class="mt-8 flex items-center gap-2 text-rosatom-600">
          <CheckCircleIcon class="h-5 w-5" />
          <span>Пройдено</span>
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
  ArrowLeftIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClipboardDocumentListIcon,
  PencilSquareIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  course: { type: Object, required: true },
  stage: { type: Object, required: true },
  progress: { type: Object, default: () => ({}) },
})

const stages = computed(() => props.course?.stages || [])
const currentIdx = computed(() => stages.value.findIndex((s) => s.id === props.stage?.id))

const prevStage = computed(() => {
  const i = currentIdx.value
  return i > 0 ? stages.value[i - 1] : null
})

const nextStage = computed(() => {
  const i = currentIdx.value
  return i >= 0 && i < stages.value.length - 1 ? stages.value[i + 1] : null
})

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

function embedVideoUrl(url) {
  if (!url) return ''
  // YouTube
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\s]+)/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  // Vimeo
  const vimeo = url.match(/vimeo\.com\/(\d+)/)
  if (vimeo) return `https://player.vimeo.com/video/${vimeo[1]}`
  return url
}
</script>
