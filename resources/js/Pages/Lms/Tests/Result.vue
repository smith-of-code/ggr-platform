<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Результат: ${test?.title} – ${event?.title || event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <Link
        :href="route('lms.tests.show', { event: event?.slug, test: test?.id })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к тесту
      </Link>

      <RCard elevation="raised">
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
            {{ attempt?.passed ? 'Тест сдан!' : 'Тест не сдан' }}
          </p>
          <p class="mt-1 text-sm text-gray-500">
            {{ attempt?.score ?? 0 }} / {{ attempt?.max_score ?? 100 }} баллов
          </p>
        </div>

        <!-- Progress bar -->
        <div class="mt-6">
          <RProgress
            :percentage="Math.min(attempt?.percentage ?? 0, 100)"
            label="Результат"
            show-label
            size="md"
            :variant="attempt?.passed ? 'success' : 'error'"
          />
          <p class="mt-1 text-xs text-gray-400">Для сдачи нужно: {{ requiredCorrect }} из {{ test?.questions_count ?? '?' }} правильных</p>
        </div>

        <div class="mt-8">
          <RButton variant="primary" @click="router.visit(route('lms.tests.show', { event: event?.slug, test: test?.id }))">
            <template #icon><ArrowLeftIcon class="h-4 w-4" /></template>
            Вернуться к тесту
          </RButton>
        </div>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempt: { type: Object, required: true },
  responses: { type: Array, default: () => [] },
})

const requiredCorrect = computed(() => {
  const total = props.test?.questions_count ?? 0
  const passingScore = props.test?.passing_score ?? 0
  if (!total) return 0
  return Math.ceil(passingScore * total / 100)
})
</script>
