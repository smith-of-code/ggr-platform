<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Тесты – ${event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Тесты</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="test in (tests?.data || tests || [])"
          :key="test.id"
          :href="route('lms.tests.show', { event: event?.slug, test: test.id })"
          class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300"
        >
          <div class="p-6">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ test.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ test.description }}</p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
              <span class="text-gray-400">Проходной балл: {{ test.passing_score ?? 0 }}%</span>
              <span v-if="test.best_score != null" class="font-medium text-rosatom-600">
                Ваш лучший: {{ test.best_score }}%
              </span>
              <span v-else class="text-gray-400">Не пройден</span>
            </div>
            <div class="mt-2 text-xs text-gray-400">
              Попыток: {{ test.attempt_count ?? 0 }}
              <template v-if="test.max_attempts"> / {{ test.max_attempts }}</template>
            </div>
          </div>
        </Link>
      </div>

      <div v-if="!(tests?.data?.length || tests?.length)" class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm">
        Тесты не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'

defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  tests: { type: [Object, Array], default: () => [] },
})
</script>
