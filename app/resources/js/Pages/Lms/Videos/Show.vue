<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${video?.title} – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.videos.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к видео
      </Link>

      <div class="space-y-6">
        <!-- Video player -->
        <div class="aspect-video overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <!-- Rutube embed -->
          <iframe
            v-if="video?.source === 'rutube' && video?.url"
            :src="rutubeEmbedUrl"
            class="h-full w-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          />
          <!-- Generic URL iframe (link) -->
          <iframe
            v-else-if="video?.source === 'link' && video?.url"
            :src="video.url"
            class="h-full w-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          />
          <!-- HTML5 video (upload) -->
          <video
            v-else-if="video?.source === 'upload' && video?.file_path"
            :src="video.file_path"
            class="h-full w-full"
            controls
            playsinline
          />
          <div v-else class="flex h-full items-center justify-center text-gray-400">
            <VideoCameraIcon class="h-20 w-20" />
          </div>
        </div>

        <div>
          <h1 class="font-brand text-2xl font-bold text-gray-900">{{ video?.title }}</h1>
          <p v-if="video?.description" class="mt-3 text-gray-500">{{ video.description }}</p>
        </div>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, VideoCameraIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  video: { type: Object, required: true },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

const rutubeEmbedUrl = computed(() => {
  const url = props.video?.url
  if (!url) return ''
  const match = url.match(/(?:rutube\.ru\/video\/|rutube\.ru\/play\/embed\/)([a-zA-Z0-9-]+)/)
  const videoId = match ? match[1] : url.split('/').pop()
  return `https://rutube.ru/play/embed/${videoId}`
})
</script>
