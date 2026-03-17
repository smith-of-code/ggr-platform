<template>
  <LmsAdminLayout :event="event">
    <div class="mx-auto max-w-4xl">
      <button
        @click="router.visit(route('lms.admin.users.index', event.slug))"
        class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-rosatom-600"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к участникам
      </button>

      <!-- Success flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
        {{ $page.props.flash.success }}
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Profile card -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-1">
          <div class="flex flex-col items-center text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-rosatom-100 text-2xl font-bold text-rosatom-600">
              {{ initials }}
            </div>
            <h2 class="mt-4 text-lg font-bold text-gray-900">{{ profile.user?.name }}</h2>
            <p v-if="profile.user?.patronymic" class="text-sm text-gray-500">{{ profile.user.patronymic }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ profile.user?.email }}</p>
            <p v-if="profile.user?.phone || profile.phone" class="text-sm text-gray-400">{{ profile.user?.phone || profile.phone }}</p>
            <span
              v-if="profile.lms_role"
              class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold"
              :class="roleBadgeClass(profile.lms_role.slug)"
            >
              {{ profile.lms_role.name }}
            </span>
            <p v-if="profile.position" class="mt-2 text-xs text-gray-400">{{ profile.position }}</p>
          </div>
        </div>

        <!-- Edit form -->
        <div class="space-y-6 lg:col-span-2">
          <form @submit.prevent="submitUpdate" class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
              <h3 class="mb-4 text-lg font-bold text-gray-900">Редактировать профиль</h3>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">ФИО</label>
                  <input v-model="editForm.name" type="text"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">Отчество</label>
                  <input v-model="editForm.patronymic" type="text"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">Телефон</label>
                  <input v-model="editForm.phone" type="tel"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-medium text-gray-700">Должность</label>
                  <input v-model="editForm.position" type="text"
                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                  />
                </div>
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
              <button type="submit" :disabled="editForm.processing"
                class="mt-5 rounded-xl bg-rosatom-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
              >
                {{ editForm.processing ? 'Сохранение...' : 'Сохранить изменения' }}
              </button>
            </div>
          </form>

          <!-- Course assignment -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-bold text-gray-900">Назначенные курсы</h3>

            <div v-if="enrollments?.length" class="mb-4 space-y-2">
              <div v-for="e in enrollments" :key="e.id" class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ e.course?.title }}</p>
                  <p class="text-xs text-gray-400">Статус: {{ enrollmentStatus(e.status) }}</p>
                </div>
                <span
                  class="rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="e.status === 'completed' ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700'"
                >
                  {{ enrollmentStatus(e.status) }}
                </span>
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
              <button
                type="submit"
                :disabled="courseForm.processing || courseForm.course_ids.length === 0"
                class="mt-3 rounded-xl bg-rosatom-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
              >
                Назначить курсы
              </button>
            </form>
          </div>
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
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  profile: Object,
  enrollments: Array,
  roles: Array,
  courses: Array,
})

const initials = computed(() => {
  const name = props.profile?.user?.name || ''
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const editForm = useForm({
  name: props.profile?.user?.name || '',
  patronymic: props.profile?.user?.patronymic || '',
  phone: props.profile?.user?.phone || props.profile?.phone || '',
  position: props.profile?.position || '',
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
  return { enrolled: 'Записан', in_progress: 'Проходит', completed: 'Завершён' }[status] || status
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
</script>
