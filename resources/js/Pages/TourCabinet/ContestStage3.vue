<template>
  <div class="min-h-dvh bg-gray-50 px-4 py-8 font-sans">
    <Head title="Конкурс — этап 3" />
    <div class="mx-auto max-w-3xl">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Конкурс: этап 3</h1>
          <p class="mt-1 text-sm text-gray-500">Проверочное задание: текст и ссылка на видео</p>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
          <Link :href="route('tour-cabinet.dashboard')" class="font-medium text-rosatom-600 hover:text-rosatom-800">Главная ЛК</Link>
          <Link :href="`${route('tour-cabinet.dashboard')}#tour-cabinet-profile`" class="font-medium text-gray-600 hover:text-gray-900">Профиль</Link>
        </div>
      </div>

      <ContestStage3Panel
        :progress="contestStage3Progress"
        :locked="contestStage3Locked"
        :lock-notice="contestStage3LockNotice"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ContestStage3Panel from './Contest/ContestStage3Panel.vue'

const props = defineProps({
  contestStage3Progress: { type: Object, required: true },
  contestProgress: { type: Object, required: true },
})

const contestStage3LockNotice = computed(() => {
  const st = Number(props.contestProgress?.current_stage)
  if (!Number.isFinite(st) || st < 3) {
    return 'early'
  }
  const t = props.contestStage3Progress?.stage3_text
  if (typeof t === 'string' && t.trim() !== '') {
    return 'saved'
  }

  return null
})

const contestStage3Locked = computed(() => contestStage3LockNotice.value !== null)
</script>
