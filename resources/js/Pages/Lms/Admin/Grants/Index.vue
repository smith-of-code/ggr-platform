<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Возможности</h1>
        <p class="mt-1 text-sm text-gray-500">Управление грантами, субсидиями, кредитами и событиями</p>
      </div>
      <Link :href="route('lms.admin.grants.create', event.slug)">
        <RButton>
          <template #icon><PlusIcon class="h-4 w-4" /></template>
          Создать
        </RButton>
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
              <th class="px-6 py-3 font-medium">Название</th>
              <th class="px-6 py-3 font-medium">Тип</th>
              <th class="px-6 py-3 font-medium">Приём заявок</th>
              <th class="px-6 py-3 font-medium">Участники</th>
              <th class="px-6 py-3 font-medium">Статус</th>
              <th class="px-6 py-3 text-right font-medium">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="g in grantsList" :key="g.id" class="transition hover:bg-gray-50/50">
              <td class="px-6 py-4 font-medium text-gray-900">{{ g.title }}</td>
              <td class="px-6 py-4 text-gray-500">{{ typeLabels[g.type] || g.type }}</td>
              <td class="px-6 py-4 text-gray-500">{{ formatDateRange(g) }}</td>
              <td class="px-6 py-4">
                <button
                  v-if="(g.enrollments_count ?? 0) > 0"
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-rosatom-50 px-2.5 py-1 text-sm font-medium text-rosatom-700 transition hover:bg-rosatom-100"
                  @click="showEnrollments(g)"
                >
                  <UsersIcon class="h-3.5 w-3.5" />
                  {{ g.enrollments_count }}
                </button>
                <span v-else class="text-gray-400">0</span>
              </td>
              <td class="px-6 py-4">
                <RBadge :variant="g.is_active ? 'success' : 'neutral'" size="sm">
                  {{ g.is_active ? 'Активен' : 'Неактивен' }}
                </RBadge>
              </td>
              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <Link :href="route('lms.admin.grants.edit', [event.slug, g.id])">
                    <RButton variant="outline" size="sm">Редактировать</RButton>
                  </Link>
                  <RButton variant="ghost" size="sm" class="text-red-500 hover:bg-red-50" type="button" @click="destroy(g)">
                    <template #icon>
                      <TrashIcon class="h-4 w-4" />
                    </template>
                  </RButton>
                </div>
              </td>
            </tr>
            <tr v-if="!grantsList.length">
              <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">Возможности не найдены</td>
            </tr>
          </tbody>
        </table>
      </div>
    </RCard>

    <RModal v-model="enrollmentsModalOpen" :title="enrollmentsModalTitle" size="lg">
      <div v-if="enrollmentsLoading" class="flex items-center justify-center py-12">
        <svg class="h-6 w-6 animate-spin text-rosatom-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>
      <div v-else-if="enrollmentsList.length" class="max-h-[60vh] overflow-y-auto">
        <table class="w-full text-left text-sm">
          <thead class="sticky top-0 bg-white">
            <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-400">
              <th class="px-4 py-2 font-medium">#</th>
              <th class="px-4 py-2 font-medium">ФИО</th>
              <th class="px-4 py-2 font-medium">Email</th>
              <th class="px-4 py-2 font-medium">Телефон</th>
              <th class="px-4 py-2 font-medium">Дата</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(e, idx) in enrollmentsList" :key="e.id" class="hover:bg-gray-50/50">
              <td class="px-4 py-2.5 text-gray-400">{{ idx + 1 }}</td>
              <td class="px-4 py-2.5 font-medium text-gray-900">
                {{ [e.user?.last_name, e.user?.first_name, e.user?.patronymic].filter(Boolean).join(' ') || e.user?.name || '—' }}
              </td>
              <td class="px-4 py-2.5 text-gray-500">{{ e.user?.email || '—' }}</td>
              <td class="px-4 py-2.5 text-gray-500">{{ e.user?.phone || '—' }}</td>
              <td class="px-4 py-2.5 text-gray-400">{{ e.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="py-12 text-center text-sm text-gray-400">
        Нет записавшихся участников
      </div>
      <template #footer>
        <RButton variant="outline" @click="enrollmentsModalOpen = false">Закрыть</RButton>
      </template>
    </RModal>
  </LmsAdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { PlusIcon, TrashIcon, UsersIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  grants: Object,
})

const grantsList = computed(() => {
  const raw = props.grants?.data || props.grants || []
  return Array.isArray(raw) ? raw : []
})

const typeLabels = { grant: 'Грант', subsidy: 'Субсидия', credit: 'Кредит', event: 'Событие' }

const enrollmentsModalOpen = ref(false)
const enrollmentsModalTitle = ref('')
const enrollmentsList = ref([])
const enrollmentsLoading = ref(false)

async function showEnrollments(grant) {
  enrollmentsModalTitle.value = `Участники: ${grant.title}`
  enrollmentsList.value = []
  enrollmentsLoading.value = true
  enrollmentsModalOpen.value = true

  try {
    const url = route('lms.admin.grants.enrollments', [props.event.slug, grant.id])
    const response = await fetch(url, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    enrollmentsList.value = await response.json()
  } catch {
    enrollmentsList.value = []
  } finally {
    enrollmentsLoading.value = false
  }
}

function destroy(grant) {
  if (!confirm(`Удалить «${grant.title}»?`)) return
  router.delete(route('lms.admin.grants.destroy', [props.event.slug, grant.id]))
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateRange(g) {
  const parts = []
  if (g.application_start) parts.push(formatDate(g.application_start))
  if (g.application_end) parts.push(formatDate(g.application_end))
  return parts.join(' – ') || '—'
}
</script>
