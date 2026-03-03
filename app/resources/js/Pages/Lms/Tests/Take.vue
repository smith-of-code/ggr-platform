<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Тест: ${test?.title} – ${event?.name}`" />
    <div class="mx-auto max-w-3xl space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="font-brand text-xl font-bold text-gray-900">{{ test?.title }}</h1>
        <div
          v-if="test?.time_limit"
          :class="[
            'rounded-lg px-4 py-2 font-mono text-lg font-semibold',
            timeLeft < 300 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-700',
          ]"
        >
          {{ formatTime(timeLeft) }}
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <div
          v-for="(q, qi) in (questions || [])"
          :key="q.id"
          class="rounded-xl border border-gray-200 bg-white shadow-sm p-6"
        >
          <p class="font-medium text-gray-900">
            {{ qi + 1 }}. {{ q.question_text || q.text }}
          </p>
          <div class="mt-4 space-y-3">
            <!-- Single choice -->
            <label
              v-for="a in (q.answers || [])"
              v-show="q.type === 'single'"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50"
              :class="{ 'border-rosatom-500/50 bg-rosatom-50': responses[q.id] === a.id }"
            >
              <input
                type="radio"
                :name="`q-${q.id}`"
                :value="a.id"
                v-model="responses[q.id]"
                class="h-4 w-4 border-gray-300 text-rosatom-500 focus:ring-rosatom-500/20"
              />
              <span class="text-gray-700">{{ a.answer_text || a.text }}</span>
            </label>
            <!-- Multiple choice -->
            <label
              v-for="a in (q.answers || [])"
              v-show="q.type === 'multiple'"
              :key="a.id"
              class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50"
              :class="{ 'border-rosatom-500/50 bg-rosatom-50': (responses[q.id] || []).includes(a.id) }"
            >
              <input
                type="checkbox"
                :value="a.id"
                v-model="responses[q.id]"
                class="h-4 w-4 rounded border-gray-300 text-rosatom-500 focus:ring-rosatom-500/20"
              />
              <span class="text-gray-700">{{ a.answer_text || a.text }}</span>
            </label>
            <!-- Text -->
            <div v-if="q.type === 'text'" class="mt-2">
              <textarea
                v-model="responses[q.id]"
                rows="4"
                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
                :placeholder="'Введите ответ'"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="processing"
            class="rounded-xl bg-rosatom-600 px-8 py-3 font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            <span v-if="processing">Отправка...</span>
            <span v-else>Завершить тест</span>
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
  else responses[q.id] = q.type === 'text' ? '' : null
})

const timeLimit = computed(() => (props.test?.time_limit || 0) * 60)
const timeLeft = ref(timeLimit.value)
let timer = null

onMounted(() => {
  if (props.test?.time_limit) {
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
