<template>
  <AdminLayout>
    <Head title="Вопросы этапа 2 конкурса" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-stage2`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Конкурс ЛК туров: вопросы этапа 2</h1>
      </div>

      <TourCabinetAdminStage2QuestionsPanel :questions="questions" :directions="directions" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminStage2QuestionsPanel from '../TourCabinetAdminStage2QuestionsPanel.vue'

defineProps({
  questions: { type: Array, default: () => [] },
  directions: { type: Array, default: () => [] },
})
</script>
