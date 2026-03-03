<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${assignment?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-4xl space-y-8">
      <Link
        :href="route('lms.assignments.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к заданиям
      </Link>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ assignment?.title }}</h1>
        <span
          :class="[
            'mt-2 inline-block rounded px-2 py-0.5 text-xs font-medium',
            statusBadgeClass(submission?.status || 'not_submitted'),
          ]"
        >
          {{ statusLabel(submission?.status || 'not_submitted') }}
        </span>

        <!-- Description -->
        <div class="mt-6 prose max-w-none text-gray-700" v-html="assignment?.description" />

        <!-- Template download -->
        <div v-if="assignment?.template_file" class="mt-6">
          <a
            :href="assignment.template_file"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
          >
            <ArrowDownTrayIcon class="h-4 w-4" />
            Скачать шаблон
          </a>
        </div>

        <!-- Status timeline -->
        <div class="mt-8">
          <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Статус</h3>
          <div class="flex items-center gap-2">
            <div
              v-for="(step, i) in timelineSteps"
              :key="step"
              class="flex flex-1 items-center"
            >
              <div
                :class="[
                  'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold',
                  timelineStepClass(step, i),
                ]"
              >
                {{ i + 1 }}
              </div>
              <div v-if="i < timelineSteps.length - 1" class="mx-1 h-0.5 flex-1 bg-gray-200" />
            </div>
          </div>
          <div class="mt-2 flex justify-between text-xs text-gray-400">
            <span>Сдача</span>
            <span>Проверка</span>
            <span>Доработка</span>
            <span>Финально</span>
          </div>
        </div>
      </div>

      <!-- Submission form -->
      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h2 class="font-brand text-lg font-semibold text-gray-900">Отправка работы</h2>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
          <div>
            <label for="text" class="block text-sm font-medium text-gray-700">Текст решения</label>
            <textarea
              id="text"
              v-model="form.text"
              rows="6"
              class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
              placeholder="Опишите решение или вставьте текст..."
            />
            <p v-if="form.errors.text" class="mt-1.5 text-sm text-red-600">{{ form.errors.text }}</p>
          </div>

          <div>
            <label for="link" class="block text-sm font-medium text-gray-700">Ссылка</label>
            <input
              id="link"
              v-model="form.link"
              type="url"
              class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
              placeholder="https://..."
            />
            <p v-if="form.errors.link" class="mt-1.5 text-sm text-red-600">{{ form.errors.link }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Файл</label>
            <input
              type="file"
              @change="form.file = $event.target.files[0]"
              class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-200 file:px-4 file:py-2 file:text-gray-700 hover:file:bg-gray-300"
            />
            <p v-if="form.errors.file" class="mt-1.5 text-sm text-red-600">{{ form.errors.file }}</p>
          </div>

          <div class="flex gap-3">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-rosatom-600 px-6 py-3 font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Отправка...' : 'Отправить' }}
            </button>
          </div>
        </form>

        <!-- Reviews -->
        <div v-if="reviews?.length" class="mt-8 border-t border-gray-200 pt-8">
          <h3 class="font-brand mb-4 text-lg font-semibold text-gray-900">Комментарии проверяющего</h3>
          <div class="space-y-4">
            <div
              v-for="r in reviews"
              :key="r.id"
              class="rounded-lg border border-gray-200 bg-gray-50 p-4"
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
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  assignment: { type: Object, required: true },
  submission: { type: Object, default: () => null },
  reviews: { type: Array, default: () => [] },
})

const form = useForm({
  text: props.submission?.text || '',
  link: props.submission?.link || '',
  file: null,
})

const timelineSteps = ['submitted', 'review', 'revision', 'approved']

function statusLabel(status) {
  const map = {
    not_submitted: 'Не сдано',
    submitted: 'Сдано',
    revision: 'На доработке',
    approved: 'Принято',
    rejected: 'Отклонено',
  }
  return map[status] || status
}

function statusBadgeClass(status) {
  const map = {
    not_submitted: 'bg-gray-200 text-gray-700',
    submitted: 'bg-rosatom-50 text-rosatom-500',
    revision: 'bg-accent-yellow/10 text-accent-yellow',
    approved: 'bg-accent-green/10 text-accent-green',
    rejected: 'bg-red-100 text-red-600',
  }
  return map[status] || 'bg-gray-200 text-gray-700'
}

function timelineStepClass(step, index) {
  const status = props.submission?.status || 'not_submitted'
  const order = { not_submitted: 0, submitted: 1, revision: 2, approved: 3, rejected: 2 }
  const current = order[status] ?? 0
  if (index < current) return 'bg-rosatom-50 text-rosatom-600'
  if (index === current) return 'bg-rosatom-600 text-white'
  return 'bg-gray-100 text-gray-400'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function submit() {
  form.post(route('lms.assignments.submit', {
    event: props.event?.slug,
    assignment: props.assignment?.id,
  }), {
    forceFormData: true,
  })
}
</script>
