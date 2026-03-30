<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ course ? `Заявки — ${course.title}` : 'Заявки на курсы' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">Управление записями участников на курсы</p>
      </div>
      <div v-if="course">
        <RButton variant="ghost" size="sm" @click="router.visit(route('lms.admin.courses.edit', [event.slug, course.id]))">
          <template #icon><ArrowLeftIcon class="h-4 w-4" /></template>
          К курсу
        </RButton>
      </div>
    </div>

    <!-- Status tabs -->
    <RTabs :model-value="currentStatus" :tabs="statusTabs" @update:model-value="filterByStatus">
      <RCard elevation="raised" flush>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                <th class="px-6 py-3 font-medium">Участник</th>
                <th v-if="!course" class="px-6 py-3 font-medium">Курс</th>
                <th class="px-6 py-3 font-medium">Организация / Должность</th>
                <th class="px-6 py-3 font-medium">Проект / Идея</th>
                <th class="px-6 py-3 font-medium">Дата заявки</th>
                <th class="px-6 py-3 font-medium">Статус</th>
                <th class="px-6 py-3 font-medium">Рассмотрено</th>
                <th class="px-6 py-3 text-right font-medium">Действия</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="e in enrollmentsList" :key="e.id" class="transition hover:bg-gray-50/50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <RAvatar :name="e.user?.name" size="sm" />
                    <div>
                      <p class="font-medium text-gray-900">{{ e.user?.name }}</p>
                      <p v-if="e.profile_data?.phone" class="text-xs text-gray-400">
                        {{ e.profile_data.phone }}
                        <span v-if="e.profile_data.preferred_channel" class="ml-1 rounded bg-gray-100 px-1 py-0.5 text-[10px] font-medium uppercase text-gray-500">
                          {{ e.profile_data.preferred_channel }}
                        </span>
                      </p>
                      <p v-else class="text-xs text-gray-400">{{ e.user?.email }}</p>
                    </div>
                  </div>
                </td>
                <td v-if="!course" class="px-6 py-4 text-gray-600">{{ e.course?.title }}</td>
                <td class="px-6 py-4">
                  <p v-if="e.profile_data?.organization" class="text-sm text-gray-700">{{ e.profile_data.organization }}</p>
                  <p v-if="e.profile_data?.position" class="text-xs text-gray-400">{{ e.profile_data.position }}</p>
                  <span v-if="!e.profile_data?.organization && !e.profile_data?.position" class="text-xs text-gray-300">—</span>
                </td>
                <td class="px-6 py-4">
                  <p v-if="e.profile_data?.project_description" class="line-clamp-2 max-w-xs text-sm text-gray-600">{{ e.profile_data.project_description }}</p>
                  <span v-else class="text-xs text-gray-300">—</span>
                </td>
                <td class="px-6 py-4 text-gray-500">{{ formatDate(e.created_at) }}</td>
                <td class="px-6 py-4">
                  <RBadge :variant="statusVariant(e.status)" size="sm">{{ statusLabel(e.status) }}</RBadge>
                </td>
                <td class="px-6 py-4">
                  <template v-if="e.reviewed_at">
                    <p class="text-xs text-gray-500">{{ e.reviewer?.name }}</p>
                    <p class="text-xs text-gray-400">{{ formatDate(e.reviewed_at) }}</p>
                  </template>
                  <span v-else class="text-xs text-gray-300">—</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-end gap-2">
                    <template v-if="e.status === 'pending'">
                      <RButton variant="primary" size="sm" @click="approve(e.id)">Одобрить</RButton>
                      <RButton variant="outline" size="sm" @click="openReassign(e)">Перевести</RButton>
                      <RButton variant="danger" size="sm" @click="reject(e.id)">Отклонить</RButton>
                    </template>
                    <template v-else-if="e.status === 'rejected'">
                      <RButton variant="outline" size="sm" @click="approve(e.id)">Одобрить</RButton>
                    </template>
                    <template v-if="e.status === 'enrolled'">
                      <RButton variant="outline" size="sm" @click="openReassign(e)">Перевести</RButton>
                      <RButton variant="outline" size="sm" class="text-gray-500" @click="unenroll(e)">
                        Отписать
                      </RButton>
                    </template>
                    <RButton variant="ghost" size="sm" class="text-red-500 hover:bg-red-50" @click="remove(e)">
                      <template #icon><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></template>
                    </RButton>
                  </div>
                </td>
              </tr>
              <tr v-if="!enrollmentsList.length">
                <td :colspan="course ? 7 : 8" class="px-6 py-12 text-center text-sm text-gray-400">
                  Заявок не найдено
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </RCard>
    </RTabs>

    <!-- Pagination -->
    <div v-if="enrollments.last_page > 1" class="mt-6 flex items-center justify-between">
      <p class="text-xs text-gray-500">{{ enrollments.from }}–{{ enrollments.to }} из {{ enrollments.total }}</p>
      <div class="flex gap-1">
        <button
          v-for="link in enrollments.links"
          :key="link.label"
          @click="link.url && router.visit(link.url, { preserveState: true })"
          :disabled="!link.url"
          class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
          :class="link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100 disabled:opacity-30'"
          v-html="link.label"
        />
      </div>
    </div>
    <!-- Reassign modal -->
    <RModal v-model="showReassignModal" title="Перевести на другой курс" size="sm">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Участник: <span class="font-medium text-gray-900">{{ reassignEnrollment?.user?.name }}</span>
        </p>
        <p class="text-sm text-gray-600">
          Текущий курс: <span class="font-medium text-gray-900">{{ reassignEnrollment?.course?.title }}</span>
        </p>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700">Новый курс</label>
          <select
            v-model="reassignCourseId"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
          >
            <option :value="null" disabled>Выберите курс</option>
            <option
              v-for="c in availableCoursesForReassign"
              :key="c.id"
              :value="c.id"
            >
              {{ c.title }}
            </option>
          </select>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <RButton variant="outline" size="sm" type="button" @click="showReassignModal = false">Отмена</RButton>
          <RButton variant="primary" size="sm" type="button" :disabled="!reassignCourseId" @click="submitReassign">Перевести</RButton>
        </div>
      </template>
    </RModal>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  enrollments: Object,
  course: { type: Object, default: null },
  courses: Array,
  currentStatus: String,
  counts: Object,
})

const enrollmentsList = computed(() => {
  const raw = props.enrollments?.data || props.enrollments || []
  return Array.isArray(raw) ? raw : []
})

const statusTabs = computed(() => [
  { value: 'pending', label: `Ожидающие (${props.counts?.pending ?? 0})` },
  { value: 'enrolled', label: `Одобренные (${props.counts?.enrolled ?? 0})` },
  { value: 'rejected', label: `Отклонённые (${props.counts?.rejected ?? 0})` },
  { value: 'all', label: `Все (${props.counts?.all ?? 0})` },
])

function filterByStatus(status) {
  const routeName = props.course
    ? 'lms.admin.enrollments.course'
    : 'lms.admin.enrollments.index'
  const params = props.course
    ? [props.event.slug, props.course.id]
    : [props.event.slug]

  router.get(route(routeName, params), { status }, { preserveState: true })
}

function approve(id) {
  router.post(route('lms.admin.enrollments.approve', [props.event.slug, id]))
}

function reject(id) {
  router.post(route('lms.admin.enrollments.reject', [props.event.slug, id]))
}

function unenroll(enrollment) {
  const userName = enrollment.user?.name || 'участника'
  const courseName = enrollment.course?.title || 'курса'
  if (!confirm(`Отписать ${userName} от ${courseName}? Прогресс обучения будет удалён.`)) return
  router.delete(route('lms.admin.enrollments.destroy', [props.event.slug, enrollment.id]))
}

function remove(enrollment) {
  const userName = enrollment.user?.name || 'запись'
  if (!confirm(`Удалить запись ${userName}? Это действие нельзя отменить.`)) return
  router.delete(route('lms.admin.enrollments.destroy', [props.event.slug, enrollment.id]))
}

const showReassignModal = ref(false)
const reassignEnrollment = ref(null)
const reassignCourseId = ref(null)

const availableCoursesForReassign = computed(() => {
  if (!reassignEnrollment.value) return []
  return (props.courses || []).filter(c => c.id !== reassignEnrollment.value.course?.id)
})

function openReassign(enrollment) {
  reassignEnrollment.value = enrollment
  reassignCourseId.value = null
  showReassignModal.value = true
}

function submitReassign() {
  if (!reassignCourseId.value || !reassignEnrollment.value) return
  router.post(
    route('lms.admin.enrollments.reassign', [props.event.slug, reassignEnrollment.value.id]),
    { course_id: reassignCourseId.value },
    { onSuccess: () => { showReassignModal.value = false } }
  )
}

function statusVariant(status) {
  return { pending: 'warning', enrolled: 'success', rejected: 'error', in_progress: 'info', completed: 'success' }[status] || 'neutral'
}

function statusLabel(status) {
  return { pending: 'Ожидает', enrolled: 'Одобрена', rejected: 'Отклонена', in_progress: 'В процессе', completed: 'Завершён' }[status] || status
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>
