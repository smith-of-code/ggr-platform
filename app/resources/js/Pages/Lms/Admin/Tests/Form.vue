<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.tests.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к тестам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ test ? 'Редактировать тест' : 'Новый тест' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">Основные параметры</h2>
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
            <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Лимит времени (мин)</label>
            <input v-model.number="form.time_limit_minutes" type="number" min="0" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Проходной балл (%)</label>
            <input v-model.number="form.passing_score" type="number" min="0" max="100" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Макс. попыток</label>
            <input v-model.number="form.max_attempts" type="number" min="0" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="0 = без ограничений" />
          </div>
        </div>
        <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-5">
          <label class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-300 px-4 py-2.5 transition hover:bg-gray-50" :class="form.shuffle_questions ? 'border-rosatom-500 bg-rosatom-50' : ''">
            <input v-model="form.shuffle_questions" type="checkbox" class="h-4 w-4 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
            <span class="text-sm font-medium text-gray-700">Перемешивать вопросы</span>
          </label>
          <label class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-300 px-4 py-2.5 transition hover:bg-gray-50" :class="form.shuffle_answers ? 'border-rosatom-500 bg-rosatom-50' : ''">
            <input v-model="form.shuffle_answers" type="checkbox" class="h-4 w-4 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
            <span class="text-sm font-medium text-gray-700">Перемешивать ответы</span>
          </label>
          <label class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-300 px-4 py-2.5 transition hover:bg-gray-50" :class="form.show_correct_answers ? 'border-rosatom-500 bg-rosatom-50' : ''">
            <input v-model="form.show_correct_answers" type="checkbox" class="h-4 w-4 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
            <span class="text-sm font-medium text-gray-700">Показывать правильные ответы</span>
          </label>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="mb-4 text-base font-bold text-gray-900">Вопросы</h2>
        <div class="space-y-4">
          <div v-for="(q, qIdx) in form.questions" :key="qIdx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Вопрос {{ qIdx + 1 }}</span>
              <button type="button" @click="form.questions.splice(qIdx, 1)" class="rounded p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <div class="space-y-3">
              <div>
                <input v-model="q.question" type="text" required placeholder="Текст вопроса" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
              </div>
              <div class="flex gap-4">
                <div>
                  <label class="mb-1 block text-xs text-gray-500">Тип</label>
                  <select v-model="q.type" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                    <option value="single">Один ответ</option>
                    <option value="multiple">Несколько ответов</option>
                    <option value="text">Текстовый</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-gray-500">Баллы</label>
                  <input v-model.number="q.points" type="number" min="0" class="w-20 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
                </div>
              </div>
              <div v-if="q.type === 'single' || q.type === 'multiple'" class="space-y-2">
                <label class="block text-xs text-gray-500">Варианты ответов</label>
                <div v-for="(a, aIdx) in q.answers" :key="aIdx" class="flex gap-2">
                  <input v-model="a.answer" type="text" placeholder="Текст ответа" class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
                  <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-500" :class="a.is_correct ? 'border-rosatom-500 bg-rosatom-50 text-rosatom-700' : ''">
                    <input v-model="a.is_correct" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
                    Верно
                  </label>
                  <button v-if="q.answers.length > 1" type="button" @click="q.answers.splice(aIdx, 1)" class="rounded p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <button type="button" @click="q.answers.push({ answer: '', is_correct: false })" class="text-sm text-rosatom-600 hover:text-rosatom-700">+ Добавить ответ</button>
              </div>
            </div>
          </div>
        </div>
        <button type="button" @click="addQuestion" class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 py-3 text-sm font-medium text-gray-500 transition hover:border-rosatom-500 hover:text-rosatom-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Добавить вопрос
        </button>
      </div>

      <div class="flex gap-3">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Сохранить
        </button>
        <Link :href="route('lms.admin.tests.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
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

const form = useForm({
  title: props.test?.title ?? '',
  description: props.test?.description ?? '',
  time_limit_minutes: props.test?.time_limit_minutes ?? null,
  shuffle_questions: props.test?.shuffle_questions ?? false,
  shuffle_answers: props.test?.shuffle_answers ?? false,
  show_correct_answers: props.test?.show_correct_answers ?? true,
  passing_score: props.test?.passing_score ?? null,
  max_attempts: props.test?.max_attempts ?? null,
  questions: buildQuestions(),
})

function addQuestion() {
  form.questions.push({ question: '', type: 'single', points: 1, answers: [{ answer: '', is_correct: false }] })
}

function submit() {
  const questions = form.questions.map((q, i) => ({
    question: q.question,
    type: q.type,
    points: q.points,
    position: i,
    answers: (q.answers || []).map((a, j) => ({ answer: a.answer, is_correct: a.is_correct ?? false, position: j })),
  }))
  if (props.test) {
    form.transform(data => ({ ...data, questions })).put(route('lms.admin.tests.update', [props.event.id, props.test.id]))
  } else {
    form.transform(data => ({ ...data, questions })).post(route('lms.admin.tests.store', props.event.id))
  }
}
</script>
