<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`${course?.title} – ${event?.title}`" />
    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Back -->
      <button
        @click="router.visit(route('lms.courses.index', { event: event?.slug }))"
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-rosatom-600"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Все курсы
      </button>

      <!-- Course header -->
      <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div v-if="course?.image" class="h-48 overflow-hidden bg-gray-100">
          <img :src="course.image" :alt="course.title" class="h-full w-full object-cover" />
        </div>
        <div class="p-6 lg:p-8">
          <h1 class="font-brand text-2xl font-bold text-gray-900">{{ course?.title }}</h1>
          <p v-if="course?.description" class="mt-2 text-gray-600">{{ course.description }}</p>

          <!-- Dates -->
          <div v-if="course?.starts_at || course?.ends_at" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
            <CalendarIcon class="h-4 w-4" />
            <span v-if="course.starts_at">{{ formatDateFull(course.starts_at) }}</span>
            <span v-if="course.starts_at && course.ends_at">—</span>
            <span v-if="course.ends_at">{{ formatDateFull(course.ends_at) }}</span>
          </div>

          <!-- Enroll button -->
          <div class="mt-6">
            <template v-if="isEnrolled">
              <div class="flex items-center gap-2 text-sm font-medium text-green-600">
                <CheckCircleIcon class="h-5 w-5" />
                Вы записаны на курс
              </div>
              <div class="mt-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">Прогресс</span>
                  <span class="font-bold text-rosatom-600">{{ overallProgress }}%</span>
                </div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100">
                  <div class="h-full rounded-full bg-rosatom-500 transition-all" :style="{ width: overallProgress + '%' }" />
                </div>
              </div>
            </template>
            <button
              v-else
              @click="enroll"
              class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            >
              Записаться на курс
            </button>
          </div>
        </div>
      </div>

      <!-- Program / Schedule -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
        <h2 class="font-brand text-xl font-bold text-gray-900">Программа курса</h2>
        <p class="mt-1 text-sm text-gray-500">Расписание модулей и уроков</p>

        <!-- Modules -->
        <div v-if="modules?.length" class="mt-6 space-y-6">
          <div
            v-for="(mod, mi) in modules"
            :key="mod.module.id"
            class="rounded-xl border border-gray-100 overflow-hidden"
          >
            <!-- Module header -->
            <button
              @click="toggleModule(mi)"
              class="flex w-full items-center gap-4 bg-gray-50 px-5 py-4 text-left transition hover:bg-gray-100"
            >
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold"
                :class="mod.is_available ? 'bg-rosatom-100 text-rosatom-600' : 'bg-gray-200 text-gray-400'"
              >
                {{ mi + 1 }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-semibold text-gray-900">{{ mod.module.title }}</p>
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                  <span>{{ mod.stages.length }} {{ stageWord(mod.stages.length) }}</span>
                  <template v-if="mod.module.available_from">
                    <span class="flex items-center gap-1">
                      <CalendarIcon class="h-3 w-3" />
                      {{ formatDateFull(mod.module.available_from) }}
                    </span>
                  </template>
                  <span v-if="!mod.is_available" class="rounded-full bg-amber-100 px-2 py-0.5 font-medium text-amber-700">
                    Откроется {{ formatDateFull(mod.module.available_from) }}
                  </span>
                  <span v-else-if="moduleProgress(mod) === 100" class="rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-700">
                    Завершён
                  </span>
                </div>
              </div>
              <ChevronDownIcon class="h-5 w-5 shrink-0 text-gray-400 transition" :class="{ 'rotate-180': expandedModules[mi] }" />
            </button>

            <!-- Module description -->
            <p v-if="mod.module.description && expandedModules[mi]" class="border-t border-gray-100 bg-gray-50/50 px-5 py-3 text-sm text-gray-500">
              {{ mod.module.description }}
            </p>

            <!-- Stages list -->
            <div v-if="expandedModules[mi]" class="divide-y divide-gray-50">
              <div
                v-for="item in mod.stages"
                :key="item.stage.id"
                class="flex items-center gap-4 px-5 py-3 transition"
                :class="item.is_available && isEnrolled ? 'cursor-pointer hover:bg-rosatom-50' : 'opacity-60'"
                @click="item.is_available && isEnrolled && goToStage(item.stage.id)"
              >
                <!-- Status icon -->
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="stageStatusClass(item)">
                  <CheckIcon v-if="item.progress?.status === 'completed'" class="h-4 w-4" />
                  <LockClosedIcon v-else-if="!item.is_available" class="h-4 w-4" />
                  <component :is="stageTypeIcon(item.stage.type)" v-else class="h-4 w-4" />
                </div>

                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ item.stage.title }}</p>
                  <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span>{{ stageTypeLabel(item.stage.type) }}</span>
                    <span v-if="item.stage.duration_minutes">~{{ item.stage.duration_minutes }} мин</span>
                    <span v-if="item.stage.available_from && !item.is_available">
                      Откроется {{ formatDateFull(item.stage.available_from) }}
                    </span>
                  </div>
                </div>

                <span
                  v-if="item.progress?.status === 'completed'"
                  class="shrink-0 text-xs font-medium text-green-600"
                >
                  Пройден
                </span>
                <ChevronRightIcon v-else-if="item.is_available && isEnrolled" class="h-4 w-4 shrink-0 text-gray-300" />
              </div>
            </div>
          </div>
        </div>

        <!-- Orphan stages (no module) -->
        <div v-if="orphanStages?.length" class="mt-6">
          <p v-if="modules?.length" class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Другие уроки</p>
          <div class="divide-y divide-gray-50 rounded-xl border border-gray-100">
            <div
              v-for="item in orphanStages"
              :key="item.stage.id"
              class="flex items-center gap-4 px-5 py-3 transition"
              :class="item.is_available && isEnrolled ? 'cursor-pointer hover:bg-rosatom-50' : 'opacity-60'"
              @click="item.is_available && isEnrolled && goToStage(item.stage.id)"
            >
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="stageStatusClass(item)">
                <CheckIcon v-if="item.progress?.status === 'completed'" class="h-4 w-4" />
                <component :is="stageTypeIcon(item.stage.type)" v-else class="h-4 w-4" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900">{{ item.stage.title }}</p>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                  <span>{{ stageTypeLabel(item.stage.type) }}</span>
                  <span v-if="item.stage.duration_minutes">~{{ item.stage.duration_minutes }} мин</span>
                </div>
              </div>
              <span v-if="item.progress?.status === 'completed'" class="text-xs font-medium text-green-600">Пройден</span>
            </div>
          </div>
        </div>

        <div v-if="!modules?.length && !orphanStages?.length" class="mt-8 text-center text-sm text-gray-400">
          Программа курса пока не заполнена
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  ArrowLeftIcon,
  CalendarIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  LockClosedIcon,
  BookOpenIcon,
  PlayIcon,
  ClipboardDocumentListIcon,
  PencilSquareIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline'
import { CheckIcon, CheckCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: Object,
  course: Object,
  enrollment: Object,
  modules: Array,
  orphanStages: Array,
  stages: Array,
})

const isEnrolled = computed(() => !!props.enrollment)

const expandedModules = reactive(
  (props.modules || []).reduce((acc, mod, i) => {
    acc[i] = mod.is_available
    return acc
  }, {})
)

function toggleModule(i) {
  expandedModules[i] = !expandedModules[i]
}

const overallProgress = computed(() => {
  const allStages = [
    ...(props.modules || []).flatMap(m => m.stages),
    ...(props.orphanStages || []),
  ]
  if (!allStages.length) return 0
  const completed = allStages.filter(s => s.progress?.status === 'completed').length
  return Math.round((completed / allStages.length) * 100)
})

function moduleProgress(mod) {
  if (!mod.stages.length) return 0
  const completed = mod.stages.filter(s => s.progress?.status === 'completed').length
  return Math.round((completed / mod.stages.length) * 100)
}

function enroll() {
  router.post(route('lms.courses.enroll', { event: props.event?.slug, course: props.course?.id }))
}

function goToStage(stageId) {
  router.visit(route('lms.stages.show', { event: props.event?.slug, course: props.course?.id, stage: stageId }))
}

function stageStatusClass(item) {
  if (item.progress?.status === 'completed') return 'bg-green-100 text-green-600'
  if (!item.is_available) return 'bg-gray-100 text-gray-400'
  return 'bg-rosatom-50 text-rosatom-500'
}

function stageTypeIcon(type) {
  return { content: DocumentTextIcon, video: PlayIcon, test: ClipboardDocumentListIcon, assignment: PencilSquareIcon, scorm: BookOpenIcon }[type] || DocumentTextIcon
}

function stageTypeLabel(type) {
  return { content: 'Теория', video: 'Видео', test: 'Тест', assignment: 'Задание', scorm: 'SCORM' }[type] || 'Урок'
}

function stageWord(n) {
  if (n === 1) return 'урок'
  if (n >= 2 && n <= 4) return 'урока'
  return 'уроков'
}

function formatDateFull(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
