<template>
  <MainLayout>
    <Head title="Атомные города" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="reveal">
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Атомные города</h1>
        <p class="mt-3 max-w-2xl text-lg text-gray-500">Каталог городов программы «Гостеприимные города Росатома»</p>
      </div>

      <div class="reveal mt-10">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md sm:p-8">
          <form @submit.prevent="applyFilters" class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="min-w-0 flex-1">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">Поиск</label>
              <div class="relative">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                  v-model="localFilters.search"
                  type="text"
                  placeholder="Название или регион…"
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm font-medium text-gray-700 transition-all duration-200 hover:border-[#003274]/40 hover:bg-white focus:border-[#003274] focus:bg-white focus:shadow-[0_0_0_3px_rgba(0,50,116,0.1)] focus:outline-none"
                  @input="debouncedApply"
                />
              </div>
            </div>

            <div class="min-w-[180px]">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">Регион</label>
              <div class="relative">
                <select
                  v-model="localFilters.region"
                  class="peer w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 pl-4 pr-10 text-sm font-medium text-gray-700 transition-all duration-200 hover:border-[#003274]/40 hover:bg-white focus:border-[#003274] focus:bg-white focus:shadow-[0_0_0_3px_rgba(0,50,116,0.1)] focus:outline-none"
                  @change="applyFilters"
                >
                  <option value="">Все регионы</option>
                  <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 peer-hover:text-[#003274] peer-focus:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                  </svg>
                </div>
              </div>
            </div>

            <div class="min-w-[180px]">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">Сортировка</label>
              <div class="relative">
                <select
                  v-model="localFilters.sort"
                  class="peer w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 pl-4 pr-10 text-sm font-medium text-gray-700 transition-all duration-200 hover:border-[#003274]/40 hover:bg-white focus:border-[#003274] focus:bg-white focus:shadow-[0_0_0_3px_rgba(0,50,116,0.1)] focus:outline-none"
                  @change="applyFilters"
                >
                  <option value="">По умолчанию</option>
                  <option value="name">По названию (А–Я)</option>
                  <option value="population_desc">По населению (убыв.)</option>
                  <option value="population_asc">По населению (возр.)</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 peer-hover:text-[#003274] peer-focus:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                  </svg>
                </div>
              </div>
            </div>

            <div class="flex gap-3 lg:pb-0">
              <button
                type="button"
                @click="resetFilters"
                class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
              >
                Сбросить
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="mt-8">
        <p class="text-sm text-gray-500">
          <span v-if="cities.length > 0">
            <span class="font-semibold text-gray-700">{{ cities.length }}</span>
            {{ cityWord(cities.length) }}
          </span>
          <span v-else>Города не найдены</span>
        </p>
      </div>

      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(city, i) in cities"
          :key="city.id"
          :href="route('cities.show', city.slug)"
          class="reveal group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#003274]/20 hover:shadow-lg"
          :class="'reveal-delay-' + ((i % 3) + 1)"
        >
          <div class="aspect-video overflow-hidden bg-gray-100">
            <img
              v-if="city.image"
              :src="city.image"
              :alt="city.name"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
              <svg class="h-14 w-14 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 7.5h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
              </svg>
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-start justify-between gap-2">
              <h2 class="text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ city.name }}</h2>
              <span v-if="city.population" class="shrink-0 rounded-full bg-[#003274]/10 px-2.5 py-0.5 text-xs font-semibold text-[#003274]">
                {{ formatPopulation(city.population) }}
              </span>
            </div>
            <p v-if="city.region" class="mt-1 text-xs font-medium text-gray-400">{{ city.region }}</p>
            <p v-if="city.description" class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">
              {{ stripHtml(city.description) }}
            </p>
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-[#003274] transition group-hover:gap-2">
              Подробнее
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </span>
          </div>
        </Link>
      </div>

      <div v-if="cities.length === 0" class="py-16 text-center">
        <p class="text-gray-600">По выбранным фильтрам городов не найдено.</p>
        <button type="button" class="mt-4 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#025ea1]" @click="resetFilters">
          Сбросить фильтры
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  cities: Array,
  regions: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const localFilters = reactive({
  search: props.filters?.search || '',
  region: props.filters?.region || '',
  sort: props.filters?.sort || '',
})

let debounceTimer = null

function debouncedApply() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 400)
}

function applyFilters() {
  const q = {}
  if (localFilters.search) q.search = localFilters.search
  if (localFilters.region) q.region = localFilters.region
  if (localFilters.sort) q.sort = localFilters.sort
  router.get(route('cities.index'), q, { preserveState: true })
}

function resetFilters() {
  localFilters.search = ''
  localFilters.region = ''
  localFilters.sort = ''
  router.get(route('cities.index'))
}

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim()
}

function formatPopulation(v) {
  const n = Number(v)
  if (Number.isFinite(n)) {
    if (n >= 1000) return Math.round(n / 1000) + ' тыс.'
    return new Intl.NumberFormat('ru-RU').format(n)
  }
  return String(v)
}

function cityWord(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return 'городов'
  if (y > 1 && y < 5) return 'города'
  if (y === 1) return 'город'
  return 'городов'
}
</script>
