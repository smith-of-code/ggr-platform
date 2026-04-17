<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`${course?.title} – ${event?.title}`" />
    <div class="mx-auto max-w-4xl space-y-6">
      <!-- Back -->
      <RButton variant="ghost" size="sm" @click="router.visit(route('lms.courses.index', { event: event?.slug }))">
        <template #icon><ArrowLeftIcon class="h-4 w-4" /></template>
        Все программы
      </RButton>

      <!-- Course header -->
      <RCard elevation="raised" flush>
        <div v-if="course?.image" class="h-48 overflow-hidden bg-gray-100">
          <img :src="course.image" :alt="course.title" class="h-full w-full object-cover" />
        </div>
        <div class="p-6 lg:p-8">
          <h1 class="font-brand text-2xl font-bold text-gray-900">{{ course?.title }}</h1>
          <div v-if="course?.description" class="prose prose-sm mt-2 max-w-none text-gray-600" v-html="course.description" />

          <!-- Dates -->
          <div v-if="course?.starts_at || course?.ends_at" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
            <CalendarIcon class="h-4 w-4" />
            <span v-if="course.starts_at">{{ formatDateFull(course.starts_at) }}</span>
            <span v-if="course.starts_at && course.ends_at">—</span>
            <span v-if="course.ends_at">{{ formatDateFull(course.ends_at) }}</span>
          </div>

          <!-- Enroll status -->
          <div class="mt-6">
            <template v-if="enrollmentStatus === 'enrolled' || enrollmentStatus === 'in_progress' || enrollmentStatus === 'completed'">
              <div class="flex items-center gap-2 text-sm font-medium text-green-600">
                <CheckCircleIcon class="h-5 w-5" />
                Вы записаны на программу
                <span v-if="course?.is_mandatory" class="ml-1 rounded-full bg-rosatom-100 px-2.5 py-0.5 text-xs font-semibold text-rosatom-700">Обязательный</span>
              </div>
              <RProgress :percentage="overallProgress" label="Прогресс" show-label size="sm" class="mt-3" />
              <button
                v-if="enrollmentStatus === 'enrolled' && canCancel && !course?.is_mandatory"
                type="button"
                class="mt-3 cursor-pointer text-sm font-medium text-gray-500 transition hover:text-red-600"
                @click="unenroll"
              >
                Отменить заявку
              </button>
            </template>
            <template v-else-if="enrollmentStatus === 'pending'">
              <div class="rounded-xl bg-amber-50 px-5 py-4">
                <div class="flex items-start gap-3">
                  <ClockIcon class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" />
                  <div>
                    <p class="text-sm font-medium text-amber-800">Ваша заявка на рассмотрении</p>
                    <p class="mt-1 text-sm text-amber-700">Мы проверяем, насколько ваш проект соответствует выбранной программе.</p>
                  </div>
                </div>
                <button
                  v-if="!course?.is_mandatory"
                  type="button"
                  class="mt-3 cursor-pointer text-sm font-medium text-gray-500 transition hover:text-red-600"
                  @click="unenroll"
                >
                  Отменить заявку
                </button>
              </div>
            </template>
            <template v-else-if="enrollmentStatus === 'rejected'">
              <div class="flex items-center gap-3 rounded-xl bg-red-50 px-4 py-3">
                <XCircleIcon class="h-5 w-5 shrink-0 text-red-500" />
                <div class="flex-1">
                  <p class="text-sm font-medium text-red-800">Заявка отклонена</p>
                  <p class="text-xs text-red-600">Вы можете подать заявку повторно</p>
                </div>
                <RButton variant="outline" size="sm" @click="enroll">
                  Подать повторно
                </RButton>
              </div>
            </template>
            <template v-else>
              <div v-if="existingOtherEnrollment" class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4">
                <p class="text-sm font-medium text-blue-800">
                  Вы уже записаны на программу «{{ existingOtherEnrollment.course_title }}».
                </p>
                <p class="mt-1 text-sm text-blue-700">Чтобы записаться на другой, отмените текущую заявку.</p>
              </div>
              <div v-else-if="!isProfileComplete && !course?.is_mandatory" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3">
                <p class="text-sm font-medium text-amber-800">Для записи на программу необходимо заполнить профиль</p>
                <Link :href="route('lms.profile.edit', { event: event?.slug })" class="mt-1 inline-block text-sm font-medium text-rosatom-600 hover:underline">
                  Перейти в личный кабинет
                </Link>
              </div>
              <div v-else-if="$page.props.errors?.enroll" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                <p class="text-sm font-medium text-red-800">{{ $page.props.errors.enroll }}</p>
              </div>
              <RButton v-else variant="primary" @click="enroll">
                Записаться
              </RButton>
            </template>
          </div>
        </div>
      </RCard>

      <!-- Program / Schedule -->
      <RCard elevation="raised">
        <h2 class="font-brand text-xl font-bold text-gray-900">Программа</h2>
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
                  <RBadge v-if="!mod.is_available" variant="warning" size="sm">
                    Откроется {{ formatDateFull(mod.module.available_from) }}
                  </RBadge>
                  <RBadge v-else-if="moduleProgress(mod) === 100" variant="success" size="sm">
                    Завершён
                  </RBadge>
                </div>
              </div>
              <ChevronDownIcon class="h-5 w-5 shrink-0 text-gray-400 transition" :class="{ 'rotate-180': expandedModules[mi] }" />
            </button>

            <!-- Module description -->
            <div v-if="expandedModules[mi] && (mod.module.description || hasMaterials)" class="border-t border-gray-100 bg-gray-50/50 px-5 py-3">
              <div v-if="mod.module.description" class="prose prose-sm max-w-none text-gray-500" v-html="mod.module.description" />
              <Link
                v-if="hasMaterials"
                :href="route('lms.materials.index', { event: event?.slug })"
                class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-rosatom-600 transition hover:text-rosatom-700"
              >
                <BookOpenIcon class="h-4 w-4" />
                Материалы
                <ChevronRightIcon class="h-3.5 w-3.5" />
              </Link>
            </div>

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
                  <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                    <span>{{ stageTypeLabel(item.stage.type) }}</span>
                    <span v-if="item.stage.scheduled_at" class="flex items-center gap-1">
                      <CalendarIcon class="h-3 w-3" />
                      {{ formatStageSchedule(item.stage) }}
                    </span>
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
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                  <span>{{ stageTypeLabel(item.stage.type) }}</span>
                  <span v-if="item.stage.scheduled_at" class="flex items-center gap-1">
                    <CalendarIcon class="h-3 w-3" />
                    {{ formatStageSchedule(item.stage) }}
                  </span>
                  <span v-if="item.stage.duration_minutes">~{{ item.stage.duration_minutes }} мин</span>
                </div>
              </div>
              <span v-if="item.progress?.status === 'completed'" class="text-xs font-medium text-green-600">Пройден</span>
            </div>
          </div>
        </div>

        <div v-if="!modules?.length && !orphanStages?.length" class="mt-8 text-center text-sm text-gray-400">
          Программа пока не заполнена
        </div>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
  ClockIcon,
  XCircleIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'
import { CheckIcon, CheckCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: Object,
  course: Object,
  enrollment: Object,
  existingOtherEnrollment: { type: Object, default: null },
  modules: Array,
  orphanStages: Array,
  stages: Array,
  hasMaterials: { type: Boolean, default: false },
  isProfileComplete: { type: Boolean, default: false },
})

const enrollmentStatus = computed(() => props.enrollment?.status ?? null)
const isEnrolled = computed(() => ['enrolled', 'in_progress', 'completed'].includes(enrollmentStatus.value))

const canCancel = computed(() => {
  if (!props.course?.starts_at) return true
  return new Date() < new Date(props.course.starts_at)
})

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

function unenroll() {
  if (!confirm('Отменить заявку на программу?')) return
  router.delete(route('lms.courses.unenroll', { event: props.event?.slug, course: props.course?.id }))
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
  return {
    content: DocumentTextIcon, video: PlayIcon, test: ClipboardDocumentListIcon,
    assignment: PencilSquareIcon, scorm: BookOpenIcon,
    workshop: CalendarIcon, city_meeting: CalendarIcon, curator_meeting: CalendarIcon,
    file: ArrowDownTrayIcon,
  }[type] || DocumentTextIcon
}

function stageTypeLabel(type) {
  return {
    content: 'Теория', video: 'Видео', test: 'Тест', assignment: 'Задание', scorm: 'SCORM',
    workshop: 'Воркшоп', city_meeting: 'Встреча города', curator_meeting: 'Встреча с куратором',
    file: 'Файл',
  }[type] || 'Урок'
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

function formatScheduleDate(d) {
  if (!d) return ''
  const date = new Date(d)
  const opts = { day: 'numeric', month: 'long' }
  const h = date.getHours()
  const m = date.getMinutes()
  if (h || m) {
    opts.hour = '2-digit'
    opts.minute = '2-digit'
  }
  return date.toLocaleDateString('ru-RU', opts)
}

function formatStageSchedule(stage) {
  if (!stage?.scheduled_at) return ''
  if (!stage.scheduled_ends_at) return formatScheduleDate(stage.scheduled_at)
  const d1 = new Date(stage.scheduled_at)
  const d2 = new Date(stage.scheduled_ends_at)
  if (d1.toDateString() === d2.toDateString()) {
    const datePart = d1.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' })
    const t1 = d1.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
    const t2 = d2.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
    return `${datePart}, ${t1}–${t2}`
  }
  return `${formatScheduleDate(stage.scheduled_at)} — ${formatScheduleDate(stage.scheduled_ends_at)}`
}
</script>
