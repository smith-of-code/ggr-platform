<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Рейтинг – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Рейтинг участников</h1>

      <!-- Current user rank card -->
      <RCard v-if="currentUserEntry" class="border-2 border-rosatom-500/50">
        <template #default>
          <p class="text-sm font-medium text-gray-500">Ваше место в рейтинге</p>
          <div class="mt-2 flex items-center gap-4">
            <span class="text-3xl font-bold text-rosatom-600">#{{ currentUserEntry.rank }}</span>
            <div class="flex items-center gap-3">
              <RAvatar
                :name="currentUserEntry.user?.name || '?'"
                size="lg"
              />
              <div>
                <p class="font-semibold text-gray-900">{{ currentUserEntry.user?.name }}</p>
                <p class="text-lg font-bold text-rosatom-600">{{ currentUserEntry.total_points }} баллов</p>
              </div>
            </div>
          </div>
        </template>
      </RCard>

      <!-- User leaderboard -->
      <Leaderboard
        title="Топ участников"
        :entries="leaderboardEntries"
      />

      <!-- Group leaderboard (if present) -->
      <div v-if="(groupLeaderboard || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Рейтинг групп</h2>
        <div class="space-y-2">
          <RCard
            v-for="(entry, idx) in groupLeaderboard"
            :key="entry.group?.id || idx"
            class="flex items-center justify-between px-6 py-4"
          >
            <template #default>
              <div class="flex items-center gap-3">
                <span class="font-bold text-gray-500">#{{ idx + 1 }}</span>
                <span class="font-medium text-gray-900">{{ entry.group?.title || '–' }}</span>
              </div>
              <span class="font-semibold text-rosatom-600">{{ entry.total_points ?? 0 }} баллов</span>
            </template>
          </RCard>
        </div>
      </div>

      <RCard
        v-if="!(leaderboardWithRank?.length)"
        class="py-16 text-center text-gray-400"
      >
        Рейтинг пока пуст
      </RCard>
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

const leaderboardEntries = computed(() => {
  return (leaderboardWithRank.value || []).map((entry, idx) => ({
    id: entry.user?.id ?? idx,
    name: entry.user?.name || '–',
    avatar: entry.user?.avatar,
    group: entry.group?.title,
    points: entry.total_points ?? 0,
    highlight: entry.user?.id === authUserId.value,
  }))
})

function isCurrentUser(entry) {
  return entry.user?.id === authUserId.value
}
</script>
