<template>
  <AdminLayout>
    <Head title="Сброс прогресса конкурса" />

    <div class="mb-8">
      <div class="flex items-center gap-3">
        <Link
          :href="route('admin.settings.index')"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Сброс прогресса конкурса</h1>
          <p class="mt-1 text-sm text-gray-500">
            Полный сброс прогресса участника по конкурсу: удаляются выбор направления и городов, отправленные анкеты этапа 1, ответы этапа 2 и материалы этапа 3 (включая загруженный файл). Запись участника, его профиль и заявки на туры — не трогаются. Действие нельзя отменить.
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="page.props.flash?.success"
      class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
    >
      {{ page.props.flash.success }}
    </div>
    <div
      v-if="page.props.flash?.error"
      class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
    >
      {{ page.props.flash.error }}
    </div>

    <form class="mb-6 flex flex-wrap items-end gap-3" @submit.prevent="submitSearch">
      <div class="flex-1 min-w-[260px]">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500" for="contest-reset-search">
          Поиск участника
        </label>
        <input
          id="contest-reset-search"
          v-model="form.q"
          type="search"
          placeholder="Email, ФИО или ID"
          class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
        />
      </div>
      <button
        type="submit"
        class="inline-flex items-center gap-1.5 rounded-lg bg-[#003274] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#003274]/90 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="form.processing"
      >
        Найти
      </button>
      <button
        v-if="filters.q"
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
        @click="resetSearch"
      >
        Сбросить фильтр
      </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div v-if="users.data.length === 0" class="px-6 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-gray-700">
          {{ filters.q ? 'Никто не найден по запросу' : 'Никто из участников не начал конкурс' }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
          В список попадают только пользователи, у которых уже есть выбор направления, отправленная анкета этапа 1 или ответ этапа 2.
        </p>
      </div>

      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <th class="px-6 py-3.5">ID</th>
            <th class="px-6 py-3.5">Email</th>
            <th class="px-6 py-3.5">ФИО</th>
            <th class="px-6 py-3.5">Направление</th>
            <th class="px-6 py-3.5 text-center">Этап</th>
            <th class="px-6 py-3.5">Этап 2 отправлен</th>
            <th class="px-6 py-3.5 text-right">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-gray-50/50">
            <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ user.id }}</td>
            <td class="px-6 py-4 text-sm text-gray-900">{{ user.email || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ user.fio || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ user.direction_label || '—' }}</td>
            <td class="px-6 py-4 text-center text-sm text-gray-700">
              <span v-if="user.current_stage" class="inline-flex items-center justify-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                {{ user.current_stage }}
              </span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(user.stage2_submitted_at) }}</td>
            <td class="px-6 py-4">
              <div class="flex justify-end">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="resettingUserId === user.id"
                  @click="openConfirm(user)"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                  {{ resettingUserId === user.id ? 'Сбрасывается…' : 'Сбросить' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="users.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-6 py-3">
        <p class="text-xs text-gray-500">{{ users.from }}–{{ users.to }} из {{ users.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in users.links"
            :key="link.label"
            type="button"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-[#003274] text-white' : 'text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30'"
            @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <Modal :show="confirmOpen" max-width="lg" @close="closeConfirm">
      <div class="p-6">
        <h2 class="text-lg font-bold text-gray-900">Сбросить прогресс конкурса?</h2>
        <p v-if="confirmTarget" class="mt-2 text-sm text-gray-600">
          Участник: <span class="font-semibold text-gray-900">{{ confirmTarget.email || ('ID ' + confirmTarget.id) }}</span><span v-if="confirmTarget.fio">, {{ confirmTarget.fio }}</span>.
        </p>
        <ul class="mt-4 list-inside list-disc space-y-1 text-sm text-gray-700">
          <li>Будут удалены выбор направления и список выбранных городов.</li>
          <li>Будут удалены все отправленные анкеты этапа 1 (ссылка на сабмишен формы LMS), сами ответы LMS остаются в БД.</li>
          <li>Будут удалены все ответы участника на этап 2.</li>
          <li>Будут удалены текст, ссылка на видео и файл-вложение этапа 3 (файл стирается со storage).</li>
          <li>Участник на дашборде ЛК снова увидит выбор направления — как новый участник.</li>
        </ul>
        <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
          Действие нельзя отменить. Email-уведомление участнику не отправляется.
        </p>
        <div class="mt-6 flex items-center justify-end gap-2">
          <button
            type="button"
            class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            @click="closeConfirm"
          >
            Отмена
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="resettingUserId !== null"
            @click="confirmReset"
          >
            {{ resettingUserId !== null ? 'Сбрасывается…' : 'Сбросить прогресс' }}
          </button>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  users: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ q: '' }),
  },
})

const page = usePage()

const form = useForm({
  q: props.filters?.q ?? '',
})

const resettingUserId = ref(null)
const confirmOpen = ref(false)
const confirmTarget = ref(null)

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('ru-RU', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function submitSearch() {
  router.get(
    route('admin.settings.contest-reset.index'),
    { q: form.q },
    { preserveState: true, preserveScroll: true, replace: true },
  )
}

function resetSearch() {
  form.q = ''
  router.get(
    route('admin.settings.contest-reset.index'),
    {},
    { preserveState: true, preserveScroll: true, replace: true },
  )
}

function openConfirm(user) {
  confirmTarget.value = user
  confirmOpen.value = true
}

function closeConfirm() {
  if (resettingUserId.value !== null) return
  confirmOpen.value = false
  confirmTarget.value = null
}

function confirmReset() {
  if (!confirmTarget.value || resettingUserId.value !== null) return
  const userId = confirmTarget.value.id
  resettingUserId.value = userId
  router.post(
    route('admin.settings.contest-reset.reset', userId),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        resettingUserId.value = null
        confirmOpen.value = false
        confirmTarget.value = null
      },
    },
  )
}
</script>
