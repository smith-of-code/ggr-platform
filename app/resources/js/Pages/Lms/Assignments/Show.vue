<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${assignment?.title} – ${event?.title}`" />
    <div class="mx-auto max-w-4xl space-y-8">
      <Link
        :href="route('lms.assignments.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к заданиям
      </Link>

      <!-- Assignment info -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
        <div class="flex items-start justify-between gap-4">
          <h1 class="font-brand text-2xl font-bold text-gray-900">{{ assignment?.title }}</h1>
          <span :class="['shrink-0 rounded-full px-3 py-1 text-xs font-bold', statusBadgeClass(submission?.status || 'not_submitted')]">
            {{ statusLabel(submission?.status || 'not_submitted') }}
          </span>
        </div>

        <!-- Deadline -->
        <div v-if="assignment?.deadline" class="mt-3 flex items-center gap-2 text-sm text-gray-500">
          <ClockIcon class="h-4 w-4" />
          <span>Дедлайн: {{ formatDate(assignment.deadline) }}</span>
          <span v-if="isOverdue" class="font-semibold text-red-500">· Просрочено</span>
        </div>

        <!-- Description (HTML) -->
        <div
          v-if="assignment?.description"
          class="mt-6 prose max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600 prose-li:text-gray-700 prose-strong:text-gray-800"
          v-html="assignment.description"
        />

        <!-- Template download -->
        <div v-if="assignment?.template_file" class="mt-6">
          <a
            :href="assignment.template_file"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
          >
            <ArrowDownTrayIcon class="h-4 w-4" />
            Скачать шаблон задания
          </a>
        </div>

        <!-- Status timeline -->
        <div class="mt-8 rounded-xl bg-gray-50 p-4">
          <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Прогресс</p>
          <div class="flex items-center gap-1">
            <div v-for="(step, i) in timelineSteps" :key="step.key" class="flex flex-1 items-center">
              <div
                :class="[
                  'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold transition',
                  timelineStepClass(i),
                ]"
              >
                <CheckIcon v-if="isStepDone(i)" class="h-4 w-4" />
                <span v-else>{{ i + 1 }}</span>
              </div>
              <div v-if="i < timelineSteps.length - 1" :class="['mx-1 h-0.5 flex-1 rounded', isStepDone(i) ? 'bg-rosatom-400' : 'bg-gray-200']" />
            </div>
          </div>
          <div class="mt-2 flex justify-between text-[11px] text-gray-400">
            <span v-for="step in timelineSteps" :key="step.key">{{ step.label }}</span>
          </div>
        </div>
      </div>

      <!-- Submission form -->
      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
        <h2 class="font-brand text-lg font-bold text-gray-900">
          {{ submission?.status === 'submitted' ? 'Ваша работа отправлена' : 'Отправка работы' }}
        </h2>

        <!-- Already submitted info -->
        <div v-if="submission?.status === 'submitted' || submission?.status === 'approved'" class="mt-4 rounded-xl bg-accent-green/10 p-4">
          <div class="flex items-center gap-2 text-accent-green">
            <CheckCircleIcon class="h-5 w-5" />
            <span class="font-semibold">{{ submission.status === 'approved' ? 'Работа принята!' : 'Работа отправлена на проверку' }}</span>
          </div>
          <div v-if="submission?.text_content" class="mt-3 rounded-lg bg-white p-3 text-sm text-gray-700">
            {{ submission.text_content }}
          </div>
          <div v-if="submission?.link" class="mt-2">
            <a :href="submission.link" target="_blank" class="text-sm text-rosatom-600 hover:underline">{{ submission.link }}</a>
          </div>
          <div v-if="submission?.files?.length" class="mt-2 flex flex-wrap gap-2">
            <a
              v-for="(file, idx) in submission.files"
              :key="idx"
              :href="`/storage/${file}`"
              target="_blank"
              class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
            >
              <PaperClipIcon class="h-3.5 w-3.5" />
              Файл {{ idx + 1 }}
            </a>
          </div>
        </div>

        <!-- Submit / resubmit form -->
        <form
          v-if="!submission || submission.status === 'not_submitted' || submission.status === 'revision'"
          @submit.prevent="submitWork"
          class="mt-6 space-y-5"
        >
          <div>
            <label for="text_content" class="block text-sm font-medium text-gray-700">Текст решения</label>
            <textarea
              id="text_content"
              v-model="form.text_content"
              rows="6"
              class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
              placeholder="Опишите решение, ответьте на вопросы задания..."
            />
            <p v-if="form.errors.text_content" class="mt-1.5 text-sm text-red-600">{{ form.errors.text_content }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Ссылка (по желанию)</label>
            <input
              v-model="form.link"
              type="url"
              placeholder="https://docs.google.com/..."
              class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p v-if="form.errors.link" class="mt-1.5 text-sm text-red-600">{{ form.errors.link }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Прикрепить файлы</label>
            <div
              class="relative mt-2 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-rosatom-400"
              :class="{ 'border-rosatom-500 bg-rosatom-50': selectedFiles.length > 0 }"
            >
              <template v-if="selectedFiles.length > 0">
                <PaperClipIcon class="mx-auto mb-2 h-8 w-8 text-rosatom-500" />
                <p class="text-sm font-medium text-gray-900">{{ selectedFiles.length }} {{ fileWord(selectedFiles.length) }}</p>
                <div class="mt-2 flex flex-wrap justify-center gap-2">
                  <span
                    v-for="(f, i) in selectedFiles"
                    :key="i"
                    class="inline-flex items-center gap-1 rounded-lg bg-white px-2 py-1 text-xs text-gray-600 shadow-sm"
                  >
                    <DocumentIcon class="h-3.5 w-3.5" />
                    {{ f.name }}
                  </span>
                </div>
                <button type="button" class="mt-2 text-xs text-rosatom-600 hover:underline" @click="clearFiles">Убрать файлы</button>
              </template>
              <template v-else>
                <ArrowUpTrayIcon class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                <p class="text-sm font-medium text-gray-700">Нажмите или перетащите файлы</p>
                <p class="mt-1 text-xs text-gray-400">PDF, DOCX, XLSX, PNG, JPG — до 20 МБ</p>
              </template>
              <input
                ref="fileInput"
                type="file"
                multiple
                accept=".pdf,.docx,.xlsx,.pptx,.png,.jpg,.jpeg,.zip"
                class="absolute inset-0 cursor-pointer opacity-0"
                @change="onFilesSelected"
              />
            </div>
            <p v-if="form.errors.files" class="mt-1.5 text-sm text-red-600">{{ form.errors.files }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Отправка...' : (submission?.status === 'revision' ? 'Отправить доработку' : 'Отправить работу') }}
          </button>
        </form>

        <!-- Reviews -->
        <div v-if="submission?.reviews?.length" class="mt-8 border-t border-gray-200 pt-8">
          <h3 class="mb-4 text-lg font-bold text-gray-900">Комментарии проверяющего</h3>
          <div class="space-y-3">
            <div
              v-for="r in submission.reviews"
              :key="r.id"
              class="rounded-xl border border-gray-200 bg-gray-50 p-4"
            >
              <p class="text-sm text-gray-700">{{ r.comment || r.text }}</p>
              <p class="mt-2 text-xs text-gray-400">{{ formatDate(r.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  ClockIcon,
  PaperClipIcon,
  DocumentIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon, CheckIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  assignment: { type: Object, required: true },
  submission: { type: Object, default: null },
  reviews: { type: Array, default: () => [] },
})

const fileInput = ref(null)
const selectedFiles = ref([])

const form = useForm({
  text_content: props.submission?.text_content || '',
  link: props.submission?.link || '',
  files: [],
})

const isOverdue = computed(() => {
  if (!props.assignment?.deadline) return false
  return new Date(props.assignment.deadline) < new Date()
})

const timelineSteps = [
  { key: 'submit', label: 'Сдача' },
  { key: 'review', label: 'Проверка' },
  { key: 'revision', label: 'Доработка' },
  { key: 'done', label: 'Принято' },
]

const currentStepIndex = computed(() => {
  const status = props.submission?.status || 'not_submitted'
  const map = { not_submitted: -1, submitted: 1, revision: 2, approved: 3, rejected: 2, resubmitted: 1 }
  return map[status] ?? -1
})

function isStepDone(idx) {
  return idx < currentStepIndex.value
}

function timelineStepClass(index) {
  if (index < currentStepIndex.value) return 'bg-rosatom-100 text-rosatom-600'
  if (index === currentStepIndex.value) return 'bg-rosatom-600 text-white'
  return 'bg-gray-100 text-gray-400'
}

function statusLabel(status) {
  return { not_submitted: 'Не сдано', submitted: 'На проверке', revision: 'На доработке', approved: 'Принято', rejected: 'Отклонено', resubmitted: 'Пересдано' }[status] || status
}

function statusBadgeClass(status) {
  return {
    not_submitted: 'bg-gray-100 text-gray-600',
    submitted: 'bg-blue-50 text-blue-600',
    revision: 'bg-amber-50 text-amber-600',
    approved: 'bg-green-50 text-green-600',
    rejected: 'bg-red-50 text-red-600',
    resubmitted: 'bg-blue-50 text-blue-600',
  }[status] || 'bg-gray-100 text-gray-600'
}

function onFilesSelected(e) {
  const files = Array.from(e.target.files || [])
  selectedFiles.value = files
  form.files = files
}

function clearFiles() {
  selectedFiles.value = []
  form.files = []
  if (fileInput.value) fileInput.value.value = ''
}

function fileWord(n) {
  if (n === 1) return 'файл выбран'
  if (n >= 2 && n <= 4) return 'файла выбрано'
  return 'файлов выбрано'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function submitWork() {
  form.post(route('lms.assignments.submit', { event: props.event?.slug, assignment: props.assignment?.id }), {
    forceFormData: true,
  })
}
</script>
