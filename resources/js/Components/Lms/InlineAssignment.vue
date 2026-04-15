<template>
  <div class="space-y-4">
    <!-- Assignment info -->
    <div class="rounded-xl border border-gray-200 bg-white p-5">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
            <PencilSquareIcon class="h-5 w-5 text-blue-600" />
          </div>
          <h3 class="font-medium text-gray-900">{{ assignment.title }}</h3>
        </div>
        <RBadge :variant="statusBadgeVariant(submission?.status || 'not_submitted')" size="sm" class="shrink-0">
          {{ statusLabel(submission?.status || 'not_submitted') }}
        </RBadge>
      </div>

      <div v-if="assignment.deadline" class="mt-3 flex items-center gap-2 text-sm text-gray-500">
        <ClockIcon class="h-4 w-4" />
        <span>Дедлайн: {{ formatDate(assignment.deadline) }}</span>
        <span v-if="isOverdue" class="font-semibold text-red-500">· Просрочено</span>
      </div>

      <div
        v-if="assignment.description"
        class="mt-4 prose max-w-none text-sm text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600"
        v-html="assignment.description"
      />

      <div v-if="assignment.template_file" class="mt-4">
        <a
          :href="assignment.template_file"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
        >
          <ArrowDownTrayIcon class="h-4 w-4" />
          {{ assignment.template_file_name || 'Скачать шаблон задания' }}
        </a>
      </div>

      <!-- Status timeline -->
      <div class="mt-6 rounded-xl bg-gray-50 p-3">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Прогресс</p>
        <div class="flex items-center gap-1">
          <div v-for="(step, i) in timelineSteps" :key="step.key" class="flex flex-1 items-center">
            <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition', timelineStepClass(i)]">
              <CheckIcon v-if="isStepDone(i)" class="h-3.5 w-3.5" />
              <span v-else>{{ i + 1 }}</span>
            </div>
            <div v-if="i < timelineSteps.length - 1" :class="['mx-1 h-0.5 flex-1 rounded', isStepDone(i) ? 'bg-rosatom-400' : 'bg-gray-200']" />
          </div>
        </div>
        <div class="mt-1.5 flex justify-between text-[10px] text-gray-400">
          <span v-for="step in timelineSteps" :key="step.key">{{ step.label }}</span>
        </div>
      </div>
    </div>

    <!-- Submitted summary -->
    <div v-if="submission?.status === 'submitted' || submission?.status === 'approved'" class="rounded-xl border border-gray-200 bg-white p-5">
      <h3 class="font-brand text-base font-bold text-gray-900">Ваша работа отправлена</h3>
      <div class="mt-3 rounded-xl bg-accent-green/10 p-3">
        <div class="flex items-center gap-2 text-accent-green">
          <CheckCircleIcon class="h-5 w-5" />
          <span class="text-sm font-semibold">{{ submission.status === 'approved' ? 'Работа принята!' : 'Работа отправлена на проверку' }}</span>
        </div>

        <template v-if="hasTasks && submission.answers?.length">
          <div v-for="(task, tIdx) in assignment.tasks" :key="task.id" class="mt-3 rounded-lg bg-white p-2.5">
            <div class="mb-1.5 flex items-start gap-2">
              <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gray-100 text-[10px] font-bold text-gray-500">{{ tIdx + 1 }}</span>
              <p class="text-sm font-semibold text-gray-900">{{ task.title }}</p>
            </div>
            <div class="ml-7">
              <template v-for="answer in answersForTask(task.id)" :key="answer.id">
                <p v-if="answer.text_content" class="text-sm text-gray-700">{{ answer.text_content }}</p>
                <a v-if="answer.link" :href="answer.link" target="_blank" class="text-sm text-rosatom-600 hover:underline">{{ answer.link }}</a>
                <div v-if="answer.files?.length" class="mt-1 flex flex-wrap gap-1.5">
                  <a v-for="(f, fi) in answer.files" :key="fi" :href="fileUrl(typeof f === 'string' ? f : f.path)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-100">
                    <PaperClipIcon class="h-3 w-3" />
                    {{ typeof f === 'string' ? `Файл ${fi+1}` : (f.name || `Файл ${fi+1}`) }}
                  </a>
                </div>
              </template>
            </div>
          </div>
        </template>

        <template v-else-if="!hasTasks">
          <p v-if="submission.text_content" class="mt-2 rounded-lg bg-white p-2 text-sm text-gray-700">{{ submission.text_content }}</p>
          <a v-if="submission.link" :href="submission.link" target="_blank" class="mt-1 block text-sm text-rosatom-600 hover:underline">{{ submission.link }}</a>
          <div v-if="submission.files?.length" class="mt-1.5 flex flex-wrap gap-1.5">
            <a v-for="(file, idx) in submission.files" :key="idx" :href="fileUrl(file)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
              <PaperClipIcon class="h-3 w-3" />
              Файл {{ idx + 1 }}
            </a>
          </div>
        </template>
      </div>
    </div>

    <!-- Submit form -->
    <div
      v-if="!submission || submission.status === 'not_submitted' || submission.status === 'revision' || submission.status === 'draft'"
      class="rounded-xl border border-gray-200 bg-white p-5"
    >
      <h3 class="font-brand text-base font-bold text-gray-900">
        {{ submission?.status === 'revision' ? 'Доработка' : 'Отправка работы' }}
      </h3>

      <form @submit.prevent="submitWork" class="mt-4 space-y-4">
        <!-- Tasks form -->
        <template v-if="hasTasks">
          <div v-for="(task, tIdx) in assignment.tasks" :key="task.id" class="rounded-xl border border-gray-200 p-3">
            <div class="mb-2 flex items-start gap-2">
              <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rosatom-100 text-xs font-bold text-rosatom-600">{{ tIdx + 1 }}</span>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900">{{ task.title }}</p>
                <p v-if="task.description" class="mt-0.5 whitespace-pre-line text-xs text-gray-500">{{ task.description }}</p>
                <a v-if="task.template_file" :href="task.template_file" target="_blank" class="mt-1 inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200">
                  <ArrowDownTrayIcon class="h-3 w-3" />
                  {{ task.template_file_name || 'Скачать шаблон' }}
                </a>
              </div>
            </div>

            <div v-if="task.response_type === 'text'">
              <textarea v-model="taskAnswers[task.id].text_content" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Введите ваш ответ..." />
            </div>
            <div v-else-if="task.response_type === 'link'">
              <RInput v-model="taskAnswers[task.id].link" type="url" placeholder="https://..." />
            </div>
            <div v-else-if="task.response_type === 'file'">
              <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-3 text-center transition hover:border-rosatom-400" :class="{ 'border-rosatom-500 bg-rosatom-50': taskAnswers[task.id]._files?.length > 0 }">
                <template v-if="taskAnswers[task.id]._files?.length > 0">
                  <PaperClipIcon class="mx-auto mb-0.5 h-5 w-5 text-rosatom-500" />
                  <p class="text-sm font-medium text-gray-900">{{ taskAnswers[task.id]._files.length }} {{ fileWord(taskAnswers[task.id]._files.length) }}</p>
                  <button type="button" class="mt-0.5 text-xs text-rosatom-600 hover:underline" @click="taskAnswers[task.id]._files = []">Убрать</button>
                </template>
                <template v-else>
                  <ArrowUpTrayIcon class="mx-auto mb-0.5 h-5 w-5 text-gray-400" />
                  <p class="text-xs font-medium text-gray-700">Нажмите или перетащите файлы</p>
                  <p class="text-[10px] text-gray-400">PDF, DOCX, XLSX, PNG, JPG — до 20 МБ</p>
                </template>
                <input type="file" multiple accept=".pdf,.docx,.xlsx,.pptx,.png,.jpg,.jpeg,.zip" class="absolute inset-0 cursor-pointer opacity-0" @change="e => taskAnswers[task.id]._files = Array.from(e.target.files || [])" />
              </div>
            </div>
          </div>
        </template>

        <!-- Legacy form -->
        <template v-else>
          <div>
            <label class="block text-sm font-medium text-gray-700">Текст решения</label>
            <textarea v-model="form.text_content" rows="4" class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Опишите решение..." />
          </div>
          <RInput v-model="form.link" type="url" label="Ссылка (по желанию)" placeholder="https://docs.google.com/..." />
          <div>
            <label class="block text-sm font-medium text-gray-700">Прикрепить файлы</label>
            <div class="relative mt-1.5 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-4 text-center transition hover:border-rosatom-400" :class="{ 'border-rosatom-500 bg-rosatom-50': selectedFiles.length > 0 }">
              <template v-if="selectedFiles.length > 0">
                <PaperClipIcon class="mx-auto mb-1 h-6 w-6 text-rosatom-500" />
                <p class="text-sm font-medium text-gray-900">{{ selectedFiles.length }} {{ fileWord(selectedFiles.length) }}</p>
                <button type="button" class="mt-1 text-xs text-rosatom-600 hover:underline" @click="clearFiles">Убрать</button>
              </template>
              <template v-else>
                <ArrowUpTrayIcon class="mx-auto mb-1 h-6 w-6 text-gray-400" />
                <p class="text-xs font-medium text-gray-700">Нажмите или перетащите файлы</p>
                <p class="text-[10px] text-gray-400">PDF, DOCX, XLSX, PNG, JPG — до 20 МБ</p>
              </template>
              <input ref="fileInput" type="file" multiple accept=".pdf,.docx,.xlsx,.pptx,.png,.jpg,.jpeg,.zip" class="absolute inset-0 cursor-pointer opacity-0" @change="onFilesSelected" />
            </div>
          </div>
        </template>

        <div class="flex gap-2">
          <RButton variant="primary" :loading="formProcessing" :disabled="formProcessing || draftSaving">
            {{ submission?.status === 'revision' ? 'Отправить доработку' : 'Отправить работу' }}
          </RButton>
          <RButton type="button" variant="outline" :loading="draftSaving" :disabled="formProcessing || draftSaving" @click="saveDraft">
            Черновик
          </RButton>
        </div>
      </form>
    </div>

    <!-- Discussion -->
    <div v-if="submission && dialogMessages.length > 0" class="rounded-xl border border-gray-200 bg-white p-5">
      <h3 class="mb-4 font-brand text-base font-bold text-gray-900">Обсуждение</h3>
      <div class="space-y-3">
        <div
          v-for="msg in dialogMessages"
          :key="msg.key"
          :class="['rounded-xl border p-3 text-sm', msg.isReview ? 'border-amber-200 bg-amber-50' : msg.isMine ? 'ml-6 border-rosatom-200 bg-rosatom-50' : 'mr-6 border-blue-200 bg-blue-50']"
        >
          <div class="mb-1 flex items-center justify-between">
            <span class="text-xs font-semibold" :class="msg.isReview ? 'text-amber-800' : msg.isMine ? 'text-rosatom-700' : 'text-blue-800'">{{ msg.authorName }}</span>
            <span class="text-[10px] text-gray-400">{{ formatDate(msg.date) }}</span>
          </div>
          <p class="whitespace-pre-wrap text-gray-800">{{ msg.text }}</p>
        </div>
      </div>
    </div>

    <!-- Comment form -->
    <div v-if="submission" class="rounded-xl border border-gray-200 bg-white p-5">
      <form @submit.prevent="submitComment" class="flex gap-2">
        <textarea v-model="commentText" rows="1" class="flex-1 rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Комментарий..." />
        <RButton variant="primary" :disabled="!commentText.trim() || commentSending" :loading="commentSending">
          Отправить
        </RButton>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { fileUrl } from '@/lib/fileUrl'
import { formatLmsAssignmentDeadline } from '@/utils/lmsAssignmentDeadline'
import {
  PencilSquareIcon, ArrowDownTrayIcon, ArrowUpTrayIcon,
  ClockIcon, PaperClipIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon, CheckIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: { type: Object, required: true },
  assignment: { type: Object, required: true },
  submission: { type: Object, default: null },
  user: { type: Object, default: () => ({}) },
})

const hasTasks = computed(() => (props.assignment?.tasks?.length ?? 0) > 0)
const isOverdue = computed(() => {
  if (!props.assignment?.deadline) return false
  return new Date(props.assignment.deadline) < new Date()
})
const isAutoAccept = computed(() => props.assignment?.completion_mode === 'on_submit')

const fileInput = ref(null)
const selectedFiles = ref([])
const formProcessing = ref(false)
const draftSaving = ref(false)
const commentText = ref('')
const commentSending = ref(false)

const form = reactive({
  text_content: props.submission?.text_content || '',
  link: props.submission?.link || '',
})

const taskAnswers = reactive(
  Object.fromEntries(
    (props.assignment?.tasks || []).map(task => {
      const existing = (props.submission?.answers || []).find(a => a.lms_assignment_task_id === task.id)
      return [task.id, {
        text_content: existing?.text_content || '',
        link: existing?.link || '',
        _files: [],
      }]
    })
  )
)

function answersForTask(taskId) {
  return (props.submission?.answers || []).filter(a => a.lms_assignment_task_id === taskId)
}

const timelineSteps = computed(() =>
  isAutoAccept.value
    ? [{ key: 'submit', label: 'Сдача' }, { key: 'done', label: 'Принято' }]
    : [{ key: 'submit', label: 'Сдача' }, { key: 'review', label: 'Проверка' }, { key: 'revision', label: 'Доработка' }, { key: 'done', label: 'Принято' }]
)

const currentStepIndex = computed(() => {
  const status = props.submission?.status || 'not_submitted'
  if (isAutoAccept.value) {
    return { not_submitted: -1, draft: -1, approved: 1 }[status] ?? -1
  }
  return { not_submitted: -1, draft: -1, submitted: 1, revision: 2, approved: 3, rejected: 2, resubmitted: 1 }[status] ?? -1
})

function isStepDone(idx) { return idx < currentStepIndex.value }
function timelineStepClass(index) {
  if (index < currentStepIndex.value) return 'bg-rosatom-100 text-rosatom-600'
  if (index === currentStepIndex.value) return 'bg-rosatom-600 text-white'
  return 'bg-gray-100 text-gray-400'
}

function statusLabel(s) {
  return { not_submitted: 'Не сдано', draft: 'Черновик', submitted: 'На проверке', revision: 'На доработке', approved: 'Принято', rejected: 'Отклонено', resubmitted: 'Пересдано' }[s] || s
}
function statusBadgeVariant(s) {
  return { not_submitted: 'neutral', draft: 'neutral', submitted: 'info', revision: 'warning', approved: 'success', rejected: 'error', resubmitted: 'info' }[s] || 'neutral'
}

const dialogMessages = computed(() => {
  if (!props.submission) return []
  const messages = []
  for (const r of props.submission.reviews || []) {
    messages.push({ key: `r-${r.id}`, isReview: true, isMine: false, authorName: r.reviewer?.name || 'Проверяющий', text: r.comment || '', decision: r.decision, date: r.created_at, ts: new Date(r.created_at).getTime() })
  }
  for (const c of props.submission.comments || []) {
    messages.push({ key: `c-${c.id}`, isReview: false, isMine: c.user_id === props.user?.id, authorName: c.user?.name || 'Пользователь', text: c.text, date: c.created_at, ts: new Date(c.created_at).getTime() })
  }
  messages.sort((a, b) => a.ts - b.ts)
  return messages
})

function buildFormData() {
  const fd = new FormData()
  if (hasTasks.value) {
    const tasks = props.assignment.tasks || []
    tasks.forEach((task, idx) => {
      fd.append(`answers[${idx}][task_id]`, task.id)
      fd.append(`answers[${idx}][text_content]`, taskAnswers[task.id]?.text_content || '')
      fd.append(`answers[${idx}][link]`, taskAnswers[task.id]?.link || '')
      if (task.response_type === 'file' && taskAnswers[task.id]?._files?.length) {
        taskAnswers[task.id]._files.forEach(f => fd.append(`answers[${idx}][files][]`, f))
      }
    })
  } else {
    fd.append('text_content', form.text_content || '')
    fd.append('link', form.link || '')
    selectedFiles.value.forEach(f => fd.append('files[]', f))
  }
  return fd
}

function submitWork() {
  formProcessing.value = true
  router.post(
    route('lms.assignments.submit', { event: props.event?.slug, assignment: props.assignment?.id }),
    buildFormData(),
    { forceFormData: true, preserveScroll: true, onFinish: () => { formProcessing.value = false } },
  )
}

function saveDraft() {
  draftSaving.value = true
  router.post(
    route('lms.assignments.draft', { event: props.event?.slug, assignment: props.assignment?.id }),
    buildFormData(),
    { forceFormData: true, preserveScroll: true, onFinish: () => { draftSaving.value = false } },
  )
}

function submitComment() {
  commentSending.value = true
  router.post(
    route('lms.assignments.comment', { event: props.event?.slug, assignment: props.assignment?.id }),
    { text: commentText.value },
    { preserveScroll: true, onFinish: () => { commentSending.value = false }, onSuccess: () => { commentText.value = '' } },
  )
}

function onFilesSelected(e) {
  selectedFiles.value = Array.from(e.target.files || [])
}
function clearFiles() {
  selectedFiles.value = []
  if (fileInput.value) fileInput.value.value = ''
}

function fileWord(n) {
  if (n === 1) return 'файл'
  if (n >= 2 && n <= 4) return 'файла'
  return 'файлов'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return formatLmsAssignmentDeadline(dateStr, 'long')
}
</script>
