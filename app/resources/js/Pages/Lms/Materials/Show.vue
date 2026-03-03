<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${section?.title} – Материалы`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.materials.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к материалам
      </Link>

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-6 lg:p-8">
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ section?.title }}</h1>
        <div
          v-if="section?.content"
          class="material-content prose mt-6 max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600 prose-a:no-underline hover:prose-a:underline"
          v-html="section.content"
        />
        <p v-else class="mt-6 text-gray-400">Контент отсутствует</p>
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
  section: { type: Object, required: true },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
</script>
