<template>
  <div class="rounded-xl border border-gray-200 bg-white">
    <!-- Phase: RESULT (just completed) -->
    <div v-if="phase === 'result'" class="p-5">
      <div
        :class="[
          'flex flex-col items-center justify-center rounded-xl py-10',
          latestResult?.passed ? 'bg-accent-green/10' : 'bg-red-50',
        ]"
      >
        <div class="text-5xl font-bold" :class="latestResult?.passed ? 'text-accent-green' : 'text-red-600'">
          {{ latestResult?.percentage ?? 0 }}%
        </div>
        <p class="mt-2 text-lg font-medium" :class="latestResult?.passed ? 'text-accent-green' : 'text-red-600'">
          {{ latestResult?.passed ? 'Тест сдан!' : 'Тест не сдан' }}
        </p>
        <p class="mt-1 text-sm text-gray-500">
          {{ latestResult?.score ?? 0 }} / {{ latestResult?.max_score ?? 100 }} баллов
        </p>
      </div>

      <div class="mt-4">
        <RProgress
          :percentage="Math.min(latestResult?.percentage ?? 0, 100)"
          label="Результат"
          show-label
          size="md"
          :variant="latestResult?.passed ? 'success' : 'error'"
        />
        <p class="mt-1 text-xs text-gray-400">Для сдачи нужно: {{ requiredCorrect }} из {{ test.questions_count }} правильных</p>
      </div>

      <RButton
        v-if="canRetake"
        variant="outline"
        class="mt-4"
        @click="showInfo = true"
      >
        Пройти ещё раз
      </RButton>
      <button
        v-if="showInfo"
        type="button"
        class="mt-2 text-sm text-rosatom-600 hover:underline"
        @click="showInfo = false"
      >
        Скрыть информацию
      </button>
    </div>

    <!-- Phase: INFO (test overview + start button) -->
    <div v-if="phase === 'info' || showInfo" class="p-5">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
          <ClipboardDocumentListIcon class="h-5 w-5 text-amber-600" />
        </div>
        <div>
          <p class="font-medium text-gray-900">{{ test.title }}</p>
          <p v-if="test.description" class="mt-1 text-sm text-gray-500">{{ test.description }}</p>
        </div>
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <div class="rounded-lg bg-gray-50 p-3">
          <p class="text-xs text-gray-500">Лимит времени</p>
          <p class="text-sm font-medium text-gray-900">{{ test.time_limit_minutes ? `${test.time_limit_minutes} мин` : 'Без ограничения' }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 p-3">
          <p class="text-xs text-gray-500">Правильных для сдачи</p>
          <p class="text-sm font-medium text-gray-900">{{ requiredCorrect }} из {{ test.questions_count }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 p-3">
          <p class="text-xs text-gray-500">Макс. попыток</p>
          <p class="text-sm font-medium text-gray-900">{{ test.max_attempts ?? 'Не ограничено' }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 p-3">
          <p class="text-xs text-gray-500">Ваших попыток</p>
          <p class="text-sm font-medium text-gray-900">{{ attempts?.length ?? 0 }}</p>
        </div>
      </div>

      <RButton
        v-if="canRetake || phase === 'info'"
        variant="primary"
        class="mt-4"
        :disabled="starting"
        :loading="starting"
        @click="startTest"
      >
        {{ attempts?.length ? 'Пройти ещё раз' : 'Начать тест' }}
      </RButton>

      <!-- Past attempts -->
      <div v-if="attempts?.length" class="mt-6">
        <p class="mb-2 text-sm font-semibold text-gray-700">Предыдущие попытки</p>
        <div class="overflow-hidden rounded-lg border border-gray-200">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Дата</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Баллы</th>
                <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Результат</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="a in attempts" :key="a.id">
                <td class="px-3 py-2 text-gray-700">{{ formatDate(a.finished_at || a.started_at) }}</td>
                <td class="px-3 py-2 text-gray-700">{{ a.percentage ?? a.score ?? '–' }}%</td>
                <td class="px-3 py-2">
                  <RBadge :variant="a.passed ? 'success' : (a.status === 'in_progress' ? 'warning' : 'error')" size="sm">
                    {{ a.passed ? 'Сдан' : (a.status === 'in_progress' ? 'В процессе' : 'Не сдан') }}
                  </RBadge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Phase: TAKING (questions) -->
    <div v-if="phase === 'taking'" class="p-5">
      <div class="flex items-center justify-between">
        <p class="font-brand text-lg font-bold text-gray-900">{{ test.title }}</p>
        <div
          v-if="test.time_limit_minutes"
          :class="[
            'rounded-lg px-3 py-1.5 font-mono text-base font-semibold',
            timeLeft < 300 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-700',
          ]"
        >
          {{ formatTime(timeLeft) }}
        </div>
      </div>

      <form @submit.prevent="submitTest" class="mt-4 space-y-4">
        <div
          v-for="(q, qi) in questions"
          :key="q.id"
          class="rounded-xl border border-gray-200 bg-gray-50 p-4"
        >
          <p class="text-sm font-semibold text-gray-900">{{ qi + 1 }}. {{ q.text }}</p>

          <div v-if="q.type === 'single'" class="mt-3 space-y-1.5">
            <label
              v-for="a in (q.answers || [])"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition"
              :class="responses[q.id] === a.id ? 'border-rosatom-500 bg-rosatom-50' : 'border-gray-200 bg-white hover:bg-gray-50'"
            >
              <input type="radio" :name="`q_${q.id}`" :value="a.id" v-model="responses[q.id]" class="h-4 w-4 text-rosatom-600 focus:ring-rosatom-500" />
              <span class="text-gray-700">{{ a.text }}</span>
            </label>
          </div>

          <div v-else-if="q.type === 'multiple'" class="mt-3 space-y-1.5">
            <label
              v-for="a in (q.answers || [])"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition"
              :class="(responses[q.id] || []).includes(a.id) ? 'border-rosatom-500 bg-rosatom-50' : 'border-gray-200 bg-white hover:bg-gray-50'"
            >
              <input type="checkbox" :value="a.id" :checked="(responses[q.id] || []).includes(a.id)" @change="toggleMultiple(q.id, a.id, $event.target.checked)" class="h-4 w-4 rounded text-rosatom-600 focus:ring-rosatom-500" />
              <span class="text-gray-700">{{ a.text }}</span>
            </label>
          </div>

          <div v-else-if="q.type === 'text'" class="mt-3">
            <textarea
              v-model="responses[q.id]"
              rows="3"
              placeholder="Введите ваш ответ..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
          </div>
        </div>

        <div class="flex justify-end">
          <RButton variant="primary" :loading="submitting" :disabled="submitting">
            {{ submitting ? 'Отправка...' : 'Завершить тест' }}
          </RButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  test: { type: Object, required: true },
  attempts: { type: Array, default: () => [] },
  activeAttempt: { type: Object, default: null },
  questions: { type: Array, default: null },
  latestResult: { type: Object, default: null },
  returnUrl: { type: String, required: true },
})

const starting = ref(false)
const submitting = ref(false)
const showInfo = ref(false)

const responses = reactive({})
if (props.questions) {
  props.questions.forEach(q => {
    if (q.type === 'multiple') responses[q.id] = []
    else if (q.type === 'text') responses[q.id] = ''
    else responses[q.id] = null
  })
}

const phase = computed(() => {
  if (props.activeAttempt && props.questions) return 'taking'
  if (props.latestResult) return 'result'
  return 'info'
})

const requiredCorrect = computed(() => {
  const total = props.test?.questions_count ?? 0
  const passingScore = props.test?.passing_score ?? 0
  if (!total) return 0
  return Math.ceil(passingScore * total / 100)
})

const canRetake = computed(() => {
  if (!props.test?.max_attempts) return true
  return (props.attempts?.length ?? 0) < props.test.max_attempts
})

const timeLimit = computed(() => (props.test?.time_limit_minutes || 0) * 60)
const timeLeft = ref(timeLimit.value)
let timer = null

onMounted(() => {
  if (phase.value === 'taking' && props.test?.time_limit_minutes) {
    timer = setInterval(() => {
      timeLeft.value = Math.max(0, timeLeft.value - 1)
      if (timeLeft.value <= 0 && timer) {
        clearInterval(timer)
        submitTest()
      }
    }, 1000)
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

function toggleMultiple(qId, aId, checked) {
  if (!responses[qId]) responses[qId] = []
  if (checked) {
    if (!responses[qId].includes(aId)) responses[qId].push(aId)
  } else {
    const idx = responses[qId].indexOf(aId)
    if (idx >= 0) responses[qId].splice(idx, 1)
  }
}

function startTest() {
  starting.value = true
  router.post(
    route('lms.tests.start', { event: props.event?.slug, test: props.test?.id }),
    { return_url: props.returnUrl },
    { onFinish: () => { starting.value = false } },
  )
}

function submitTest() {
  submitting.value = true
  router.post(
    route('lms.tests.submit', {
      event: props.event?.slug,
      test: props.test?.id,
      attempt: props.activeAttempt?.id,
    }),
    { responses: { ...responses }, return_url: props.returnUrl },
    { preserveScroll: true, onFinish: () => { submitting.value = false } },
  )
}

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${s.toString().padStart(2, '0')}`
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>
