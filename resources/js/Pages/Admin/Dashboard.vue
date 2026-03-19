<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Дашборд</h1>
      <p class="mt-1 text-sm text-gray-500">Обзор платформы Росатом Travel</p>
    </div>

    <!-- Stats -->
    <div class="grid gap-5 sm:grid-cols-3">
      <Link :href="route('admin.cities.index')" class="group">
        <RCard elevation="raised" hoverable>
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50">
              <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
            </div>
            <svg class="h-5 w-5 text-gray-300 transition group-hover:text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900">{{ stats.cities }}</p>
          <p class="mt-1 text-sm text-gray-500">Городов</p>
        </RCard>
      </Link>

      <Link :href="route('admin.tours.index')" class="group">
        <RCard elevation="raised" hoverable>
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50">
              <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
            </div>
            <svg class="h-5 w-5 text-gray-300 transition group-hover:text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900">{{ stats.tours }}</p>
          <p class="mt-1 text-sm text-gray-500">Туров</p>
        </RCard>
      </Link>

      <Link :href="route('admin.applications.index')" class="group">
        <RCard elevation="raised" hoverable>
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50">
              <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
            </div>
            <svg class="h-5 w-5 text-gray-300 transition group-hover:text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </div>
          <p class="mt-4 text-3xl font-bold text-gray-900">{{ stats.applications }}</p>
          <p class="mt-1 text-sm text-gray-500">Заявок</p>
        </RCard>
      </Link>
    </div>

    <!-- Recent applications -->
    <div class="mt-10">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">Последние заявки</h2>
        <Link :href="route('admin.applications.index')" class="flex items-center gap-1 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]">
          Все заявки
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
        </Link>
      </div>
      <RCard elevation="raised" flush class="mt-4">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Имя</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тур</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Дата</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="app in recentApplications" :key="app.id" class="transition hover:bg-gray-50/50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ app.name }}</td>
              <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.email }}</td>
              <td class="px-5 py-3.5 text-sm text-gray-500">{{ app.tour?.title ?? '—' }}</td>
              <td class="px-5 py-3.5">
                <RBadge :variant="statusBadgeVariant(app.status)" size="sm">
                  {{ statusLabel(app.status) }}
                </RBadge>
              </td>
              <td class="px-5 py-3.5 text-sm text-gray-400">{{ formatDate(app.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="recentApplications.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">
          Заявок пока нет
        </div>
      </RCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ stats: Object, recentApplications: Array })

function formatDate(d) { return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' }) }

function statusLabel(s) {
  return { new: 'Новая', in_progress: 'В работе', approved: 'Одобрена', rejected: 'Отклонена' }[s] || s
}

function statusBadgeVariant(s) {
  return { new: 'info', in_progress: 'warning', approved: 'success', rejected: 'error' }[s] || 'neutral'
}
</script>
