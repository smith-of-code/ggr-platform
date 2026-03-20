<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Рейтинг – ${event?.title}`" />

    <div class="mx-auto max-w-5xl space-y-8">
      <div class="flex items-end justify-between">
        <div>
          <h1 class="font-brand text-2xl font-bold text-gray-900">Рейтинг</h1>
          <p class="mt-1 text-sm text-gray-500">Топ участников и групп по баллам</p>
        </div>
        <div class="flex rounded-xl bg-gray-100 p-1">
          <button
            v-for="t in tabs"
            :key="t.id"
            :class="[
              'rounded-lg px-4 py-2 text-sm font-medium transition',
              activeTab === t.id ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700',
            ]"
            @click="activeTab = t.id"
          >
            {{ t.label }}
          </button>
        </div>
      </div>

      <!-- My stats card -->
      <div v-if="userRank || userPoints" class="grid gap-4 sm:grid-cols-3">
        <RCard class="relative overflow-hidden border-2 border-rosatom-500/30">
          <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rosatom-500/5" />
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ваше место</p>
          <p class="mt-1 text-4xl font-black text-rosatom-600">
            {{ userRank ? `#${userRank}` : '—' }}
          </p>
          <p class="mt-1 text-sm text-gray-500">из {{ userLeaderboard.length }} участников</p>
        </RCard>
        <RCard class="relative overflow-hidden">
          <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-500/5" />
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Ваши баллы</p>
          <p class="mt-1 text-4xl font-black text-amber-600">{{ userPoints ?? 0 }}</p>
          <p class="mt-1 text-sm text-gray-500">
            <Link :href="route('lms.gamification.my-points', { event: event?.slug })" class="text-rosatom-600 hover:underline">
              Подробнее
            </Link>
          </p>
        </RCard>
        <RCard v-if="topUser" class="relative overflow-hidden">
          <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-green-500/5" />
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Лидер</p>
          <p class="mt-1 truncate text-lg font-bold text-gray-900">{{ topUser.user?.name }}</p>
          <p class="mt-1 text-2xl font-black text-green-600">{{ topUser.total_points }} баллов</p>
        </RCard>
      </div>

      <!-- User leaderboard -->
      <div v-show="activeTab === 'users'">
        <!-- Podium for top 3 -->
        <div v-if="userLeaderboard.length >= 3" class="mb-8 flex items-end justify-center gap-3 sm:gap-6">
          <div
            v-for="pos in podiumOrder"
            :key="pos.rank"
            class="flex flex-col items-center"
            :class="pos.rank === 1 ? 'order-2' : pos.rank === 2 ? 'order-1' : 'order-3'"
          >
            <div class="relative mb-2">
              <RAvatar
                :name="userLeaderboard[pos.rank - 1]?.user?.name || '?'"
                :size="pos.rank === 1 ? 'xl' : 'lg'"
                :class="[
                  'ring-4',
                  pos.rank === 1 ? 'ring-amber-400' : pos.rank === 2 ? 'ring-gray-300' : 'ring-amber-700/60',
                ]"
              />
              <span
                :class="[
                  'absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full text-xs font-black text-white shadow-lg',
                  pos.rank === 1 ? 'bg-amber-400' : pos.rank === 2 ? 'bg-gray-400' : 'bg-amber-700',
                ]"
              >
                {{ pos.rank }}
              </span>
            </div>
            <p class="mt-1 max-w-[100px] truncate text-center text-sm font-semibold text-gray-900">
              {{ userLeaderboard[pos.rank - 1]?.user?.name }}
            </p>
            <p :class="['text-lg font-black', pos.rank === 1 ? 'text-amber-500' : 'text-gray-500']">
              {{ userLeaderboard[pos.rank - 1]?.total_points }}
            </p>
            <div
              :class="[
                'mt-1 w-20 rounded-t-xl sm:w-24',
                pos.rank === 1 ? 'h-24 bg-gradient-to-t from-amber-400/80 to-amber-300/40' : pos.rank === 2 ? 'h-16 bg-gradient-to-t from-gray-300/80 to-gray-200/40' : 'h-12 bg-gradient-to-t from-amber-700/60 to-amber-600/20',
              ]"
            />
          </div>
        </div>

        <!-- Full list -->
        <RCard flush>
          <div class="divide-y divide-gray-100">
            <div
              v-for="(entry, idx) in userLeaderboard"
              :key="entry.user?.id ?? idx"
              :class="[
                'flex items-center gap-4 px-5 py-3.5 transition',
                entry.user?.id === user?.id ? 'bg-rosatom-50/60' : 'hover:bg-gray-50',
              ]"
            >
              <span
                :class="[
                  'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                  idx === 0 ? 'bg-amber-400 text-white' : idx === 1 ? 'bg-gray-300 text-white' : idx === 2 ? 'bg-amber-700 text-white' : 'bg-gray-100 text-gray-500',
                ]"
              >
                {{ idx + 1 }}
              </span>
              <RAvatar :name="entry.user?.name || '?'" size="md" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                  {{ entry.user?.name }}
                  <span v-if="entry.user?.id === user?.id" class="ml-1 text-xs text-rosatom-500">(вы)</span>
                </p>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold text-gray-900">{{ entry.total_points }}</p>
                <p class="text-[10px] text-gray-400">баллов</p>
              </div>
              <!-- Points bar -->
              <div class="hidden w-32 sm:block">
                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="idx === 0 ? 'bg-amber-400' : idx === 1 ? 'bg-gray-400' : idx === 2 ? 'bg-amber-700' : 'bg-rosatom-400'"
                    :style="{ width: `${maxUserPoints ? (entry.total_points / maxUserPoints) * 100 : 0}%` }"
                  />
                </div>
              </div>
            </div>
          </div>
          <div v-if="userLeaderboard.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">
            Рейтинг пока пуст — будьте первым!
          </div>
        </RCard>
      </div>

      <!-- Group leaderboard -->
      <div v-show="activeTab === 'groups'">
        <div v-if="groupLeaderboard.length > 0" class="space-y-4">
          <!-- Top group card -->
          <RCard v-if="groupLeaderboard[0]" class="border-2 border-amber-400/40 bg-gradient-to-r from-amber-50 to-white">
            <div class="flex items-center gap-5">
              <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-amber-400 text-2xl font-black text-white shadow-lg">
                1
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-lg font-bold text-gray-900">{{ groupLeaderboard[0].group?.title }}</p>
                <p class="text-sm text-gray-500">{{ groupLeaderboard[0].group?.members_count ?? '?' }} участников</p>
              </div>
              <div class="text-right">
                <p class="text-3xl font-black text-amber-500">{{ groupLeaderboard[0].total_points }}</p>
                <p class="text-xs text-gray-400">баллов</p>
              </div>
            </div>
          </RCard>

          <!-- Rest of groups -->
          <RCard flush>
            <div class="divide-y divide-gray-100">
              <div
                v-for="(entry, idx) in groupLeaderboard.slice(1)"
                :key="entry.group?.id ?? idx"
                class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50"
              >
                <span
                  :class="[
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold',
                    idx === 0 ? 'bg-gray-300 text-white' : idx === 1 ? 'bg-amber-700 text-white' : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  {{ idx + 2 }}
                </span>
                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-gray-900">{{ entry.group?.title }}</p>
                  <p class="text-xs text-gray-400">{{ entry.group?.members_count ?? '?' }} участников</p>
                </div>
                <div class="text-right">
                  <p class="text-lg font-bold text-gray-900">{{ entry.total_points }}</p>
                  <p class="text-[10px] text-gray-400">баллов</p>
                </div>
                <div class="hidden w-32 sm:block">
                  <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full bg-amber-400 transition-all duration-500"
                      :style="{ width: `${maxGroupPoints ? (entry.total_points / maxGroupPoints) * 100 : 0}%` }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </RCard>
        </div>
        <RCard v-else class="px-5 py-16 text-center text-sm text-gray-400">
          Рейтинг групп пока пуст
        </RCard>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
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

const tabs = [
  { id: 'users', label: 'Участники' },
  { id: 'groups', label: 'Группы' },
]
const activeTab = ref('users')

const topUser = computed(() => props.userLeaderboard[0] ?? null)

const maxUserPoints = computed(() => {
  if (!props.userLeaderboard.length) return 0
  return props.userLeaderboard[0]?.total_points || 1
})

const maxGroupPoints = computed(() => {
  if (!props.groupLeaderboard.length) return 0
  return props.groupLeaderboard[0]?.total_points || 1
})

const podiumOrder = [
  { rank: 2 },
  { rank: 1 },
  { rank: 3 },
]
</script>
