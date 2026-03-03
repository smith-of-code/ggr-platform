<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${test?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <Link
        :href="route('lms.tests.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к тестам
      </Link>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ test?.title }}</h1>
        <p class="mt-3 text-gray-500">{{ test?.description }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          <div class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Лимит времени</p>
            <p class="font-medium text-gray-900">{{ test?.time_limit ? `${test.time_limit} мин` : 'Без ограничения' }}</p>
          </div>
          <div class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Проходной балл</p>
            <p class="font-medium text-gray-900">{{ test?.passing_score ?? 0 }}%</p>
          </div>
          <div class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Макс. попыток</p>
            <p class="font-medium text-gray-900">{{ test?.max_attempts ?? 'Не ограничено' }}</p>
          </div>
          <div class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Ваших попыток</p>
            <p class="font-medium text-gray-900">{{ attempts?.length ?? 0 }}</p>
          </div>
        </div>

        <div class="mt-8">
          <Link
            :href="route('lms.tests.take', { event: event?.slug, test: test?.id })"
            class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 font-semibold text-white transition hover:bg-rosatom-700"
          >
            <PlayIcon class="h-5 w-5" />
            Начать тест
          </Link>
        </div>

        <!-- Past attempts -->
        <div v-if="attempts?.length" class="mt-10">
          <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Предыдущие попытки</h2>
          <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Дата</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Баллы</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Результат</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="a in attempts" :key="a.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(a.completed_at || a.created_at) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ a.score ?? a.percentage }}%</td>
                  <td class="px-4 py-3">
                    <span
                      :class="[
                        'rounded px-2 py-0.5 text-xs font-medium',
                        a.passed ? 'bg-accent-green/10 text-accent-green' : 'bg-red-100 text-red-600',
                      ]"
                    >
                      {{ a.passed ? 'Сдан' : 'Не сдан' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, PlayIcon } from '@heroicons/vue/24/outline'

defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempts: { type: Array, default: () => [] },
})

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
</script>
