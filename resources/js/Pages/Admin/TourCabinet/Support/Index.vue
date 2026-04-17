<template>
  <AdminLayout>
    <Head title="Обращения ЛК туров" />
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Обращения ЛК туров</h1>
        <p class="mt-1 text-sm text-gray-500">Очередь сообщений участников конкурса и туров</p>
      </div>
      <Link :href="route('admin.tour-cabinet.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">← Хаб ЛК туров</Link>
    </div>

    <div class="mb-6 flex flex-wrap items-end gap-4">
      <div>
        <label class="mb-1.5 block text-xs font-medium text-gray-500">Статус</label>
        <select
          v-model="localStatus"
          class="cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          @change="applyFilters"
        >
          <option value="">Все</option>
          <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-medium text-gray-500">Категория</label>
        <select
          v-model="localCategory"
          class="cursor-pointer rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          @change="applyFilters"
        >
          <option value="">Все</option>
          <option v-for="c in categoryOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
        </select>
      </div>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">ID</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Тема</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Обновлён</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="t in tickets.data"
            :key="t.id"
            class="cursor-pointer transition hover:bg-blue-50/30"
            @click="openTicket(t.id)"
          >
            <td class="px-5 py-3.5 font-mono text-sm text-gray-600">#{{ t.id }}</td>
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ t.subject }}</p>
              <p class="mt-0.5 text-xs text-gray-400">{{ t.category_label }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              <p>{{ t.user.email }}</p>
              <p v-if="t.user.name" class="text-xs text-gray-400">{{ t.user.name }}</p>
            </td>
            <td class="px-5 py-3.5">
              <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800">{{ t.status_label }}</span>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ formatDate(t.last_message_at || t.created_at) }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="tickets.prev_page_url || tickets.next_page_url" class="flex justify-end gap-3 border-t border-gray-100 px-4 py-3 text-sm">
        <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="font-medium text-[#003274] hover:underline" preserve-scroll>Назад</Link>
        <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="font-medium text-[#003274] hover:underline" preserve-scroll>Вперёд</Link>
      </div>
      <p v-if="!tickets.data.length" class="px-6 py-10 text-center text-sm text-gray-500">Нет обращений по выбранным фильтрам.</p>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  tickets: { type: Object, required: true },
  filters: { type: Object, required: true },
  statusOptions: { type: Array, required: true },
  categoryOptions: { type: Array, required: true },
})

const localStatus = ref(props.filters.status || '')
const localCategory = ref(props.filters.category || '')

watch(
  () => props.filters,
  (f) => {
    localStatus.value = f.status || ''
    localCategory.value = f.category || ''
  },
  { deep: true },
)

function applyFilters() {
  router.get(
    route('admin.tour-cabinet.support.index'),
    {
      status: localStatus.value || undefined,
      category: localCategory.value || undefined,
    },
    { preserveState: true, replace: true },
  )
}

function openTicket(id) {
  router.visit(route('admin.tour-cabinet.support.show', id))
}

function formatDate(iso) {
  if (!iso) return '—'
  try {
    return new Date(iso).toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return iso
  }
}
</script>
