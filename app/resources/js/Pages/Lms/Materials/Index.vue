<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Материалы – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Материалы</h1>

      <div class="space-y-3">
        <Link
          v-for="section in (sections || [])"
          :key="section.id"
          :href="route('lms.materials.show', { event: event?.slug, section: section.id })"
          class="group flex items-center justify-between rounded-xl border border-gray-200 bg-white shadow-sm p-5 transition hover:border-gray-300"
        >
          <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ section.title }}</h3>
            <p v-if="contentSnippet(section)" class="mt-1 line-clamp-2 text-sm text-gray-500">
              {{ contentSnippet(section) }}
            </p>
          </div>
          <ChevronRightIcon class="ml-4 h-5 w-5 shrink-0 text-gray-400" />
        </Link>
      </div>

      <div
        v-if="!(sections?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        Материалы не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  sections: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function contentSnippet(section) {
  const content = section?.content
  if (!content || typeof content !== 'string') return ''
  const stripped = content.replace(/<[^>]*>/g, '').trim()
  return stripped.length > 120 ? stripped.slice(0, 120) + '…' : stripped
}
</script>
