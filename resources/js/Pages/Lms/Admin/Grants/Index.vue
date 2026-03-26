<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Гранты</h1>
        <p class="mt-1 text-sm text-gray-500">Управление грантами и субсидиями</p>
      </div>
      <Link :href="route('lms.admin.grants.create', event.slug)">
        <RButton>
          <template #icon><PlusIcon class="h-4 w-4" /></template>
          Создать грант
        </RButton>
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
              <th class="px-6 py-3 font-medium">Название</th>
              <th class="px-6 py-3 font-medium">Приём заявок</th>
              <th class="px-6 py-3 font-medium">Участники</th>
              <th class="px-6 py-3 font-medium">Статус</th>
              <th class="px-6 py-3 text-right font-medium">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="g in grantsList" :key="g.id" class="transition hover:bg-gray-50/50">
              <td class="px-6 py-4 font-medium text-gray-900">{{ g.title }}</td>
              <td class="px-6 py-4 text-gray-500">{{ formatDateRange(g) }}</td>
              <td class="px-6 py-4 text-gray-500">{{ g.enrollments_count ?? 0 }}</td>
              <td class="px-6 py-4">
                <RBadge :variant="g.is_active ? 'success' : 'neutral'" size="sm">
                  {{ g.is_active ? 'Активен' : 'Неактивен' }}
                </RBadge>
              </td>
              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <Link :href="route('lms.admin.grants.edit', [event.slug, g.id])">
                    <RButton variant="outline" size="sm">Редактировать</RButton>
                  </Link>
                  <RButton variant="ghost" size="sm" class="text-red-500 hover:bg-red-50" type="button" @click="destroy(g)">
                    <template #icon>
                      <TrashIcon class="h-4 w-4" />
                    </template>
                  </RButton>
                </div>
              </td>
            </tr>
            <tr v-if="!grantsList.length">
              <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">Гранты не найдены</td>
            </tr>
          </tbody>
        </table>
      </div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  grants: Object,
})

const grantsList = computed(() => {
  const raw = props.grants?.data || props.grants || []
  return Array.isArray(raw) ? raw : []
})

function destroy(grant) {
  if (!confirm(`Удалить грант «${grant.title}»?`)) return
  router.delete(route('lms.admin.grants.destroy', [props.event.slug, grant.id]))
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateRange(g) {
  const parts = []
  if (g.application_start) parts.push(formatDate(g.application_start))
  if (g.application_end) parts.push(formatDate(g.application_end))
  return parts.join(' – ') || '—'
}
</script>
