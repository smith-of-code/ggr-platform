<template>
  <div>
    <p v-if="locked" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
      <template v-if="contestStage === 1">Этот этап станет доступен для заполнения после завершения этапа 1.</template>
      <template v-else>Просмотр ответов этапа 2. Редактирование недоступно после перехода к этапу 3.</template>
    </p>

    <form v-if="!locked" class="mt-8 space-y-6" @submit.prevent="submit">
      <template v-if="questions.length">
        <div v-for="q in questions" :key="q.id" class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-sm font-medium text-gray-900">{{ q.body }}</p>
          <textarea
            v-model="form.answers[q.id]"
            rows="4"
            class="mt-3 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            required
          />
          <p v-if="form.errors['answers.' + q.id]" class="mt-1 text-xs text-red-600">{{ form.errors['answers.' + q.id] }}</p>
        </div>
      </template>
      <div v-else class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Для вашего направления пока нет активных вопросов этапа 2. Нажмите «Далее», чтобы перейти к этапу 3.
      </div>

      <div v-if="form.errors.answers" class="text-sm text-red-600">{{ form.errors.answers }}</div>

      <div class="flex flex-wrap gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
          {{ questions.length ? 'Сохранить и перейти к этапу 3' : 'Перейти к этапу 3' }}
        </RButton>
      </div>
    </form>

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

const form = useForm({ answers: {} })

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

function submit() {
  form.post(route('tour-cabinet.contest.stage2.store'), { preserveScroll: true })
}
</script>
