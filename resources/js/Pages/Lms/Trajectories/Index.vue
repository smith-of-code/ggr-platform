<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Траектория – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Траектория</h1>

      <div v-if="trajectory && timeline?.length" class="relative mx-auto max-w-3xl">
        <div class="absolute left-6 top-0 bottom-8 w-0.5 bg-gray-200 sm:left-8" />

        <div v-for="(item, idx) in timeline" :key="idx" class="relative flex items-start gap-4 sm:gap-6" :class="idx < timeline.length - 1 ? 'mb-8' : ''">
          <!-- Dot -->
          <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:h-16 sm:w-16"
               :class="dotClass(item)">
            <component :is="dotIcon(item)" />
          </div>

          <!-- Content -->
          <div class="min-w-0 flex-1 rounded-2xl border bg-white shadow-sm transition-all" :class="borderClass(item)">
            <button
              type="button"
              class="flex w-full items-start justify-between gap-2 p-5 text-left"
              @click="toggle(idx)"
            >
              <div class="min-w-0 flex-1">
                <span class="text-base font-semibold" :class="item.route ? 'text-rosatom-600' : 'text-gray-900'">
                  {{ item.title }}
                </span>
                <!-- Compact info when collapsed -->
                <div v-if="!expanded[idx]" class="mt-1 flex flex-wrap items-center gap-2">
                  <span v-if="item.type === 'course' && item.enrolled" class="text-xs text-gray-400">{{ item.progress }}% пройдено</span>
                  <span v-if="item.type === 'grant' && item.enrolled" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700">Выбран</span>
                  <span v-if="item.type === 'task'" class="text-xs" :class="submissionStatusClass(item.submission_status)">
                    {{ submissionStatusLabel(item.submission_status) }}
                  </span>
                </div>
              </div>
              <div class="flex shrink-0 items-center gap-2 pt-0.5">
                <span v-if="displayDate(item)" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
                  {{ displayDate(item) }}
                </span>
                <svg
                  class="h-5 w-5 text-gray-400 transition-transform duration-200"
                  :class="{ 'rotate-180': expanded[idx] }"
                  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </div>
            </button>

            <!-- Expanded details -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              leave-active-class="transition-all duration-150 ease-in"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-[600px] opacity-100"
              leave-from-class="max-h-[600px] opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div v-if="expanded[idx]" class="overflow-hidden">
                <div class="border-t border-gray-100 px-5 pb-5 pt-4">
                  <!-- Description -->
                  <p v-if="item.description" class="mb-4 line-clamp-4 text-sm text-gray-500">{{ item.description }}</p>

                  <!-- Course progress bar -->
                  <div v-if="item.type === 'course' && item.enrolled" class="mb-4">
                    <div class="mb-1 flex justify-between text-xs text-gray-400">
                      <span>Прогресс</span>
                      <span>{{ item.progress }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                      <div class="h-full rounded-full transition-all duration-500"
                           :class="item.progress >= 100 ? 'bg-green-500' : 'bg-rosatom-500'"
                           :style="{ width: item.progress + '%' }" />
                    </div>
                  </div>

                  <!-- Course stages list -->
                  <div v-if="item.type === 'course' && item.enrolled && item.stages?.length" class="mb-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Этапы</p>
                    <div class="space-y-1.5">
                      <div v-for="(stage, si) in item.stages" :key="si" class="flex items-center gap-2.5 rounded-lg px-2 py-1.5" :class="stage.completed ? 'bg-green-50' : 'bg-gray-50'">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full" :class="stage.completed ? 'bg-green-500 text-white' : 'border border-gray-300 bg-white'">
                          <svg v-if="stage.completed" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <span class="text-sm" :class="stage.completed ? 'text-green-700' : 'text-gray-600'">{{ stage.title }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Grant badge -->
                  <div v-if="item.type === 'grant' && item.enrolled" class="mb-4">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                      Вы выбрали этот грант
                    </span>
                  </div>

                  <!-- Task submission status -->
                  <div v-if="item.type === 'task'" class="mb-4">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                          :class="submissionBadgeClass(item.submission_status)">
                      {{ submissionStatusLabel(item.submission_status) }}
                    </span>
                  </div>

                  <!-- Action link -->
                  <a v-if="item.route" :href="item.route"
                     class="inline-flex items-center gap-1.5 rounded-lg bg-rosatom-50 px-4 py-2 text-sm font-medium text-rosatom-700 transition hover:bg-rosatom-100">
                    {{ actionLabel(item) }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                  </a>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>

      <div v-else class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <p class="text-sm text-gray-400">Траектория пока не настроена</p>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { h, reactive } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  trajectory: { type: Object, default: null },
  timeline: { type: Array, default: () => [] },
})

const user = computed(() => props.user || usePage().props.auth?.user || {})

const expanded = reactive({})

function toggle(idx) {
  expanded[idx] = !expanded[idx]
}

function formatDate(d) {
  if (!d) return null
  const parts = d.split('-')
  if (parts.length !== 3) return d
  return `${parts[2]}.${parts[1]}.${parts[0]}`
}

function displayDate(item) {
  if (item.date_label) return item.date_label
  if (item.date_start && item.date_end) {
    const s = formatDate(item.date_start)
    const e = formatDate(item.date_end)
    return `${s.substring(0, 5)} – ${e}`
  }
  if (item.date_start) return `с ${formatDate(item.date_start)}`
  if (item.date_end) return `до ${formatDate(item.date_end)}`
  return null
}

function dotClass(item) {
  if (item.type === 'course') {
    if (item.enrolled && item.progress >= 100) return 'bg-green-100 text-green-600'
    if (item.enrolled) return 'bg-green-100 text-green-600'
    return 'bg-gray-100 text-gray-400'
  }
  if (item.type === 'grant') {
    return item.enrolled ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-400'
  }
  if (item.type === 'task') {
    if (item.submission_status === 'approved') return 'bg-green-100 text-green-600'
    if (item.submission_status === 'submitted' || item.submission_status === 'in_review') return 'bg-yellow-100 text-yellow-600'
    return 'bg-purple-100 text-purple-600'
  }
  return 'bg-blue-100 text-blue-600'
}

function borderClass(item) {
  if (item.type === 'course') {
    if (item.enrolled) return 'border-green-200'
    return 'border-gray-200'
  }
  if (item.type === 'grant') return item.enrolled ? 'border-amber-200' : 'border-gray-200'
  if (item.type === 'task') {
    if (item.submission_status === 'approved') return 'border-green-200'
    if (item.submission_status === 'submitted' || item.submission_status === 'in_review') return 'border-yellow-200'
    return 'border-purple-200'
  }
  return 'border-blue-200'
}

function submissionStatusLabel(status) {
  const map = {
    draft: 'Черновик',
    submitted: 'Отправлено',
    in_review: 'На проверке',
    approved: 'Принято',
    rejected: 'Отклонено',
  }
  return map[status] || 'Не начато'
}

function submissionStatusClass(status) {
  const map = {
    draft: 'text-gray-500',
    submitted: 'text-yellow-600',
    in_review: 'text-yellow-600',
    approved: 'text-green-600',
    rejected: 'text-red-600',
  }
  return map[status] || 'text-gray-400'
}

function submissionBadgeClass(status) {
  const map = {
    draft: 'bg-gray-100 text-gray-600',
    submitted: 'bg-yellow-50 text-yellow-700',
    in_review: 'bg-yellow-50 text-yellow-700',
    approved: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
  }
  return map[status] || 'bg-gray-100 text-gray-500'
}

function actionLabel(item) {
  if (item.type === 'course') return 'Перейти к курсу'
  if (item.type === 'grant') return 'Подробнее о гранте'
  if (item.type === 'task') return 'Перейти к заданию'
  return 'Подробнее'
}

const StaticIcon = {
  render: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z' }),
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M6 6h.008v.008H6V6Z' }),
  ])
}
const CourseIcon = {
  render: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25' }),
  ])
}
const GrantIcon = {
  render: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 8h4.5a2.5 2.5 0 0 1 0 5H9V8Zm0 5v3m0 0v2m0-2H7m2 0h4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' }),
  ])
}
const TaskIcon = {
  render: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z' }),
  ])
}

function dotIcon(item) {
  if (item.type === 'course') return CourseIcon
  if (item.type === 'grant') return GrantIcon
  if (item.type === 'task') return TaskIcon
  return StaticIcon
}
</script>
