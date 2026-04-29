<template>
  <div>
    <p v-if="locked" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      <template v-if="contestStage === 1">Этот этап станет доступен для заполнения после завершения этапа 1.</template>
      <template v-else>Просмотр ответов этапа 2. Редактирование недоступно после перехода к этапу 3.</template>
    </p>

    <div v-if="!locked" class="mt-8 space-y-6">
      <template v-if="questions.length">
        <div v-for="q in questions" :key="q.id" class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm font-medium text-gray-900">{{ q.body }}</p>
          <p v-if="lengthHint(q)" class="mt-1 text-xs text-gray-500">{{ lengthHint(q) }}</p>
          <textarea
            v-model="form.answers[q.id]"
            rows="6"
            :maxlength="q.max_length || undefined"
            class="mt-3 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            placeholder="Развёрнутый ответ…"
          />
          <p
            v-if="q.min_length || q.max_length"
            class="mt-1 text-xs"
            :class="counterClass(q, form.answers[q.id])"
          >
            {{ counterLabel(q, form.answers[q.id]) }}
          </p>
          <p v-if="form.errors['answers.' + q.id]" class="mt-1 text-xs text-red-600">{{ form.errors['answers.' + q.id] }}</p>
        </div>
        <p class="text-xs text-gray-500">
          «Сохранить» — черновик только у вас. «Отправить» — ответы уйдут организаторам, этап 2 будет считаться завершённым.
        </p>
      </template>
      <div v-else class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Для вашего направления пока нет активных вопросов этапа 2. Нажмите кнопку ниже, чтобы перейти к этапу 3.
      </div>

      <div v-if="form.errors.answers" class="text-sm text-red-600">{{ form.errors.answers }}</div>

      <div class="flex flex-wrap gap-3">
        <template v-if="questions.length">
          <RButton type="button" variant="outline" :loading="form.processing" :disabled="form.processing" @click="saveDraft">
            Сохранить
          </RButton>
          <RButton type="button" variant="primary" :loading="form.processing" :disabled="form.processing" @click="submitFinalize">
            Отправить
          </RButton>
        </template>
        <RButton v-else type="button" variant="primary" :loading="form.processing" :disabled="form.processing" @click="submitFinalize">
          Перейти к этапу 3
        </RButton>
      </div>
    </div>

    <div v-else-if="questions.length" class="mt-8 space-y-4">
      <div v-for="q in questions" :key="q.id" class="rounded-xl border border-gray-200 bg-white p-4">
        <p class="text-sm font-medium text-gray-900">{{ q.body }}</p>
        <p class="mt-3 whitespace-pre-wrap text-sm text-gray-700">{{ q.answer_text || '—' }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  questions: { type: Array, default: () => [] },
  locked: { type: Boolean, default: false },
  contestStage: { type: Number, default: 1 },
})

const form = useForm({
  answers: {},
  finalize: false,
})

watch(
  () => props.questions,
  (list) => {
    for (const q of list) {
      if (form.answers[q.id] === undefined) {
        form.answers[q.id] = q.answer_text ?? ''
      }
    }
  },
  { immediate: true },
)

function saveDraft() {
  form.finalize = false
  form.post(route('tour-cabinet.contest.stage2.store'), { preserveScroll: true })
}

function submitFinalize() {
  form.finalize = true
  form.post(route('tour-cabinet.contest.stage2.store'), { preserveScroll: true })
}

function answerLength(value) {
  if (typeof value !== 'string') return 0
  return Array.from(value.trim()).length
}

function lengthHint(q) {
  const min = q.min_length
  const max = q.max_length
  if (!min && !max) return ''
  if (min && max) return `Ограничения: от ${min} до ${max} символов.`
  if (min) return `Ограничения: не менее ${min} символов.`
  return `Ограничения: не более ${max} символов.`
}

function counterLabel(q, value) {
  const len = answerLength(value)
  const max = q.max_length
  if (max) return `${len} / ${max} символов`
  return `${len} символов`
}

function counterClass(q, value) {
  const len = answerLength(value)
  const min = q.min_length || 0
  const max = q.max_length || 0
  if (max && len > max) return 'text-red-600'
  if (min && len > 0 && len < min) return 'text-amber-600'
  return 'text-gray-500'
}
</script>
