<template>
  <MainLayout>
    <Head title="Книга атомных рецептов" />

    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-amber-50/90 via-orange-50/40 to-gray-50" />
      <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-amber-200/30 blur-3xl" />
      <div class="pointer-events-none absolute -bottom-32 -left-16 h-64 w-64 rounded-full bg-orange-200/25 blur-3xl" />

      <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-amber-800 shadow-sm ring-1 ring-amber-200/60">
              <span aria-hidden="true">🍳</span>
              Атомы вкуса
            </div>
            <h1 class="mt-4 text-3xl font-bold text-gray-900 sm:text-4xl">
              Книга <span class="text-[#003274]">атомных</span> рецептов
            </h1>
            <p class="mt-3 max-w-2xl text-lg text-gray-600">
              Блюда из городов атомной отрасли — готовьте дома и открывайте новые вкусы регионов.
            </p>
            <div class="mt-6 flex gap-2">
              <Link :href="route('research.index')" class="rounded-full border border-gray-200 bg-white px-5 py-2 text-sm font-semibold text-gray-600 transition hover:border-[#003274]/30 hover:bg-blue-50 hover:text-[#003274]">
                Исследования
              </Link>
              <span class="rounded-full bg-amber-600 px-5 py-2 text-sm font-semibold text-white">Атомы вкуса</span>
            </div>
          </div>
        </div>

        <div class="reveal mt-10">
          <div class="rounded-2xl border border-amber-100/80 bg-white/90 p-6 shadow-md backdrop-blur-sm sm:p-8">
            <form @submit.prevent="applyFilters" class="flex flex-col gap-4 sm:flex-row sm:items-end">
              <div class="min-w-[220px] flex-1">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-amber-900/50">Город</label>
                <div class="relative">
                  <select
                    v-model="filters.city"
                    class="peer w-full cursor-pointer appearance-none rounded-xl border border-amber-100 bg-amber-50/50 py-3 pl-4 pr-10 text-sm font-medium text-gray-800 transition-all duration-200 hover:border-[#003274]/30 hover:bg-white focus:border-[#003274] focus:bg-white focus:shadow-[0_0_0_3px_rgba(0,50,116,0.1)] focus:outline-none"
                  >
                    <option value="">Все города</option>
                    <option v-for="c in cities" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                  </select>
                  <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 peer-hover:text-[#003274] peer-focus:text-[#003274]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                  </div>
                </div>
              </div>
              <div class="flex gap-3">
                <button
                  type="button"
                  @click="resetFilters"
                  class="rounded-xl border border-amber-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-amber-50"
                >
                  Сбросить
                </button>
                <button
                  type="submit"
                  class="rounded-xl bg-[#003274] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]"
                >
                  Показать
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="relative mt-8">
          <p class="text-sm text-gray-600">
            <span v-if="recipes.data.length > 0">
              <span class="font-semibold text-gray-800">{{ recipes.total }}</span>
              {{ recipeWord(recipes.total) }}
            </span>
            <span v-else>Рецепты не найдены</span>
          </p>
        </div>

        <div class="relative mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <Link
            v-for="(item, i) in recipes.data"
            :key="item.id"
            :href="route('recipes.show', item.slug)"
            class="reveal"
            :class="'reveal-delay-' + ((i % 3) + 1)"
          >
            <RCard elevation="raised" hoverable class="group h-full overflow-hidden border border-amber-100/60 bg-white/95">
              <template #cover>
                <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-amber-100 to-orange-50">
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.title"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                  />
                  <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 text-amber-800/40">
                    <span class="text-5xl" aria-hidden="true">🥘</span>
                  </div>
                </div>
              </template>
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <RBadge v-if="item.city" variant="primary" size="sm">{{ item.city.name }}</RBadge>
                  <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', difficultyClass(item.difficulty)]">
                    {{ difficultyLabel(item.difficulty) }}
                  </span>
                </div>
                <h2 class="mt-2 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ item.title }}</h2>
                <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500">
                  <span v-if="item.cooking_time" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-amber-600/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ item.cooking_time }}
                  </span>
                  <span v-if="item.servings != null" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-amber-600/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    {{ servingsLabel(item.servings) }}
                  </span>
                </div>
              </div>
            </RCard>
          </Link>
        </div>

        <div v-if="recipes.data.length === 0" class="relative py-16 text-center">
          <p class="text-gray-600">По выбранному городу рецептов пока нет.</p>
          <button type="button" class="mt-4 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#025ea1]" @click="resetFilters">
            Показать все
          </button>
        </div>

        <div v-if="recipes.data.length > 0 && recipes.last_page > 1" class="relative mt-12 flex flex-wrap justify-center gap-2">
          <template v-for="(link, i) in recipes.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm font-medium transition-all duration-200',
                link.active ? 'bg-[#003274] text-white shadow-lg shadow-[#003274]/20' : 'bg-white text-gray-600 ring-1 ring-amber-100 hover:bg-amber-50/80',
              ]"
            >
              <span v-html="link.label" />
            </Link>
            <span
              v-else
              :class="[
                'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm',
                link.active ? 'bg-[#003274] text-white' : 'bg-gray-50 text-gray-300',
              ]"
              v-html="link.label"
            />
          </template>
        </div>
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
  recipes: Object,
  cities: Array,
  filters: Object,
})

const filters = reactive({
  city: props.filters?.city != null && props.filters?.city !== '' ? String(props.filters.city) : '',
})

function applyFilters() {
  const q = {}
  if (filters.city) q.city = filters.city
  router.get(route('recipes.index'), q, { preserveState: true })
}

function resetFilters() {
  filters.city = ''
  router.get(route('recipes.index'))
}

function difficultyLabel(key) {
  const map = { easy: 'Легко', medium: 'Средне', hard: 'Сложно' }
  return map[key] || key
}

function difficultyClass(key) {
  const map = {
    easy: 'bg-green-100 text-green-800 ring-1 ring-green-200/80',
    medium: 'bg-amber-100 text-amber-900 ring-1 ring-amber-200/80',
    hard: 'bg-red-100 text-red-800 ring-1 ring-red-200/80',
  }
  return map[key] || 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function servingsLabel(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return `${n} порций`
  if (y > 1 && y < 5) return `${n} порции`
  if (y === 1) return `${n} порция`
  return `${n} порций`
}

function recipeWord(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return 'рецептов'
  if (y > 1 && y < 5) return 'рецепта'
  if (y === 1) return 'рецепт'
  return 'рецептов'
}
</script>
