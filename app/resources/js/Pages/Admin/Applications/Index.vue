<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Заявки</h1>
        <p class="mt-1 text-sm text-gray-500">Управление заявками на туры</p>
      </div>
      <a :href="route('admin.applications.export')" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
        Экспорт CSV
      </a>
    </div>

    <!-- Status tabs -->
    <div class="flex gap-2 border-b border-gray-200 pb-px">
      <button
        v-for="tab in statusTabs"
        :key="tab.key"
        @click="filterByStatus(tab.key)"
        :class="[
          currentStatus === tab.key
            ? 'border-[#003274] text-[#003274] font-semibold'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
          'flex items-center gap-2 border-b-2 px-4 py-3 text-sm transition'
        ]"
      >
        {{ tab.label }}
        <span :class="[currentStatus === tab.key ? 'bg-[#003274] text-white' : 'bg-gray-100 text-gray-600']" class="rounded-full px-2 py-0.5 text-xs font-medium">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Table -->
    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Имя</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Телефон</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тур</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Дата</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="app in applications.data" :key="app.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ app.name }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.email }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.phone ?? '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.tour?.title ?? '—' }}</td>
            <td class="px-5 py-3.5">
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
          </tr>
        </tbody>
      </table>
      <div v-if="applications.data.length === 0" class="px-5 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
        <p class="mt-3 text-sm text-gray-400">Заявок не найдено</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ applications: Object, filters: Object, statusCounts: Object })

const currentStatus = computed(() => props.filters?.status || 'all')

const statusTabs = computed(() => [
  { key: 'all', label: 'Все', count: props.statusCounts?.all || 0 },
  { key: 'new', label: 'Новые', count: props.statusCounts?.new || 0 },
  { key: 'in_progress', label: 'В работе', count: props.statusCounts?.in_progress || 0 },
  { key: 'approved', label: 'Одобрены', count: props.statusCounts?.approved || 0 },
  { key: 'rejected', label: 'Отклонены', count: props.statusCounts?.rejected || 0 },
])

function filterByStatus(status) {
  router.get(route('admin.applications.index'), status === 'all' ? {} : { status }, { preserveState: true })
}

function updateStatus(id, status) {
  router.patch(route('admin.applications.updateStatus', id), { status }, { preserveState: true })
}

function selectStatusClass(s) {
  return {
    new: 'bg-blue-50 text-blue-700',
    in_progress: 'bg-amber-50 text-amber-700',
    approved: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
  }[s] || 'bg-gray-50 text-gray-600'
}

function formatDate(d) { return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' }) }
</script>
