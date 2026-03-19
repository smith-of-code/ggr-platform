<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Тест: ${test?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ test?.title }}</h1>
        <div
          v-if="test?.time_limit_minutes"
          :class="[
            'rounded-lg px-4 py-2 font-mono text-lg font-semibold',
            timeLeft < 300 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-700',
          ]"
        >
          {{ formatTime(timeLeft) }}
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <RCard
          v-for="(q, qi) in (questions || [])"
          :key="q.id"
          elevation="raised"
        >
          <p class="text-base font-semibold text-gray-900">
            {{ qi + 1 }}. {{ q.text }}
          </p>

          <!-- Single choice -->
          <div v-if="q.type === 'single'" class="mt-4 space-y-2">
            <label
              v-for="a in (q.answers || [])"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition"
              :class="responses[q.id] === a.id
                ? 'border-rosatom-500 bg-rosatom-50'
                : 'border-gray-200 hover:bg-gray-50'"
            >
              <input
                type="radio"
                :name="`q_${q.id}`"
                :value="a.id"
                v-model="responses[q.id]"
                class="h-4 w-4 text-rosatom-600 focus:ring-rosatom-500"
              />
              <span class="text-sm text-gray-700">{{ a.text }}</span>
            </label>
          </div>

          <!-- Multiple choice -->
          <div v-else-if="q.type === 'multiple'" class="mt-4 space-y-2">
            <label
              v-for="a in (q.answers || [])"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 transition"
              :class="(responses[q.id] || []).includes(a.id)
                ? 'border-rosatom-500 bg-rosatom-50'
                : 'border-gray-200 hover:bg-gray-50'"
            >
              <input
                type="checkbox"
                :value="a.id"
                :checked="(responses[q.id] || []).includes(a.id)"
                @change="toggleMultipleAnswer(q.id, a.id, $event.target.checked)"
                class="h-4 w-4 rounded text-rosatom-600 focus:ring-rosatom-500"
              />
              <span class="text-sm text-gray-700">{{ a.text }}</span>
            </label>
          </div>

          <!-- Text answer -->
          <div v-else-if="q.type === 'text'" class="mt-4">
            <textarea
              v-model="responses[q.id]"
              rows="3"
              placeholder="Введите ваш ответ..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
          </div>
        </RCard>

        <div v-if="!questions?.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
          <p class="text-sm text-gray-400">В этом тесте нет вопросов</p>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="processing"
            class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            {{ processing ? 'Отправка...' : 'Завершить тест' }}
          </button>
        </div>
      </form>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { reactive, ref, computed, onMounted, onUnmounted } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  test: { type: Object, required: true },
  attempt: { type: Object, required: true },
  questions: { type: Array, default: () => [] },
})

const responses = reactive({})
props.questions?.forEach((q) => {
  if (q.type === 'multiple') responses[q.id] = []
  else if (q.type === 'text') responses[q.id] = ''
  else responses[q.id] = null
})

const timeLimit = computed(() => (props.test?.time_limit_minutes || 0) * 60)
const timeLeft = ref(timeLimit.value)
let timer = null

onMounted(() => {
  if (props.test?.time_limit_minutes) {
    timer = setInterval(() => {
      timeLeft.value = Math.max(0, timeLeft.value - 1)
      if (timeLeft.value <= 0 && timer) {
        clearInterval(timer)
        submit()
      }
    }, 1000)
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

function toggleMultipleAnswer(qId, aId, checked) {
  if (!responses[qId]) responses[qId] = []
  if (checked) {
    if (!responses[qId].includes(aId)) responses[qId].push(aId)
  } else {
    const idx = responses[qId].indexOf(aId)
    if (idx >= 0) responses[qId].splice(idx, 1)
  }
}

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${s.toString().padStart(2, '0')}`
}

const processing = ref(false)

function submit() {
  processing.value = true
  router.post(
    route('lms.tests.submit', {
      event: props.event?.slug,
      test: props.test?.id,
      attempt: props.attempt?.id,
    }),
    { responses: { ...responses } },
    { preserveScroll: true, onFinish: () => { processing.value = false } }
  )
}
</script>
