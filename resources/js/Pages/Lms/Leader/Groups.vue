<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Группы – Кабинет руководителя`" />
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="font-brand text-2xl font-bold text-gray-900">Группы</h1>
        <Link
          :href="route('lms.leader.dashboard', { event: event?.slug })"
          class="text-sm text-gray-500 hover:text-rosatom-600"
        >
          ← В кабинет
        </Link>
      </div>

      <RCard flush>
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Группа</th>
                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Куратор</th>
                <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Участников</th>
                <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Прогресс</th>
                <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="row in (groups || [])"
                :key="row.group?.id"
                class="transition hover:bg-gray-50"
              >
                <td class="px-6 py-4">
                  <Link
                    :href="route('lms.leader.groups.show', { event: event?.slug, group: row.group?.id })"
                    class="font-medium text-gray-900 hover:text-rosatom-600"
                  >
                    {{ row.group?.title || '–' }}
                  </Link>
                </td>
                <td class="px-6 py-4 text-gray-500">
                  {{ row.group?.curator?.name ?? row.curator?.name ?? '–' }}
                </td>
                <td class="px-6 py-4 text-right text-gray-700">
                  {{ row.group?.members_count ?? 0 }}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <RProgress :percentage="row.progress ?? 0" show-label size="sm" />
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <Link
                    :href="route('lms.leader.groups.show', { event: event?.slug, group: row.group?.id })"
                    class="text-sm font-medium text-rosatom-600 hover:text-rosatom-700"
                  >
                    Подробнее →
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </RCard>

      <RCard v-if="!(groups?.length)" class="py-16 text-center text-gray-400">
        Группы не найдены
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  groups: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
</script>
