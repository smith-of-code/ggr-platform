<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Каталог туров</h1>
        <p class="mt-1 text-sm text-gray-500">Управление каталогом туров</p>
      </div>
      <Link :href="route('admin.tours.create')" class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Добавить тур
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тур</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Логистические точки</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Проект</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Цена</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="tour in tours.data" :key="tour.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div v-if="tour.image" class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                  <img :src="tour.image" :alt="tour.title" class="h-full w-full object-cover" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ tour.title }}</p>
                  <p class="text-xs text-gray-400">{{ tour.duration }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ tour.start_city }}</td>
            <td class="px-5 py-3.5">
              <RBadge v-if="tour.project" variant="primary" size="sm">{{ projectLabel(tour.project) }}</RBadge>
              <span v-else class="text-sm text-gray-400">—</span>
            </td>
            <td class="px-5 py-3.5 text-sm font-medium text-gray-700">
              {{ tour.price_from ? formatPrice(tour.price_from) + ' ₽' : 'Бесплатно' }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <button
                @click="toggleActive(tour)"
                :class="tour.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition"
              >
                <span :class="tour.is_active ? 'bg-green-500' : 'bg-gray-400'" class="h-1.5 w-1.5 rounded-full" />
                {{ tour.is_active ? 'Активен' : 'Скрыт' }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('admin.tours.edit', tour.id)" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                </Link>
                <RButton variant="danger" size="sm" icon-only @click="confirmDestroy(tour)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="tours.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">Туров пока нет</div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ tours: Object })

function projectLabel(k) {
  return { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }[k] || k || '—'
}

function formatPrice(v) { return new Intl.NumberFormat('ru-RU').format(v) }

function toggleActive(tour) {
  router.patch(route('admin.tours.toggleActive', tour.id), {}, { preserveState: true })
}

function confirmDestroy(tour) {
  if (confirm(`Удалить тур "${tour.title}"?`)) {
    router.delete(route('admin.tours.destroy', tour.id))
  }
}
</script>
