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
          <RButton
            v-if="prevStage"
            variant="outline"
            size="sm"
            @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: prevStage.id }))"
          >
            <template #icon><ChevronLeftIcon class="h-4 w-4" /></template>
            Назад
          </RButton>
          <RButton
            v-if="nextStage"
            variant="primary"
            size="sm"
            @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: nextStage.id }))"
          >
            Далее
          </RButton>
        </div>
      </div>

      <RCard elevation="raised">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ stage?.title }}</h1>
        <RBadge :variant="typeBadgeVariant(stage?.type)" size="sm" class="mt-2">
          {{ typeLabel(stage?.type) }}
        </RBadge>

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
            <RButton variant="outline" @click="router.visit(route('lms.tests.show', { event: event?.slug, test: stage.lms_test_id }))">
              <template #icon><ClipboardDocumentListIcon class="h-5 w-5" /></template>
              Перейти к тесту
            </RButton>
          </div>

          <!-- assignment: redirect -->
          <div v-else-if="stage?.type === 'assignment' && stage?.lms_assignment_id">
            <RButton variant="outline" @click="router.visit(route('lms.assignments.show', { event: event?.slug, assignment: stage.lms_assignment_id }))">
              <template #icon><PencilSquareIcon class="h-5 w-5" /></template>
              Перейти к заданию
            </RButton>
          </div>

          <!-- video: embed -->
          <div v-else-if="stage?.type === 'video'" class="mt-4">
            <div v-if="videoEmbedUrl" class="relative aspect-video w-full overflow-hidden rounded-lg">
              <!-- Play overlay — shown until user clicks to start watching -->
              <div
                v-if="!videoStarted && videoDuration && !isCompleted"
                class="absolute inset-0 z-10 flex cursor-pointer flex-col items-center justify-center bg-gray-900/70 transition hover:bg-gray-900/60"
                @click="startWatching"
              >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/90 shadow-xl transition hover:scale-110">
                  <PlayCircleIcon class="h-12 w-12 text-rosatom-700" />
                </div>
                <p class="mt-4 text-lg font-semibold text-white">Начать просмотр</p>
                <p class="mt-1 text-sm text-white/60">Длительность: {{ formatTime(videoDuration) }}</p>
              </div>
              <iframe
                :src="videoStarted || !videoDuration || isCompleted ? videoEmbedUrl : ''"
                class="h-full w-full"
                title="Video"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                allowfullscreen
              />
            </div>
            <p v-else class="text-gray-400">Видео недоступно</p>

            <!-- Video watch progress -->
            <div v-if="videoDuration && !isCompleted && videoStarted" class="mt-4">
              <div class="flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center gap-2">
                  <PlayCircleIcon class="h-5 w-5" />
                  <span>Просмотрено: {{ formatTime(watchedSeconds) }} / {{ formatTime(videoDuration) }}</span>
                </div>
                <span v-if="!videoWatchComplete" class="text-xs text-gray-400">
                  Осталось: {{ formatTime(Math.max(0, videoDuration - watchedSeconds)) }}
                </span>
              </div>
              <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="videoWatchComplete ? 'bg-accent-green' : 'bg-rosatom-500'"
                  :style="{ width: videoProgressPercent + '%' }"
                />
              </div>
              <p v-if="!videoWatchComplete" class="mt-2 text-xs text-gray-400">
                Таймер останавливается при переключении на другую вкладку
              </p>
            </div>
          </div>

          <div v-else class="py-12 text-center text-gray-400">
            Контент не найден
          </div>
        </div>

        <!-- Mark as Complete -->
        <div v-if="stage?.type !== 'test' && stage?.type !== 'assignment' && !isCompleted" class="mt-8">
          <RButton
            v-if="!videoDuration || videoWatchComplete"
            variant="primary"
            @click="markComplete"
          >
            Отметить как пройденное
          </RButton>
          <p v-else class="text-sm text-gray-400">
            Кнопка завершения появится после полного просмотра видео
          </p>
        </div>
        <div v-else-if="isCompleted" class="mt-8 flex items-center gap-2 text-accent-green">
          <CheckCircleIcon class="h-5 w-5" />
          <span class="font-medium">Этап пройден</span>
        </div>
      </RCard>
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
  PlayCircleIcon,
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

// ── Video watch tracking ──
const watchedSeconds = ref(0)
const isVideoTimerRunning = ref(false)
const videoStarted = ref(false)
let videoTimerInterval = null
let heartbeatInterval = null

const videoDuration = computed(() => {
  if (props.stage?.type !== 'video') return 0
  return props.linkedVideo?.duration_seconds ?? 0
})

const videoWatchComplete = computed(() => {
  if (!videoDuration.value) return true
  return watchedSeconds.value >= Math.floor(videoDuration.value * 0.9)
})

const videoProgressPercent = computed(() => {
  if (!videoDuration.value) return 100
  return Math.min(100, Math.round((watchedSeconds.value / videoDuration.value) * 100))
})

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

function startVideoTimer() {
  if (isVideoTimerRunning.value) return
  isVideoTimerRunning.value = true

  videoTimerInterval = setInterval(() => {
    if (videoWatchComplete.value) {
      stopVideoTimer()
      sendHeartbeat()
      return
    }
    watchedSeconds.value++
  }, 1000)

  heartbeatInterval = setInterval(sendHeartbeat, 15000)
}

function stopVideoTimer() {
  isVideoTimerRunning.value = false
  if (videoTimerInterval) {
    clearInterval(videoTimerInterval)
    videoTimerInterval = null
  }
  if (heartbeatInterval) {
    clearInterval(heartbeatInterval)
    heartbeatInterval = null
  }
}

function sendHeartbeat() {
  const url = route('lms.stages.heartbeat', {
    event: props.event?.slug,
    course: props.course?.id,
    stage: props.stage?.id,
  })
  axios.post(url, { watched_seconds: watchedSeconds.value }).catch(() => {})
}

function startWatching() {
  videoStarted.value = true
  if (!videoWatchComplete.value) {
    startVideoTimer()
    document.addEventListener('visibilitychange', handleVisibilityChange)
  }
}

function handleVisibilityChange() {
  if (document.hidden) {
    stopVideoTimer()
  } else if (videoStarted.value && videoDuration.value && !videoWatchComplete.value) {
    startVideoTimer()
  }
}

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

  if (props.stage?.type === 'video' && videoDuration.value) {
    watchedSeconds.value = props.progress?.watched_seconds ?? 0
    if (watchedSeconds.value > 0) {
      videoStarted.value = true
      if (!videoWatchComplete.value) {
        startVideoTimer()
        document.addEventListener('visibilitychange', handleVisibilityChange)
      }
    }
  }
})

onUnmounted(() => {
  if (props.stage?.type === 'scorm') {
    if (window.API) delete window.API
    if (window.API_1484_11) delete window.API_1484_11
  }

  stopVideoTimer()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})

function typeLabel(type) {
  const map = { content: 'Контент', scorm: 'SCORM', test: 'Тест', assignment: 'Задание', video: 'Видео' }
  return map[type] || type || 'Контент'
}

function typeBadgeVariant(type) {
  return { content: 'neutral', scorm: 'primary', test: 'warning', assignment: 'info', video: 'error' }[type] || 'neutral'
}

function markComplete() {
  router.post(route('lms.stages.complete', {
    event: props.event?.slug,
    course: props.course?.id,
    stage: props.stage?.id,
  }))
}
</script>
