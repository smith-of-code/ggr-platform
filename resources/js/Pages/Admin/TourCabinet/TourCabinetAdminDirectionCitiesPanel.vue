<template>
  <div>
    <div class="mb-6 flex flex-wrap gap-2">
      <Link
        v-for="d in directions"
        :key="d.key"
        :href="`${route('admin.tour-cabinet.index', { project_key: d.key })}#tour-cabinet-admin-cities`"
        class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium transition"
        :class="
          d.key === projectKey
            ? 'bg-[#003274] text-white'
            : 'border border-gray-200 bg-white text-gray-700 hover:border-[#003274]/40 hover:bg-gray-50'
        "
      >
        {{ d.label }}
      </Link>
    </div>

    <RCard class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Добавить город</h2>
      <form class="mt-4 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end" @submit.prevent="submitAdd">
        <input v-model="addForm.project_key" type="hidden" />
        <div class="min-w-[12rem] flex-1">
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
        <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
          <input v-model="addForm.needs_more_data" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#003274]" />
          Нужно больше данных
        </label>
        <RButton type="submit" variant="primary" :loading="addForm.processing" :disabled="addForm.processing || !cityOptions.length">
          Добавить
        </RButton>
      </form>
      <p v-if="!cityOptions.length" class="mt-3 text-xs text-amber-700">Все активные города уже в списке для этого направления.</p>
    </RCard>

    <RCard elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Текущий список</h2>
      <div v-if="!rows.length" class="mt-4 text-sm text-gray-500">Пока пусто — добавьте города выше.</div>
      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead>
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
              <th class="py-2 pr-4">Город</th>
              <th class="py-2 pr-4">Пометка</th>
              <th class="py-2 pr-4">Порядок</th>
              <th class="py-2 pr-0 text-right">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in rows" :key="row.id">
              <td class="py-3 pr-4">
                <span class="font-medium text-gray-900">{{ row.city.name }}</span>
                <span class="ml-2 font-mono text-xs text-gray-400">{{ row.city.slug }}</span>
                <RBadge v-if="!row.city.is_active" class="ml-2" variant="neutral" size="sm">неактивен</RBadge>
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
import { watch } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  directions: { type: Array, default: () => [] },
  projectKey: { type: String, required: true },
  rows: { type: Array, default: () => [] },
  cityOptions: { type: Array, default: () => [] },
})

const addForm = useForm({
  project_key: props.projectKey,
  city_id: '',
  needs_more_data: false,
})

watch(
  () => props.projectKey,
  (pk) => {
    addForm.project_key = pk
    addForm.city_id = ''
    addForm.needs_more_data = false
  },
)

function submitAdd() {
  addForm.post(route('admin.tour-cabinet.direction-cities.store'), {
    preserveScroll: true,
    onSuccess: () => {
      addForm.city_id = ''
      addForm.needs_more_data = false
    },
  })
}

function patchRow(row, payload) {
  router.patch(route('admin.tour-cabinet.direction-cities.update', row.id), payload, { preserveScroll: true })
}

function destroyRow(row) {
  if (!confirm(`Убрать «${row.city.name}» из направления?`)) return
  router.delete(route('admin.tour-cabinet.direction-cities.destroy', row.id), { preserveScroll: true })
}
</script>
