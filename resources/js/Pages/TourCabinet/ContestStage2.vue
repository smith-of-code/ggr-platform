<template>
  <div class="min-h-dvh bg-gray-50 px-4 py-8 font-sans">
    <Head title="Конкурс — этап 2" />
    <div class="mx-auto max-w-3xl">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Конкурс: этап 2</h1>
          <p class="mt-1 text-sm text-gray-500">Текстовые ответы на вопросы организаторов</p>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
          <Link :href="route('tour-cabinet.dashboard')" class="font-medium text-rosatom-600 hover:text-rosatom-800">Главная ЛК</Link>
          <Link :href="`${route('tour-cabinet.dashboard')}#tour-cabinet-profile`" class="font-medium text-gray-600 hover:text-gray-900">Профиль</Link>
        </div>
      </div>

      <ContestStage2Panel
        :questions="contestStage2Questions"
        :locked="contestProgress.current_stage !== 2"
        :contest-stage="contestProgress.current_stage"
      />
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ContestStage2Panel from './Contest/ContestStage2Panel.vue'

defineProps({
  contestStage2Questions: { type: Array, default: () => [] },
  contestProgress: { type: Object, required: true },
})
</script>
