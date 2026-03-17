<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Траектории – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Образовательные траектории</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="t in (trajectories || [])"
          :key="t.trajectory?.id"
          class="flex flex-col overflow-hidden"
        >
          <RCard class="flex flex-1 flex-col">
            <template #default>
              <Link
                :href="route('lms.trajectories.show', { event: event?.slug, trajectory: t.trajectory?.id })"
                class="flex-1 block"
              >
                <h3 class="font-semibold text-gray-900 hover:text-rosatom-600">{{ t.trajectory?.title }}</h3>
                <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ t.trajectory?.description || '–' }}</p>
                <div class="mt-4">
                  <div class="flex justify-between text-sm text-gray-500">
                    <span>Шагов: {{ stepsCount(t) }}</span>
                    <span>{{ progressPercent(t) }}%</span>
                  </div>
                  <RProgress
                    :percentage="progressPercent(t)"
                    :show-label="false"
                    class="mt-1.5"
                  />
                </div>
              </Link>
            </template>
            <template #footer>
              <div class="border-t border-gray-200 p-4">
                <Link
                  v-if="!t.enrolled"
                  :href="route('lms.trajectories.enroll', { event: event?.slug, trajectory: t.trajectory?.id })"
                  method="post"
                  as="div"
                  class="block"
                >
                  <RButton block>
                    Записаться
                  </RButton>
                </Link>
                <Link
                  v-else
                  :href="route('lms.trajectories.show', { event: event?.slug, trajectory: t.trajectory?.id })"
                  as="div"
                  class="block"
                >
                  <RButton variant="outline" block>
                    Подробнее
                  </RButton>
                </Link>
              </div>
            </template>
          </RCard>
        </div>
      </div>

      <RCard
        v-if="!(trajectories?.length)"
        class="py-16 text-center text-gray-400"
      >
        Траектории не найдены
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
  trajectories: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function stepsCount(t) {
  return t.steps_count ?? t.trajectory?.steps_count ?? 0
}

function progressPercent(t) {
  const completed = t.completed_steps ?? 0
  const total = stepsCount(t) || 1
  return Math.round((completed / total) * 100)
}
</script>
