<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`База знаний – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">База знаний</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="section in (sections || [])"
          :key="section.id"
          :href="route('lms.kb.show', { event: event?.slug, section: section.id })"
          class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300"
        >
          <div class="p-6">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ section.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ section.description || '–' }}</p>
            <p class="mt-3 text-xs text-gray-400">
              {{ childrenCount(section) }} материалов
            </p>
          </div>
        </Link>
      </div>

      <div
        v-if="!(sections?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        Разделы не найдены
      </div>
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
  sections: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function childrenCount(section) {
  const children = section?.children
  if (Array.isArray(children)) return children.length
  return section?.children_count ?? 0
}
</script>
