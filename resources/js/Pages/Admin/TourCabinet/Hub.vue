<template>
  <AdminLayout>
    <Head title="ЛК туров" />
    <div class="mx-auto max-w-6xl">
      <header class="border-b border-slate-200/80 pb-8">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Портал</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Личный кабинет туров</h1>
      </header>

      <div v-if="$page.props.flash?.success" class="mt-8 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mt-8 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
        {{ $page.props.flash.error }}
      </div>

      <div class="mt-10 space-y-12">
        <section id="tour-cabinet-admin-cities" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Города по направлениям</h2>
          <p class="mt-2 text-sm text-slate-600">
            Список для шага выбора городов в ЛК
            <code class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-800">/tour-cabinet</code>
          </p>
          <div class="mt-6">
            <TourCabinetAdminDirectionCitiesPanel v-bind="directionCitiesSection" />
          </div>
        </section>

        <section id="tour-cabinet-admin-forms" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Формы и этап 1</h2>
          <p class="mt-2 text-sm text-slate-600">
            Анкеты LMS для конкурса
            <code class="rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-800">{{ formsSection.configSlug }}</code>
          </p>
          <div class="mt-6">
            <TourCabinetAdminFormsPanel v-bind="formsSection" />
          </div>
        </section>

        <section id="tour-cabinet-admin-stage2" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Вопросы этапа 2</h2>
          <p class="mt-2 text-sm text-slate-600">Тексты вопросов конкурса и привязка к направлению.</p>
          <div class="mt-6">
            <TourCabinetAdminStage2QuestionsPanel v-bind="stage2Section" />
          </div>
        </section>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminDirectionCitiesPanel from './TourCabinetAdminDirectionCitiesPanel.vue'
import TourCabinetAdminFormsPanel from './TourCabinetAdminFormsPanel.vue'
import TourCabinetAdminStage2QuestionsPanel from './TourCabinetAdminStage2QuestionsPanel.vue'

defineProps({
  formsSection: { type: Object, required: true },
  directionCitiesSection: { type: Object, required: true },
  stage2Section: { type: Object, required: true },
})
</script>
