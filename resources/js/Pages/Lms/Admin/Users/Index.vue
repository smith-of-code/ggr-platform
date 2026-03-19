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

    <!-- Bulk action bar -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="selectedIds.length > 0" class="mb-4 flex items-center gap-4 rounded-xl border border-rosatom-200 bg-rosatom-50 px-5 py-3">
        <span class="text-sm font-medium text-rosatom-800">
          Выбрано: {{ selectedIds.length }}
        </span>
        <RButton variant="primary" size="sm" :loading="sendingInvites" @click="handleSendInvitations">
          <template #icon><EnvelopeIcon class="h-4 w-4" /></template>
          Отправить приглашения
        </RButton>
        <RButton variant="outline" size="sm" @click="showBulkEnrollModal = true">
          <template #icon><AcademicCapIcon class="h-4 w-4" /></template>
          Записать на курсы
        </RButton>
        <button type="button" class="ml-auto text-xs text-gray-500 hover:text-gray-700" @click="selectedIds = []">
          Снять выделение
        </button>
      </div>
    </Transition>

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
          @update:model-value="v => applyFilter('role_id', v ?? '')"
          :options="roles"
          value-key="id"
          label-key="name"
          placeholder="Все роли"
          :searchable="false"
        />
      </div>
      <div class="w-48">
        <SearchSelect
          :model-value="filters?.status ?? null"
          @update:model-value="v => applyFilter('status', v ?? '')"
          :options="statusOptions"
          value-key="value"
          label-key="label"
          placeholder="Все статусы"
          :searchable="false"
        />
      </div>
      <div class="w-48">
        <SearchSelect
          :model-value="filters?.group ? Number(filters.group) : null"
          @update:model-value="v => applyFilter('group', v ?? '')"
          :options="groups"
          value-key="id"
          label-key="title"
          placeholder="Все группы"
          :searchable="false"
        />
      </div>
      <div class="w-48">
        <SearchSelect
          :model-value="filters?.city ?? null"
          @update:model-value="v => applyFilter('city', v ?? '')"
          :options="cityOptions"
          value-key="value"
          label-key="label"
          placeholder="Все города"
          :searchable="true"
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
            <th class="w-10 px-3 py-3">
              <RCheckbox
                :model-value="allOnPageSelected"
                @update:model-value="toggleSelectAll"
              />
            </th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Телефон</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Должность</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="profile in profiles.data" :key="profile.id" class="transition hover:bg-gray-50"
              :class="{ 'bg-rosatom-50/50': selectedIds.includes(profile.id) }">
            <td class="w-10 px-3 py-3.5">
              <RCheckbox
                :model-value="selectedIds.includes(profile.id)"
                @update:model-value="toggleSelect(profile.id)"
              />
            </td>
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
            <td class="px-5 py-3.5">
              <div class="relative">
                <button
                  v-if="profile.invite_token && profile.status === 'invited'"
                  type="button"
                  class="group"
                  @click="toggleActivateLink(profile.id)"
                >
                  <RBadge :variant="profileStatus(profile).variant" size="sm" class="cursor-pointer transition group-hover:ring-2 group-hover:ring-amber-300">
                    {{ profileStatus(profile).label }}
                  </RBadge>
                </button>
                <RBadge v-else :variant="profileStatus(profile).variant" size="sm">
                  {{ profileStatus(profile).label }}
                </RBadge>
                <p v-if="profile.invited_at && profile.status === 'invited'" class="mt-0.5 text-[10px] text-gray-400">
                  {{ formatDate(profile.invited_at) }}
                </p>
                <!-- Activate link popover -->
                <Transition
                  enter-active-class="transition duration-150 ease-out"
                  enter-from-class="opacity-0 scale-95"
                  enter-to-class="opacity-100 scale-100"
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="opacity-100 scale-100"
                  leave-to-class="opacity-0 scale-95"
                >
                  <div v-if="expandedLinkId === profile.id && profile.invite_token"
                       class="absolute left-0 top-full z-20 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-lg">
                    <p class="mb-1.5 text-xs font-medium text-gray-500">Ссылка активации:</p>
                    <div class="flex items-center gap-2">
                      <input
                        type="text"
                        readonly
                        :value="getActivateUrl(profile)"
                        class="flex-1 truncate rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none"
                        @click="$event.target.select()"
                      />
                      <button
                        type="button"
                        class="shrink-0 rounded-lg bg-rosatom-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-rosatom-700"
                        @click.stop="copyActivateLink(profile)"
                      >
                        {{ copiedProfileId === profile.id ? 'Скопировано!' : 'Копировать' }}
                      </button>
                    </div>
                    <button type="button" class="mt-2 text-[10px] text-gray-400 hover:text-gray-600" @click="expandedLinkId = null">
                      Закрыть
                    </button>
                  </div>
                </Transition>
              </div>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <RButton v-if="profile.invite_token" variant="ghost" size="sm" icon-only title="Копировать ссылку активации"
                  @click="copyActivateLink(profile)"
                >
                  <template #icon><ClipboardDocumentIcon class="h-4 w-4" :class="copiedProfileId === profile.id ? 'text-green-500' : ''" /></template>
                </RButton>
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
            Скачать шаблон Excel
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

    <!-- Bulk Enroll Modal -->
    <div class="enroll-modal-wrapper">
      <RModal v-model="showBulkEnrollModal" title="Записать на курсы" :subtitle="`Выбрано участников: ${selectedIds.length}`" size="lg">
        <div class="min-h-[340px] space-y-5">
          <MultiSelect
            v-model="bulkEnrollCourseIds"
            :options="courses"
            value-key="id"
            label-key="title"
            label="Курсы"
            placeholder="Выберите курсы..."
            :searchable="true"
          />
          <div v-if="bulkEnrollCourseIds.length" class="space-y-3">
            <p class="text-sm text-gray-500">
              Будет создано до <strong>{{ selectedIds.length * bulkEnrollCourseIds.length }}</strong> записей
              ({{ selectedIds.length }} участников × {{ bulkEnrollCourseIds.length }} курсов)
            </p>
            <div class="flex flex-wrap gap-2">
              <RBadge v-for="cid in bulkEnrollCourseIds" :key="cid" variant="primary" size="sm">
                {{ courses.find(c => c.id === cid)?.title }}
              </RBadge>
            </div>
          </div>
        </div>

        <template #footer>
          <RButton variant="outline" @click="showBulkEnrollModal = false">
            Отмена
          </RButton>
          <RButton variant="primary" :loading="enrolling" :disabled="!bulkEnrollCourseIds.length || enrolling" @click="handleBulkEnroll">
            {{ enrolling ? 'Запись...' : 'Записать' }}
          </RButton>
        </template>
      </RModal>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import {
  PlusIcon,
  ArrowUpTrayIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  TrashIcon,
  DocumentIcon,
  LinkIcon,
  ChevronDownIcon,
  EnvelopeIcon,
  ClipboardDocumentIcon,
  AcademicCapIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  profiles: Object,
  roles: Array,
  groups: Array,
  courses: Array,
  cities: Array,
  filters: Object,
  invitations: Array,
})

const showImportModal = ref(false)
const showInviteModal = ref(false)
const showInvitations = ref(false)
const copiedId = ref(null)
const copiedProfileId = ref(null)
const expandedLinkId = ref(null)
const selectedIds = ref([])
const sendingInvites = ref(false)
const showBulkEnrollModal = ref(false)
const bulkEnrollCourseIds = ref([])
const enrolling = ref(false)

const statusOptions = [
  { value: 'imported', label: 'Импортирован' },
  { value: 'invited', label: 'Приглашён' },
  { value: 'active', label: 'Активен' },
]

const cityOptions = computed(() =>
  (props.cities || []).map(c => ({ value: c, label: c }))
)

const allOnPageSelected = computed(() => {
  if (!props.profiles.data.length) return false
  return props.profiles.data.every(p => selectedIds.value.includes(p.id))
})

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
    applyFilter('search', e.target.value)
  }, 400)
}

function applyFilter(key, value) {
  const current = { ...(props.filters || {}) }
  if (value) {
    current[key] = value
  } else {
    delete current[key]
  }
  Object.keys(current).forEach(k => { if (!current[k]) delete current[k] })
  router.get(route('lms.admin.users.index', props.event.slug), current, { preserveState: true })
}

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id)
  if (idx >= 0) {
    selectedIds.value.splice(idx, 1)
  } else {
    selectedIds.value.push(id)
  }
}

function toggleSelectAll(val) {
  if (val) {
    const pageIds = props.profiles.data.map(p => p.id)
    const merged = new Set([...selectedIds.value, ...pageIds])
    selectedIds.value = [...merged]
  } else {
    const pageIds = new Set(props.profiles.data.map(p => p.id))
    selectedIds.value = selectedIds.value.filter(id => !pageIds.has(id))
  }
}

function handleSendInvitations() {
  if (!confirm(`Отправить приглашения ${selectedIds.value.length} участникам?`)) return

  sendingInvites.value = true
  router.post(
    route('lms.admin.users.send-invitations', props.event.slug),
    { profile_ids: selectedIds.value },
    {
      onSuccess: () => {
        selectedIds.value = []
      },
      onFinish: () => {
        sendingInvites.value = false
      },
    }
  )
}

function handleBulkEnroll() {
  if (!bulkEnrollCourseIds.value.length) return

  enrolling.value = true
  router.post(
    route('lms.admin.users.bulk-enroll', props.event.slug),
    { profile_ids: selectedIds.value, course_ids: bulkEnrollCourseIds.value },
    {
      onSuccess: () => {
        showBulkEnrollModal.value = false
        bulkEnrollCourseIds.value = []
        selectedIds.value = []
      },
      onFinish: () => {
        enrolling.value = false
      },
    }
  )
}

function profileStatus(profile) {
  const map = {
    imported: { label: 'Импортирован', variant: 'neutral' },
    invited: { label: 'Приглашён', variant: 'warning' },
    active: { label: 'Активен', variant: 'success' },
  }
  return map[profile.status] || { label: profile.status || '—', variant: 'neutral' }
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

function toggleActivateLink(profileId) {
  expandedLinkId.value = expandedLinkId.value === profileId ? null : profileId
}

function getActivateUrl(profile) {
  return route('lms.activate', { event: props.event.slug, token: profile.invite_token })
}

function copyActivateLink(profile) {
  const url = getActivateUrl(profile)
  navigator.clipboard.writeText(url).then(() => {
    copiedProfileId.value = profile.id
    setTimeout(() => { copiedProfileId.value = null }, 2000)
  })
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

<style scoped>
.enroll-modal-wrapper :deep([class*="max-w-lg"]) {
  max-width: 40rem;
}
</style>
