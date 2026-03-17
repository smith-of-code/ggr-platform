<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Траектории</h1>
        <p class="mt-1 text-sm text-gray-500">Траектории события «{{ event.title }}»</p>
      </div>
      <Link :href="route('lms.admin.trajectories.create', event.slug)" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Создать траекторию
      </Link>
    </div>

    <RCard flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Название</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Этапов</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="t in trajectories.data" :key="t.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ t.title }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ t.steps?.length ?? 0 }}</td>
            <td class="px-5 py-3.5 text-center">
              <RBadge :variant="t.is_active ? 'success' : 'neutral'">
                {{ t.is_active ? 'Активна' : 'Скрыта' }}
              </RBadge>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('lms.admin.trajectories.edit', [event.slug, t.id])" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <RButton variant="danger" size="sm" iconOnly @click="confirmDestroy(t)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79" /></svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="trajectories.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Траекторий пока нет</div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, trajectories: Object })

function confirmDestroy(trajectory) {
  if (confirm(`Удалить траекторию "${trajectory.title}"?`)) {
    router.delete(route('lms.admin.trajectories.destroy', [props.event.slug, trajectory.id]))
  }
}
</script>
