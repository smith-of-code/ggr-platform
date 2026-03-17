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
            <RBadge :variant="statusBadgeVariant(sub.status)">
              {{ statusLabel(sub.status) }}
            </RBadge>
            <svg class="h-5 w-5 text-gray-400 transition" :class="expanded[sub.id] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </RButton>

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

          <div v-if="sub.status !== 'approved' && sub.status !== 'rejected'" class="space-y-3 border-t border-gray-200 pt-4">
            <label class="block text-xs font-semibold text-gray-500">Комментарий</label>
            <textarea v-model="reviewForms[sub.id].comment" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" placeholder="Комментарий к работе..." />
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

const props = defineProps({ event: Object, assignment: Object, submissions: Object })

const expanded = ref({})
const reviewForms = reactive(
  Object.fromEntries((props.submissions?.data ?? []).map(s => [s.id, { comment: '' }]))
)

function statusLabel(status) {
  const map = { submitted: 'На проверке', approved: 'Принято', revision: 'На доработке', rejected: 'Отклонено', resubmitted: 'Пересдано' }
  return map[status] || status
}

function statusBadgeVariant(status) {
  const map = {
    submitted: 'info',
    approved: 'success',
    revision: 'warning',
    rejected: 'error',
    resubmitted: 'primary',
  }
  return map[status] || 'neutral'
}

function submitReview(sub, decision) {
  router.post(route('lms.admin.assignments.review', [props.event.slug, props.assignment.id, sub.id]), {
    decision: decision || 'approve',
    comment: reviewForms[sub.id]?.comment ?? '',
  })
}
</script>
