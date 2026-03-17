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
          <button
            v-if="prevStage"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: prevStage.id }))"
          >
            <ChevronLeftIcon class="h-4 w-4" />
            Назад
          </button>
          <button
            v-if="nextStage"
            class="inline-flex items-center gap-1.5 rounded-lg bg-rosatom-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: nextStage.id }))"
          >
            Далее
            <ChevronRightIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ stage?.title }}</h1>
        <span :class="['mt-2 inline-block rounded-md px-2 py-0.5 text-xs font-medium', typeBadgeClass(stage?.type)]">
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

          <!-- scorm: iframe with API adapter -->
          <div v-else-if="stage?.type === 'scorm' && stage?.scorm_package" class="aspect-video w-full overflow-hidden rounded-lg border border-gray-200">
            <iframe
              ref="scormFrame"
              :src="stage.scorm_package"
              class="h-full w-full"
              title="SCORM content"
              allowfullscreen
            />
          </div>

          <!-- test: redirect -->
          <div v-else-if="stage?.type === 'test' && stage?.lms_test_id">
            <button
              class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-6 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100"
              @click="router.visit(route('lms.tests.show', { event: event?.slug, test: stage.lms_test_id }))"
            >
              <ClipboardDocumentListIcon class="h-5 w-5" />
              Перейти к тесту
            </button>
          </div>

          <!-- assignment: redirect -->
          <div v-else-if="stage?.type === 'assignment' && stage?.lms_assignment_id">
            <button
              class="inline-flex items-center gap-2 rounded-xl bg-purple-50 px-6 py-3 text-sm font-semibold text-purple-700 transition hover:bg-purple-100"
              @click="router.visit(route('lms.assignments.show', { event: event?.slug, assignment: stage.lms_assignment_id }))"
            >
              <PencilSquareIcon class="h-5 w-5" />
              Перейти к заданию
            </button>
          </div>

          <!-- video: embed -->
          <div v-else-if="stage?.type === 'video'" class="mt-4">
            <div v-if="videoEmbedUrl" class="aspect-video w-full overflow-hidden rounded-lg">
              <iframe
                :src="videoEmbedUrl"
                class="h-full w-full"
                title="Video"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
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
        <div v-if="stage?.type !== 'test' && stage?.type !== 'assignment' && !isCompleted" class="mt-8">
          <button
            class="rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            @click="markComplete"
          >
            Отметить как пройденное
          </button>
        </div>
        <div v-else-if="isCompleted" class="mt-8 flex items-center gap-2 text-accent-green">
          <CheckCircleIcon class="h-5 w-5" />
          <span class="font-medium">Этап пройден</span>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  ArrowLeftIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClipboardDocumentListIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  course: { type: Object, required: true },
  stage: { type: Object, required: true },
  linkedTest: { type: Object, default: null },
  linkedAssignment: { type: Object, default: null },
  linkedVideo: { type: Object, default: null },
  progress: { type: Object, default: null },
  stages: { type: Array, default: () => [] },
})

const scormFrame = ref(null)

const allStages = computed(() => {
  if (props.stages?.length) return props.stages
  return props.course?.stages || []
})

const currentIdx = computed(() => {
  return allStages.value.findIndex((s) => {
    const id = s.stage?.id ?? s.id
    return id === props.stage?.id
  })
})

const prevStage = computed(() => {
  const i = currentIdx.value
  if (i <= 0) return null
  const s = allStages.value[i - 1]
  return s.stage || s
})

const nextStage = computed(() => {
  const i = currentIdx.value
  if (i < 0 || i >= allStages.value.length - 1) return null
  const s = allStages.value[i + 1]
  return s.stage || s
})

const isCompleted = computed(() => {
  return props.progress?.status === 'completed' || props.progress?.is_completed
})

const videoEmbedUrl = computed(() => {
  const url = props.linkedVideo?.url || props.stage?.content
  if (!url) return ''
  const rutube = url.match(/rutube\.ru\/video\/([a-zA-Z0-9]+)/)
  if (rutube) return `https://rutube.ru/play/embed/${rutube[1]}`
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\s]+)/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  if (url.startsWith('http')) return url
  return ''
})

// ── SCORM API Adapter ──
let scormData = {}

function createScormApi() {
  // SCORM 1.2 API
  const API = {
    LMSInitialize: () => { return 'true' },
    LMSFinish: () => { saveScormData(); return 'true' },
    LMSGetValue: (key) => { return scormData[key] ?? '' },
    LMSSetValue: (key, value) => { scormData[key] = value; return 'true' },
    LMSCommit: () => { saveScormData(); return 'true' },
    LMSGetLastError: () => '0',
    LMSGetErrorString: () => 'No error',
    LMSGetDiagnostic: () => 'No diagnostic',
  }

  // SCORM 2004 API
  const API_1484_11 = {
    Initialize: () => 'true',
    Terminate: () => { saveScormData(); return 'true' },
    GetValue: (key) => scormData[key] ?? '',
    SetValue: (key, value) => { scormData[key] = value; return 'true' },
    Commit: () => { saveScormData(); return 'true' },
    GetLastError: () => '0',
    GetErrorString: () => 'No error',
    GetDiagnostic: () => 'No diagnostic',
  }

  window.API = API
  window.API_1484_11 = API_1484_11
}

function saveScormData() {
  const scormEndpoint = route('lms.stages.scorm', {
    event: props.event?.slug,
    course: props.course?.id,
    stage: props.stage?.id,
  })
  axios.post(scormEndpoint, { scorm_data: scormData }).catch(() => {})
}

onMounted(() => {
  if (props.stage?.type === 'scorm') {
    if (props.progress?.scorm_data) {
      scormData = { ...props.progress.scorm_data }
    }
    createScormApi()
  }
})

onUnmounted(() => {
  if (props.stage?.type === 'scorm') {
    if (window.API) delete window.API
    if (window.API_1484_11) delete window.API_1484_11
  }
})

function typeLabel(type) {
  const map = { content: 'Контент', scorm: 'SCORM', test: 'Тест', assignment: 'Задание', video: 'Видео' }
  return map[type] || type || 'Контент'
}

function typeBadgeClass(type) {
  const map = {
    content: 'bg-gray-100 text-gray-600',
    scorm: 'bg-rosatom-50 text-rosatom-600',
    test: 'bg-amber-50 text-amber-600',
    assignment: 'bg-purple-50 text-purple-600',
    video: 'bg-rose-50 text-rose-600',
  }
  return map[type] || 'bg-gray-100 text-gray-600'
}

function markComplete() {
  router.post(route('lms.stages.complete', {
    event: props.event?.slug,
    course: props.course?.id,
    stage: props.stage?.id,
  }))
}
</script>
