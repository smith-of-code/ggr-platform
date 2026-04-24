<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Промокоды</h1>
        <p class="mt-1 text-sm text-gray-500">Управление скидочными промокодами</p>
      </div>
      <Link :href="route('admin.promocodes.create')" class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Добавить промокод
      </Link>
    </div>

    <div class="mb-4">
      <RInput
        v-model="searchQuery"
        placeholder="Поиск по коду или названию…"
        @keyup.enter="applySearch"
      >
        <template #suffix>
          <button v-if="searchQuery" type="button" class="text-gray-400 hover:text-gray-600" @click="clearSearch">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
          </button>
        </template>
      </RInput>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Код</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Скидка</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Срок действия</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="promo in promocodes.data" :key="promo.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ promo.name }}</td>
            <td class="px-5 py-3.5 font-mono text-sm text-gray-600">{{ promo.code }}</td>
            <td class="px-5 py-3.5 text-center">
              <RBadge variant="primary">-{{ promo.discount_percent }}%</RBadge>
            </td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">
              {{ formatDate(promo.valid_from) }} — {{ formatDate(promo.valid_until) }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <button
                @click="toggleActive(promo)"
                :class="statusClass(promo)"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition"
              >
                <span :class="statusDotClass(promo)" class="h-1.5 w-1.5 rounded-full" />
                {{ statusLabel(promo) }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('admin.promocodes.edit', promo.id)" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                </Link>
                <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500" @click="confirmDestroy(promo)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="promocodes.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">Промокодов пока нет</div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  promocodes: Object,
  filters: Object,
})

const searchQuery = ref(props.filters?.search ?? '')

function applySearch() {
  router.get(route('admin.promocodes.index'), { search: searchQuery.value || undefined }, { preserveState: true })
}

function clearSearch() {
  searchQuery.value = ''
  applySearch()
}

function toggleActive(promo) {
  router.patch(route('admin.promocodes.toggleActive', promo.id), {}, { preserveState: true })
}

function confirmDestroy(promo) {
  if (confirm(`Удалить промокод "${promo.code}"?`)) {
    router.delete(route('admin.promocodes.destroy', promo.id))
  }
}

function isExpired(promo) {
  return new Date(promo.valid_until) < new Date()
}

function statusLabel(promo) {
  if (!promo.is_active) return 'Неактивен'
  if (isExpired(promo)) return 'Истёк'
  return 'Активен'
}

function statusClass(promo) {
  if (!promo.is_active) return 'bg-gray-100 text-gray-500 hover:bg-gray-200'
  if (isExpired(promo)) return 'bg-amber-50 text-amber-700 hover:bg-amber-100'
  return 'bg-green-50 text-green-700 hover:bg-green-100'
}

function statusDotClass(promo) {
  if (!promo.is_active) return 'bg-gray-400'
  if (isExpired(promo)) return 'bg-amber-500'
  return 'bg-green-500'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
