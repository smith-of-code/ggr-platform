<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Тесты</h1>
        <p class="mt-1 text-sm text-gray-500">Тесты события «{{ event.title }}»</p>
      </div>
      <Link :href="route('lms.admin.tests.create', event.slug)" class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Создать тест
      </Link>
    </div>

    <RCard flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Название</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Вопросов</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Попыток</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Проходной балл</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="test in tests.data" :key="test.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ test.title }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ test.questions_count ?? 0 }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ test.attempts_count ?? 0 }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ test.passing_score ?? '—' }}%</td>
            <td class="px-5 py-3.5 text-center">
              <RBadge :variant="test.is_active ? 'primary' : 'neutral'" :dot="true">
                {{ test.is_active ? 'Активен' : 'Скрыт' }}
              </RBadge>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('lms.admin.tests.edit', [event.slug, test.id])" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <RButton variant="danger" size="sm" icon-only @click="confirmDestroy(test)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="tests.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Тестов пока нет</div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, tests: Object })

function confirmDestroy(test) {
  if (confirm(`Удалить тест "${test.title}"?`)) {
    router.delete(route('lms.admin.tests.destroy', [props.event.slug, test.id]))
  }
}
</script>
