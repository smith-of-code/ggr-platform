<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Рейтинг – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Рейтинг участников</h1>

      <!-- Current user rank card -->
      <div
        v-if="currentUserEntry"
        class="rounded-xl border-2 border-rosatom-500/50 bg-white p-6 shadow-sm"
      >
        <p class="text-sm font-medium text-gray-500">Ваше место в рейтинге</p>
        <div class="mt-2 flex items-center gap-4">
          <span class="text-3xl font-bold text-rosatom-600">#{{ currentUserEntry.rank }}</span>
          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-full bg-rosatom-50 text-lg font-bold text-rosatom-600"
            >
              {{ (currentUserEntry.user?.name || '?').charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="font-semibold text-gray-900">{{ currentUserEntry.user?.name }}</p>
              <p class="text-lg font-bold text-rosatom-600">{{ currentUserEntry.total_points }} баллов</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User leaderboard -->
      <div>
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Топ участников</h2>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Место</th>
                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Участник</th>
                <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Баллы</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(entry, idx) in leaderboardWithRank"
                :key="entry.user?.id || idx"
                :class="[
                  'transition',
                  isCurrentUser(entry) ? 'bg-rosatom-50' : 'hover:bg-gray-50',
                ]"
              >
                <td class="whitespace-nowrap px-6 py-4">
                  <span
                    :class="[
                      'font-bold',
                      idx < 3 ? 'text-accent-yellow' : 'text-gray-500',
                    ]"
                  >
                    #{{ idx + 1 }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-medium text-gray-700"
                    >
                      {{ (entry.user?.name || '?').charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium text-gray-900">{{ entry.user?.name || '–' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-rosatom-600">
                  {{ entry.total_points ?? 0 }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Group leaderboard (if present) -->
      <div v-if="(groupLeaderboard || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Рейтинг групп</h2>
        <div class="space-y-2">
          <div
            v-for="(entry, idx) in groupLeaderboard"
            :key="entry.group?.id || idx"
            class="flex items-center justify-between rounded-xl border border-gray-200 bg-white shadow-sm px-6 py-4"
          >
            <div class="flex items-center gap-3">
              <span class="font-bold text-gray-500">#{{ idx + 1 }}</span>
              <span class="font-medium text-gray-900">{{ entry.group?.title || '–' }}</span>
            </div>
            <span class="font-semibold text-rosatom-600">{{ entry.total_points ?? 0 }} баллов</span>
          </div>
        </div>
      </div>

      <div
        v-if="!(leaderboardWithRank?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        Рейтинг пока пуст
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  userLeaderboard: { type: Array, default: () => [] },
  groupLeaderboard: { type: Array, default: () => [] },
  userRank: { type: Number, default: null },
  userPoints: { type: Number, default: null },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
const authUserId = computed(() => user.value?.id)

const leaderboardWithRank = computed(() => props.userLeaderboard || [])

const currentUserEntry = computed(() => {
  const list = leaderboardWithRank.value
  const idx = list.findIndex((e) => e.user?.id === authUserId.value)
  if (idx < 0) return null
  return { ...list[idx], rank: idx + 1 }
})

function isCurrentUser(entry) {
  return entry.user?.id === authUserId.value
}
</script>
