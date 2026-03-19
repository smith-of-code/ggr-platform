<template>
  <LmsLayout :event="event" :user="page.props.user" :profile="page.props.profile">
    <Head :title="`Тесты – ${event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Тесты</h1>

      <div class="mb-4">
        <input
          :value="filters?.search ?? ''"
          @input="debouncedSearch"
          type="text"
          placeholder="Поиск..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
        />
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="test in (tests?.data || tests || [])"
          :key="test.id"
          class="cursor-pointer"
          @click="router.visit(route('lms.tests.show', { event: event?.slug, test: test.id }))"
        >
        <RCard hoverable>
          <div class="p-6">
            <h3 class="font-semibold text-gray-900">{{ test.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ test.description }}</p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
              <span class="text-gray-400">Проходной балл: {{ test.passing_score ?? 0 }}%</span>
              <RBadge v-if="test.best_score != null" variant="primary" size="sm">
                Ваш лучший: {{ test.best_score }}%
              </RBadge>
              <span v-else class="text-gray-400">Не пройден</span>
            </div>
            <div class="mt-2 text-xs text-gray-400">
              Попыток: {{ test.attempt_count ?? 0 }}
              <template v-if="test.max_attempts"> / {{ test.max_attempts }}</template>
            </div>
          </div>
        </RCard>
        </div>
      </div>

      <RCard v-if="!(tests?.data?.length || tests?.length)" class="py-16 text-center text-gray-400">
        Тесты не найдены
      </RCard>

      <div v-if="items.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ items.from }}–{{ items.to }} из {{ items.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in items.links"
            :key="link.label"
            @click="link.url && router.visit(link.url, { preserveState: true })"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100 disabled:opacity-30'"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  tests: { type: [Object, Array], default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const items = computed(() => {
  const t = props.tests
  if (t?.data && typeof t.last_page === 'number') return t
  return { last_page: 1, from: 0, to: 0, total: 0, links: [] }
})

let searchTimeout = null
function debouncedSearch(e) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('lms.tests.index', { event: props.event.slug }), { search: e.target.value || undefined }, { preserveState: true })
  }, 400)
}
</script>
