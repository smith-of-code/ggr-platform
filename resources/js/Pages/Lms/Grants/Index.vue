<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`Возможности – ${event?.title}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Возможности</h1>

      <div v-if="!isProfileComplete" class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div>
          <p class="font-semibold text-amber-800">Заполните профиль</p>
          <p class="mt-1 text-sm text-amber-700">Для выбора возможности необходимо заполнить личный кабинет.</p>
          <Link :href="route('lms.profile.edit', { event: event?.slug })" class="mt-2 inline-block text-sm font-medium text-rosatom-600 hover:underline">
            Перейти в личный кабинет
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-end gap-4">
        <div class="w-48">
          <FilterDropdown
            v-model="filterType"
            label="Тип"
            :options="typeFilterOptions"
            placeholder="Все типы"
          />
        </div>
        <div class="w-64">
          <SearchSelect
            v-model="filterCity"
            :options="cityOptions"
            value-key="value"
            label-key="label"
            label="Город"
            placeholder="Все города"
            search-placeholder="Поиск города..."
          />
        </div>
        <button
          v-if="filterType || filterCity"
          type="button"
          class="mt-auto h-[42px] text-sm font-medium text-gray-500 transition hover:text-rosatom-600"
          @click="resetFilters"
        >
          Сбросить
        </button>
      </div>

      <!-- Enrolled section -->
      <div v-if="enrolledItems.length" class="space-y-4">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Мои возможности</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <GrantCard
            v-for="item in enrolledItems"
            :key="item.grant.id"
            :item="item"
            :event="event"
            badge-variant="enrolled"
          />
        </div>
      </div>

      <!-- Active items -->
      <div v-if="activeItems.length" class="space-y-4">
        <h2 v-if="enrolledItems.length" class="text-sm font-semibold uppercase tracking-wider text-gray-400">Доступные</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <GrantCard
            v-for="item in activeItems"
            :key="item.grant.id"
            :item="item"
            :event="event"
          />
        </div>
      </div>

      <!-- Expired items -->
      <div v-if="expiredItems.length" class="space-y-4">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Срок подачи истёк</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <GrantCard
            v-for="item in expiredItems"
            :key="item.grant.id"
            :item="item"
            :event="event"
            badge-variant="expired"
          />
        </div>
      </div>

      <div v-if="!grants?.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h4.5a2.5 2.5 0 0 1 0 5H9V8Zm0 5v3m0 0v2m0-2H7m2 0h4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <p class="mt-3 text-sm text-gray-400">Возможности пока не добавлены</p>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import FilterDropdown from '@/Components/FilterDropdown.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import GrantCard from './GrantCard.vue'

const props = defineProps({
  event: Object,
  grants: Array,
  isProfileComplete: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
})

const typeFilterOptions = [
  { value: '', label: 'Все типы' },
  { value: 'grant', label: 'Грант' },
  { value: 'subsidy', label: 'Субсидия' },
  { value: 'credit', label: 'Кредит' },
]

const CITY_NAMES = [
  'Ангарск', 'Байкальск', 'Балаково', 'Билибино', 'Волгодонск',
  'Глазов', 'Десногорск', 'Димитровград', 'Железногорск',
  'Заречный (Пензенская область)', 'Заречный (Свердловская область)',
  'Зеленогорск', 'Краснокаменск', 'Курчатов', 'Лесной', 'Неман',
  'Нововоронеж', 'Новоуральск', 'Обнинск', 'Озёрск', 'Певек',
  'Полярные Зори', 'Саров', 'Северск', 'Снежинск', 'Советск',
  'Сосновый Бор', 'Трёхгорный', 'Удомля', 'Усолье-Сибирское', 'Электросталь',
]
const cityOptions = CITY_NAMES.map(name => ({ value: name, label: name }))

const filterType = ref(props.filters?.type || '')
const filterCity = ref(props.filters?.city || '')

function applyFilters() {
  const params = {}
  if (filterType.value) params.type = filterType.value
  if (filterCity.value) params.city = filterCity.value
  router.get(route('lms.grants.index', { event: props.event?.slug }), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

watch(filterType, applyFilters)
watch(filterCity, applyFilters)

function resetFilters() {
  filterType.value = ''
  filterCity.value = ''
}

function isExpired(grant) {
  if (!grant.application_end) return false
  return new Date(grant.application_end) < new Date()
}

const enrolledItems = computed(() =>
  (props.grants || []).filter(item => item.enrolled && !isExpired(item.grant))
)

const activeItems = computed(() =>
  (props.grants || []).filter(item => !item.enrolled && !isExpired(item.grant))
)

const expiredItems = computed(() =>
  (props.grants || []).filter(item => isExpired(item.grant) && !item.enrolled)
)
</script>
