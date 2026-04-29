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
        <section id="tour-cabinet-admin-tour-users" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Клиенты</h2>
          <p class="mt-2 max-w-3xl text-sm text-slate-600">
            Участники с доступом к ЛК туров, пользователи с заявкой на тур (совпадение email) и статусы документов; подтверждение или отклонение с комментарием (как в LMS).
          </p>
          <div class="mt-5">
            <Link
              :href="route('admin.tour-cabinet.tour-users.index')"
              class="inline-flex items-center gap-2 rounded-xl bg-[#003274] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1]"
            >
              Открыть «Клиенты»
            </Link>
          </div>
        </section>

        <section id="tour-cabinet-admin-cities" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Города по направлениям</h2>
          <p class="mt-2 text-sm text-slate-600">Список для шага выбора городов в ЛК.</p>
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

        <section id="tour-cabinet-admin-deadlines" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Сроки этапов конкурса</h2>
          <p class="mt-2 max-w-3xl text-sm text-slate-600">
            Общие даты начала и окончания для каждого из трёх этапов — одинаковые для всех участников. Отображаются в ЛК туров рядом со статусом этапа.
          </p>
          <div class="mt-6">
            <TourCabinetAdminContestDeadlinesPanel v-bind="contestDeadlinesSection" />
          </div>
        </section>

        <section id="tour-cabinet-admin-stage2" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Вопросы этапа 2</h2>
          <p class="mt-2 text-sm text-slate-600">
            Тексты вопросов конкурса и привязка к направлению.
            <Link :href="route('admin.tour-cabinet.stage2-answers.index')" class="font-medium text-[#003274] hover:underline">
              Ответы участников
            </Link>
          </p>
          <div class="mt-6">
            <TourCabinetAdminStage2QuestionsPanel v-bind="stage2Section" />
          </div>
        </section>

        <section id="tour-cabinet-admin-stage3" class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Этап 3 — проверочное задание</h2>
          <p class="mt-2 text-sm text-slate-600">
            Название и описание задания, формат ответа участника (текст + видео или текст + файл) задаются отдельно для каждого направления конкурса.
            <Link :href="route('admin.tour-cabinet.stage3-answers.index')" class="font-medium text-[#003274] hover:underline">
              Ответы участников
            </Link>
          </p>
          <div class="mt-6">
            <TourCabinetAdminStage3ConfigsPanel :configs="stage3ConfigSection.configs" />
          </div>
        </section>

        <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Коммерческие туры</h2>
          <p class="mt-2 max-w-3xl text-sm text-slate-600">
            Отдельный блок в ЛК туров под блоком «Конкурс»: выбор города и тура (этап 1), анкета доп. данных по городу (этап 2), статичный экран ожидания обратной связи (этап 3).
          </p>
          <div class="mt-6">
            <TourCabinetAdminCommerceToursPanel v-bind="commerceToursSection" />
          </div>
        </section>

        <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8">
          <h2 class="text-xl font-bold tracking-tight text-slate-900">Разделение — «Твой билет в атомный город»</h2>
          <p class="mt-2 max-w-3xl text-sm text-slate-600">
            Текстовый блок-разделитель между «Стандартной анкетой» и «Конкурсом» в ЛК туров. Кнопки прокручивают участника к блокам конкурса и коммерческих туров на этой же странице.
          </p>
          <div class="mt-6">
            <TourCabinetAdminAtomicTicketPanel v-bind="atomicTicketSection" />
          </div>
        </section>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TourCabinetAdminAtomicTicketPanel from './TourCabinetAdminAtomicTicketPanel.vue'
import TourCabinetAdminCommerceToursPanel from './TourCabinetAdminCommerceToursPanel.vue'
import TourCabinetAdminContestDeadlinesPanel from './TourCabinetAdminContestDeadlinesPanel.vue'
import TourCabinetAdminDirectionCitiesPanel from './TourCabinetAdminDirectionCitiesPanel.vue'
import TourCabinetAdminFormsPanel from './TourCabinetAdminFormsPanel.vue'
import TourCabinetAdminStage2QuestionsPanel from './TourCabinetAdminStage2QuestionsPanel.vue'
import TourCabinetAdminStage3ConfigsPanel from './TourCabinetAdminStage3ConfigsPanel.vue'

defineProps({
  formsSection: { type: Object, required: true },
  directionCitiesSection: { type: Object, required: true },
  contestDeadlinesSection: { type: Object, required: true },
  stage2Section: { type: Object, required: true },
  stage3ConfigSection: { type: Object, required: true },
  commerceToursSection: { type: Object, required: true },
  atomicTicketSection: { type: Object, required: true },
})
</script>
