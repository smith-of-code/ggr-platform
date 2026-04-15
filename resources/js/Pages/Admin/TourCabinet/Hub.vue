<template>
  <AdminLayout>
    <Head title="ЛК туров" />
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Личный кабинет туров</h1>
      <p class="mt-1 text-sm text-gray-500">
        Настройки раздела
        <code class="rounded bg-gray-100 px-1 text-xs">/tour-cabinet</code>
        для участников конкурса и туров. Событие LMS:
        <span v-if="lmsEvent" class="font-medium text-gray-800">«{{ lmsEvent.title }}»</span>
        <span v-else class="font-mono text-amber-800">{{ configSlug }}</span>
        <span v-if="!lmsEvent" class="text-amber-700"> — событие не найдено в БД.</span>
      </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
      <RCard elevation="raised" class="flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900">Формы и этап 1 конкурса</h2>
        <div class="mt-6">
          <Link :href="route('admin.tour-cabinet.forms.index')">
            <RButton variant="primary" block>Открыть</RButton>
          </Link>
        </div>
      </RCard>

      <RCard elevation="raised" class="flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900">Города по направлениям</h2>
        <div class="mt-6">
          <Link :href="route('admin.tour-cabinet.direction-cities.index')">
            <RButton variant="primary" block>Открыть</RButton>
          </Link>
        </div>
      </RCard>

      <RCard elevation="raised" class="flex flex-col">
        <h2 class="text-lg font-semibold text-gray-900">Вопросы этапа 2</h2>
        <div class="mt-6">
          <Link :href="route('admin.tour-cabinet.stage2-questions.index')">
            <RButton variant="primary" block>Открыть</RButton>
          </Link>
        </div>
      </RCard>

      <RCard elevation="raised" class="flex flex-col bg-gray-50/60">
        <h2 class="text-lg font-semibold text-gray-900">Этап 3</h2>
        <p class="mt-4 text-sm text-gray-500">Только в ЛК участника после этапа 2.</p>
      </RCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  lmsEvent: { type: Object, default: null },
  configSlug: { type: String, default: '' },
})
</script>
