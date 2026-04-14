<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Заявки</h1>
        <p class="mt-1 text-sm text-gray-500">Управление входящими заявками</p>
      </div>
      <a :href="route('admin.applications.export')" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
        Экспорт CSV
      </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap items-end gap-4">
      <!-- Search -->
      <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-xs font-medium text-gray-500">Поиск</label>
        <div class="relative">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Имя, email или телефон..."
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
            @input="debouncedSearch"
          />
          <button v-if="searchQuery" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:text-gray-600" @click="clearSearch">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>

      <!-- Type filter -->
      <div>
        <label class="mb-1.5 block text-xs font-medium text-gray-500">Тип заявки</label>
        <select
          v-model="typeFilter"
          class="cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          @change="applyFilters"
        >
          <option value="">Все типы</option>
          <option value="tour">Заявка на тур</option>
          <option value="research">Исследование</option>
          <option value="program_info">Информация о программе</option>
        </select>
      </div>

      <!-- Reset -->
      <button
        v-if="hasActiveFilters"
        type="button"
        class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:bg-gray-50 hover:text-gray-700"
        @click="resetFilters"
      >
        Сбросить
      </button>
    </div>

    <!-- Status tabs -->
    <RTabs :model-value="currentStatus" :tabs="statusTabs" @update:model-value="filterByStatus">
    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Имя</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тип</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тур</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Дата</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="app in applications.data"
            :key="app.id"
            class="cursor-pointer transition hover:bg-blue-50/30"
            @click="openApplication(app)"
          >
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ app.name }}</p>
              <p v-if="app.phone" class="mt-0.5 text-xs text-gray-400">{{ app.phone }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.email }}</td>
            <td class="px-5 py-3.5">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="typeClass(app.type)">
                {{ typeLabel(app.type) }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.tour?.title ?? '—' }}</td>
            <td class="px-5 py-3.5" @click.stop>
              <select
                :value="app.status"
                @change="updateStatus(app.id, $event.target.value)"
                :class="selectStatusClass(app.status)"
                class="cursor-pointer rounded-full border-0 py-1 pl-3 pr-8 text-xs font-semibold transition focus:ring-2 focus:ring-[#003274]/20"
              >
                <option value="new">Новая</option>
                <option value="in_progress">В работе</option>
                <option value="approved">Одобрена</option>
                <option value="rejected">Отклонена</option>
              </select>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-400">{{ formatDate(app.created_at) }}</td>
            <td class="px-5 py-3.5 text-right" @click.stop>
              <button
                type="button"
                class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]"
                title="Подробнее"
                @click="openApplication(app)"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="applications.data.length === 0" class="px-5 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
        <p class="mt-3 text-sm text-gray-400">Заявок не найдено</p>
        <button v-if="hasActiveFilters" type="button" class="mt-2 text-sm font-medium text-[#003274] hover:underline" @click="resetFilters">
          Сбросить фильтры
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="applications.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">{{ applications.from }}–{{ applications.to }} из {{ applications.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in applications.links"
            :key="link.label"
            type="button"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-[#003274] text-white' : 'text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30'"
            @click="link.url && router.visit(link.url, { preserveState: true })"
            v-html="link.label"
          />
        </div>
      </div>
    </RCard>
    </RTabs>

    <!-- Detail Modal -->
    <teleport to="body">
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="selectedApp" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm" @click.self="selectedApp = null">
          <div class="my-8 w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Заявка #{{ selectedApp.id }}</h3>
                <p class="mt-0.5 text-sm text-gray-400">{{ formatDate(selectedApp.created_at) }}</p>
              </div>
              <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" @click="selectedApp = null">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <!-- Body -->
            <div class="space-y-5 px-6 py-5">
              <!-- Status + Type -->
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="selectStatusClass(selectedApp.status)">
                  {{ statusLabel(selectedApp.status) }}
                </span>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="typeClass(selectedApp.type)">
                  {{ typeLabel(selectedApp.type) }}
                </span>
              </div>

              <!-- Contact info -->
              <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4">
                  <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-400">ФИО</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedApp.name }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-4">
                  <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-400">Email</p>
                  <a :href="`mailto:${selectedApp.email}`" class="text-sm font-semibold text-[#003274] hover:underline">{{ selectedApp.email }}</a>
                </div>
                <div v-if="selectedApp.phone" class="rounded-xl bg-gray-50 p-4">
                  <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-400">Телефон</p>
                  <a :href="`tel:${selectedApp.phone}`" class="text-sm font-semibold text-[#003274] hover:underline">{{ selectedApp.phone }}</a>
                </div>
                <div v-if="selectedApp.tour" class="rounded-xl bg-gray-50 p-4">
                  <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-400">Тур</p>
                  <p class="text-sm font-semibold text-gray-900">{{ selectedApp.tour.title }}</p>
                  <p v-if="selectedApp.tour_departure" class="mt-0.5 text-xs text-gray-500">
                    Заезд: {{ formatDate(selectedApp.tour_departure.start_date) }}
                  </p>
                </div>
              </div>

              <!-- Extra data -->
              <div v-if="selectedApp.data && Object.keys(selectedApp.data).length" class="rounded-xl border border-gray-200 p-4">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Дополнительные данные</p>
                <dl class="space-y-2">
                  <div v-for="(value, key) in selectedApp.data" :key="key" class="flex items-start gap-3">
                    <dt class="min-w-[120px] shrink-0 text-sm font-medium text-gray-500">{{ dataKeyLabel(key) }}</dt>
                    <dd class="text-sm text-gray-900">{{ value }}</dd>
                  </div>
                </dl>
              </div>

              <!-- Status change -->
              <div class="rounded-xl bg-blue-50 p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-blue-600">Изменить статус</p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="s in statuses"
                    :key="s.value"
                    type="button"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                    :class="selectedApp.status === s.value ? s.activeClass : 'bg-white text-gray-600 shadow-sm hover:shadow'"
                    @click="changeStatus(s.value)"
                  >
                    {{ s.label }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end border-t border-gray-100 px-6 py-4">
              <button
                type="button"
                class="rounded-xl bg-[#003274] px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#025ea1]"
                @click="selectedApp = null"
              >
                Закрыть
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ applications: Object, filters: Object, statusCounts: Object })

const searchQuery = ref(props.filters?.search || '')
const typeFilter = ref(props.filters?.type || '')
const selectedApp = ref(null)
let searchTimeout = null

const currentStatus = computed(() => props.filters?.status || 'all')
const hasActiveFilters = computed(() => searchQuery.value || typeFilter.value || (props.filters?.status && props.filters.status !== 'all'))

const statusTabs = computed(() => [
  { value: 'all', label: 'Все', count: props.statusCounts?.all || 0 },
  { value: 'new', label: 'Новые', count: props.statusCounts?.new || 0 },
  { value: 'in_progress', label: 'В работе', count: props.statusCounts?.in_progress || 0 },
  { value: 'approved', label: 'Одобрены', count: props.statusCounts?.approved || 0 },
  { value: 'rejected', label: 'Отклонены', count: props.statusCounts?.rejected || 0 },
])

const statuses = [
  { value: 'new', label: 'Новая', activeClass: 'bg-blue-100 text-blue-700' },
  { value: 'in_progress', label: 'В работе', activeClass: 'bg-amber-100 text-amber-700' },
  { value: 'approved', label: 'Одобрена', activeClass: 'bg-green-100 text-green-700' },
  { value: 'rejected', label: 'Отклонена', activeClass: 'bg-red-100 text-red-700' },
]

function buildParams() {
  const params = {}
  if (currentStatus.value !== 'all') params.status = currentStatus.value
  if (typeFilter.value) params.type = typeFilter.value
  if (searchQuery.value) params.search = searchQuery.value
  return params
}

function applyFilters() {
  router.get(route('admin.applications.index'), buildParams(), { preserveState: true })
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 400)
}

function clearSearch() {
  searchQuery.value = ''
  applyFilters()
}

function filterByStatus(status) {
  const params = {}
  if (status !== 'all') params.status = status
  if (typeFilter.value) params.type = typeFilter.value
  if (searchQuery.value) params.search = searchQuery.value
  router.get(route('admin.applications.index'), params, { preserveState: true })
}

function resetFilters() {
  searchQuery.value = ''
  typeFilter.value = ''
  router.get(route('admin.applications.index'), {}, { preserveState: true })
}

function updateStatus(id, status) {
  router.patch(route('admin.applications.updateStatus', id), { status }, { preserveState: true })
}

function openApplication(app) {
  selectedApp.value = { ...app }
}

function changeStatus(status) {
  if (!selectedApp.value) return
  router.patch(
    route('admin.applications.updateStatus', selectedApp.value.id),
    { status },
    {
      preserveState: true,
      onSuccess: () => { selectedApp.value.status = status },
    },
  )
}

function selectStatusClass(s) {
  return {
    new: 'bg-blue-50 text-blue-700',
    in_progress: 'bg-amber-50 text-amber-700',
    approved: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
  }[s] || 'bg-gray-50 text-gray-600'
}

function statusLabel(s) {
  return { new: 'Новая', in_progress: 'В работе', approved: 'Одобрена', rejected: 'Отклонена' }[s] || s
}

function typeLabel(t) {
  return { tour: 'Тур', research: 'Исследование', program_info: 'Узнать подробнее' }[t] || t
}

function typeClass(t) {
  return {
    tour: 'bg-indigo-50 text-indigo-700',
    research: 'bg-cyan-50 text-cyan-700',
    program_info: 'bg-violet-50 text-violet-700',
  }[t] || 'bg-gray-100 text-gray-600'
}

function dataKeyLabel(key) {
  return {
    participants: 'Участники',
    comment: 'Комментарий',
    organization: 'Организация',
    topic: 'Тема',
    question: 'Вопрос',
    message: 'Сообщение',
  }[key] || key
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
