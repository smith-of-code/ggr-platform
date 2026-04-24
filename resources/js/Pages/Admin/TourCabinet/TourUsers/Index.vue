<template>
  <AdminLayout>
    <Head title="Клиенты ЛК туров — документы" />
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Клиенты ЛК туров</h1>
        <p class="mt-1 text-sm text-gray-500">
          Участники с доступом к ЛК туров (регистрация в ЛК или профиль ВШГР) и аккаунты сайта, чей email совпал с заявкой на тур.
        </p>
      </div>
      <Link :href="route('admin.tour-cabinet.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">← Хаб ЛК туров</Link>
    </div>

    <form class="mb-6 flex flex-wrap items-end gap-3" @submit.prevent="applySearch">
      <div class="min-w-[200px] flex-1">
        <label class="mb-1.5 block text-xs font-medium text-gray-500">Поиск по имени или email</label>
        <input
          v-model="localQ"
          type="search"
          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          placeholder="ivan@mail.ru"
          autocomplete="off"
        />
      </div>
      <RButton type="submit" variant="primary">Найти</RButton>
      <RButton v-if="filters.q" type="button" variant="outline" @click="clearSearch">Сбросить</RButton>
    </form>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Документы</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="u in users.data"
            :key="u.id"
            class="cursor-pointer transition hover:bg-blue-50/30"
            @click="openUser(u.id)"
          >
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ u.display_name }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-600">{{ u.email }}</td>
            <td class="px-5 py-3.5">
              <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', overviewClass(u.documents_overview.tone)]">
                {{ u.documents_overview.label }}
              </span>
              <span class="ml-2 text-xs text-gray-400">
                подтверждено {{ u.documents_overview.approved }}/{{ u.documents_overview.required }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="users.prev_page_url || users.next_page_url" class="flex justify-end gap-3 border-t border-gray-100 px-4 py-3 text-sm">
        <Link v-if="users.prev_page_url" :href="users.prev_page_url" class="font-medium text-[#003274] hover:underline" preserve-scroll>Назад</Link>
        <Link v-if="users.next_page_url" :href="users.next_page_url" class="font-medium text-[#003274] hover:underline" preserve-scroll>Вперёд</Link>
      </div>
      <div v-if="!users.data.length" class="px-6 py-10 text-center text-sm text-gray-500">
        <p>Клиентов по текущим критериям не найдено.</p>
        <p class="mx-auto mt-3 max-w-xl text-xs text-gray-400">
          Заявка на тур без регистрации на сайте с тем же email сюда не попадает — в списке только пользователи из таблицы
          <code class="rounded bg-gray-100 px-1 font-mono text-[11px]">users</code>.
          Загрузка документов в ЛК возможна после входа в раздел «ЛК туров».
        </p>
      </div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  users: { type: Object, required: true },
  filters: { type: Object, required: true },
})

const localQ = ref(props.filters.q || '')

watch(
  () => props.filters,
  (f) => {
    localQ.value = f.q || ''
  },
  { deep: true },
)

function overviewClass(tone) {
  if (tone === 'success') return 'bg-emerald-50 text-emerald-900'
  if (tone === 'warning') return 'bg-amber-50 text-amber-900'
  if (tone === 'amber') return 'bg-orange-50 text-orange-900'
  return 'bg-gray-100 text-gray-800'
}

function applySearch() {
  router.get(route('admin.tour-cabinet.tour-users.index'), { q: localQ.value || undefined }, { preserveState: true, replace: true })
}

function clearSearch() {
  localQ.value = ''
  router.get(route('admin.tour-cabinet.tour-users.index'), {}, { preserveState: true, replace: true })
}

function openUser(id) {
  router.visit(route('admin.tour-cabinet.tour-users.show', id))
}
</script>
