<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <Link :href="route('lms.admin.assignments.index', event.slug)" class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
          Назад к заданиям
        </Link>
        <RButton v-if="filters?.status || filters?.only_unread" variant="outline" size="sm" @click="clearStatusFilter">
          Показать все
        </RButton>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">{{ assignment.title }}</h1>
      <p class="mt-1 text-sm text-gray-500">Работы участников</p>
      <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">
          Всего: {{ submissions.total }}
        </span>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">
          На странице: {{ submissions.data.length }}
        </span>
        <span v-if="filters?.only_unread" class="rounded-full bg-amber-100 px-3 py-1 text-amber-700">
          Режим: только с новыми
        </span>
      </div>
    </div>

    <RCard class="mb-4">
      <div class="mb-4 flex flex-wrap gap-3">
        <div class="w-full min-w-[220px] flex-1 sm:max-w-xs">
          <label class="mb-1 block text-xs font-medium text-gray-500">Учебная программа</label>
          <SearchSelect
            :model-value="filterForm.direction || null"
            :options="directionSelectOptions"
            value-key="value"
            label-key="label"
            placeholder="Все программы"
            :searchable="false"
            @update:model-value="onDirectionChange"
          />
        </div>
        <div v-if="showFacultyFilter" class="w-full min-w-[220px] flex-1 sm:max-w-xs">
          <label class="mb-1 block text-xs font-medium text-gray-500">Факультет</label>
          <SearchSelect
            :model-value="filterForm.faculty || null"
            :options="facultySelectOptions"
            value-key="value"
            label-key="label"
            placeholder="Все факультеты"
            :searchable="true"
            @update:model-value="onFacultyChange"
          />
        </div>
      </div>
      <div class="mb-4 flex flex-wrap gap-2">
        <button
          v-for="item in statusFilterButtons"
          :key="item.value"
          type="button"
          :class="[
            'rounded-full border px-3 py-1.5 text-xs font-semibold transition',
            isStatusFilterActive(item.value)
              ? 'border-rosatom-500 bg-rosatom-50 text-rosatom-700'
              : 'border-gray-200 bg-white text-gray-500 hover:border-rosatom-200 hover:text-rosatom-700',
          ]"
          @click="applyStatusFilter(item.value)"
        >
          {{ item.label }}: {{ item.count }}
        </button>
      </div>
      <div class="grid gap-3 md:grid-cols-4">
        <div class="md:col-span-3">
          <label class="mb-1 block text-xs font-medium text-gray-500">Поиск участника</label>
          <input
            v-model="filterForm.search"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
            placeholder="ФИО или email"
            @keyup.enter="applyFilters"
          >
        </div>
        <div class="flex items-end">
          <RButton variant="primary" size="sm" @click="applyFilters">
            Применить
          </RButton>
        </div>
      </div>
    </RCard>

    <div class="space-y-4">
      <RCard
        v-for="sub in submissions.data"
        :key="sub.id"
        :class="[
          'overflow-hidden border shadow-sm',
          sub.is_overdue ? 'border-red-200 bg-red-50/70 ring-1 ring-red-100' : 'border-gray-200',
        ]"
      >
        <div class="w-full">
        <!-- Collapse header -->
        <button
          type="button"
          :class="[
            'flex w-full items-center justify-between px-5 py-4 text-left',
            sub.is_overdue ? 'bg-red-50 hover:bg-red-100/60' : 'bg-white hover:bg-gray-50',
          ]"
          @click="toggleExpanded(sub)"
        >
          <div class="flex min-w-0 flex-1 items-start gap-4">
            <RAvatar :name="sub.user?.name" size="md" />
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-gray-900">{{ sub.user?.name }}</p>
                <span
                  v-if="threadCount(sub)"
                  class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500"
                >
                  {{ threadCount(sub) }} сообщ.
                </span>
                <RBadge v-if="sub.has_unread" variant="warning" size="sm">
                  Новое
                </RBadge>
                <RBadge v-if="sub.is_overdue" variant="danger" size="sm">
                  Просрочено
                </RBadge>
                <RBadge :variant="statusBadgeVariant(sub.status)">
                  {{ statusLabel(sub.status) }}
                </RBadge>
              </div>
              <p class="truncate text-xs text-gray-500">{{ sub.user?.email }}</p>
              <p v-if="participantCourseTitles(sub).length" class="mt-0.5 truncate text-[11px] text-gray-500">
                Программа: {{ participantCourseTitles(sub).join(', ') }}
              </p>
              <p v-if="sub.participant_last_activity_at" class="mt-0.5 text-[11px] text-gray-400">
                Активность участника: {{ formatDate(sub.participant_last_activity_at) }}
              </p>
            </div>
          </div>
          <div class="ml-3 flex shrink-0 items-center">
            <svg class="h-5 w-5 text-gray-400 transition" :class="expanded[sub.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </button>

        <!-- Expanded content -->
        <div v-show="expanded[sub.id]" class="border-t border-gray-200 bg-gray-50/80 p-5">
          <!-- Participant context -->
          <div
            v-if="participantCourseTitles(sub).length || hasProfileContext(sub)"
            class="mb-5 rounded-xl border border-rosatom-100 bg-rosatom-50/50 p-4"
          >
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-rosatom-700">
              Контекст участника
            </p>
            <div class="grid gap-3 text-sm md:grid-cols-2">
              <div>
                <p class="text-xs font-medium text-gray-400">Программа</p>
                <div v-if="participantCourseTitles(sub).length" class="mt-1 flex flex-wrap gap-1.5">
                  <span
                    v-for="course in participantCourseTitles(sub)"
                    :key="course"
                    class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-700 ring-1 ring-rosatom-100"
                  >
                    {{ course }}
                  </span>
                </div>
                <p v-else class="mt-1 text-gray-500">Не найдена привязка к программе</p>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-400">Профиль</p>
                <p class="mt-1 text-gray-700">
                  {{ profileSummary(sub) || 'Данные профиля не заполнены' }}
                </p>
              </div>
            </div>
            <div v-if="sub.participant_context?.profile?.project_description" class="mt-3">
              <p class="text-xs font-medium text-gray-400">Идея / проект</p>
              <p class="mt-1 whitespace-pre-wrap text-sm text-gray-800">
                {{ sub.participant_context.profile.project_description }}
              </p>
            </div>
          </div>

          <!-- Submitted work -->
          <div v-if="canReviewAssignments" class="mb-5 rounded-xl border border-gray-200 bg-white p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Ответ участника</p>

            <!-- Answers by tasks -->
            <template v-if="assignment.tasks?.length && sub.answers?.length">
              <div v-for="task in assignment.tasks" :key="task.id" class="mb-3 rounded-lg border border-gray-100 bg-gray-50 p-3">
                <p class="mb-1 text-xs font-semibold text-gray-500">
                  {{ task.title }}
                  <span class="ml-1 font-normal text-gray-400">({{ responseTypeLabel(task.response_type) }})</span>
                </p>
                <template v-for="answer in getAnswersForTask(sub, task.id)" :key="answer.id">
                  <p v-if="answer.text_content" class="whitespace-pre-wrap text-sm text-gray-900">{{ answer.text_content }}</p>
                  <a v-if="answer.link" :href="answer.link" target="_blank" rel="noopener" class="text-sm text-rosatom-600 underline hover:text-rosatom-700">{{ answer.link }}</a>
                  <div v-if="answer.files?.length" class="mt-1 flex flex-wrap gap-2">
                    <a
                      v-for="(f, fi) in answer.files"
                      :key="fi"
                      :href="fileUrl(typeof f === 'string' ? f : f.path)"
                      target="_blank"
                      class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-100"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                      {{ typeof f === 'string' ? `Файл ${fi+1}` : (f.name || `Файл ${fi+1}`) }}
                    </a>
                  </div>
                  <p v-if="!answer.text_content && !answer.link && !answer.files?.length" class="text-xs text-gray-400">Не заполнено</p>
                </template>
                <p v-if="!getAnswersForTask(sub, task.id).length" class="text-xs text-gray-400">Не заполнено</p>
              </div>
            </template>

            <!-- Legacy display -->
            <template v-else>
              <div v-if="sub.text_content" class="mb-3">
                <p class="whitespace-pre-wrap text-sm text-gray-900">{{ sub.text_content }}</p>
              </div>
              <div v-if="sub.link" class="mb-3">
                <a :href="sub.link" target="_blank" rel="noopener" class="text-sm text-rosatom-600 underline hover:text-rosatom-700">{{ sub.link }}</a>
              </div>
              <div v-if="sub.files?.length" class="flex flex-wrap gap-2">
                <a
                  v-for="(f, i) in sub.files"
                  :key="i"
                  :href="fileUrl(typeof f === 'string' ? f : f.path)"
                  target="_blank"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-100"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                  {{ typeof f === 'string' ? `Файл ${i+1}` : (f.name || `Файл ${i+1}`) }}
                </a>
              </div>
              <p v-if="!sub.text_content && !sub.link && !sub.files?.length" class="text-sm text-gray-400">Пустой ответ</p>
            </template>
            <div v-if="sub.has_unread" class="mt-2">
              <RBadge variant="warning" size="sm">Есть новые действия участника</RBadge>
            </div>
          </div>

          <!-- Dialog thread -->
          <div v-if="getDialogMessages(sub).length > 0" class="mb-5 space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Обсуждение</p>
            <div
              v-for="msg in getDialogMessages(sub)"
              :key="msg.key"
              :class="[
                'rounded-xl border p-4',
                msg.isReview
                  ? 'border-amber-200 bg-amber-50'
                  : msg.isStudent
                    ? 'mr-8 border-blue-200 bg-blue-50'
                    : 'ml-8 border-rosatom-200 bg-rosatom-50',
              ]"
            >
              <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-semibold" :class="msg.isReview ? 'text-amber-800' : msg.isStudent ? 'text-blue-800' : 'text-rosatom-700'">
                    {{ msg.authorName }}
                  </span>
                  <RBadge v-if="msg.isReview" :variant="decisionBadgeVariant(msg.decision)" size="sm">
                    {{ decisionLabel(msg.decision) }}
                  </RBadge>
                  <span v-if="!msg.isReview" class="text-[10px] text-gray-400">
                    {{ msg.isStudent ? 'участник' : 'преподаватель' }}
                  </span>
                  <RBadge
                    v-if="msg.isStudent && isMessageUnread(sub, msg)"
                    variant="warning"
                    size="sm"
                  >
                    Новое
                  </RBadge>
                </div>
                <span class="text-xs text-gray-400">{{ formatDate(msg.date) }}</span>
              </div>
              <p class="whitespace-pre-wrap text-sm text-gray-800">{{ msg.text }}</p>
              <div v-if="msg.files?.length" class="mt-2 flex flex-wrap gap-2">
                <a
                  v-for="(f, i) in msg.files"
                  :key="i"
                  :href="fileUrl(typeof f === 'string' ? f : f.path)"
                  target="_blank"
                  class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-600 shadow-sm hover:bg-gray-50"
                >
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                  {{ typeof f === 'string' ? `Файл ${i+1}` : (f.name || `Файл ${i+1}`) }}
                </a>
              </div>
            </div>
          </div>

          <!-- Admin comment form -->
          <div v-if="canReviewAssignments" class="mb-5 rounded-xl border border-gray-200 bg-white p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Комментарий</p>
            <textarea
              v-model="commentForms[sub.id].text"
              rows="2"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400"
              placeholder="Написать комментарий участнику..."
            />
            <div class="mt-2 flex items-center gap-3">
              <label class="flex cursor-pointer items-center gap-1.5 text-xs text-gray-500 hover:text-rosatom-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                <span>{{ commentForms[sub.id]._files?.length > 0 ? `${commentForms[sub.id]._files.length} файл(ов)` : 'Прикрепить файл' }}</span>
                <input
                  type="file"
                  multiple
                  accept=".pdf,.docx,.xlsx,.pptx,.png,.jpg,.jpeg,.zip"
                  class="hidden"
                  @change="e => onCommentFiles(sub.id, e)"
                />
              </label>
              <button
                v-if="commentForms[sub.id]._files?.length > 0"
                type="button"
                class="text-xs text-red-500 hover:underline"
                @click="clearCommentFiles(sub.id)"
              >
                Убрать
              </button>
              <div class="flex-1" />
              <RButton
                variant="secondary"
                size="sm"
                :disabled="!commentForms[sub.id].text?.trim()"
                @click="submitComment(sub)"
              >
                Отправить
              </RButton>
            </div>
            <div v-if="commentForms[sub.id]._files?.length" class="mt-2 flex flex-wrap gap-1">
              <span v-for="(f, i) in commentForms[sub.id]._files" :key="i" class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">
                {{ f.name }}
              </span>
            </div>
          </div>

          <!-- Review decision form -->
          <div
            v-if="canReviewAssignments && sub.status !== 'approved' && sub.status !== 'rejected'"
            class="space-y-3 border-t border-gray-200 pt-4"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Решение по работе</p>
            <textarea
              v-model="reviewForms[sub.id].comment"
              rows="2"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400"
              placeholder="Комментарий к решению..."
            />
            <div>
              <label class="flex cursor-pointer items-center gap-1.5 text-xs text-gray-500 hover:text-rosatom-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                <span>{{ reviewForms[sub.id]._files?.length > 0 ? `${reviewForms[sub.id]._files.length} файл(ов)` : 'Прикрепить файлы' }}</span>
                <input
                  type="file"
                  multiple
                  accept=".pdf,.docx,.xlsx,.pptx,.png,.jpg,.jpeg,.zip"
                  class="hidden"
                  @change="e => onReviewFiles(sub.id, e)"
                />
              </label>
              <div v-if="reviewForms[sub.id]._files?.length" class="mt-2 flex flex-wrap gap-1">
                <span v-for="(f, i) in reviewForms[sub.id]._files" :key="i" class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">
                  {{ f.name }}
                </span>
              </div>
            </div>
            <div class="flex gap-2">
              <RButton variant="primary" @click="submitReview(sub, 'approve')">
                Принять
              </RButton>
              <RButton variant="secondary" @click="submitReview(sub, 'revision')">
                На доработку
              </RButton>
              <RButton variant="danger" @click="submitReview(sub, 'reject')">
                Отклонить
              </RButton>
            </div>
          </div>
        </div>
        </div>
      </RCard>
    </div>

    <RCard v-if="submissions.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">
      Пока нет отправленных работ
    </RCard>

    <div v-if="submissions.last_page > 1" class="mt-6 flex items-center justify-between">
      <p class="text-xs text-gray-500">{{ submissions.from }}–{{ submissions.to }} из {{ submissions.total }}</p>
      <div class="flex gap-1">
        <button
          v-for="link in submissions.links"
          :key="link.label"
          @click="link.url && router.visit(link.url)"
          :disabled="!link.url"
          class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
          :class="[
            link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100',
            link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-30',
          ]"
          v-html="link.label"
        />
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, reactive, watch } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import { fileUrl } from '@/lib/fileUrl'
import axios from 'axios'

const ENTREPRENEURS_DIRECTION = 'entrepreneurs'

const props = defineProps({
  event: Object,
  assignment: Object,
  submissions: Object,
  statusCounts: { type: Object, default: () => ({}) },
  canReviewAssignments: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
  directionLabels: { type: Object, default: () => ({}) },
  facultyLabels: { type: Object, default: () => ({}) },
  directionFaculties: { type: Object, default: () => ({}) },
})

const filterForm = reactive({
  search: props.filters?.search || '',
  direction: props.filters?.direction || '',
  faculty: props.filters?.faculty || '',
})

const expanded = ref({})

const reviewForms = reactive({})
const commentForms = reactive({})

const directionSelectOptions = computed(() =>
  Object.entries(props.directionLabels || {}).map(([value, label]) => ({ value, label }))
)

const showFacultyFilter = computed(() => filterForm.direction === ENTREPRENEURS_DIRECTION)

const facultySelectOptions = computed(() => {
  const keys = props.directionFaculties?.[ENTREPRENEURS_DIRECTION] || []
  return keys.map(value => ({
    value,
    label: props.facultyLabels?.[value] || value,
  }))
})

function buildListQuery(overrides = {}) {
  const direction = filterForm.direction || undefined
  const query = {
    search: filterForm.search || undefined,
    direction,
    faculty: direction === ENTREPRENEURS_DIRECTION ? (filterForm.faculty || undefined) : undefined,
  }
  if (!('status' in overrides)) {
    if (props.filters?.status) {
      query.status = props.filters.status
    } else if (props.filters?.only_unread) {
      query.status = 'new'
    }
  }
  return { ...query, ...overrides }
}

function onDirectionChange(value) {
  filterForm.direction = value || ''
  if (filterForm.direction !== ENTREPRENEURS_DIRECTION) {
    filterForm.faculty = ''
  }
  applyFilters()
}

function onFacultyChange(value) {
  filterForm.faculty = value || ''
  applyFilters()
}

const statusFilterButtons = computed(() => [
  { value: 'approved', label: 'Принято', count: props.statusCounts?.approved ?? 0 },
  { value: 'submitted', label: 'На проверке', count: props.statusCounts?.submitted ?? 0 },
  { value: 'revision', label: 'На доработке', count: props.statusCounts?.revision ?? 0 },
  { value: 'new', label: 'Новое', count: props.statusCounts?.new ?? 0 },
  { value: 'overdue', label: 'Просрочено', count: props.statusCounts?.overdue ?? 0 },
])

function ensureSubmissionForms(subId) {
  if (!reviewForms[subId]) {
    reviewForms[subId] = { comment: '', _files: [] }
  }
  if (!commentForms[subId]) {
    commentForms[subId] = { text: '', _files: [] }
  }
}

watch(
  () => (props.submissions?.data ?? []).map(s => s.id),
  (ids) => {
    ids.forEach((id) => ensureSubmissionForms(id))
    expanded.value = Object.fromEntries(
      Object.entries(expanded.value).filter(([id]) => ids.includes(Number(id)))
    )
  },
  { immediate: true },
)

function getAnswersForTask(sub, taskId) {
  return (sub.answers || []).filter(a => a.lms_assignment_task_id === taskId)
}

function responseTypeLabel(type) {
  return { text: 'текст', link: 'ссылка', file: 'файл' }[type] || type
}

function statusLabel(status) {
  const map = { submitted: 'На проверке', approved: 'Принято', revision: 'На доработке', rejected: 'Отклонено', resubmitted: 'Пересдано' }
  return map[status] || status
}

function statusBadgeVariant(status) {
  return { submitted: 'info', approved: 'success', revision: 'warning', rejected: 'error', resubmitted: 'primary' }[status] || 'neutral'
}

function decisionLabel(decision) {
  return { approve: 'Принято', revision: 'На доработку', reject: 'Отклонено' }[decision] || decision
}

function decisionBadgeVariant(decision) {
  return { approve: 'success', revision: 'warning', reject: 'error' }[decision] || 'neutral'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function threadCount(sub) {
  return (sub.reviews?.length || 0) + (sub.comments?.length || 0)
}

function participantCourseTitles(sub) {
  return (sub.participant_context?.courses || [])
    .map(course => course.title)
    .filter(Boolean)
}

function hasProfileContext(sub) {
  const profile = sub.participant_context?.profile
  return Boolean(profile && [
    profile.project_description,
    profile.city,
    profile.position,
    profile.organization,
    profile.direction_label,
    profile.faculty_label,
  ].some(Boolean))
}

function profileSummary(sub) {
  const profile = sub.participant_context?.profile
  if (!profile) return ''

  return [
    profile.faculty_label,
    profile.direction_label,
    profile.city,
    profile.position,
    profile.organization,
  ].filter(Boolean).join(' · ')
}

function getDialogMessages(sub) {
  const msgs = []
  const studentUserId = sub.user_id

  for (const r of sub.reviews || []) {
    msgs.push({
      key: `review-${r.id}`,
      isReview: true,
      isStudent: false,
      authorName: r.reviewer?.name || 'Проверяющий',
      text: r.comment || '',
      files: r.files,
      decision: r.decision,
      date: r.created_at,
      ts: new Date(r.created_at).getTime(),
    })
  }

  for (const c of sub.comments || []) {
    msgs.push({
      key: `comment-${c.id}`,
      isReview: false,
      isStudent: c.user_id === studentUserId,
      authorName: c.user?.name || 'Пользователь',
      text: c.text,
      files: c.files,
      date: c.created_at,
      ts: new Date(c.created_at).getTime(),
    })
  }

  msgs.sort((a, b) => a.ts - b.ts)
  return msgs
}

function isMessageUnread(sub, msg) {
  if (!msg?.isStudent || !msg?.date) return false
  if (!sub?.read_at) return true
  return new Date(msg.date).getTime() > new Date(sub.read_at).getTime()
}

function onReviewFiles(subId, e) {
  ensureSubmissionForms(subId)
  reviewForms[subId]._files = Array.from(e.target.files || [])
}

function onCommentFiles(subId, e) {
  ensureSubmissionForms(subId)
  commentForms[subId]._files = Array.from(e.target.files || [])
}

function clearCommentFiles(subId) {
  ensureSubmissionForms(subId)
  commentForms[subId]._files = []
}

function buildFormData(fields) {
  const fd = new FormData()
  for (const [key, value] of Object.entries(fields)) {
    if (key === 'files' && Array.isArray(value)) {
      value.forEach(f => fd.append('files[]', f))
    } else if (value !== null && value !== undefined) {
      fd.append(key, value)
    }
  }
  return fd
}

function submitReview(sub, decision) {
  ensureSubmissionForms(sub.id)
  const data = buildFormData({
    decision,
    comment: reviewForms[sub.id]?.comment ?? '',
    files: reviewForms[sub.id]?._files ?? [],
  })

  router.post(
    route('lms.admin.assignments.review', [props.event.slug, props.assignment.id, sub.id]),
    data,
    {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        reviewForms[sub.id].comment = ''
        reviewForms[sub.id]._files = []
      },
    }
  )
}

function submitComment(sub) {
  ensureSubmissionForms(sub.id)
  const data = buildFormData({
    text: commentForms[sub.id]?.text ?? '',
    files: commentForms[sub.id]?._files ?? [],
  })

  router.post(
    route('lms.admin.assignments.comment', [props.event.slug, props.assignment.id, sub.id]),
    data,
    {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        commentForms[sub.id].text = ''
        commentForms[sub.id]._files = []
      },
    }
  )
}

function isStatusFilterActive(status) {
  return props.filters?.status === status || (status === 'new' && props.filters?.only_unread)
}

function applyStatusFilter(status) {
  const nextStatus = isStatusFilterActive(status) ? undefined : status
  router.get(route('lms.admin.assignments.show', [props.event.slug, props.assignment.id]), buildListQuery({ status: nextStatus }), {
    preserveState: false,
    replace: true,
  })
}

function applyFilters() {
  router.get(route('lms.admin.assignments.show', [props.event.slug, props.assignment.id]), buildListQuery(), {
    preserveState: false,
    replace: true,
  })
}

function clearStatusFilter() {
  router.get(route('lms.admin.assignments.show', [props.event.slug, props.assignment.id]), buildListQuery({ status: undefined }), {
    preserveState: false,
    replace: true,
  })
}

function markAsRead(sub) {
  if (!sub?.has_unread) return

  sub.has_unread = false
  sub.read_at = new Date().toISOString()

  axios.post(route('lms.admin.assignments.mark-read', [props.event.slug, props.assignment.id, sub.id]))
}

function toggleExpanded(sub) {
  const next = !expanded.value[sub.id]
  expanded.value[sub.id] = next
  if (next) {
    markAsRead(sub)
  }
}
</script>
