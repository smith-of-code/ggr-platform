<template>
  <MainLayout>
    <Head title="Исследования" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="reveal">
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Исследования</h1>
        <p class="mt-3 max-w-2xl text-lg text-gray-500">
          Аналитика и материалы о туристическом потенциале атомных городов: тренды, аудитория и развитие дестинаций.
        </p>
      </div>

      <div class="reveal mt-10">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md sm:p-8">
          <form @submit.prevent="applyFilters" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="min-w-[220px] flex-1">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">Город</label>
              <div class="relative">
                <select
                  v-model="filters.city"
                  class="peer w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 py-3 pl-4 pr-10 text-sm font-medium text-gray-700 transition-all duration-200 hover:border-[#003274]/40 hover:bg-white focus:border-[#003274] focus:bg-white focus:shadow-[0_0_0_3px_rgba(0,50,116,0.1)] focus:outline-none"
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
                class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
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

      <div class="mt-8">
        <p class="text-sm text-gray-500">
          <span v-if="researches.data.length > 0">
            Найдено <span class="font-semibold text-gray-700">{{ researches.total }}</span>
            {{ researchWord(researches.total) }}
          </span>
          <span v-else>Материалы не найдены</span>
        </p>
      </div>

      <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(item, i) in researches.data"
          :key="item.id"
          :href="route('research.show', item.slug)"
          class="reveal"
          :class="'reveal-delay-' + ((i % 3) + 1)"
        >
          <RCard elevation="raised" hoverable class="group h-full">
            <template #cover>
              <div class="aspect-video overflow-hidden bg-gray-100">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
                  <svg class="h-12 w-12 text-[#003274]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                  </svg>
                </div>
              </div>
            </template>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <RBadge v-if="item.city" variant="primary" size="sm">{{ item.city.name }}</RBadge>
                <span v-if="item.year" class="text-xs font-medium text-gray-500">{{ item.year }}</span>
              </div>
              <h2 class="mt-2 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ item.title }}</h2>
              <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ excerpt(item.description) }}</p>
            </div>
          </RCard>
        </Link>
      </div>

      <div v-if="researches.data.length === 0" class="py-16 text-center">
        <p class="text-gray-600">По выбранным условиям ничего не найдено.</p>
        <button type="button" class="mt-4 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#025ea1]" @click="resetFilters">
          Сбросить фильтр
        </button>
      </div>

      <div v-if="researches.data.length > 0 && researches.last_page > 1" class="mt-12 flex flex-wrap justify-center gap-2">
        <template v-for="(link, i) in researches.links" :key="i">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm font-medium transition-all duration-200',
              link.active ? 'bg-[#003274] text-white shadow-lg shadow-[#003274]/20' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50',
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
  </MainLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  researches: Object,
  cities: Array,
  filters: Object,
})

const filters = reactive({
  city: props.filters?.city != null && props.filters?.city !== '' ? String(props.filters.city) : '',
})

function applyFilters() {
  const q = {}
  if (filters.city) q.city = filters.city
  router.get(route('research.index'), q, { preserveState: true })
}

function resetFilters() {
  filters.city = ''
  router.get(route('research.index'))
}

function excerpt(text, max = 160) {
  if (!text) return ''
  const plain = String(text)
    .replace(/<[^>]*>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return plain.length > max ? `${plain.slice(0, max)}…` : plain
}

function researchWord(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return 'исследований'
  if (y > 1 && y < 5) return 'исследования'
  if (y === 1) return 'исследование'
  return 'исследований'
}
</script>
