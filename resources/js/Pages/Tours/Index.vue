<template>
  <MainLayout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="reveal">
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Каталог туров</h1>
        <p class="mt-3 max-w-2xl text-lg text-gray-500">Каталог туров в атомные города России</p>
      </div>

      <!-- Modern Filters -->
      <div class="reveal relative z-10 mt-10">
        <div class="overflow-visible rounded-2xl border border-gray-100 bg-white p-6 shadow-md sm:p-8">
          <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#003274]/10">
              <svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Фильтры</h2>
              <p class="text-sm text-gray-400">Подберите идеальный тур</p>
            </div>
          </div>

          <form @submit.prevent="applyFilters">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
              <FilterDropdown v-model="filters.project" label="Проект" :options="projectOptions" />
              <FilterDropdown v-model="filters.season" label="Сезон" :options="seasonOptions" />
              <FilterDropdown v-model="filters.participation_type" label="Участие" :options="participationOptions" />
              <FilterDropdown v-model="filters.city" label="Город" :options="cityOptions" />
            </div>

            <!-- Checkboxes & Actions -->
            <div class="mt-6 flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-gray-100 pt-6">
              <RCheckbox v-model="filters.for_children" label="Отдых с детьми" />
              <RCheckbox v-model="filters.for_foreigners" label="Подходит иностранцам" />

              <div class="ml-auto flex gap-3">
                <button
                  type="button"
                  @click="resetFilters"
                  class="group flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-500 transition-all duration-200 hover:border-gray-300 hover:text-gray-700 hover:shadow-sm active:scale-[0.98]"
                >
                  <svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                  </svg>
                  Сбросить
                </button>
                <button
                  type="submit"
                  class="flex items-center gap-2 rounded-xl bg-[#003274] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition-all duration-200 hover:bg-[#025ea1] hover:shadow-xl hover:shadow-[#003274]/30 active:scale-[0.98]"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                  </svg>
                  Показать
                </button>
              </div>
            </div>

            <!-- Active filter chips -->
            <div v-if="hasActiveFilters" class="mt-5 flex flex-wrap gap-2">
              <span
                v-if="filters.project"
                class="inline-flex items-center gap-1.5 rounded-full bg-[#003274]/5 px-3 py-1.5 text-xs font-medium text-[#003274]"
              >
                {{ projectLabel(filters.project) }}
                <button @click="filters.project = ''; applyFilters()" class="ml-0.5 rounded-full p-0.5 transition hover:bg-[#003274]/10">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </span>
              <span
                v-if="filters.season"
                class="inline-flex items-center gap-1.5 rounded-full bg-[#003274]/5 px-3 py-1.5 text-xs font-medium text-[#003274]"
              >
                {{ seasonLabel(filters.season) }}
                <button @click="filters.season = ''; applyFilters()" class="ml-0.5 rounded-full p-0.5 transition hover:bg-[#003274]/10">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </span>
              <span
                v-if="filters.participation_type"
                class="inline-flex items-center gap-1.5 rounded-full bg-[#003274]/5 px-3 py-1.5 text-xs font-medium text-[#003274]"
              >
                {{ participationLabel(filters.participation_type) }}
                <button @click="filters.participation_type = ''; applyFilters()" class="ml-0.5 rounded-full p-0.5 transition hover:bg-[#003274]/10">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </span>
              <span
                v-if="filters.city"
                class="inline-flex items-center gap-1.5 rounded-full bg-[#003274]/5 px-3 py-1.5 text-xs font-medium text-[#003274]"
              >
                {{ cityLabel(filters.city) }}
                <button @click="filters.city = ''; applyFilters()" class="ml-0.5 rounded-full p-0.5 transition hover:bg-[#003274]/10">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </span>
            </div>
          </form>
        </div>
      </div>

      <!-- Results count -->
      <div class="mt-8 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          <span v-if="tours.data.length > 0">Найдено <span class="font-semibold text-gray-700">{{ tours.total }}</span> {{ tourWord(tours.total) }}</span>
          <span v-else>Туры не найдены</span>
        </p>
      </div>

      <!-- Tours grid -->
      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(tour, i) in tours.data"
          :key="tour.id"
          :href="route('tours.show', tour.slug)"
          class="reveal"
          :class="'reveal-delay-' + ((i % 3) + 1)"
        >
        <RCard elevation="raised" hoverable class="group h-full">
          <template #cover>
          <div class="aspect-video overflow-hidden rounded-t-2xl bg-gray-100">
            <img
              v-if="tour.image"
              :src="tour.image"
              :alt="tour.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
              <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
              </svg>
            </div>
          </div>
          </template>
          <div>
            <div class="flex items-center gap-2">
              <RBadge variant="primary" size="sm">{{ projectLabel(tour.project) }}</RBadge>
              <span class="flex items-center gap-1 text-xs text-gray-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ tour.duration }}
              </span>
            </div>
            <h2 class="mt-3 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h2>
            <div v-if="tour.cities?.length" class="mt-1.5 flex flex-wrap gap-1.5">
              <RBadge v-for="city in tour.cities" :key="city.id" variant="info" size="md">{{ city.name }}</RBadge>
            </div>
            <p v-if="tour.start_city" class="mt-1.5 text-sm text-gray-500">
              <span class="font-medium text-gray-600">Логистические точки:</span> {{ tour.start_city }}
            </p>
            <p v-if="nearestDeparture(tour)" class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
              <svg class="h-3.5 w-3.5 shrink-0 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
              <span>{{ formatDateShort(nearestDeparture(tour).start_date) }} — {{ formatDateShort(nearestDeparture(tour).end_date) }}</span>
            </p>
            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
              <p class="text-lg font-bold text-[#003274]">
                <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                <template v-else><span class="font-semibold text-green-600">Бесплатно</span></template>
              </p>
              <span class="flex items-center gap-1 text-sm font-medium text-[#003274] opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-2">
                Подробнее
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </span>
            </div>
          </div>
        </RCard>
        </Link>
      </div>

      <div v-if="tours.data.length === 0" class="py-20 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
          <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
        </div>
        <p class="mt-4 text-lg font-medium text-gray-600">Туры не найдены</p>
        <p class="mt-1 text-sm text-gray-400">Попробуйте изменить параметры фильтра</p>
        <button @click="resetFilters" class="mt-4 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#025ea1]">
          Сбросить фильтры
        </button>
      </div>

      <div v-if="tours.data.length > 0 && tours.last_page > 1" class="mt-12 flex justify-center gap-2">
        <template v-for="(link, i) in tours.links" :key="i">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm font-medium transition-all duration-200',
              link.active ? 'bg-[#003274] text-white shadow-lg shadow-[#003274]/20' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <span v-html="link.label" />
          </Link>
          <span
            v-else
            :class="[
              'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm',
              link.active ? 'bg-[#003274] text-white' : 'bg-gray-50 text-gray-300'
            ]"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive, computed, ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import FilterDropdown from '@/Components/FilterDropdown.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  tours: Object,
  cities: Array,
  filters: Object,
})

const projectOptions = [
  { value: '', label: 'Все проекты' },
  { value: 'start_atomgrad', label: 'Старт в Атомград' },
  { value: 'atoms_vkusa', label: 'Атомы вкуса' },
  { value: 'llr', label: 'Лучшие люди Росатома' },
]

const iconSnowflake = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-sky-500"><path d="M10 2a.75.75 0 0 1 .75.75v2.69l1.72-1.72a.75.75 0 1 1 1.06 1.06L11.81 6.5H14.5a.75.75 0 0 1 0 1.5h-3.75v1.75h3.75a.75.75 0 0 1 0 1.5H11.81l1.72 1.72a.75.75 0 1 1-1.06 1.06l-1.72-1.72v2.69a.75.75 0 0 1-1.5 0v-2.69l-1.72 1.72a.75.75 0 0 1-1.06-1.06L8.19 11.25H5.5a.75.75 0 0 1 0-1.5h3.75V8H5.5a.75.75 0 0 1 0-1.5h2.69L6.47 4.78a.75.75 0 0 1 1.06-1.06l1.72 1.72V2.75A.75.75 0 0 1 10 2Z"/></svg>'
const iconLeaf = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-emerald-500"><path fill-rule="evenodd" d="M13.5 3A3.5 3.5 0 0 0 10 6.5V8h6.5A3.5 3.5 0 0 0 20 4.5V3h-6.5ZM10 9.5v2A3.5 3.5 0 0 1 6.5 15H0v1.5A3.5 3.5 0 0 0 3.5 20H10v-2a3.5 3.5 0 0 1 3.5-3.5H20V13h-6.5A3.5 3.5 0 0 1 10 9.5Z" clip-rule="evenodd"/></svg>'
const iconSun = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-amber-500"><path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.06 1.06l1.06 1.06Z"/></svg>'
const iconMaple = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-orange-500"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>'
const iconCert = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-indigo-500"><path fill-rule="evenodd" d="M1 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H4a3 3 0 0 1-3-3V6Zm4 1.5a2 2 0 1 1 4 0 2 2 0 0 1-4 0Zm2 3a4 4 0 0 0-3.665 2.395.75.75 0 0 0 .416 1A8.98 8.98 0 0 0 7 14.5a8.98 8.98 0 0 0 3.249-.605.75.75 0 0 0 .416-1A4 4 0 0 0 7 10.5Zm5-3.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm0 2.5a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>'
const iconTrophy = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-yellow-500"><path fill-rule="evenodd" d="M10 1c-1.716 0-3.408.106-5.07.31C3.806 1.45 3 2.414 3 3.517V5h-.5A1.5 1.5 0 0 0 1 6.5v1A2.5 2.5 0 0 0 3.5 10H4a6 6 0 0 0 5.25 5.944V17.5H7A.75.75 0 0 0 7 19h6a.75.75 0 0 0 0-1.5h-2.25v-1.556A6 6 0 0 0 16 10h.5A2.5 2.5 0 0 0 19 7.5v-1A1.5 1.5 0 0 0 17.5 5H17V3.517c0-1.103-.806-2.068-1.93-2.207A41.403 41.403 0 0 0 10 1Z" clip-rule="evenodd"/></svg>'
const iconCard = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-teal-500"><path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/></svg>'

const iconGlobe = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-emerald-500"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-1.5 0a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0ZM10 3.5a.75.75 0 0 0-.75.75v.553a6.513 6.513 0 0 0-4.2 2.447H6.5a.75.75 0 0 0 0 1.5H4.25a6.5 6.5 0 0 0-.247 1.5H5.5a.75.75 0 0 0 0 1.5H4.003c.086.525.233 1.03.434 1.5H5.5a.75.75 0 0 0 0 1.5h-.563A6.513 6.513 0 0 0 10 17.5a6.513 6.513 0 0 0 5.063-2.75H14.5a.75.75 0 0 0 0-1.5h1.063c.201-.47.348-.975.434-1.5H14.5a.75.75 0 0 0 0-1.5h1.497a6.5 6.5 0 0 0-.247-1.5H13.5a.75.75 0 0 0 0-1.5h1.45a6.513 6.513 0 0 0-4.2-2.447V4.25A.75.75 0 0 0 10 3.5Z" clip-rule="evenodd"/></svg>'

const seasonOptions = [
  { value: '', label: 'Любой сезон' },
  { value: 'winter', label: 'Зима', icon: iconSnowflake },
  { value: 'spring', label: 'Весна', icon: iconLeaf },
  { value: 'summer', label: 'Лето', icon: iconSun },
  { value: 'autumn', label: 'Осень', icon: iconMaple },
  { value: 'all_season', label: 'Всесезонный', icon: iconGlobe },
]

const participationOptions = [
  { value: '', label: 'Любое' },
  { value: 'bchp', label: 'Сертификат БЧП', icon: iconCert },
  { value: 'contest', label: 'Конкурс', icon: iconTrophy },
  { value: 'paid', label: 'За свой счёт', icon: iconCard },
]

const cityOptions = computed(() => [
  { value: '', label: 'Все города' },
  ...(props.cities || []).map(c => ({ value: c.id, label: c.name })),
])

const filters = reactive({
  ...props.filters,
  for_children: props.filters.for_children === true || props.filters.for_children === '1',
  for_foreigners: props.filters.for_foreigners === true || props.filters.for_foreigners === '1',
})

const hasActiveFilters = computed(() => {
  return filters.project || filters.season || filters.participation_type || filters.city
})

function applyFilters() {
  router.get(route('tours.index'), filters, { preserveState: true })
}

function resetFilters() {
  Object.assign(filters, {
    project: '',
    season: '',
    participation_type: '',
    city: '',
    for_children: false,
    for_foreigners: false,
  })
  router.get(route('tours.index'))
}

function projectLabel(key) {
  const labels = { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }
  return labels[key] || key
}

function seasonLabel(key) {
  const labels = { winter: 'Зима', spring: 'Весна', summer: 'Лето', autumn: 'Осень', all_season: 'Всесезонный' }
  return labels[key] || key
}

function participationLabel(key) {
  const labels = { bchp: 'Сертификат БЧП', contest: 'Конкурс', paid: 'За свой счёт' }
  return labels[key] || key
}

function cityLabel(id) {
  const city = props.cities?.find(c => String(c.id) === String(id))
  return city ? city.name : id
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatDateShort(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

function nearestDeparture(tour) {
  if (!tour.departures?.length) return null
  const now = new Date()
  const future = tour.departures
    .filter(d => new Date(d.end_date) >= now)
    .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
  return future[0] || tour.departures[tour.departures.length - 1]
}

function tourWord(count) {
  const n = Math.abs(count) % 100
  const n1 = n % 10
  if (n > 10 && n < 20) return 'туров'
  if (n1 > 1 && n1 < 5) return 'тура'
  if (n1 === 1) return 'тур'
  return 'туров'
}
</script>
