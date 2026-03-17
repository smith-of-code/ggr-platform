<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Участники</h1>
        <p class="mt-1 text-sm text-gray-500">Управление участниками события «{{ event.title }}»</p>
      </div>
      <div class="flex gap-2">
        <button
          @click="showImportModal = true"
          class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
        >
          <span class="flex items-center gap-2">
            <ArrowUpTrayIcon class="h-4 w-4" />
            Импорт из Excel
          </span>
        </button>
        <button
          @click="router.visit(route('lms.admin.users.create', event.slug))"
          class="rounded-xl bg-rosatom-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700"
        >
          <span class="flex items-center gap-2">
            <PlusIcon class="h-4 w-4" />
            Добавить
          </span>
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-5 flex flex-wrap items-end gap-3">
      <div class="w-64">
        <input
          :value="filters?.search ?? ''"
          @input="debouncedSearch"
          type="text"
          placeholder="Поиск по ФИО, email, телефону..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
        />
      </div>
      <div class="w-48">
        <SearchSelect
          :model-value="filters?.role_id ? Number(filters.role_id) : null"
          @update:model-value="v => applyFilters({ role_id: v ?? '', search: filters?.search ?? '' })"
          :options="roles"
          value-key="id"
          label-key="name"
          placeholder="Все роли"
          :searchable="false"
        />
      </div>
    </div>

    <!-- Success flash -->
    <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
      {{ $page.props.flash.success }}
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Телефон</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Должность</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="profile in profiles.data" :key="profile.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5">
              <button
                @click="router.visit(route('lms.admin.users.show', [event.slug, profile.user_id]))"
                class="block text-left"
              >
                <p class="text-sm font-medium text-gray-900 hover:text-rosatom-600">{{ profile.user?.name }}</p>
                <p class="text-xs text-gray-500">{{ profile.user?.email }}</p>
              </button>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.user?.phone || profile.phone || '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.position || '—' }}</td>
            <td class="px-5 py-3.5">
              <span
                v-if="profile.lms_role"
                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                :class="roleBadgeClass(profile.lms_role.slug)"
              >
                {{ profile.lms_role.name }}
              </span>
              <span v-else class="text-xs text-gray-400">{{ roleLabel(profile.role) }}</span>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <button
                  @click="router.visit(route('lms.admin.users.show', [event.slug, profile.user_id]))"
                  class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                  title="Подробнее"
                >
                  <EyeIcon class="h-4 w-4" />
                </button>
                <button
                  @click="confirmDestroy(profile)"
                  class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                  title="Удалить"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="profiles.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">
        Участников пока нет
      </div>

      <!-- Pagination -->
      <div v-if="profiles.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">
          {{ profiles.from }}–{{ profiles.to }} из {{ profiles.total }}
        </p>
        <div class="flex gap-1">
          <button
            v-for="link in profiles.links"
            :key="link.label"
            @click="link.url && router.visit(link.url, { preserveState: true })"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100 disabled:opacity-30'"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showImportModal = false">
          <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-900">Импорт из Excel / CSV</h2>
            <p class="mt-1 text-sm text-gray-500">Загрузите файл со списком пользователей</p>

            <form @submit.prevent="submitImport" class="mt-6 space-y-5">
              <div>
                <a
                  :href="route('lms.admin.users.template', event.slug)"
                  class="inline-flex items-center gap-2 text-sm font-medium text-rosatom-600 hover:underline"
                >
                  <ArrowDownTrayIcon class="h-4 w-4" />
                  Скачать шаблон CSV
                </a>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Файл</label>
                <div
                  class="relative mt-2 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-rosatom-400"
                  :class="{ 'border-rosatom-500 bg-rosatom-50': importForm.file }"
                >
                  <template v-if="importForm.file">
                    <DocumentIcon class="mx-auto mb-2 h-8 w-8 text-rosatom-500" />
                    <p class="text-sm font-medium text-gray-900">{{ importForm.file.name }}</p>
                    <button type="button" class="mt-1 text-xs text-rosatom-600 hover:underline" @click="importForm.file = null">Убрать</button>
                  </template>
                  <template v-else>
                    <ArrowUpTrayIcon class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                    <p class="text-sm font-medium text-gray-700">Нажмите или перетащите файл</p>
                    <p class="mt-1 text-xs text-gray-400">XLSX, XLS или CSV</p>
                  </template>
                  <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="absolute inset-0 cursor-pointer opacity-0"
                    @change="importForm.file = $event.target.files[0]"
                  />
                </div>
              </div>

              <SearchSelect
                v-model="importForm.default_role_id"
                :options="roles"
                value-key="id"
                label-key="name"
                label="Роль по умолчанию"
                placeholder="Выберите роль"
              />

              <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showImportModal = false"
                  class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                  Отмена
                </button>
                <button type="submit" :disabled="!importForm.file || importForm.processing"
                  class="rounded-xl bg-rosatom-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
                >
                  {{ importForm.processing ? 'Импорт...' : 'Импортировать' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </LmsAdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import {
  PlusIcon,
  ArrowUpTrayIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  TrashIcon,
  DocumentIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  profiles: Object,
  roles: Array,
  groups: Array,
  courses: Array,
  filters: Object,
})

const showImportModal = ref(false)

const importForm = useForm({
  file: null,
  default_role_id: null,
})

let searchTimeout = null
function debouncedSearch(e) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters({ search: e.target.value, role_id: props.filters?.role_id ?? '' })
  }, 400)
}

function applyFilters(params) {
  const p = { ...params }
  Object.keys(p).forEach(k => { if (!p[k]) delete p[k] })
  router.get(route('lms.admin.users.index', props.event.slug), p, { preserveState: true })
}

function roleLabel(role) {
  return { participant: 'Участник', curator: 'Куратор', leader: 'Лидер', admin: 'Админ' }[role] || role
}

function roleBadgeClass(slug) {
  return {
    admin: 'bg-red-50 text-red-700',
    curator: 'bg-amber-50 text-amber-700',
    leader: 'bg-purple-50 text-purple-700',
    expert: 'bg-blue-50 text-blue-700',
    observer: 'bg-gray-100 text-gray-600',
    participant: 'bg-green-50 text-green-700',
  }[slug] || 'bg-gray-100 text-gray-600'
}

function confirmDestroy(profile) {
  if (confirm(`Удалить участника «${profile.user?.name}» из события?`)) {
    router.delete(route('lms.admin.users.destroy', [props.event.slug, profile.user_id]))
  }
}

function submitImport() {
  importForm.post(route('lms.admin.users.import', props.event.slug), {
    forceFormData: true,
    onSuccess: () => {
      showImportModal.value = false
      importForm.reset()
    },
  })
}
</script>
