<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Кабинет руководителя – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Кабинет руководителя</h1>

      <!-- Stats cards -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <RCard elevation="raised">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-rosatom-50 p-3">
              <UsersIcon class="h-6 w-6 text-rosatom-600" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Участников</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats?.total_participants ?? totalParticipants ?? 0 }}</p>
            </div>
          </div>
        </RCard>
        <RCard elevation="raised">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-rosatom-50 p-3">
              <ChartBarIcon class="h-6 w-6 text-rosatom-500" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Средний прогресс</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats?.avg_progress ?? avgProgress ?? 0 }}%</p>
            </div>
          </div>
        </RCard>
        <RCard elevation="raised">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-accent-yellow/10 p-3">
              <BookOpenIcon class="h-6 w-6 text-accent-yellow" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Активных курсов</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats?.active_courses ?? activeCourses ?? 0 }}</p>
            </div>
          </div>
        </RCard>
        <RCard elevation="raised">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-accent-magenta/10 p-3">
              <UserGroupIcon class="h-6 w-6 text-accent-magenta" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Групп</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats?.total_groups ?? totalGroups ?? 0 }}</p>
            </div>
          </div>
        </RCard>
      </div>

      <!-- Quick links -->
      <div class="grid gap-4 sm:grid-cols-2">
        <Link
          :href="route('lms.leader.groups', { event: event?.slug })"
          class="block"
        >
          <RCard hoverable class="flex items-center gap-4 p-6">
            <UserGroupIcon class="h-10 w-10 text-rosatom-600" />
            <div>
              <h3 class="font-semibold text-gray-900">Группы</h3>
              <p class="text-sm text-gray-500">Управление группами и просмотр прогресса</p>
            </div>
            <ChevronRightIcon class="ml-auto h-5 w-5 text-gray-400" />
          </RCard>
        </Link>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  UsersIcon,
  ChartBarIcon,
  BookOpenIcon,
  UserGroupIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
  totalParticipants: { type: Number, default: 0 },
  avgProgress: { type: Number, default: 0 },
  activeCourses: { type: Number, default: 0 },
  totalGroups: { type: Number, default: 0 },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
</script>
