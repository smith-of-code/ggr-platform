<template>
  <AdminLayout>
    <Head title="Формы ЛК туров" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-forms`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div v-if="page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ page.props.flash.error }}
      </div>

      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Формы личного кабинета туров</h1>
          <p class="mt-1 text-sm text-gray-500">
            Те же анкеты, что в LMS Admin для события
            <span class="font-mono text-gray-700">{{ configSlug }}</span>
            (настройка: <code class="rounded bg-gray-100 px-1 text-xs">TOUR_CABINET_LMS_EVENT_SLUG</code>).
          </p>
        </div>
      </div>

      <TourCabinetAdminFormsPanel
        :lms-event="lmsEvent"
        :forms="forms"
        :config-slug="configSlug"
        :contest-form-slug-overrides="contestFormSlugOverrides"
        :contest-form-slugs-effective="contestFormSlugsEffective"
        :form-options="formOptions"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminFormsPanel from '../TourCabinetAdminFormsPanel.vue'

const page = usePage()

defineProps({
  lmsEvent: { type: Object, default: null },
  forms: { type: Array, default: () => [] },
  configSlug: { type: String, default: '' },
  contestFormSlugOverrides: {
    type: Object,
    default: () => ({ standard: '', more_data: '' }),
  },
  contestFormSlugsEffective: {
    type: Object,
    default: () => ({ standard: null, more_data: null }),
  },
  formOptions: { type: Array, default: () => [] },
})
</script>
