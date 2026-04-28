<template>
  <LmsAdminLayout :event="event">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <div>
        <div class="mb-1">
          <Link :href="route('lms.admin.tests.index', event.slug)" class="text-sm text-rosatom-600 hover:underline">
            ← К списку тестов
          </Link>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Результаты теста</h1>
        <p class="mt-1 text-sm text-gray-500">{{ test.title }}</p>
      </div>
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
      <RCard>
        <p class="text-xs text-gray-500">Всего попыток</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_attempts }}</p>
      </RCard>
      <RCard>
        <p class="text-xs text-gray-500">Завершено</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.completed_attempts }}</p>
      </RCard>
      <RCard>
        <p class="text-xs text-gray-500">Сдано</p>
        <p class="mt-1 text-2xl font-semibold text-green-600">{{ stats.passed_attempts }}</p>
      </RCard>
      <RCard>
        <p class="text-xs text-gray-500">Участников</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.unique_users }}</p>
      </RCard>
      <RCard>
        <p class="text-xs text-gray-500">Средний балл</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.avg_percentage }}%</p>
      </RCard>
    </div>

    <RCard class="mb-6">
      <div class="grid gap-3 md:grid-cols-4">
        <div class="md:col-span-2">
          <label class="mb-1 block text-xs font-medium text-gray-500">Поиск по участнику</label>
          <input
            v-model="form.search"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
            placeholder="ФИО или email"
            @keyup.enter="applyFilters"
          >
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-500">Статус сдачи</label>
          <select v-model="form.status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" @change="applyFilters">
            <option value="all">Все</option>
            <option value="passed">Сдали</option>
            <option value="failed">Не сдали</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <label class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input v-model="form.show_answers" type="checkbox" class="rounded border-gray-300">
            Показать ответы
          </label>
          <RButton variant="primary" @click="applyFilters">Применить</RButton>
        </div>
      </div>
    </RCard>

    <RCard flush>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Попытка</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Балл</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Результат</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Дата</th>
              <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Детали</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="attempt in attempts.data" :key="attempt.id">
              <tr class="transition hover:bg-gray-50">
                <td class="px-5 py-3.5">
                  <p class="text-sm font-medium text-gray-900">{{ attempt.user?.name || 'Участник' }}</p>
                  <p class="text-xs text-gray-400">{{ attempt.user?.email || '—' }}</p>
                </td>
                <td class="px-5 py-3.5 text-center text-sm text-gray-600">#{{ attempt.id }}</td>
                <td class="px-5 py-3.5 text-center text-sm text-gray-900">
                  <template v-if="attempt.status === 'completed'">
                    {{ attempt.score ?? 0 }} / {{ attempt.max_score ?? 0 }}
                    <span class="ml-1 text-xs text-gray-500">({{ attempt.percentage ?? 0 }}%)</span>
                  </template>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                  <RBadge :variant="resultBadgeVariant(attempt)" size="sm">
                    {{ resultLabel(attempt) }}
                  </RBadge>
                </td>
                <td class="px-5 py-3.5 text-center text-sm text-gray-500">
                  {{ formatDate(attempt.finished_at || attempt.started_at) }}
                </td>
                <td class="px-5 py-3.5 text-right">
                  <RButton
                    v-if="filters.show_answers"
                    size="sm"
                    variant="ghost"
                    @click="toggleExpanded(attempt.id)"
                  >
                    {{ expandedAttemptIds.includes(attempt.id) ? 'Скрыть ответы' : 'Показать ответы' }}
                  </RButton>
                </td>
              </tr>

              <tr v-if="filters.show_answers && expandedAttemptIds.includes(attempt.id)">
                <td colspan="6" class="bg-gray-50 px-5 py-4">
                  <div v-if="attempt.responses?.length" class="space-y-3">
                    <div v-for="(response, idx) in attempt.responses" :key="idx" class="rounded-lg border border-gray-200 bg-white p-3">
                      <div class="mb-1 flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-900">{{ response.question }}</p>
                        <RBadge :variant="response.is_correct ? 'success' : 'danger'" size="sm">
                          {{ response.is_correct ? 'Верно' : 'Неверно' }}
                        </RBadge>
                      </div>
                      <p class="text-xs text-gray-500">Баллы: {{ response.points_earned }}</p>
                      <p v-if="response.selected_answers?.length" class="mt-1 text-xs text-gray-700">
                        Ответ участника: {{ response.selected_answers.join('; ') }}
                      </p>
                      <p v-else-if="response.text_answer" class="mt-1 text-xs text-gray-700">
                        Ответ участника: {{ response.text_answer }}
                      </p>
                      <p v-else class="mt-1 text-xs text-gray-400">Ответ отсутствует</p>
                      <p v-if="response.correct_answers?.length" class="mt-1 text-xs text-green-700">
                        Правильный ответ: {{ response.correct_answers.join('; ') }}
                      </p>
                    </div>
                  </div>
                  <p v-else class="text-sm text-gray-500">Ответы отсутствуют</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div v-if="attempts.data.length === 0" class="px-5 py-12 text-center text-sm text-gray-500">
        Попытки не найдены
      </div>
    </RCard>

    <div v-if="attempts.last_page > 1" class="mt-6 flex items-center justify-between">
      <p class="text-xs text-gray-500">{{ attempts.from }}–{{ attempts.to }} из {{ attempts.total }}</p>
      <div class="flex gap-1">
        <button
          v-for="link in attempts.links"
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
import { Link, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({
  event: Object,
  test: Object,
  attempts: Object,
  stats: Object,
  filters: Object,
})

const form = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || 'all',
  show_answers: !!props.filters?.show_answers,
})

const expandedAttemptIds = ref([])

function applyFilters() {
  router.get(route('lms.admin.tests.results', [props.event.slug, props.test.id]), {
    search: form.search || undefined,
    status: form.status && form.status !== 'all' ? form.status : undefined,
    show_answers: form.show_answers ? 1 : undefined,
  }, {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      expandedAttemptIds.value = []
    },
  })
}

function toggleExpanded(attemptId) {
  if (expandedAttemptIds.value.includes(attemptId)) {
    expandedAttemptIds.value = expandedAttemptIds.value.filter((id) => id !== attemptId)
    return
  }
  expandedAttemptIds.value.push(attemptId)
}

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleString('ru-RU')
}

function resultLabel(attempt) {
  if (attempt.status !== 'completed') return 'В процессе'
  return attempt.passed ? 'Сдан' : 'Не сдан'
}

function resultBadgeVariant(attempt) {
  if (attempt.status !== 'completed') return 'neutral'
  return attempt.passed ? 'success' : 'danger'
}
</script>
