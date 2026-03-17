<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Мои баллы – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Мои баллы</h1>

      <!-- Total at top -->
      <RCard>
        <template #default>
          <p class="text-sm font-medium text-gray-500">Всего баллов</p>
          <p class="mt-1 text-3xl font-bold text-rosatom-600">{{ totalPoints ?? 0 }}</p>
        </template>
      </RCard>

      <!-- History -->
      <div>
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">История начислений</h2>
        <div class="space-y-2">
          <RCard
            v-for="p in (points || [])"
            :key="p.id"
            class="flex items-center justify-between px-6 py-4"
          >
            <template #default>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-gray-900">{{ reasonLabel(p) }}</p>
                <p class="mt-0.5 text-sm text-gray-400">{{ formatDate(p.created_at) }}</p>
              </div>
              <RBadge
                :variant="(p.points ?? 0) >= 0 ? 'success' : 'error'"
                class="shrink-0 font-bold"
              >
                {{ (p.points ?? 0) >= 0 ? '+' : '' }}{{ p.points ?? 0 }}
              </RBadge>
            </template>
          </RCard>
        </div>

        <RCard
          v-if="!(points?.length)"
          class="py-12 text-center text-gray-400"
        >
          Пока нет начислений
        </RCard>
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
  points: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function reasonLabel(p) {
  return p.reason || p.rule?.title || 'Начислены баллы'
}

function formatDate(dateStr) {
  if (!dateStr) return '–'
  return new Date(dateStr).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
