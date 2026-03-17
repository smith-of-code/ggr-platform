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

      <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:p-8">
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
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Результат</span>
            <span class="font-semibold" :class="attempt?.passed ? 'text-accent-green' : 'text-red-600'">
              {{ attempt?.percentage ?? 0 }}%
            </span>
          </div>
          <div class="mt-2 h-3 overflow-hidden rounded-full bg-gray-100">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="attempt?.passed ? 'bg-accent-green' : 'bg-red-500'"
              :style="{ width: `${Math.min(attempt?.percentage ?? 0, 100)}%` }"
            />
          </div>
          <p class="mt-1 text-xs text-gray-400">Проходной балл: {{ test?.passing_score ?? 0 }}%</p>
        </div>

        <div class="mt-8">
          <button
            class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            @click="router.visit(route('lms.tests.show', { event: event?.slug, test: test?.id }))"
          >
            <ArrowLeftIcon class="h-4 w-4" />
            Вернуться к тесту
          </button>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempt: { type: Object, required: true },
  responses: { type: Array, default: () => [] },
})
</script>
