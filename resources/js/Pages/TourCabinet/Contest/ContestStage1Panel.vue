<template>
  <div>
    <p v-if="$page.props.errors?.stage" class="mt-4 text-sm text-red-600">{{ $page.props.errors.stage }}</p>

    <!-- Шаг: направление -->
    <div v-if="step === 'direction'" class="mt-8">
      <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Выберите направление</h3>
      <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <button
          v-for="d in directions"
          :key="d.key"
          type="button"
          class="cursor-pointer rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-rosatom-300 hover:shadow-md"
          @click="submitDirection(d.key)"
        >
          <p class="font-semibold text-gray-900">{{ d.label }}</p>
        </button>
      </div>
    </div>

    <!-- Шаг: города -->
    <div v-else-if="step === 'cities'" class="mt-8">
      <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Выберите от 1 до 3 городов</h3>
      <p v-if="!cities.length" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Для этого направления пока не настроен список городов. Заполните состав в админке:
        <code class="rounded bg-white/80 px-1">/admin/tour-cabinet/direction-cities</code>.
      </p>
      <form v-else class="mt-4 space-y-4" @submit.prevent="submitCities">
        <div class="space-y-2 rounded-xl border border-gray-200 bg-white p-4">
          <label
            v-for="c in cities"
            :key="c.id"
            class="flex cursor-pointer items-start gap-3 rounded-lg px-2 py-2 hover:bg-gray-50"
          >
            <input
              v-model="cityForm.city_ids"
              type="checkbox"
              class="mt-1 h-4 w-4 rounded border-gray-300 text-rosatom-600 focus:ring-rosatom-500"
              :value="c.id"
              :disabled="isCityCheckboxDisabled(c.id)"
              @change="onCityChange"
            />
            <span class="flex-1 text-sm text-gray-900">
              {{ c.name }}
              <span
                v-if="c.needs_more_data"
                class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900"
              >
                Нужно больше данных
              </span>
            </span>
          </label>
        </div>
        <RButton type="submit" variant="primary" :loading="cityForm.processing" :disabled="cityForm.processing || cityForm.city_ids.length < 1">
          Далее
        </RButton>
      </form>
    </div>

    <!-- Шаг: формы по городам -->
    <div v-else class="mt-8">
      <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Анкеты по выбранным городам</h3>
      <p
        v-if="!formSlugsConfigured.standard || !formSlugsConfigured.more_data"
        class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        В админке портала:
        <code class="rounded bg-white/80 px-1">/admin/tour-cabinet/forms</code>
        (блок «Конкурс, этап 1») или в
        <code class="rounded bg-white/80 px-1">config/tour_cabinet.php</code>
        /
        <code class="rounded bg-white/80 px-1">TOUR_CABINET_CONTEST_STAGE1_FORM_SLUG_*</code>
        задайте оба slug:
        <code class="rounded bg-white/80 px-1">contest_stage1_form_slug_standard</code>
        и
        <code class="rounded bg-white/80 px-1">contest_stage1_form_slug_more_data</code>.
      </p>
      <div class="mt-4 space-y-3">
        <div
          v-for="c in selectedCitiesForForms"
          :key="c.id"
          class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div class="min-w-0">
            <p class="font-semibold text-gray-900">{{ c.name }}</p>
            <p v-if="c.needs_more_data" class="mt-1 text-xs font-medium text-amber-800">Нужно больше данных — отдельная анкета</p>
            <p v-else class="mt-1 text-xs text-gray-500">Стандартная анкета</p>
          </div>
          <div class="flex shrink-0 flex-wrap gap-2">
            <span
              v-if="c.submitted"
              class="inline-flex items-center rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800"
            >
              Отправлено
            </span>
            <a
              v-else-if="c.form_slug"
              :href="route('tour-cabinet.contest.city-form', { city: c.id })"
              class="inline-flex items-center justify-center rounded-lg bg-rosatom-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            >
              Заполнить анкету
            </a>
          </div>
        </div>
      </div>

      <div
        v-if="stage1Complete && formSlugsConfigured.standard && formSlugsConfigured.more_data"
        class="mt-8 rounded-xl border border-emerald-200 bg-emerald-50 p-4"
      >
        <p class="text-sm font-medium text-emerald-900">Этап 1 выполнен: все анкеты по выбранным городам отправлены.</p>
        <form class="mt-3" @submit.prevent="advanceToStage2">
          <RButton type="submit" variant="primary" size="sm">Перейти к этапу 2</RButton>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  step: { type: String, required: true },
  progress: { type: Object, required: true },
  directions: { type: Array, default: () => [] },
  cities: { type: Array, default: () => [] },
  selectedCitiesForForms: { type: Array, default: () => [] },
  formSlugsConfigured: { type: Object, default: () => ({ standard: false, more_data: false }) },
  stage1Complete: { type: Boolean, default: false },
})

const cityForm = useForm({ city_ids: [] })

watch(
  () => [props.step, props.progress.selected_city_ids],
  () => {
    if (props.step === 'cities') {
      cityForm.city_ids = [...(props.progress.selected_city_ids || [])]
    }
  },
  { immediate: true },
)

function submitDirection(key) {
  router.post(route('tour-cabinet.contest.direction'), { project_key: key }, { preserveScroll: true })
}

function submitCities() {
  cityForm.post(route('tour-cabinet.contest.cities'), { preserveScroll: true })
}

function onCityChange() {
  if (cityForm.city_ids.length > 3) {
    cityForm.city_ids = cityForm.city_ids.slice(0, 3)
  }
}

function isCityCheckboxDisabled(id) {
  if (cityForm.city_ids.includes(id)) return false
  return cityForm.city_ids.length >= 3
}

function advanceToStage2() {
  router.post(route('tour-cabinet.contest.complete-stage-1'), {}, { preserveScroll: true })
}
</script>
