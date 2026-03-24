<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.assignments.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к заданиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ assignment.title }}</h1>
      <p class="mt-1 text-sm text-gray-500">Работы участников</p>
    </div>

    <div class="space-y-4">
      <RCard
        v-for="sub in submissions.data"
        :key="sub.id"
        flush
      >
        <!-- Collapse header -->
        <RButton
          type="button"
          variant="ghost"
          class="flex w-full items-center justify-between px-5 py-4 text-left hover:bg-gray-50"
          @click="expanded[sub.id] = !expanded[sub.id]"
        >
          <div class="flex items-center gap-4">
            <RAvatar :name="sub.user?.name" size="md" />
            <div>
              <p class="text-sm font-medium text-gray-900">{{ sub.user?.name }}</p>
              <p class="text-xs text-gray-500">{{ sub.user?.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span v-if="threadCount(sub)" class="text-xs text-gray-400">{{ threadCount(sub) }} сообщ.</span>
            <RBadge :variant="statusBadgeVariant(sub.status)">
              {{ statusLabel(sub.status) }}
            </RBadge>
            <svg class="h-5 w-5 text-gray-400 transition" :class="expanded[sub.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </RButton>

        <!-- Expanded content -->
        <div v-show="expanded[sub.id]" class="border-t border-gray-200 bg-gray-50 p-5">
          <!-- Submitted work -->
          <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Ответ участника</p>
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
          <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4">
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
          <div v-if="sub.status !== 'approved' && sub.status !== 'rejected'" class="space-y-3 border-t border-gray-200 pt-4">
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
      </RCard>
    </div>

    <RCard v-if="submissions.data.length === 0" flush class="px-5 py-16 text-center text-sm text-gray-500">
      Пока нет отправленных работ
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { fileUrl } from '@/lib/fileUrl'

const props = defineProps({ event: Object, assignment: Object, submissions: Object })

const expanded = ref({})

const reviewForms = reactive(
  Object.fromEntries((props.submissions?.data ?? []).map(s => [s.id, { comment: '', _files: [] }]))
)
const commentForms = reactive(
  Object.fromEntries((props.submissions?.data ?? []).map(s => [s.id, { text: '', _files: [] }]))
)

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

function onReviewFiles(subId, e) {
  reviewForms[subId]._files = Array.from(e.target.files || [])
}

function onCommentFiles(subId, e) {
  commentForms[subId]._files = Array.from(e.target.files || [])
}

function clearCommentFiles(subId) {
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
  const data = buildFormData({
    decision,
    comment: reviewForms[sub.id]?.comment ?? '',
    files: reviewForms[sub.id]?._files ?? [],
  })

  router.post(
    route('lms.admin.assignments.review', [props.event.slug, props.assignment.id, sub.id]),
    data,
    { forceFormData: true }
  )
}

function submitComment(sub) {
  const data = buildFormData({
    text: commentForms[sub.id]?.text ?? '',
    files: commentForms[sub.id]?._files ?? [],
  })

  router.post(
    route('lms.admin.assignments.comment', [props.event.slug, props.assignment.id, sub.id]),
    data,
    {
      forceFormData: true,
      onSuccess: () => {
        commentForms[sub.id].text = ''
        commentForms[sub.id]._files = []
      },
    }
  )
}
</script>
