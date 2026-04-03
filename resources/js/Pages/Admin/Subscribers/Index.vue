<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Подписчики рассылки</h1>
        <p class="mt-1 text-sm text-gray-500">Управление подписчиками блога</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="mb-6 grid grid-cols-3 gap-4">
      <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Всего</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-green-500">Активных</p>
        <p class="mt-1 text-2xl font-bold text-green-700">{{ stats.active }}</p>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white px-5 py-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Приостановлено</p>
        <p class="mt-1 text-2xl font-bold text-amber-700">{{ stats.paused }}</p>
      </div>
    </div>

    <!-- Add subscriber + Search -->
    <div class="mb-4 flex items-end gap-4">
      <form @submit.prevent="addSubscriber" class="flex items-end gap-2">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Добавить подписчика</label>
          <input
            v-model="addForm.email"
            type="email"
            placeholder="email@example.com"
            class="h-10 w-72 rounded-lg border border-gray-300 px-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-1 focus:ring-[#003274]"
            :class="{ 'border-red-400': addForm.errors.email }"
          />
          <p v-if="addForm.errors.email" class="mt-1 text-xs text-red-500">{{ addForm.errors.email }}</p>
        </div>
        <button
          type="submit"
          :disabled="addForm.processing"
          class="flex h-10 items-center gap-2 rounded-lg bg-[#003274] px-4 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1] disabled:opacity-50"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Добавить
        </button>
      </form>

      <div class="ml-auto">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Поиск по email..."
          class="h-10 w-64 rounded-lg border border-gray-300 px-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-1 focus:ring-[#003274]"
          @input="debouncedSearch"
        />
      </div>
    </div>

    <!-- Table -->
    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Дата подписки</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="sub in subscribers.data" :key="sub.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ sub.email }}</td>
            <td class="px-5 py-3.5 text-center">
              <button
                @click="toggleActive(sub)"
                :class="sub.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition"
              >
                <span :class="sub.is_active ? 'bg-green-500' : 'bg-amber-500'" class="h-1.5 w-1.5 rounded-full" />
                {{ sub.is_active ? 'Активен' : 'Приостановлен' }}
              </button>
            </td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ formatDate(sub.created_at) }}</td>
            <td class="px-5 py-3.5 text-right">
              <RButton variant="danger" size="sm" icon-only @click="confirmDestroy(sub)">
                <template #icon>
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </template>
              </RButton>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="subscribers.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">
        {{ filters.search ? 'Ничего не найдено' : 'Подписчиков пока нет' }}
      </div>

      <!-- Pagination -->
      <div v-if="subscribers.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">{{ subscribers.from }}–{{ subscribers.to }} из {{ subscribers.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in subscribers.links"
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
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  subscribers: Object,
  stats: Object,
  filters: Object,
})

const addForm = useForm({ email: '' })
const searchQuery = ref(props.filters.search || '')
let searchTimeout = null

function addSubscriber() {
  addForm.post(route('admin.blog-subscribers.store'), {
    preserveScroll: true,
    onSuccess: () => addForm.reset(),
  })
}

function toggleActive(sub) {
  router.patch(route('admin.blog-subscribers.toggleActive', sub.id), {}, { preserveState: true })
}

function confirmDestroy(sub) {
  if (confirm(`Удалить подписчика "${sub.email}"?`)) {
    router.delete(route('admin.blog-subscribers.destroy', sub.id))
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('admin.blog-subscribers.index'), { search: searchQuery.value || undefined }, {
      preserveState: true,
      replace: true,
    })
  }, 300)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
