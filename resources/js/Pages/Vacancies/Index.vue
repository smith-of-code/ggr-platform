<template>
  <MainLayout>
    <div class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-16 text-white sm:px-6 sm:py-20 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h1 class="text-3xl font-bold sm:text-4xl">Вакансии в атомных городах</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-white/80">
          Найдите работу мечты в современных городах Росатома
        </p>
      </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <!-- Filters -->
      <div class="mb-8 flex flex-wrap items-end gap-4">
        <div class="min-w-0 flex-1">
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Поиск</label>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input
              v-model="search"
              type="text"
              placeholder="Должность, компания..."
              class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
              @input="debouncedFilter"
            />
          </div>
        </div>
        <div>
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Город</label>
          <select v-model="cityFilter" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" @change="applyFilters">
            <option value="">Все города</option>
            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Тип занятости</label>
          <select v-model="typeFilter" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" @change="applyFilters">
            <option value="">Любой</option>
            <option value="full_time">Полная занятость</option>
            <option value="part_time">Частичная</option>
            <option value="remote">Удалённо</option>
            <option value="internship">Стажировка</option>
            <option value="contract">Подряд</option>
          </select>
        </div>
      </div>

      <!-- Results -->
      <div v-if="vacancies.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="v in vacancies.data"
          :key="v.id"
          :href="route('vacancies.show', v.slug)"
          class="group"
        >
          <RCard elevation="raised" hoverable class="h-full">
            <template v-if="v.image" #cover>
              <div class="aspect-video overflow-hidden">
                <img :src="v.image" :alt="v.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
              </div>
            </template>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <RBadge v-if="v.city" variant="info" size="sm">{{ v.city.name }}</RBadge>
                <RBadge v-if="v.employment_type" variant="neutral" size="sm">{{ typeLabel(v.employment_type) }}</RBadge>
              </div>
              <h3 class="mt-3 text-lg font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ v.title }}</h3>
              <p v-if="v.company" class="mt-1 text-sm text-gray-500">{{ v.company }}</p>
              <p v-if="v.salary" class="mt-3 text-lg font-bold text-[#003274]">{{ v.salary }}</p>
              <p v-if="v.description" class="mt-2 line-clamp-3 text-sm text-gray-500" v-html="stripHtml(v.description)" />
            </div>
          </RCard>
        </Link>
      </div>

      <div v-else class="py-20 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" /></svg>
        <p class="mt-4 text-gray-500">Вакансий пока нет</p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({ vacancies: Object, cities: Array, filters: Object })

const search = ref(props.filters?.search || '')
const cityFilter = ref(props.filters?.city || '')
const typeFilter = ref(props.filters?.type || '')
let timeout = null

const types = { full_time: 'Полная', part_time: 'Частичная', remote: 'Удалённо', internship: 'Стажировка', contract: 'Подряд' }
function typeLabel(t) { return types[t] || t }
function stripHtml(html) {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return (doc.body.textContent || '').replace(/\s+/g, ' ').trim()
}

function applyFilters() {
  const params = {}
  if (search.value) params.search = search.value
  if (cityFilter.value) params.city = cityFilter.value
  if (typeFilter.value) params.type = typeFilter.value
  router.get(route('vacancies.index'), params, { preserveState: true })
}

function debouncedFilter() {
  clearTimeout(timeout)
  timeout = setTimeout(applyFilters, 400)
}
</script>
