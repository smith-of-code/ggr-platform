<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Видео – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Видеоматериалы</h1>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="video in (videos || [])"
          :key="video.id"
          :href="route('lms.videos.show', { event: event?.slug, video: video.id })"
          class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300"
        >
          <div class="relative aspect-video overflow-hidden bg-gray-100">
            <img
              v-if="video.thumbnail"
              :src="video.thumbnail"
              :alt="video.title"
              class="h-full w-full object-cover transition group-hover:scale-105"
            />
            <div v-else class="flex h-full items-center justify-center">
              <VideoCameraIcon class="h-16 w-16 text-gray-400" />
            </div>
            <span
              v-if="video.is_recording"
              class="absolute right-2 top-2 rounded bg-rose-500/90 px-2 py-0.5 text-xs font-medium text-white"
            >
              Запись
            </span>
          </div>
          <div class="p-5">
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ video.title }}</h3>
            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ video.description || '–' }}</p>
          </div>
        </Link>
      </div>

      <div
        v-if="!(videos?.length)"
        class="rounded-xl border border-gray-200 bg-white py-16 text-center text-gray-400 shadow-sm"
      >
        Видеоматериалы не найдены
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { VideoCameraIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  videos: { type: Array, default: () => [] },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
</script>
