<template>
  <div class="min-h-dvh bg-gray-50 px-4 py-8 font-sans">
    <Head title="Конкурс — этап 1" />
    <div class="mx-auto max-w-3xl">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Конкурс: этап 1</h1>
          <p class="mt-1 text-sm text-gray-500">Направление → города (до 3) → анкеты по выбранным городам</p>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
          <Link :href="route('tour-cabinet.dashboard')" class="font-medium text-rosatom-600 hover:text-rosatom-800">Главная ЛК</Link>
          <Link :href="`${route('tour-cabinet.dashboard')}#tour-cabinet-profile`" class="font-medium text-gray-600 hover:text-gray-900">Профиль</Link>
        </div>
      </div>

      <ContestStage1Panel v-bind="contestStage1" />
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ContestStage1Panel from './Contest/ContestStage1Panel.vue'

defineProps({
  contestStage1: { type: Object, required: true },
})
</script>
