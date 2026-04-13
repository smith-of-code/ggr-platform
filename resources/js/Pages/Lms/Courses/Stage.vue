<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${stage?.title} – ${course?.title}`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.courses.show', { event: event?.slug, course: course?.id })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        {{ course?.title }}
      </Link>

      <RCard elevation="raised">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ stage?.title }}</h1>
        <div class="mt-2 flex flex-wrap gap-2">
          <RBadge v-for="(block, bIdx) in displayBlocks" :key="bIdx" :variant="typeBadgeVariant(block.type)" size="sm">
            {{ typeLabel(block.type) }}
          </RBadge>
        </div>

        <div class="mt-6 space-y-8">
          <div v-for="(block, bIdx) in displayBlocks" :key="bIdx">
            <div v-if="displayBlocks.length > 1" class="mb-3 flex items-center gap-2">
              <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">{{ bIdx + 1 }}</span>
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ typeLabel(block.type) }}</span>
            </div>

            <!-- content -->
            <div
              v-if="block.type === 'content' && block.content"
              class="prose max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600"
              v-html="block.content"
            />

            <!-- scorm -->
            <div v-else-if="block.type === 'scorm' && block.scorm_package" class="aspect-video w-full overflow-hidden rounded-lg border border-gray-200">
              <iframe
                ref="scormFrame"
                :src="block.scorm_package"
                class="h-full w-full"
                title="SCORM content"
                allowfullscreen
              />
            </div>

            <!-- test (inline) -->
            <div v-else-if="block.type === 'test' && block.lms_test_id">
              <InlineTest
                v-if="inlineTest"
                :event="event"
                :test="inlineTest.test"
                :attempts="inlineTest.attempts"
                :active-attempt="inlineTest.activeAttempt"
                :questions="inlineTest.questions"
                :latest-result="inlineTest.latestResult"
                :return-url="stageReturnUrl"
              />
              <div v-else class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                    <ClipboardDocumentListIcon class="h-5 w-5 text-amber-600" />
                  </div>
                  <p class="font-medium text-gray-900">{{ block.test?.title || 'Тест' }}</p>
                </div>
              </div>
            </div>

            <!-- assignment (inline) -->
            <div v-else-if="block.type === 'assignment' && block.lms_assignment_id">
              <InlineAssignment
                v-if="inlineAssignment"
                :event="event"
                :assignment="inlineAssignment.assignment"
                :submission="inlineAssignment.submission"
                :user="user"
              />
              <div v-else class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                    <PencilSquareIcon class="h-5 w-5 text-blue-600" />
                  </div>
                  <p class="font-medium text-gray-900">{{ block.assignment?.title || 'Задание' }}</p>
                </div>
              </div>
            </div>

            <!-- workshop / city_meeting / curator_meeting -->
            <div v-else-if="['workshop', 'city_meeting', 'curator_meeting'].includes(block.type)">
              <div class="overflow-hidden rounded-xl border"
                :class="{
                  'border-purple-200 bg-purple-50/50': block.type === 'workshop',
                  'border-teal-200 bg-teal-50/50': block.type === 'city_meeting',
                  'border-amber-200 bg-amber-50/50': block.type === 'curator_meeting',
                }"
              >
                <div class="flex items-center gap-3 px-5 py-4"
                  :class="{
                    'bg-purple-100/60': block.type === 'workshop',
                    'bg-teal-100/60': block.type === 'city_meeting',
                    'bg-amber-100/60': block.type === 'curator_meeting',
                  }"
                >
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg"
                    :class="{
                      'bg-purple-200': block.type === 'workshop',
                      'bg-teal-200': block.type === 'city_meeting',
                      'bg-amber-200': block.type === 'curator_meeting',
                    }"
                  >
                    <svg class="h-5 w-5" :class="{
                      'text-purple-700': block.type === 'workshop',
                      'text-teal-700': block.type === 'city_meeting',
                      'text-amber-700': block.type === 'curator_meeting',
                    }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                  </div>
                  <div>
                    <p class="text-sm font-bold" :class="{
                      'text-purple-800': block.type === 'workshop',
                      'text-teal-800': block.type === 'city_meeting',
                      'text-amber-800': block.type === 'curator_meeting',
                    }">
                      {{ typeLabel(block.type) }}
                    </p>
                    <p v-if="block.scheduled_at" class="text-xs font-medium" :class="{
                      'text-purple-600': block.type === 'workshop',
                      'text-teal-600': block.type === 'city_meeting',
                      'text-amber-600': block.type === 'curator_meeting',
                    }">
                      {{ formatScheduleDate(block.scheduled_at) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="block.content"
                  class="prose max-w-none px-5 py-4 text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600"
                  v-html="block.content"
                />
              </div>
            </div>

            <!-- video -->
            <div v-else-if="block.type === 'video'">
              <div v-if="getVideoEmbedUrl(block)" class="relative aspect-video w-full overflow-hidden rounded-lg">
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
                  :src="videoStarted || !videoDuration || isCompleted ? getVideoEmbedUrl(block) : ''"
                  class="h-full w-full"
                  title="Video"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                  allowfullscreen
                />
              </div>
              <p v-else class="text-gray-400">Видео недоступно</p>

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

            <div v-else class="py-6 text-center text-gray-400">
              Контент не найден
            </div>
          </div>
        </div>

        <!-- Mark as Complete -->
        <div v-if="!hasAutoCompleteBlock && !isCompleted" class="mt-8">
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

      <div class="flex items-center" :class="prevStage && nextStage ? 'justify-between' : nextStage ? 'justify-end' : 'justify-start'">
        <RButton
          v-if="prevStage"
          variant="outline"
          @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: prevStage.id }))"
        >
          <template #icon><ChevronLeftIcon class="h-4 w-4" /></template>
          Назад
        </RButton>
        <RButton
          v-if="nextStage"
          variant="primary"
          @click="router.visit(route('lms.stages.show', { event: event?.slug, course: course?.id, stage: nextStage.id }))"
        >
          Далее
          <template #icon><ChevronRightIcon class="h-4 w-4" /></template>
        </RButton>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import InlineTest from '@/Components/Lms/InlineTest.vue'
import InlineAssignment from '@/Components/Lms/InlineAssignment.vue'
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
  blocks: { type: Array, default: () => [] },
  linkedTest: { type: Object, default: null },
  linkedAssignment: { type: Object, default: null },
  linkedVideo: { type: Object, default: null },
  progress: { type: Object, default: null },
  stages: { type: Array, default: () => [] },
  inlineTest: { type: Object, default: null },
  inlineAssignment: { type: Object, default: null },
})

const scormFrame = ref(null)

const stageReturnUrl = computed(() =>
  route('lms.stages.show', { event: props.event?.slug, course: props.course?.id, stage: props.stage?.id })
)

const displayBlocks = computed(() => {
  if (props.blocks?.length) return props.blocks
  return [{
    type: props.stage?.type || 'content',
    content: props.stage?.content,
    scorm_package: props.stage?.scorm_package,
    lms_test_id: props.stage?.lms_test_id,
    lms_assignment_id: props.stage?.lms_assignment_id,
    lms_video_id: props.stage?.lms_video_id,
    test: props.linkedTest,
    assignment: props.linkedAssignment,
    video: props.linkedVideo,
  }]
})

const hasAutoCompleteBlock = computed(() => {
  return displayBlocks.value.some(b => b.type === 'test' || b.type === 'assignment')
})

const firstVideoBlock = computed(() => {
  return displayBlocks.value.find(b => b.type === 'video')
})

// ── Video watch tracking ──
const watchedSeconds = ref(0)
const isVideoTimerRunning = ref(false)
const videoStarted = ref(false)
let videoTimerInterval = null
let heartbeatInterval = null

const videoDuration = computed(() => {
  const vb = firstVideoBlock.value
  if (!vb) return 0
  return vb.video?.duration_seconds ?? props.linkedVideo?.duration_seconds ?? 0
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

function rutubeEmbedFromStoredUrl(raw) {
  if (!raw || !/rutube\.ru/i.test(raw)) return ''
  if (raw.includes('rutube.ru/play/embed/')) return raw
  try {
    const u = new URL(raw, 'https://rutube.ru')
    const path = u.pathname || ''
    const privateMatch = path.match(/\/video\/private\/([a-zA-Z0-9_-]+)/)
    if (privateMatch) {
      const id = privateMatch[1]
      const p = u.searchParams.get('p')
      const base = `https://rutube.ru/play/embed/${id}/`
      return p ? `${base}?p=${encodeURIComponent(p)}` : base
    }
    const publicMatch = path.match(/\/video\/(?!private\/)([a-zA-Z0-9_-]+)/)
    if (publicMatch) {
      return `https://rutube.ru/play/embed/${publicMatch[1]}`
    }
  } catch {
    return ''
  }
  return ''
}

function getVideoEmbedUrl(block) {
  const url = block.video?.url || block.content || props.linkedVideo?.url || props.stage?.content
  if (!url) return ''
  const rutubeEmbed = rutubeEmbedFromStoredUrl(url)
  if (rutubeEmbed) return rutubeEmbed
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\s]+)/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  if (url.startsWith('http')) return url
  return ''
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

// ── SCORM API Adapter ──
let scormData = {}

function createScormApi() {
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

const hasScormBlock = computed(() => displayBlocks.value.some(b => b.type === 'scorm'))

onMounted(() => {
  if (hasScormBlock.value) {
    if (props.progress?.scorm_data) {
      scormData = { ...props.progress.scorm_data }
    }
    createScormApi()
  }

  if (firstVideoBlock.value && videoDuration.value) {
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
  if (hasScormBlock.value) {
    if (window.API) delete window.API
    if (window.API_1484_11) delete window.API_1484_11
  }

  stopVideoTimer()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})

function typeLabel(type) {
  const map = {
    content: 'Контент', scorm: 'SCORM', test: 'Тест', assignment: 'Задание', video: 'Видео',
    workshop: 'Живой воркшоп', city_meeting: 'Встреча города', curator_meeting: 'Встреча с куратором',
  }
  return map[type] || type || 'Контент'
}

function formatScheduleDate(dateStr) {
  if (!dateStr) return ''
  const hasTime = dateStr.length > 10 && dateStr.includes('T')
  const d = new Date(dateStr)
  const opts = { day: '2-digit', month: 'long', year: 'numeric' }
  if (hasTime) {
    opts.hour = '2-digit'
    opts.minute = '2-digit'
  }
  const formatted = d.toLocaleString('ru-RU', opts)
  return hasTime ? formatted.replace(',', ' в') : formatted
}

function typeBadgeVariant(type) {
  return {
    content: 'neutral', scorm: 'primary', test: 'warning', assignment: 'info', video: 'error',
    workshop: 'primary', city_meeting: 'success', curator_meeting: 'warning',
  }[type] || 'neutral'
}

function markComplete() {
  router.post(route('lms.stages.complete', {
    event: props.event?.slug,
    course: props.course?.id,
    stage: props.stage?.id,
  }))
}
</script>
