<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Хронология событий</h1>
        <p class="mt-1 text-sm text-gray-500">Timeline-лента на главной странице</p>
      </div>
      <Link :href="route('admin.timeline.create')" class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Добавить событие
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Дата</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тип</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="ev in eventList" :key="ev.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatDate(ev.event_date) }}</td>
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ ev.title }}</p>
              <p v-if="ev.description" class="mt-0.5 line-clamp-1 text-xs text-gray-400">{{ ev.description }}</p>
            </td>
            <td class="px-5 py-3.5">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="typeClass(ev.type)">
                {{ typeLabel(ev.type) }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <button
                type="button"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold transition"
                :class="ev.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                @click="toggleActive(ev)"
              >
                {{ ev.is_active ? 'Активно' : 'Скрыто' }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('admin.timeline.edit', ev.id)" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                </Link>
                <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600" @click="destroy(ev)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="eventList.length === 0" class="px-5 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
        <p class="mt-3 text-sm text-gray-400">Событий пока нет</p>
      </div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ events: Object })

const eventList = computed(() => {
  if (!props.events) return []
  return Array.isArray(props.events) ? props.events : (props.events.data || [])
})

function typeLabel(t) {
  return { news: 'Новость', event: 'Событие', milestone: 'Веха' }[t] || t
}

function typeClass(t) {
  return {
    news: 'bg-blue-100 text-blue-800',
    event: 'bg-emerald-100 text-emerald-800',
    milestone: 'bg-amber-100 text-amber-900',
  }[t] || 'bg-gray-100 text-gray-600'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function toggleActive(ev) {
  router.patch(route('admin.timeline.toggleActive', ev.id), {}, { preserveScroll: true })
}

function destroy(ev) {
  if (confirm(`Удалить событие «${ev.title}»?`)) {
    router.delete(route('admin.timeline.destroy', ev.id))
  }
}
</script>
