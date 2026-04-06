<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.tests.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к тестам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ test ? 'Редактировать тест' : 'Новый тест' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основные параметры</h2>
        </template>
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <RInput
              v-model="form.title"
              label="Название *"
              required
              :error="form.errors.title"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <RInput v-model.number="form.time_limit_minutes" label="Лимит времени (мин)" type="number" />
          </div>
          <div>
            <RInput v-model.number="requiredCorrect" label="Правильных для сдачи" type="number" :min="0" :max="form.questions.length" placeholder="1" />
            <p class="mt-1 text-xs text-gray-400">
              {{ requiredCorrect ?? 0 }} из {{ form.questions.length }} = {{ calculatedPercent }}%
            </p>
          </div>
          <div>
            <RInput v-model.number="form.max_attempts" label="Макс. попыток" type="number" placeholder="0 = без ограничений" />
          </div>
        </div>
        <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-5">
          <RCheckbox v-model="form.in_menu" label="Показывать в меню «Тестирование»" />
          <RCheckbox v-model="form.shuffle_questions" label="Перемешивать вопросы" />
          <RCheckbox v-model="form.shuffle_answers" label="Перемешивать ответы" />
          <RCheckbox v-model="form.show_correct_answers" label="Показывать правильные ответы" />
        </div>
      </RCard>

      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Вопросы</h2>
        </template>
        <div class="space-y-4">
          <div v-for="(q, qIdx) in form.questions" :key="qIdx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Вопрос {{ qIdx + 1 }}</span>
              <RButton variant="danger" size="sm" icon-only type="button" @click="form.questions.splice(qIdx, 1)">
                <template #icon>
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                </template>
              </RButton>
            </div>
            <div class="space-y-3">
              <div>
                <RInput v-model="q.question" required placeholder="Текст вопроса" :error="form.errors[`questions.${qIdx}.question`]" />
              </div>
              <div class="flex gap-4">
                <div>
                  <label class="mb-1 block text-xs text-gray-500">Тип</label>
                  <select v-model="q.type" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
                    <option value="single">Один ответ</option>
                    <option value="multiple">Несколько ответов</option>
                    <option value="text">Текстовый</option>
                  </select>
                </div>
                <div class="w-20">
                  <label class="mb-1 block text-xs text-gray-500">Баллы</label>
                  <RInput v-model.number="q.points" type="number" />
                </div>
              </div>
              <div v-if="q.type === 'single' || q.type === 'multiple'" class="space-y-2">
                <label class="block text-xs text-gray-500">Варианты ответов</label>
                <div v-for="(a, aIdx) in q.answers" :key="aIdx" class="flex gap-2">
                  <RInput v-model="a.answer" placeholder="Текст ответа" class="flex-1" :error="form.errors[`questions.${qIdx}.answers.${aIdx}.answer`]" />
                  <RCheckbox v-model="a.is_correct" label="Верно" />
                  <RButton v-if="q.answers.length > 1" variant="danger" size="sm" icon-only type="button" @click="q.answers.splice(aIdx, 1)">
                    <template #icon>
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                    </template>
                  </RButton>
                </div>
                <RButton variant="ghost" size="sm" type="button" @click="q.answers.push({ answer: '', is_correct: false })">
                  + Добавить ответ
                </RButton>
              </div>
            </div>
          </div>
        </div>
        <RButton variant="outline" block type="button" class="mt-4" @click="addQuestion">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          </template>
          Добавить вопрос
        </RButton>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить
        </RButton>
        <Link :href="route('lms.admin.tests.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, test: Object })

const buildQuestions = () => {
  if (props.test?.questions?.length) {
    return props.test.questions.map(q => ({
      question: q.question,
      type: q.type || 'single',
      points: q.points ?? 1,
      position: q.position ?? 0,
      answers: (q.answers || []).map(a => ({ answer: a.answer, is_correct: a.is_correct ?? false })),
    }))
  }
  return [{ question: '', type: 'single', points: 1, answers: [{ answer: '', is_correct: false }] }]
}

const initialQuestions = buildQuestions()

const initRequiredCorrect = () => {
  const total = initialQuestions.length
  const score = props.test?.passing_score ?? 60
  if (!total) return 1
  return Math.ceil(score * total / 100)
}

const form = useForm({
  title: props.test?.title ?? '',
  description: props.test?.description ?? '',
  time_limit_minutes: props.test?.time_limit_minutes ?? null,
  shuffle_questions: props.test?.shuffle_questions ?? false,
  shuffle_answers: props.test?.shuffle_answers ?? false,
  show_correct_answers: props.test?.show_correct_answers ?? true,
  in_menu: props.test?.in_menu ?? false,
  passing_score: props.test?.passing_score ?? 60,
  max_attempts: props.test?.max_attempts ?? null,
  questions: initialQuestions,
})

const requiredCorrect = ref(initRequiredCorrect())

const calculatedPercent = computed(() => {
  const total = form.questions.length
  if (!total || !requiredCorrect.value) return 0
  return Math.round(requiredCorrect.value / total * 100)
})

watch(calculatedPercent, (val) => {
  form.passing_score = val
})

function addQuestion() {
  form.questions.push({ question: '', type: 'single', points: 1, answers: [{ answer: '', is_correct: false }] })
}

function submit() {
  const questions = form.questions
    .filter(q => q.question.trim() !== '')
    .map((q, i) => ({
      question: q.question,
      type: q.type,
      points: q.points,
      position: i,
      answers: (q.answers || [])
        .filter(a => a.answer.trim() !== '')
        .map((a, j) => ({ answer: a.answer, is_correct: a.is_correct ?? false, position: j })),
    }))
  if (props.test) {
    form.transform(data => ({ ...data, questions })).put(route('lms.admin.tests.update', [props.event.slug, props.test.id]))
  } else {
    form.transform(data => ({ ...data, questions })).post(route('lms.admin.tests.store', props.event.slug))
  }
}
</script>
