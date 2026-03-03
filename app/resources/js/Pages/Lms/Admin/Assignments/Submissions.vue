<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.assignments.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к заданиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ assignment.title }}</h1>
      <p class="mt-1 text-sm text-gray-500">Работы участников</p>
    </div>

    <div class="space-y-4">
      <div
        v-for="sub in submissions.data"
        :key="sub.id"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
      >
        <button
          type="button"
          @click="expanded[sub.id] = !expanded[sub.id]"
          class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-gray-50"
        >
          <div class="flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rosatom-50 text-sm font-bold text-rosatom-700">
              {{ sub.user?.name?.charAt(0) || '?' }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ sub.user?.name }}</p>
              <p class="text-xs text-gray-500">{{ sub.user?.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span :class="statusClass(sub.status)" class="rounded-full px-3 py-1 text-xs font-semibold">
              {{ statusLabel(sub.status) }}
            </span>
            <svg class="h-5 w-5 text-gray-400 transition" :class="expanded[sub.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </button>

        <div v-show="expanded[sub.id]" class="border-t border-gray-200 bg-gray-50 p-5">
          <div v-if="sub.text_content" class="mb-4">
            <label class="mb-1 block text-xs font-semibold text-gray-500">Текст</label>
            <p class="whitespace-pre-wrap rounded-lg bg-white border border-gray-200 p-3 text-sm text-gray-900">{{ sub.text_content }}</p>
          </div>
          <div v-if="sub.link" class="mb-4">
            <label class="mb-1 block text-xs font-semibold text-gray-500">Ссылка</label>
            <a :href="sub.link" target="_blank" rel="noopener" class="text-sm text-rosatom-600 underline hover:text-rosatom-700">{{ sub.link }}</a>
          </div>
          <div v-if="sub.files?.length" class="mb-4">
            <label class="mb-1 block text-xs font-semibold text-gray-500">Файлы</label>
            <ul class="space-y-1">
              <li v-for="(f, i) in sub.files" :key="i">
                <a v-if="f.url" :href="f.url" target="_blank" class="text-sm text-rosatom-600 underline hover:text-rosatom-700">{{ f.name || f.url }}</a>
                <span v-else class="text-sm text-gray-500">{{ f.name || f }}</span>
              </li>
            </ul>
          </div>

          <div v-if="sub.status !== 'approve' && sub.status !== 'reject'" class="space-y-3 border-t border-gray-200 pt-4">
            <label class="block text-xs font-semibold text-gray-500">Комментарий</label>
            <textarea v-model="reviewForms[sub.id].comment" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" placeholder="Комментарий к работе..." />
            <div class="flex gap-2">
              <button type="button" @click="submitReview(sub, 'approve')" class="rounded-lg bg-rosatom-600 px-4 py-2 text-sm font-medium text-white hover:bg-rosatom-700">
                Принять
              </button>
              <button type="button" @click="submitReview(sub, 'revision')" class="rounded-lg bg-accent-yellow px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                На доработку
              </button>
              <button type="button" @click="submitReview(sub, 'reject')" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Отклонить
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="submissions.data.length === 0" class="rounded-xl border border-gray-200 bg-white px-5 py-16 text-center text-sm text-gray-500 shadow-sm">
      Пока нет отправленных работ
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, assignment: Object, submissions: Object })

const expanded = ref({})
const reviewForms = reactive(
  Object.fromEntries((props.submissions?.data ?? []).map(s => [s.id, { comment: '' }]))
)

function statusLabel(status) {
  const map = { approve: 'Принято', revision: 'На доработку', reject: 'Отклонено', pending: 'На проверке' }
  return map[status] || status
}

function statusClass(status) {
  const map = {
    approve: 'bg-accent-green/10 text-accent-green',
    revision: 'bg-accent-yellow/10 text-accent-yellow',
    reject: 'bg-red-50 text-red-600',
    pending: 'bg-gray-100 text-gray-500',
  }
  return map[status] || 'bg-gray-100 text-gray-500'
}

function submitReview(sub, decision) {
  router.post(route('lms.admin.assignments.review', [props.event.id, props.assignment.id, sub.id]), {
    decision: decision || 'approve',
    comment: reviewForms[sub.id]?.comment ?? '',
  })
}
</script>
