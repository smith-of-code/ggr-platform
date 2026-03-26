<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Вакансии</h1>
        <p class="mt-1 text-sm text-gray-500">Управление вакансиями в атомных городах</p>
      </div>
      <Link :href="route('admin.vacancies.create')" class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Добавить вакансию
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Город</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Компания</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тип</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="v in vacancies.data" :key="v.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ v.title }}</p>
              <p v-if="v.salary" class="mt-0.5 text-xs text-gray-400">{{ v.salary }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ v.city?.name ?? '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ v.company ?? '—' }}</td>
            <td class="px-5 py-3.5">
              <span v-if="v.employment_type" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                {{ typeLabel(v.employment_type) }}
              </span>
              <span v-else class="text-sm text-gray-400">—</span>
            </td>
            <td class="px-5 py-3.5">
              <button
                type="button"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold transition"
                :class="v.is_published ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                @click="togglePublish(v)"
              >
                {{ v.is_published ? 'Опубликована' : 'Скрыта' }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('admin.vacancies.edit', v.id)" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                </Link>
                <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600" @click="destroy(v)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="vacancies.data.length === 0" class="px-5 py-16 text-center">
        <p class="text-sm text-gray-400">Вакансий пока нет</p>
      </div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ vacancies: Object })

const types = { full_time: 'Полная', part_time: 'Частичная', remote: 'Удалённо', internship: 'Стажировка', contract: 'Подряд' }
function typeLabel(t) { return types[t] || t }

function togglePublish(v) {
  router.patch(route('admin.vacancies.togglePublish', v.id), {}, { preserveScroll: true })
}

function destroy(v) {
  if (confirm(`Удалить вакансию «${v.title}»?`)) {
    router.delete(route('admin.vacancies.destroy', v.id))
  }
}
</script>
