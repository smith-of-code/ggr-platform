<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${group?.title} – Группа`" />
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Link
          :href="route('lms.leader.groups', { event: event?.slug })"
          class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          Назад к группам
        </Link>
      </div>

      <div>
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ group?.title }}</h1>
        <p v-if="group?.curator?.name" class="mt-1 text-gray-500">Куратор: {{ group.curator.name }}</p>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Участник</th>
              <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Курсы</th>
              <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Прогресс</th>
              <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Баллы</th>
              <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="m in (members || [])"
              :key="m.user?.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-700"
                  >
                    {{ (m.user?.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ m.user?.name || '–' }}</p>
                    <p class="text-sm text-gray-400">{{ m.user?.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-right text-gray-700">
                {{ m.completed_courses ?? '–' }}/{{ m.total_courses ?? '–' }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <div class="h-2 w-20 overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full bg-rosatom-500"
                      :style="{ width: `${m.progress ?? 0}%` }"
                    />
                  </div>
                  <span class="text-sm text-gray-500">{{ m.progress ?? 0 }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 text-right font-medium text-rosatom-600">
                {{ m.total_points ?? '–' }}
              </td>
              <td class="px-6 py-4 text-right">
                <Link
                  :href="route('lms.leader.users.progress', { event: event?.slug, user: m.user?.id })"
                  class="text-sm font-medium text-rosatom-600 hover:text-rosatom-700"
                >
                  Прогресс →
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="!(members?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        В группе нет участников
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  group: { type: Object, required: true },
  members: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
</script>
