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
                      <p class="text-xs text-gray-400">{{ e.user?.email }}</p>
                    </div>
                  </div>
                </td>
                <td v-if="!course" class="px-6 py-4 text-gray-600">{{ e.course?.title }}</td>
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
                      <RButton variant="danger" size="sm" @click="reject(e.id)">Отклонить</RButton>
                    </template>
                    <template v-else-if="e.status === 'rejected'">
                      <RButton variant="outline" size="sm" @click="approve(e.id)">Одобрить</RButton>
                    </template>
                    <span v-else class="text-xs text-gray-300">—</span>
                  </div>
                </td>
              </tr>
              <tr v-if="!enrollmentsList.length">
                <td :colspan="course ? 5 : 6" class="px-6 py-12 text-center text-sm text-gray-400">
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
  </LmsAdminLayout>
</template>

<script setup>
import { computed } from 'vue'
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
