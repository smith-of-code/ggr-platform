<template>
  <LmsAdminLayout :event="event">
    <div class="mx-auto max-w-4xl">
      <RButton variant="ghost" size="sm" @click="router.visit(route('lms.admin.users.index', event.slug))" class="mb-4">
        <template #icon><ArrowLeftIcon class="h-4 w-4" /></template>
        Назад к участникам
      </RButton>

      <!-- Success flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
        {{ $page.props.flash.success }}
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Profile card -->
        <RCard elevation="raised" class="lg:col-span-1">
          <div class="flex flex-col items-center text-center">
            <RAvatar :name="profile.user?.name" size="xl" />
            <h2 class="mt-4 text-lg font-bold text-gray-900">{{ profile.user?.last_name }} {{ profile.user?.first_name }}</h2>
            <p v-if="profile.user?.patronymic" class="text-sm text-gray-500">{{ profile.user.patronymic }}</p>
            <p v-if="profile.city" class="text-xs text-gray-400">{{ profile.city }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ profile.user?.email }}</p>
            <p v-if="profile.user?.phone || profile.phone" class="text-sm text-gray-400">{{ profile.user?.phone || profile.phone }}</p>
            <RBadge v-if="profile.lms_role" :variant="roleBadgeVariant(profile.lms_role.slug)" class="mt-3">
              {{ profile.lms_role.name }}
            </RBadge>
            <p v-if="profile.position" class="mt-2 text-xs text-gray-400">{{ profile.position }}</p>
            <div v-if="profile.direction || profile.faculty" class="mt-3 w-full space-y-1 border-t border-gray-100 pt-3 text-left">
              <p v-if="directionLabel" class="text-xs text-gray-500">
                <span class="font-medium text-gray-700">Направление:</span> {{ directionLabel }}
              </p>
              <p v-if="facultyLabel" class="text-xs text-gray-500">
                <span class="font-medium text-gray-700">Факультет:</span> {{ facultyLabel }}
              </p>
              <div class="mt-2 flex items-center gap-2">
                <RBadge v-if="profile.direction_approved_at" variant="success" size="sm">Одобрено</RBadge>
                <template v-else>
                  <RBadge variant="warning" size="sm">Ожидает одобрения</RBadge>
                </template>
              </div>
              <div class="mt-2 flex gap-2">
                <RButton v-if="!profile.direction_approved_at" variant="primary" size="sm" @click="approveDir">
                  Одобрить
                </RButton>
                <RButton v-else variant="outline" size="sm" @click="rejectDir">
                  Отменить одобрение
                </RButton>
              </div>
            </div>
          </div>
        </RCard>

        <!-- Edit form -->
        <div class="space-y-6 lg:col-span-2">
          <form @submit.prevent="submitUpdate" class="space-y-6">
            <RCard elevation="raised">
              <h3 class="mb-4 text-lg font-bold text-gray-900">Редактировать профиль</h3>
              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="editForm.last_name" label="Фамилия" />
                <RInput v-model="editForm.first_name" label="Имя" />
                <RInput v-model="editForm.patronymic" label="Отчество" />
                <RInput v-model="editForm.phone" type="tel" label="Телефон" />
                <RInput v-model="editForm.position" label="Должность" />
                <RInput v-model="editForm.city" label="Город" />
                <div class="sm:col-span-2">
                  <SearchSelect
                    v-model="editForm.role_id"
                    :options="roles"
                    value-key="id"
                    label-key="name"
                    label="Роль"
                    placeholder="Выберите роль"
                  />
                </div>
              </div>
              <RButton type="submit" variant="primary" class="mt-5" :loading="editForm.processing" :disabled="editForm.processing">
                {{ editForm.processing ? 'Сохранение...' : 'Сохранить изменения' }}
              </RButton>
            </RCard>
          </form>

          <!-- Course assignment -->
          <RCard elevation="raised">
            <h3 class="mb-4 text-lg font-bold text-gray-900">Назначенные курсы</h3>

            <div v-if="enrollments?.length" class="mb-4 space-y-2">
              <div v-for="e in enrollments" :key="e.id" class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ e.course?.title }}</p>
                  <p class="text-xs text-gray-400">Статус: {{ enrollmentStatus(e.status) }}</p>
                </div>
                <div class="ml-3 flex items-center gap-2">
                  <RBadge :variant="enrollmentBadgeVariant(e.status)" size="sm">
                    {{ enrollmentStatus(e.status) }}
                  </RBadge>
                  <button
                    type="button"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                    title="Отписать от курса"
                    @click="unenrollFromCourse(e)"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="mb-4 text-sm text-gray-400">Курсы не назначены</div>

            <form @submit.prevent="assignCourses" class="mt-4 border-t border-gray-100 pt-4">
              <MultiSelect
                v-model="courseForm.course_ids"
                :options="availableCourses"
                value-key="id"
                label-key="title"
                label="Добавить курсы"
                placeholder="Выберите курсы для назначения"
              />
              <RButton type="submit" variant="primary" size="sm" class="mt-3" :loading="courseForm.processing" :disabled="courseForm.processing || courseForm.course_ids.length === 0">
                Назначить курсы
              </RButton>
            </form>
          </RCard>

          <!-- Documents -->
          <RCard elevation="raised">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Документы</h3>
              <RButton v-if="documents?.length" variant="outline" size="sm" @click="downloadDocuments">
                <template #icon><ArrowDownTrayIcon class="h-4 w-4" /></template>
                Скачать {{ documents.length > 1 ? 'все' : '' }}
              </RButton>
            </div>

            <div v-if="documents?.length" class="space-y-2">
              <div v-for="doc in documents" :key="doc.id" class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                <DocumentIcon class="h-5 w-5 shrink-0 text-gray-400" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ doc.type_label }}</p>
                  <p class="truncate text-xs text-gray-400">{{ doc.original_name }}</p>
                </div>
                <CheckCircleIcon class="h-5 w-5 shrink-0 text-green-500" />
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">Документы не загружены</p>
          </RCard>
        </div>
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import { ArrowLeftIcon, ArrowDownTrayIcon, DocumentIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  profile: Object,
  enrollments: Array,
  roles: Array,
  courses: Array,
  documents: Array,
  directionLabels: { type: Object, default: () => ({}) },
  facultyLabels: { type: Object, default: () => ({}) },
})

const directionLabel = computed(() => props.directionLabels[props.profile?.direction] || '')
const facultyLabel = computed(() => props.facultyLabels[props.profile?.faculty] || '')


const editForm = useForm({
  last_name: props.profile?.user?.last_name || '',
  first_name: props.profile?.user?.first_name || '',
  patronymic: props.profile?.user?.patronymic || '',
  phone: props.profile?.user?.phone || props.profile?.phone || '',
  position: props.profile?.position || '',
  city: props.profile?.city || '',
  role_id: props.profile?.lms_role_id || null,
})

const courseForm = useForm({
  course_ids: [],
})

const enrolledCourseIds = computed(() => (props.enrollments || []).map(e => e.lms_course_id || e.course?.id))

const availableCourses = computed(() =>
  (props.courses || []).filter(c => !enrolledCourseIds.value.includes(c.id))
)

function submitUpdate() {
  editForm.patch(route('lms.admin.users.update', [props.event.slug, props.profile.user_id]))
}

function assignCourses() {
  courseForm.patch(route('lms.admin.users.update', [props.event.slug, props.profile.user_id]), {
    onSuccess: () => courseForm.reset(),
  })
}

function enrollmentStatus(status) {
  return { enrolled: 'Записан', in_progress: 'Проходит', completed: 'Завершён', pending: 'Ожидает', rejected: 'Отклонён' }[status] || status
}

function enrollmentBadgeVariant(status) {
  return { enrolled: 'success', in_progress: 'info', completed: 'success', pending: 'warning', rejected: 'error' }[status] || 'neutral'
}

function unenrollFromCourse(enrollment) {
  const courseName = enrollment.course?.title || 'курса'
  if (!confirm(`Отписать участника от «${courseName}»? Прогресс обучения будет удалён.`)) return
  router.delete(route('lms.admin.enrollments.destroy', [props.event.slug, enrollment.id]))
}

function approveDir() {
  router.post(route('lms.admin.users.approve-direction', [props.event.slug, props.profile.user_id]), {}, {
    preserveScroll: true,
  })
}

function rejectDir() {
  router.post(route('lms.admin.users.reject-direction', [props.event.slug, props.profile.user_id]), {}, {
    preserveScroll: true,
  })
}

function downloadDocuments() {
  window.location.href = route('lms.admin.users.download-documents', [props.event.slug, props.profile.user_id])
}

function roleBadgeVariant(slug) {
  return { admin: 'error', curator: 'warning', leader: 'primary', expert: 'info', observer: 'neutral', participant: 'success' }[slug] || 'neutral'
}
</script>
