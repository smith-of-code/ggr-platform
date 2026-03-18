<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Участники</h1>
        <p class="mt-1 text-sm text-gray-500">Управление участниками события «{{ event.title }}»</p>
      </div>
      <div class="flex gap-2">
        <RButton variant="outline" @click="showImportModal = true">
          <template #icon><ArrowUpTrayIcon class="h-4 w-4" /></template>
          Импорт из Excel
        </RButton>
        <RButton variant="outline" @click="showInviteModal = true">
          <template #icon><LinkIcon class="h-4 w-4" /></template>
          Создать ссылку
        </RButton>
        <RButton variant="primary" @click="router.visit(route('lms.admin.users.create', event.slug))">
          <template #icon><PlusIcon class="h-4 w-4" /></template>
          Добавить
        </RButton>
      </div>
    </div>

    <!-- Invitation links section -->
    <div v-if="invitations?.length" class="mb-6">
      <button
        type="button"
        class="flex w-full items-center gap-2 text-sm font-semibold text-gray-700"
        @click="showInvitations = !showInvitations"
      >
        <LinkIcon class="h-4 w-4" />
        Пригласительные ссылки ({{ invitations.length }})
        <ChevronDownIcon
          class="h-4 w-4 transition-transform"
          :class="{ 'rotate-180': showInvitations }"
        />
      </button>

      <div v-if="showInvitations" class="mt-3 space-y-2">
        <div
          v-for="inv in invitations"
          :key="inv.id"
          class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm"
        >
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <p class="truncate text-sm font-medium text-gray-900">
                {{ inv.label || 'Без названия' }}
              </p>
              <RBadge :variant="invitationStatus(inv).variant" size="sm">
                {{ invitationStatus(inv).label }}
              </RBadge>
            </div>
            <p class="mt-0.5 text-xs text-gray-400">
              Использовано: {{ inv.uses_count }}{{ inv.max_uses ? ` / ${inv.max_uses}` : '' }}
              <span v-if="inv.role"> · Роль: {{ inv.role.name }}</span>
              <span v-if="inv.expires_at"> · До: {{ formatDate(inv.expires_at) }}</span>
            </p>
          </div>

          <div class="flex items-center gap-1.5">
            <button
              type="button"
              class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-200"
              @click="copyLink(inv)"
            >
              {{ copiedId === inv.id ? 'Скопировано!' : 'Копировать' }}
            </button>
            <RButton
              :variant="inv.is_active ? 'outline' : 'primary'"
              size="sm"
              @click="toggleInvitation(inv)"
            >
              {{ inv.is_active ? 'Выкл' : 'Вкл' }}
            </RButton>
            <RButton variant="danger" size="sm" icon-only @click="deleteInvitation(inv)">
              <template #icon><TrashIcon class="h-3.5 w-3.5" /></template>
            </RButton>
          </div>
        </div>
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
    <RCard elevation="raised" flush>
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
                class="flex items-center gap-3 text-left"
              >
                <RAvatar :name="profile.user?.name" size="sm" />
                <div>
                  <p class="text-sm font-medium text-gray-900 hover:text-rosatom-600">{{ profile.user?.name }}</p>
                  <p class="text-xs text-gray-500">{{ profile.user?.email }}</p>
                </div>
              </button>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.user?.phone || profile.phone || '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.position || '—' }}</td>
            <td class="px-5 py-3.5">
              <RBadge v-if="profile.lms_role" :variant="roleBadgeVariant(profile.lms_role.slug)" size="sm">
                {{ profile.lms_role.name }}
              </RBadge>
              <span v-else class="text-xs text-gray-400">{{ roleLabel(profile.role) }}</span>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <RButton variant="ghost" size="sm" icon-only title="Подробнее"
                  @click="router.visit(route('lms.admin.users.show', [event.slug, profile.user_id]))"
                >
                  <template #icon><EyeIcon class="h-4 w-4" /></template>
                </RButton>
                <RButton variant="danger" size="sm" icon-only title="Удалить"
                  @click="confirmDestroy(profile)"
                >
                  <template #icon><TrashIcon class="h-4 w-4" /></template>
                </RButton>
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
    </RCard>

    <!-- Import Modal -->
    <RModal v-model="showImportModal" title="Импорт из Excel / CSV" subtitle="Загрузите файл со списком пользователей" size="lg">
      <form @submit.prevent="submitImport" class="space-y-5">
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
      </form>

      <template #footer>
        <RButton variant="outline" @click="showImportModal = false">
          Отмена
        </RButton>
        <RButton variant="primary" :loading="importForm.processing" :disabled="!importForm.file || importForm.processing" @click="submitImport">
          {{ importForm.processing ? 'Импорт...' : 'Импортировать' }}
        </RButton>
      </template>
    </RModal>

    <!-- Create Invitation Modal -->
    <RModal v-model="showInviteModal" title="Создать пригласительную ссылку" subtitle="Ссылку можно отправить пользователям для самостоятельной регистрации" size="lg">
      <form @submit.prevent="submitInvitation" class="space-y-5">
        <RInput
          v-model="inviteForm.label"
          label="Название (для удобства)"
          placeholder="Например: Набор 2026, Группа А..."
        />

        <SearchSelect
          v-model="inviteForm.lms_role_id"
          :options="roles"
          value-key="id"
          label-key="name"
          label="Роль для регистрирующихся"
          placeholder="Выберите роль"
        />

        <RInput
          v-model="inviteForm.expires_at"
          type="datetime-local"
          label="Срок действия"
          hint="Оставьте пустым для бессрочной ссылки"
        />

        <RInput
          v-model="inviteForm.max_uses"
          type="number"
          label="Максимум регистраций"
          placeholder="Без ограничения"
          :min="1"
          hint="Оставьте пустым для неограниченного количества"
        />
      </form>

      <template #footer>
        <RButton variant="outline" @click="showInviteModal = false">
          Отмена
        </RButton>
        <RButton variant="primary" :loading="inviteForm.processing" :disabled="inviteForm.processing" @click="submitInvitation">
          {{ inviteForm.processing ? 'Создание...' : 'Создать ссылку' }}
        </RButton>
      </template>
    </RModal>
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
  LinkIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  profiles: Object,
  roles: Array,
  groups: Array,
  courses: Array,
  filters: Object,
  invitations: Array,
})

const showImportModal = ref(false)
const showInviteModal = ref(false)
const showInvitations = ref(false)
const copiedId = ref(null)

const importForm = useForm({
  file: null,
  default_role_id: null,
})

const inviteForm = useForm({
  label: '',
  lms_role_id: null,
  expires_at: '',
  max_uses: null,
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

function roleBadgeVariant(slug) {
  return { admin: 'error', curator: 'warning', leader: 'primary', expert: 'info', observer: 'neutral', participant: 'success' }[slug] || 'neutral'
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

function submitInvitation() {
  inviteForm.post(route('lms.admin.invitations.store', props.event.slug), {
    onSuccess: () => {
      showInviteModal.value = false
      inviteForm.reset()
      showInvitations.value = true
    },
  })
}

function invitationStatus(inv) {
  if (!inv.is_active) return { label: 'Выключена', variant: 'neutral' }
  if (inv.expires_at && new Date(inv.expires_at) < new Date()) return { label: 'Истекла', variant: 'error' }
  if (inv.max_uses && inv.uses_count >= inv.max_uses) return { label: 'Лимит', variant: 'warning' }
  return { label: 'Активна', variant: 'success' }
}

function getInviteUrl(inv) {
  return route('lms.invite', { event: props.event.slug, token: inv.token })
}

function copyLink(inv) {
  const url = getInviteUrl(inv)
  navigator.clipboard.writeText(url).then(() => {
    copiedId.value = inv.id
    setTimeout(() => { copiedId.value = null }, 2000)
  })
}

function toggleInvitation(inv) {
  router.post(route('lms.admin.invitations.toggle', [props.event.slug, inv.id]))
}

function deleteInvitation(inv) {
  if (confirm('Удалить эту пригласительную ссылку?')) {
    router.delete(route('lms.admin.invitations.destroy', [props.event.slug, inv.id]))
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
