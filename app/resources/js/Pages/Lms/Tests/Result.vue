<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Результат: ${test?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <Link
        :href="route('lms.tests.show', { event: event?.slug, test: test?.id })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к тесту
      </Link>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h1 class="font-brand text-2xl font-bold text-gray-900">Результат теста</h1>
        <p class="mt-1 text-gray-500">{{ test?.title }}</p>

        <!-- Score display -->
        <div
          :class="[
            'mt-8 flex flex-col items-center justify-center rounded-xl py-12',
            attempt?.passed ? 'bg-accent-green/10' : 'bg-red-50',
          ]"
        >
          <div class="text-6xl font-bold" :class="attempt?.passed ? 'text-accent-green' : 'text-red-600'">
            {{ attempt?.percentage ?? attempt?.score ?? 0 }}%
          </div>
          <p class="mt-2 text-lg font-medium" :class="attempt?.passed ? 'text-accent-green' : 'text-red-600'">
            {{ attempt?.passed ? 'Тест сдан' : 'Тест не сдан' }}
          </p>
          <p class="mt-1 text-sm text-gray-500">
            {{ attempt?.score ?? 0 }} / {{ attempt?.max_score ?? 100 }} баллов
          </p>
        </div>

        <!-- Progress bar -->
        <div class="mt-6">
          <div class="flex justify-between text-sm text-gray-500">
            <span>Прогресс</span>
            <span>{{ attempt?.percentage ?? 0 }}%</span>
          </div>
          <div class="mt-1.5 h-3 overflow-hidden rounded-full bg-gray-100">
            <div
              :class="[
                'h-full rounded-full transition-all',
                attempt?.passed ? 'bg-rosatom-500' : 'bg-red-500',
              ]"
              :style="{ width: `${Math.min(attempt?.percentage ?? 0, 100)}%` }"
            />
          </div>
        </div>

        <!-- Review (if show_correct_answers) -->
        <div v-if="test?.show_correct_answers && responses?.length" class="mt-10">
          <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Разбор ответов</h2>
          <div class="space-y-4">
            <div
              v-for="(r, i) in responses"
              :key="r.question_id || i"
              :class="[
                'rounded-lg border p-4',
                r.is_correct ? 'border-rosatom-500/30 bg-rosatom-50' : 'border-red-500/30 bg-red-50',
              ]"
            >
              <div class="flex items-start gap-3">
                <CheckCircleIcon v-if="r.is_correct" class="h-5 w-5 shrink-0 text-rosatom-600" />
                <XCircleIcon v-else class="h-5 w-5 shrink-0 text-red-600" />
                <div>
                  <p class="font-medium text-gray-900">{{ r.question_text || r.question }}</p>
                  <p v-if="r.selected_answers" class="mt-1 text-sm text-gray-500">
                    Ваш ответ: {{ formatAnswers(r.selected_answers) }}
                  </p>
                  <p v-if="!r.is_correct && r.correct_answers" class="mt-1 text-sm text-rosatom-600">
                    Правильный ответ: {{ formatAnswers(r.correct_answers) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8">
          <Link
            :href="route('lms.tests.show', { event: event?.slug, test: test?.id })"
            class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 font-semibold text-white transition hover:bg-rosatom-700"
          >
            <ArrowLeftIcon class="h-4 w-4" />
            Вернуться к тесту
          </Link>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempt: { type: Object, required: true },
  responses: { type: Array, default: () => [] },
})

function formatAnswers(answers) {
  if (Array.isArray(answers)) return answers.join(', ')
  if (typeof answers === 'string') return answers
  return String(answers ?? '')
}
</script>
