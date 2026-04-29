<template>
  <RCard id="tour-cabinet-admin-atomic-ticket" class="scroll-mt-8" elevation="raised">
    <h2 class="text-lg font-semibold text-gray-900">Блок «Твой билет в атомный город»</h2>
    <p class="mt-2 text-sm text-gray-600">
      Отдельная секция на дашборде ЛК туров между блоками «Стандартная анкета» и «Конкурс». Две карточки — путь конкурса и путь «за свой счёт». Кнопки прокручивают участника к нужным блокам на этой же странице (конкурс / коммерческие туры) и подсвечивают их.
    </p>

    <form class="mt-6 space-y-6" @submit.prevent="submit">
      <label class="flex items-start gap-3">
        <input
          type="checkbox"
          class="mt-1 h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
          :checked="form.enabled"
          @change="(e) => { form.enabled = e.target.checked }"
        />
        <span class="text-sm text-gray-800">
          Блок активен
          <span class="block text-xs text-gray-500">Снимите галочку, чтобы временно скрыть блок у всех пользователей.</span>
        </span>
      </label>

      <div>
        <label class="block text-sm font-medium text-gray-700">Заголовок блока</label>
        <input
          v-model="form.title"
          type="text"
          maxlength="255"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
          :class="form.errors.title ? 'border-red-400' : ''"
        />
        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-4">
          <h3 class="text-sm font-semibold text-blue-900">Левая карточка — конкурс</h3>
          <p class="mt-1 text-xs text-blue-900/80">Кнопка ведёт к блоку «Конкурс» (#tour-cabinet-contest-detail).</p>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Заголовок карточки</label>
              <input
                v-model="form.free.title"
                type="text"
                maxlength="255"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
              />
              <p v-if="form.errors['free.title']" class="mt-1 text-xs text-red-600">{{ form.errors['free.title'] }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Текст кнопки</label>
              <input
                v-model="form.free.cta_label"
                type="text"
                maxlength="100"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
              />
              <p v-if="form.errors['free.cta_label']" class="mt-1 text-xs text-red-600">{{ form.errors['free.cta_label'] }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-700">Шаги (до 10)</span>
                <RButton
                  type="button"
                  variant="outline"
                  size="sm"
                  :disabled="form.free.steps.length >= 10"
                  @click="addStep('free')"
                >
                  Добавить шаг
                </RButton>
              </div>
              <div v-if="form.free.steps.length" class="mt-3 space-y-3">
                <div
                  v-for="(step, i) in form.free.steps"
                  :key="`free-${i}`"
                  class="rounded-lg border border-gray-200 bg-white p-3"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Шаг {{ i + 1 }}</span>
                    <button
                      type="button"
                      class="text-xs font-semibold text-red-600 hover:text-red-700"
                      @click="removeStep('free', i)"
                    >
                      Удалить
                    </button>
                  </div>
                  <input
                    v-model="step.title"
                    type="text"
                    maxlength="255"
                    placeholder="Заголовок шага"
                    class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
                  />
                  <textarea
                    v-model="step.description"
                    rows="2"
                    maxlength="1000"
                    placeholder="Описание"
                    class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
                  />
                </div>
              </div>
              <p v-else class="mt-3 text-xs text-gray-500">Шагов пока нет.</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4">
          <h3 class="text-sm font-semibold text-amber-900">Правая карточка — коммерческие туры</h3>
          <p class="mt-1 text-xs text-amber-900/80">Кнопка ведёт к блоку «Коммерческие туры» (#tour-cabinet-commerce-tours).</p>

          <div class="mt-4 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-700">Заголовок карточки</label>
              <input
                v-model="form.paid.title"
                type="text"
                maxlength="255"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
              />
              <p v-if="form.errors['paid.title']" class="mt-1 text-xs text-red-600">{{ form.errors['paid.title'] }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700">Текст кнопки</label>
              <input
                v-model="form.paid.cta_label"
                type="text"
                maxlength="100"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
              />
              <p v-if="form.errors['paid.cta_label']" class="mt-1 text-xs text-red-600">{{ form.errors['paid.cta_label'] }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-700">Шаги (до 10)</span>
                <RButton
                  type="button"
                  variant="outline"
                  size="sm"
                  :disabled="form.paid.steps.length >= 10"
                  @click="addStep('paid')"
                >
                  Добавить шаг
                </RButton>
              </div>
              <div v-if="form.paid.steps.length" class="mt-3 space-y-3">
                <div
                  v-for="(step, i) in form.paid.steps"
                  :key="`paid-${i}`"
                  class="rounded-lg border border-gray-200 bg-white p-3"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Шаг {{ i + 1 }}</span>
                    <button
                      type="button"
                      class="text-xs font-semibold text-red-600 hover:text-red-700"
                      @click="removeStep('paid', i)"
                    >
                      Удалить
                    </button>
                  </div>
                  <input
                    v-model="step.title"
                    type="text"
                    maxlength="255"
                    placeholder="Заголовок шага"
                    class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
                  />
                  <textarea
                    v-model="step.description"
                    rows="2"
                    maxlength="1000"
                    placeholder="Описание"
                    class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
                  />
                </div>
              </div>
              <p v-else class="mt-3 text-xs text-gray-500">Шагов пока нет.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить
        </RButton>
      </div>
    </form>
  </RCard>
</template>

<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  block: {
    type: Object,
    default: () => ({
      enabled: true,
      title: '',
      free: { title: '', cta_label: '', steps: [] },
      paid: { title: '', cta_label: '', steps: [] },
    }),
  },
})

function cloneCard(card) {
  return {
    title: card?.title ?? '',
    cta_label: card?.cta_label ?? '',
    steps: Array.isArray(card?.steps)
      ? card.steps.map((s) => ({
          title: s?.title ?? '',
          description: s?.description ?? '',
        }))
      : [],
  }
}

const form = useForm({
  enabled: !!props.block.enabled,
  title: props.block.title ?? '',
  free: cloneCard(props.block.free),
  paid: cloneCard(props.block.paid),
})

watch(
  () => props.block,
  (next) => {
    form.enabled = !!next.enabled
    form.title = next.title ?? ''
    form.free = cloneCard(next.free)
    form.paid = cloneCard(next.paid)
  },
  { deep: true },
)

function addStep(side) {
  form[side].steps.push({ title: '', description: '' })
}

function removeStep(side, index) {
  form[side].steps.splice(index, 1)
}

function submit() {
  form.put(route('admin.tour-cabinet.atomic-ticket.update'), {
    preserveScroll: true,
  })
}
</script>
