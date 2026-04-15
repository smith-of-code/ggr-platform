<template>
  <AdminLayout>
    <Head title="Конкурс: города по направлениям" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index', { project_key: projectKey })}#tour-cabinet-admin-cities`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div v-if="page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ page.props.flash.error }}
      </div>

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Конкурс ЛК туров: города по направлениям</h1>
        <p class="mt-1 text-sm text-gray-500">
          Состав списка для шага «выберите города» в ЛК туров
          <code class="rounded bg-gray-100 px-1 text-xs">/tour-cabinet</code>
          (таблица <code class="rounded bg-gray-100 px-1 text-xs">tour_cabinet_direction_cities</code>).
        </p>
      </div>

      <TourCabinetAdminDirectionCitiesPanel
        :directions="directions"
        :project-key="projectKey"
        :rows="rows"
        :city-options="cityOptions"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminDirectionCitiesPanel from '../TourCabinetAdminDirectionCitiesPanel.vue'

const page = usePage()

defineProps({
  directions: { type: Array, default: () => [] },
  projectKey: { type: String, required: true },
  rows: { type: Array, default: () => [] },
  cityOptions: { type: Array, default: () => [] },
})
</script>
