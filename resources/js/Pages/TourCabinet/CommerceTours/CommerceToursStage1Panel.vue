<template>
  <div class="space-y-5">
    <div>
      <h4 class="text-sm font-semibold text-slate-900">Шаг 1.1 — выбор города</h4>
      <p class="mt-1 text-xs text-slate-600">
        Выберите город — список из активных коммерческих туров. Можно выбрать только один.
      </p>
      <form class="mt-3 space-y-3" @submit.prevent="submitCity">
        <select
          v-model="cityForm.city_id"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-rosatom-500 focus:ring-rosatom-500"
          :class="cityForm.errors.city_id ? 'border-red-400' : ''"
          :disabled="locked"
        >
          <option :value="null" disabled>Выберите город</option>
          <option v-for="opt in availableCities" :key="opt.id" :value="opt.id">
            {{ opt.name }}
          </option>
        </select>
        <p v-if="cityForm.errors.city_id" class="text-xs text-red-600">{{ cityForm.errors.city_id }}</p>
        <div>
          <RButton
            type="submit"
            variant="primary"
            size="sm"
            :loading="cityForm.processing"
            :disabled="cityForm.processing || locked || !cityForm.city_id || cityForm.city_id === currentCityId"
          >
            Сохранить город
          </RButton>
        </div>
      </form>
    </div>

    <div v-if="currentCityId">
      <h4 class="text-sm font-semibold text-slate-900">Шаг 1.2 — выбор тура</h4>
      <p class="mt-1 text-xs text-slate-600">
        Активные туры выбранного города. Можно выбрать только один.
      </p>

      <p v-if="!availableTours.length" class="mt-3 rounded-lg bg-slate-100 px-4 py-3 text-sm text-slate-600">
        Для этого города пока нет доступных туров. Выберите другой город.
      </p>
      <form v-else class="mt-3 space-y-3" @submit.prevent="submitTour">
        <select
          v-model="tourForm.tour_id"
          class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-rosatom-500 focus:ring-rosatom-500"
          :class="tourForm.errors.tour_id ? 'border-red-400' : ''"
          :disabled="locked"
        >
          <option :value="null" disabled>Выберите тур</option>
          <option v-for="opt in availableTours" :key="opt.id" :value="opt.id">
            {{ opt.title }}
          </option>
        </select>
        <p v-if="tourForm.errors.tour_id" class="text-xs text-red-600">{{ tourForm.errors.tour_id }}</p>
        <div>
          <RButton
            type="submit"
            variant="primary"
            size="sm"
            :loading="tourForm.processing"
            :disabled="tourForm.processing || locked || !tourForm.tour_id || tourForm.tour_id === currentTourId"
          >
            Сохранить тур
          </RButton>
        </div>
      </form>
    </div>

    <div v-if="currentCityId && currentTourId" class="border-t border-slate-200 pt-4">
      <form @submit.prevent="completeStage1">
        <RButton
          type="submit"
          variant="primary"
          :loading="completeForm.processing"
          :disabled="completeForm.processing || locked"
        >
          Перейти к этапу 2 →
        </RButton>
      </form>
      <p v-if="completeForm.errors.stage" class="mt-2 text-xs text-red-600">{{ completeForm.errors.stage }}</p>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  availableCities: { type: Array, default: () => [] },
  availableTours: { type: Array, default: () => [] },
  currentCityId: { type: Number, default: null },
  currentTourId: { type: Number, default: null },
  locked: { type: Boolean, default: false },
})

const cityForm = useForm({ city_id: props.currentCityId ?? null })
const tourForm = useForm({ tour_id: props.currentTourId ?? null })
const completeForm = useForm({})

watch(
  () => props.currentCityId,
  (v) => {
    cityForm.city_id = v ?? null
  },
)

watch(
  () => props.currentTourId,
  (v) => {
    tourForm.tour_id = v ?? null
  },
)

function submitCity() {
  cityForm.post(route('tour-cabinet.commerce-tours.city.store'), {
    preserveScroll: true,
  })
}

function submitTour() {
  tourForm.post(route('tour-cabinet.commerce-tours.tour.store'), {
    preserveScroll: true,
  })
}

function completeStage1() {
  completeForm.post(route('tour-cabinet.commerce-tours.complete-stage-1'), {
    preserveScroll: true,
  })
}
</script>
