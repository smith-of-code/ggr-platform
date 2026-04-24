<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.promocodes.index')" class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к промокодам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ promocode ? 'Редактировать промокод' : 'Новый промокод' }}</h1>
    </div>

    <form @submit.prevent="submit">
      <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <RInput v-model="form.name" label="Название *" placeholder="Летняя акция 2026" :error="form.errors.name" required />

              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Код промокода *</label>
                <div class="flex gap-2">
                  <div class="flex-1">
                    <RInput v-model="form.code" placeholder="SUMMER2026" :error="form.errors.code" />
                  </div>
                  <button
                    type="button"
                    class="shrink-0 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                    @click="generateFromName"
                  >
                    Из названия
                  </button>
                  <button
                    type="button"
                    class="shrink-0 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
                    @click="generateRandom"
                  >
                    Случайный
                  </button>
                </div>
                <p class="mt-1 text-xs text-gray-400">Латинские буквы, цифры, дефис и подчёркивание. Будет сохранён в верхнем регистре.</p>
              </div>

              <div class="grid gap-4 sm:grid-cols-3">
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Скидка (%) *</label>
                  <input
                    v-model.number="form.discount_percent"
                    type="number"
                    min="1"
                    max="100"
                    placeholder="10"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
                    :class="form.errors.discount_percent ? 'border-red-500' : ''"
                  />
                  <p v-if="form.errors.discount_percent" class="mt-1 text-sm text-red-600">{{ form.errors.discount_percent }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Действует с *</label>
                  <input
                    v-model="form.valid_from"
                    type="date"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
                    :class="form.errors.valid_from ? 'border-red-500' : ''"
                  />
                  <p v-if="form.errors.valid_from" class="mt-1 text-sm text-red-600">{{ form.errors.valid_from }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Действует до *</label>
                  <input
                    v-model="form.valid_until"
                    type="date"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
                    :class="form.errors.valid_until ? 'border-red-500' : ''"
                  />
                  <p v-if="form.errors.valid_until" class="mt-1 text-sm text-red-600">{{ form.errors.valid_until }}</p>
                </div>
              </div>
            </div>
          </RCard>
        </div>

        <div class="space-y-6">
          <RCard elevation="raised">
            <div class="space-y-4 p-6">
              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Привязка к туру</label>
                <select
                  v-model="form.tour_id"
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
                >
                  <option :value="null">Без привязки (все туры)</option>
                  <option v-for="tour in tours" :key="tour.id" :value="tour.id">{{ tour.title }}</option>
                </select>
                <p class="mt-1 text-xs text-gray-400">Если выбран тур — промокод работает только для него</p>
              </div>
              <RCheckbox v-model="form.is_active" label="Активен" />
            </div>
          </RCard>

          <RCard v-if="form.discount_percent > 0" elevation="raised">
            <div class="p-6 text-center">
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Предпросмотр скидки</p>
              <p class="mt-2 text-4xl font-bold text-[#003274]">-{{ form.discount_percent }}%</p>
              <p v-if="form.code" class="mt-1 font-mono text-sm text-gray-500">{{ form.code.toUpperCase() }}</p>
            </div>
          </RCard>
        </div>
      </div>

      <div class="sticky bottom-0 z-10 -mx-4 mt-6 border-t border-gray-200 bg-white/95 px-4 py-4 backdrop-blur-sm sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex items-center gap-3">
          <RButton variant="primary" :loading="form.processing" :disabled="form.processing" class="px-12">
            Сохранить
          </RButton>
          <span v-if="form.isDirty" class="text-sm text-amber-600">Есть несохранённые изменения</span>
          <span v-if="form.recentlySuccessful" class="text-sm text-green-600">Сохранено</span>
        </div>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  promocode: Object,
  tours: Array,
})

const form = useForm({
  name: props.promocode?.name ?? '',
  code: props.promocode?.code ?? '',
  discount_percent: props.promocode?.discount_percent ?? 10,
  valid_from: props.promocode?.valid_from ? props.promocode.valid_from.slice(0, 10) : today(),
  valid_until: props.promocode?.valid_until ? props.promocode.valid_until.slice(0, 10) : endOfYear(),
  tour_id: resolveTourId(),
  is_active: props.promocode?.is_active ?? true,
})

function resolveTourId() {
  const p = props.promocode
  if (p?.promocodeable_type?.includes('Tour') && p?.promocodeable_id) {
    return p.promocodeable_id
  }
  return null
}

function today() {
  return new Date().toISOString().slice(0, 10)
}

function endOfYear() {
  return `${new Date().getFullYear()}-12-31`
}

async function generateFromName() {
  if (!form.name) return
  try {
    const { data } = await axios.post(route('admin.promocodes.generateCode'), { name: form.name })
    form.code = data.code
  } catch {
    // fallback
  }
}

function generateRandom() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  let code = ''
  for (let i = 0; i < 8; i++) {
    code += chars[Math.floor(Math.random() * chars.length)]
  }
  form.code = code
}

function submit() {
  props.promocode
    ? form.put(route('admin.promocodes.update', props.promocode.id))
    : form.post(route('admin.promocodes.store'))
}
</script>
