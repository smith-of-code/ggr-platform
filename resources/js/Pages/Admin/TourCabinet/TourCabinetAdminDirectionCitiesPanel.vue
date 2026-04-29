<template>
  <div>
    <div class="mb-6 flex flex-wrap gap-2">
      <Link
        v-for="d in directions"
        :key="d.key"
        :href="`${route('admin.tour-cabinet.index', { direction_id: d.key })}#tour-cabinet-admin-cities`"
        class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium transition"
        :class="
          d.key === directionId
            ? 'bg-[#003274] text-white'
            : 'border border-gray-200 bg-white text-gray-700 hover:border-[#003274]/40 hover:bg-gray-50'
        "
      >
        {{ d.label }}
      </Link>
    </div>

    <RCard class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Добавить город</h2>
      <form class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end" @submit.prevent="submitAdd">
        <input v-model="addForm.direction_id" type="hidden" />
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Город</label>
          <select
            v-model="addForm.city_id"
            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
            required
          >
            <option disabled value="">Выберите город</option>
            <option v-for="c in cityOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="addForm.errors.city_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.city_id }}</p>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-gray-600">Форма Этапа 1 (опционально)</label>
          <select
            v-model="addForm.lms_form_slug"
            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
          >
            <option value="">— Без формы (автозавершение) —</option>
            <option v-for="f in activeFormOptions" :key="f.slug" :value="f.slug">{{ f.title }}</option>
          </select>
          <p v-if="addForm.errors.lms_form_slug" class="mt-1 text-xs text-red-600">{{ addForm.errors.lms_form_slug }}</p>
          <p v-else class="mt-1 text-xs text-gray-400">Если форма не выбрана — статус города в ЛК будет «Заполнено» автоматически.</p>
        </div>
        <label class="flex cursor-pointer items-center gap-2 self-end pb-2 text-sm text-gray-700 sm:self-auto">
          <input v-model="addForm.needs_more_data" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#003274]" />
          Нужно больше данных
        </label>
        <RButton
          type="submit"
          variant="primary"
          :loading="addForm.processing"
          :disabled="addForm.processing || !cityOptions.length"
          class="self-end"
        >
          Добавить
        </RButton>
      </form>
      <p v-if="!cityOptions.length" class="mt-3 text-xs text-amber-700">Все активные города уже в списке для этого направления.</p>
    </RCard>

    <RCard elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Текущий список</h2>
      <p class="mt-1 text-xs text-gray-500">
        Если форма Этапа 1 не задана — у города в ЛК автоматически статус «Заполнено» (используется глобальный fallback из блока «Формы и этап 1», если он настроен).
      </p>
      <div v-if="!rows.length" class="mt-4 text-sm text-gray-500">Пока пусто — добавьте города выше.</div>
      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead>
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
              <th class="py-2 pr-4">Город</th>
              <th class="py-2 pr-4">Форма Этапа 1</th>
              <th class="py-2 pr-4">Пометка</th>
              <th class="py-2 pr-4">Порядок</th>
              <th class="py-2 pr-0 text-right">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in rows" :key="row.id">
              <td class="py-3 pr-4">
                <span class="font-medium text-gray-900">{{ row.city?.name }}</span>
                <span class="ml-2 font-mono text-xs text-gray-400">{{ row.city?.slug }}</span>
                <RBadge v-if="row.city && !row.city.is_active" class="ml-2" variant="neutral" size="sm">неактивен</RBadge>
                <p v-if="row.submissions_count > 0" class="mt-1 text-xs text-amber-700">{{ row.submissions_count }} сабмит(ов) от участников</p>
              </td>
              <td class="py-3 pr-4">
                <select
                  class="w-full min-w-[14rem] max-w-xs rounded-lg border border-gray-200 px-2 py-1 text-sm"
                  :value="row.lms_form_slug ?? ''"
                  @change="(e) => onFormSlugChange(row, e.target.value)"
                >
                  <option value="">— Без формы —</option>
                  <option v-for="f in activeFormOptions" :key="f.slug" :value="f.slug">{{ f.title }}</option>
                </select>
              </td>
              <td class="py-3 pr-4">
                <label class="inline-flex cursor-pointer items-center gap-2 text-gray-700">
                  <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-[#003274]"
                    :checked="row.needs_more_data"
                    @change="(e) => patchRow(row, { needs_more_data: e.target.checked })"
                  />
                  <span class="text-xs">больше данных</span>
                </label>
              </td>
              <td class="py-3 pr-4">
                <input
                  type="number"
                  min="0"
                  class="w-20 rounded-lg border border-gray-200 px-2 py-1 text-sm"
                  :value="row.position"
                  @change="(e) => patchRow(row, { position: parseInt(e.target.value, 10) || 0 })"
                />
              </td>
              <td class="py-3 pr-0 text-right">
                <button type="button" class="text-xs font-medium text-red-600 hover:text-red-800" @click="destroyRow(row)">
                  Удалить
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </RCard>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  directions: { type: Array, default: () => [] },
  directionId: { type: [Number, String], default: null },
  rows: { type: Array, default: () => [] },
  cityOptions: { type: Array, default: () => [] },
  allFormsOptions: { type: Array, default: () => [] },
})

const activeFormOptions = computed(() => props.allFormsOptions.filter((f) => f.is_active !== false))

const addForm = useForm({
  direction_id: props.directionId,
  city_id: '',
  needs_more_data: false,
  lms_form_slug: '',
})

watch(
  () => props.directionId,
  (id) => {
    addForm.direction_id = id
    addForm.city_id = ''
    addForm.needs_more_data = false
    addForm.lms_form_slug = ''
  },
)

function submitAdd() {
  addForm
    .transform((data) => ({
      ...data,
      lms_form_slug: data.lms_form_slug === '' ? null : data.lms_form_slug,
    }))
    .post(route('admin.tour-cabinet.direction-cities.store'), {
      preserveScroll: true,
      onSuccess: () => {
        addForm.city_id = ''
        addForm.needs_more_data = false
        addForm.lms_form_slug = ''
      },
    })
}

function patchRow(row, payload) {
  router.patch(route('admin.tour-cabinet.direction-cities.update', row.id), payload, { preserveScroll: true })
}

function onFormSlugChange(row, value) {
  const next = value === '' ? null : value
  if (row.submissions_count > 0) {
    const ok = window.confirm(
      `У города уже есть ${row.submissions_count} сабмит(ов) от участников. Если поменять/убрать форму — старые ответы останутся, новые участники получат другую форму. Продолжить?`,
    )
    if (!ok) return
  }
  patchRow(row, { lms_form_slug: next })
}

function destroyRow(row) {
  if (!confirm(`Убрать «${row.city?.name}» из направления?`)) return
  router.delete(route('admin.tour-cabinet.direction-cities.destroy', row.id), { preserveScroll: true })
}
</script>
