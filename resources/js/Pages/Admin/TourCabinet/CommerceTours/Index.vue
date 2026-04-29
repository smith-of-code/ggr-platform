<template>
  <AdminLayout>
    <Head title="Коммерческие туры — ЛК туров" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-commerce-tours`"
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
        <h1 class="text-2xl font-bold text-gray-900">Коммерческие туры</h1>
        <p class="mt-1 max-w-3xl text-sm text-gray-500">
          Настройка блока «Коммерческие туры» в ЛК туров: текст экрана этапа 3 и привязки «город → анкета этапа 2».
        </p>
      </div>

      <TourCabinetAdminCommerceToursPanel
        :enabled="enabled"
        :stage3="stage3"
        :city-forms="cityForms"
        :available-cities="availableCities"
        :available-forms="availableForms"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminCommerceToursPanel from '../TourCabinetAdminCommerceToursPanel.vue'

const page = usePage()

defineProps({
  enabled: { type: Boolean, default: false },
  stage3: {
    type: Object,
    default: () => ({ subject: '', body: '' }),
  },
  cityForms: { type: Array, default: () => [] },
  availableCities: { type: Array, default: () => [] },
  availableForms: { type: Array, default: () => [] },
})
</script>
