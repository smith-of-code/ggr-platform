<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Отчёты</h1>
        <p class="mt-1 text-sm text-gray-500">Статистика обучения по событию «{{ event.title }}»</p>
      </div>
      <RButton variant="primary" @click="showEmailDialog = true">
        <template #icon>
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
        </template>
        Отправить на почту
      </RButton>
    </div>

    <!-- Summary cards -->
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <RCard v-for="card in summaryCards" :key="card.label" class="relative overflow-hidden">
        <div :class="['absolute -right-3 -top-3 h-16 w-16 rounded-full opacity-10', card.bg]" />
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ card.label }}</p>
        <p :class="['mt-1 text-3xl font-black', card.color]">{{ card.value }}</p>
        <p v-if="card.sub" class="mt-0.5 text-xs text-gray-400">{{ card.sub }}</p>
      </RCard>
    </div>

    <!-- Tabs -->
    <div class="mb-6 flex rounded-xl bg-gray-100 p-1">
      <button
        v-for="t in tabs"
        :key="t.id"
        :class="[
          'rounded-lg px-4 py-2 text-sm font-medium transition',
          activeTab === t.id ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700',
        ]"
        @click="activeTab = t.id"
      >
        {{ t.label }}
        <span class="ml-1 text-xs text-gray-400">({{ t.count }})</span>
      </button>
    </div>

    <!-- Courses tab -->
    <div v-show="activeTab === 'courses'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Курс</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Записано</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">В процессе</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Завершено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% завершения</th>
              <th class="px-5 py-3 w-40 text-xs font-semibold uppercase tracking-wider text-gray-500">Прогресс</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="c in courseStats" :key="c.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ c.title }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ c.enrolled }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-amber-600 font-medium">{{ c.in_progress }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ c.completed }}</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-900">{{ coursePct(c) }}%</td>
              <td class="px-5 py-3.5">
                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                  <div class="h-full rounded-full bg-green-500 transition-all" :style="{ width: coursePct(c) + '%' }" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="courseStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Курсов пока нет</div>
      </RCard>
    </div>

    <!-- Tests tab -->
    <div v-show="activeTab === 'tests'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Тест</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Попыток</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Участников</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Сдало</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% сдачи</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. балл</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="t in testStats" :key="t.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ t.title }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ t.total_attempts }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ t.attempted }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ t.passed }}</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold" :class="testPassPct(t) >= 70 ? 'text-green-600' : testPassPct(t) >= 40 ? 'text-amber-600' : 'text-red-600'">
                {{ testPassPct(t) }}%
              </td>
              <td class="px-5 py-3.5 text-center text-sm font-medium text-gray-900">{{ Math.round(t.avg_score) }}%</td>
            </tr>
          </tbody>
        </table>
        <div v-if="testStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Тестов пока нет</div>
      </RCard>
    </div>

    <!-- Assignments tab -->
    <div v-show="activeTab === 'assignments'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Задание</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Сдано работ</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">На проверке</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Принято</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Отклонено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% принятия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="a in assignmentStats" :key="a.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ a.title }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ a.submitted }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-amber-600 font-medium">{{ a.pending }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ a.approved }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-red-600 font-medium">{{ a.rejected }}</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-900">
                {{ a.submitted > 0 ? Math.round(a.approved / a.submitted * 100) : 0 }}%
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="assignmentStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Заданий пока нет</div>
      </RCard>
    </div>

    <!-- Users tab -->
    <div v-show="activeTab === 'users'">
      <div class="mb-4">
        <div class="relative max-w-sm">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="userSearch"
            type="text"
            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            placeholder="Поиск по ФИО или email..."
          />
        </div>
      </div>

      <RCard flush>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Курсов</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Заверш.</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Тестов</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. балл</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Заданий</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="u in filteredUsers" :key="u.id" class="transition hover:bg-gray-50">
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-900">{{ u.name }}</p>
                  <p class="text-xs text-gray-400">{{ u.email }}</p>
                </td>
                <td class="px-4 py-3">
                  <RBadge variant="neutral" size="sm">{{ u.role || '—' }}</RBadge>
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ u.courses_enrolled }}</td>
                <td class="px-4 py-3 text-center text-sm font-medium" :class="u.courses_completed > 0 ? 'text-green-600' : 'text-gray-400'">
                  {{ u.courses_completed }}
                </td>
                <td class="px-4 py-3 text-center text-sm" :class="u.tests_passed > 0 ? 'text-green-600 font-medium' : 'text-gray-400'">
                  {{ u.tests_passed }}
                </td>
                <td class="px-4 py-3 text-center text-sm font-medium" :class="u.avg_test_score >= 70 ? 'text-green-600' : u.avg_test_score > 0 ? 'text-amber-600' : 'text-gray-400'">
                  {{ u.avg_test_score > 0 ? u.avg_test_score + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center text-sm" :class="u.assignments_approved > 0 ? 'text-green-600 font-medium' : 'text-gray-400'">
                  {{ u.assignments_approved }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-sm font-bold text-amber-700">{{ u.total_points }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="filteredUsers.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">
          {{ userSearch ? 'Никого не найдено' : 'Участников пока нет' }}
        </div>
      </RCard>
    </div>

    <!-- Email dialog -->
    <RModal v-model="showEmailDialog" title="Отправить отчёт на почту" size="md">
      <form @submit.prevent="sendReport" class="space-y-5">
        <RInput v-model="emailForm.email" type="email" label="Email *" required placeholder="admin@example.com" :error="emailForm.errors?.email" />

        <div>
          <label class="mb-3 block text-sm font-medium text-gray-700">Включить разделы</label>
          <div class="space-y-2">
            <RCheckbox v-model="emailSections.users" label="Участники (детальная таблица)" />
            <RCheckbox v-model="emailSections.courses" label="Курсы (статистика)" />
            <RCheckbox v-model="emailSections.tests" label="Тесты (статистика)" />
          </div>
        </div>

        <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-500">
          Отчёт будет отправлен в формате CSV (открывается в Excel). Кодировка UTF-8 с BOM.
        </div>
      </form>
      <template #footer>
        <RButton variant="outline" @click="showEmailDialog = false">Отмена</RButton>
        <RButton variant="primary" :disabled="!emailForm.email || selectedSections.length === 0" @click="sendReport">
          Отправить
        </RButton>
      </template>
    </RModal>
  </LmsAdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref, reactive, computed } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({
  event: Object,
  summary: Object,
  courseStats: Array,
  testStats: Array,
  assignmentStats: Array,
  userDetails: Array,
})

const tabs = computed(() => [
  { id: 'courses', label: 'Курсы', count: props.courseStats?.length || 0 },
  { id: 'tests', label: 'Тесты', count: props.testStats?.length || 0 },
  { id: 'assignments', label: 'Задания', count: props.assignmentStats?.length || 0 },
  { id: 'users', label: 'Участники', count: props.userDetails?.length || 0 },
])

const activeTab = ref('courses')
const userSearch = ref('')
const showEmailDialog = ref(false)
const emailForm = reactive({ email: '', errors: {} })
const emailSections = reactive({ users: true, courses: true, tests: true })

const selectedSections = computed(() => {
  const s = []
  if (emailSections.users) s.push('users')
  if (emailSections.courses) s.push('courses')
  if (emailSections.tests) s.push('tests')
  return s
})

const summaryCards = computed(() => [
  { label: 'Участников', value: props.summary?.total_users ?? 0, color: 'text-rosatom-600', bg: 'bg-rosatom-500' },
  { label: 'Курсов', value: props.summary?.total_courses ?? 0, color: 'text-blue-600', bg: 'bg-blue-500' },
  { label: 'Тестов', value: props.summary?.total_tests ?? 0, color: 'text-purple-600', bg: 'bg-purple-500' },
  { label: 'Заданий', value: props.summary?.total_assignments ?? 0, color: 'text-amber-600', bg: 'bg-amber-500' },
  { label: 'Ср. завершение курсов', value: (props.summary?.avg_course_completion ?? 0) + '%', color: 'text-green-600', bg: 'bg-green-500', sub: 'от записанных' },
  { label: 'Ср. сдача тестов', value: (props.summary?.avg_test_pass_rate ?? 0) + '%', color: 'text-emerald-600', bg: 'bg-emerald-500', sub: 'от попыток' },
])

const filteredUsers = computed(() => {
  const q = userSearch.value.toLowerCase().trim()
  if (!q) return props.userDetails || []
  return (props.userDetails || []).filter(u =>
    u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)
  )
})

function coursePct(c) {
  return c.enrolled > 0 ? Math.round(c.completed / c.enrolled * 100) : 0
}

function testPassPct(t) {
  return t.attempted > 0 ? Math.round(t.passed / t.attempted * 100) : 0
}

function sendReport() {
  router.post(route('lms.admin.reports.send', props.event.slug), {
    email: emailForm.email,
    sections: selectedSections.value,
  }, {
    onSuccess: () => {
      showEmailDialog.value = false
      emailForm.email = ''
    },
    onError: (errors) => {
      emailForm.errors = errors
    },
  })
}
</script>
