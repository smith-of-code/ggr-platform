<template>
  <div>
    <RCard class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Новый вопрос</h2>
      <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Текст вопроса</label>
          <textarea
            v-model="createForm.body"
            rows="3"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            required
          />
          <p v-if="createForm.errors.body" class="mt-1 text-xs text-red-600">{{ createForm.errors.body }}</p>
        </div>
        <div class="flex flex-wrap gap-4">
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Направление</label>
            <select v-model="createForm.project_key" class="min-w-[12rem] rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
              <option value="">Все направления</option>
              <option v-for="d in directions" :key="d.key" :value="d.key">{{ d.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Порядок</label>
            <input
              v-model.number="createForm.sort_order"
              type="number"
              min="0"
              class="w-24 rounded-lg border border-gray-200 px-3 py-2 text-sm"
              placeholder="авто"
            />
          </div>
          <label class="mt-6 flex cursor-pointer items-center gap-2 text-sm text-gray-700">
            <input v-model="createForm.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#003274]" />
            Активен
          </label>
        </div>
        <RButton type="submit" variant="primary" :loading="createForm.processing" :disabled="createForm.processing">
          Добавить
        </RButton>
      </form>
    </RCard>

    <RCard elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Список</h2>
      <p v-if="!questions.length" class="mt-4 text-sm text-gray-500">Вопросов пока нет.</p>
      <ul v-else class="mt-4 space-y-6 divide-y divide-gray-100">
        <li v-for="q in questions" :key="q.id" class="pt-6 first:pt-0">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 space-y-2">
              <textarea
                :value="drafts[q.id]?.body ?? q.body"
                rows="3"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                @input="(e) => setDraft(q.id, 'body', e.target.value)"
              />
              <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                <span>id: {{ q.id }}</span>
                <RBadge v-if="q.is_active" variant="success" size="sm">активен</RBadge>
                <RBadge v-else variant="neutral" size="sm">выкл</RBadge>
                <span v-if="q.project_key" class="font-mono">{{ q.project_key }}</span>
                <span v-else>все направления</span>
              </div>
            </div>
            <div class="flex shrink-0 flex-col gap-2 sm:items-end">
              <div class="flex flex-wrap gap-2">
                <label class="flex cursor-pointer items-center gap-1 text-xs text-gray-600">
                  <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300"
                    :checked="q.is_active"
                    @change="(e) => patchQuestion(q.id, { is_active: e.target.checked })"
                  />
                  активен
                </label>
                <input
                  type="number"
                  min="0"
                  class="w-20 rounded border border-gray-200 px-2 py-1 text-xs"
                  :value="drafts[q.id]?.sort_order ?? q.sort_order"
                  @change="(e) => patchQuestion(q.id, { sort_order: parseInt(e.target.value, 10) || 0 })"
                />
                <select
                  class="max-w-[10rem] rounded border border-gray-200 px-2 py-1 text-xs"
                  :value="q.project_key ?? ''"
                  @change="(e) => patchQuestion(q.id, { project_key: e.target.value || null })"
                >
                  <option value="">все</option>
                  <option v-for="d in directions" :key="'pk-' + q.id + d.key" :value="d.key">{{ d.label }}</option>
                </select>
              </div>
              <div class="flex flex-wrap gap-2">
                <RButton size="sm" variant="outline" @click="saveBody(q)">Сохранить текст</RButton>
                <button type="button" class="text-xs text-red-600 hover:text-red-800" @click="removeQuestion(q)">Удалить</button>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </RCard>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  questions: { type: Array, default: () => [] },
  directions: { type: Array, default: () => [] },
})

const drafts = reactive({})

function setDraft(id, field, value) {
  if (!drafts[id]) drafts[id] = {}
  drafts[id][field] = value
}

const createForm = useForm({
  body: '',
  project_key: '',
  sort_order: null,
  is_active: true,
})

function submitCreate() {
  createForm.post(route('admin.tour-cabinet.stage2-questions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset()
      createForm.is_active = true
    },
  })
}

function patchQuestion(id, payload) {
  router.patch(route('admin.tour-cabinet.stage2-questions.update', id), payload, { preserveScroll: true })
}

function saveBody(q) {
  const body = drafts[q.id]?.body ?? q.body
  patchQuestion(q.id, { body })
}

function removeQuestion(q) {
  if (!confirm('Удалить вопрос и связанные ответы участников?')) return
  router.delete(route('admin.tour-cabinet.stage2-questions.destroy', q.id), { preserveScroll: true })
}
</script>
