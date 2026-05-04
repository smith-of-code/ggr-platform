<template>
  <AdminLayout>
    <Head title="Клиенты ЛК туров" />
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Клиенты</h1>
        <p class="mt-1 text-sm text-gray-500">
          Участники с доступом к ЛК туров (регистрация в ЛК или профиль ВШГР) и аккаунты сайта, чей email совпал с заявкой на тур. Фильтры ниже действуют на таблицу и на выгрузку CSV.
        </p>
      </div>
      <Link :href="route('admin.tour-cabinet.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">← Хаб ЛК туров</Link>
    </div>

    <RCard elevation="raised" class="mb-6 p-4">
      <form class="flex flex-wrap items-end gap-4" @submit.prevent="applyFilters">
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
        <div v-if="exportCityOptions.length">
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Город</label>
          <select
            v-model="localCityId"
            class="min-w-[14rem] rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          >
            <option value="">Все города</option>
            <option v-for="c in exportCityOptions" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Тип доступа</label>
          <select
            v-model="localSegment"
            class="min-w-[16rem] rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          >
            <option v-for="opt in segmentOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
        <RButton type="submit" variant="primary">Применить</RButton>
        <RButton v-if="hasActiveFilters" type="button" variant="outline" @click="clearFilters">Сбросить</RButton>
        <div class="ml-auto flex flex-col items-stretch gap-1 sm:items-end" @click.stop>
          <span class="text-xs font-medium text-gray-500">Выгрузка (текущая выдача)</span>
          <a
            :href="exportDownloadHref"
            class="inline-flex items-center justify-center rounded-xl bg-[#003274] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1]"
          >
            Скачать CSV
          </a>
        </div>
      </form>
    </RCard>

    <RCard elevation="raised" flush>
      <div v-if="users.total != null" class="border-b border-gray-100 px-5 py-3 text-sm text-gray-600">
        Найдено участников: <span class="font-semibold text-gray-900">{{ users.total }}</span>
      </div>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Доступ</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Конкурс / города</th>
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
              <div class="flex flex-wrap gap-1">
                <span
                  v-if="u.access?.tour_cabinet_registered"
                  class="inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-900 ring-1 ring-sky-200"
                >
                  ЛК туров
                </span>
                <span
                  v-if="u.access?.lms_vshgr"
                  class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-900 ring-1 ring-indigo-200"
                >
                  ВШГР
                </span>
                <span v-if="!u.access?.tour_cabinet_registered && !u.access?.lms_vshgr" class="text-xs text-gray-600">Сайт (email в заявке)</span>
              </div>
            </td>
            <td class="max-w-xs px-5 py-3.5 text-xs text-gray-600">
              {{ u.contest_summary || '—' }}
            </td>
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
      <div v-if="users.total != null" class="border-t border-gray-100 px-4 py-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
          <p class="text-sm text-gray-700">
            <span class="font-semibold text-gray-900">Всего участников</span> (по фильтру): {{ users.total }}.
            <span class="font-semibold text-gray-900">На этой странице</span>:
            {{ onPageParticipantCount }}
            <template v-if="users.from != null && users.to != null">
              (записи с № {{ users.from }} по № {{ users.to }})
            </template>
          </p>
          <div v-if="users.last_page > 1" class="flex gap-1">
            <button
              v-for="link in users.links"
              :key="link.label"
              type="button"
              :disabled="!link.url"
              class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
              :class="
                link.active
                  ? 'bg-[#003274] text-white'
                  : 'text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30'
              "
              @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
      <div v-if="!users.data.length" class="px-6 py-10 text-center text-sm text-gray-500">
        <p>Клиентов по текущим критериям не найдено.</p>
        <p class="mx-auto mt-3 max-w-xl text-xs text-gray-400">
          Заявка на тур без регистрации на сайте с тем же email сюда не попадает — в списке только пользователи из таблицы
          <code class="rounded bg-gray-100 px-1 font-mono text-[11px]">users</code>.
        </p>
      </div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const segmentOptions = [
  { value: 'all', label: 'Все' },
  { value: 'tour_only', label: 'Регистрация только в ЛК туров (без ВШГР)' },
  { value: 'lms', label: 'Есть доступ ВШГР (профиль LMS)' },
]

const props = defineProps({
  users: { type: Object, required: true },
  filters: { type: Object, required: true },
  exportCityOptions: { type: Array, default: () => [] },
})

const localQ = ref(props.filters.q || '')
const localCityId = ref(props.filters.city_id || '')
const localSegment = ref(props.filters.segment || 'all')

const hasActiveFilters = computed(() => {
  return !!(localQ.value || localCityId.value || (localSegment.value && localSegment.value !== 'all'))
})

const exportDownloadHref = computed(() => {
  const params = {}
  if (localQ.value) params.q = localQ.value
  if (localCityId.value) params.city_id = localCityId.value
  if (localSegment.value && localSegment.value !== 'all') params.segment = localSegment.value
  return route('admin.tour-cabinet.tour-users.export', params)
})

const onPageParticipantCount = computed(() => props.users.data?.length ?? 0)

watch(
  () => props.filters,
  (f) => {
    localQ.value = f.q || ''
    localCityId.value = f.city_id || ''
    localSegment.value = f.segment || 'all'
  },
  { deep: true },
)

function overviewClass(tone) {
  if (tone === 'success') return 'bg-emerald-50 text-emerald-900'
  if (tone === 'warning') return 'bg-amber-50 text-amber-900'
  if (tone === 'amber') return 'bg-orange-50 text-orange-900'
  return 'bg-gray-100 text-gray-800'
}

function filterQuery() {
  return {
    q: localQ.value || undefined,
    city_id: localCityId.value || undefined,
    segment: localSegment.value && localSegment.value !== 'all' ? localSegment.value : undefined,
  }
}

function applyFilters() {
  router.get(route('admin.tour-cabinet.tour-users.index'), filterQuery(), { preserveState: true, replace: true })
}

function clearFilters() {
  localQ.value = ''
  localCityId.value = ''
  localSegment.value = 'all'
  router.get(route('admin.tour-cabinet.tour-users.index'), {}, { preserveState: true, replace: true })
}

function openUser(id) {
  router.visit(route('admin.tour-cabinet.tour-users.show', id))
}
</script>
