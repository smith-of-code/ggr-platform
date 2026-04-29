<template>
  <AdminLayout>
    <Head title="Разделение — ЛК туров" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-atomic-ticket`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div v-if="page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ page.props.flash.error }}
      </div>
      <div v-if="page.props.flash?.success" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        {{ page.props.flash.success }}
      </div>

      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Блок «Твой билет в атомный город»</h1>
        <p class="mt-1 max-w-3xl text-sm text-gray-500">
          Текстовое содержимое блока на дашборде ЛК туров (две карточки: путь конкурса и путь «за свой счёт»).
        </p>
      </div>

      <TourCabinetAdminAtomicTicketPanel :block="block" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminAtomicTicketPanel from '../TourCabinetAdminAtomicTicketPanel.vue'

const page = usePage()

defineProps({
  block: {
    type: Object,
    required: true,
  },
})
</script>
