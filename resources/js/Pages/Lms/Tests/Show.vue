<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${test?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <Link
        :href="route('lms.tests.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к тестам
      </Link>

      <RCard class="p-6 lg:p-8">
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ test?.title }}</h1>
        <p class="mt-3 text-gray-500">{{ test?.description }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          <RCard flush class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Лимит времени</p>
            <p class="font-medium text-gray-900">{{ test?.time_limit_minutes ? `${test.time_limit_minutes} мин` : 'Без ограничения' }}</p>
          </RCard>
          <RCard flush class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Правильных для сдачи</p>
            <p class="font-medium text-gray-900">{{ requiredCorrect }} из {{ test?.questions_count ?? '?' }}</p>
          </RCard>
          <RCard flush class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Макс. попыток</p>
            <p class="font-medium text-gray-900">{{ test?.max_attempts ?? 'Не ограничено' }}</p>
          </RCard>
          <RCard flush class="rounded-lg bg-gray-50 p-4">
            <p class="text-sm text-gray-500">Ваших попыток</p>
            <p class="font-medium text-gray-900">{{ attempts?.length ?? 0 }}</p>
          </RCard>
        </div>

        <div class="mt-8">
          <RButton
            variant="primary"
            :disabled="starting"
            :loading="starting"
            @click="startTest"
          >
            <template #icon>
              <PlayIcon class="h-5 w-5" />
            </template>
            <span v-if="starting">Загрузка...</span>
            <span v-else>Начать тест</span>
          </RButton>
        </div>

        <!-- Past attempts -->
        <div v-if="attempts?.length" class="mt-10">
          <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Предыдущие попытки</h2>
          <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Дата</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Баллы</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Результат</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="a in attempts" :key="a.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(a.completed_at || a.created_at) }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ a.score ?? a.percentage }}%</td>
                  <td class="px-4 py-3">
                    <RBadge :variant="a.passed ? 'success' : 'error'" size="sm">
                      {{ a.passed ? 'Сдан' : 'Не сдан' }}
                    </RBadge>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, PlayIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempts: { type: Array, default: () => [] },
})

const starting = ref(false)

const requiredCorrect = computed(() => {
  const total = props.test?.questions_count ?? 0
  const passingScore = props.test?.passing_score ?? 0
  if (!total) return 0
  return Math.ceil(passingScore * total / 100)
})

function startTest() {
  starting.value = true
  router.post(
    route('lms.tests.start', { event: props.event?.slug, test: props.test?.id }),
    {},
    { onFinish: () => { starting.value = false } },
  )
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
