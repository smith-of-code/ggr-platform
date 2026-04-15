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

      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <!-- Video player -->
        <div class="aspect-video bg-black">
          <iframe
            v-if="embedUrl"
            :src="embedUrl"
            class="h-full w-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            allowfullscreen
          />
          <video
            v-else-if="video?.file_path"
            :src="video.file_path"
            class="h-full w-full"
            controls
            playsinline
          />
          <div v-else class="flex h-full items-center justify-center text-gray-500">
            <VideoCameraIcon class="h-20 w-20" />
          </div>
        </div>

        <!-- Info -->
        <div class="p-6">
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

function rutubeEmbedFromStoredUrl(raw) {
  if (!raw || !/rutube\.ru/i.test(raw)) return ''
  if (raw.includes('rutube.ru/play/embed/')) {
    return raw
  }
  try {
    const u = new URL(raw, 'https://rutube.ru')
    const path = u.pathname || ''
    const privateMatch = path.match(/\/video\/private\/([a-zA-Z0-9_-]+)/)
    if (privateMatch) {
      const id = privateMatch[1]
      const p = u.searchParams.get('p')
      const base = `https://rutube.ru/play/embed/${id}/`
      return p ? `${base}?p=${encodeURIComponent(p)}` : base
    }
    const publicMatch = path.match(/\/video\/(?!private\/)([a-zA-Z0-9_-]+)/)
    if (publicMatch) {
      return `https://rutube.ru/play/embed/${publicMatch[1]}`
    }
  } catch {
    return ''
  }
  return ''
}

const embedUrl = computed(() => {
  const url = props.video?.url
  if (!url) return ''

  const rutubeEmbed = rutubeEmbedFromStoredUrl(url)
  if (rutubeEmbed) {
    return rutubeEmbed
  }

  // YouTube
  const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/)
  if (ytMatch) {
    return `https://www.youtube.com/embed/${ytMatch[1]}`
  }

  // VK Video
  const vkMatch = url.match(/vk\.com\/video(-?\d+_\d+)/)
  if (vkMatch) {
    return `https://vk.com/video_ext.php?oid=${vkMatch[1].split('_')[0]}&id=${vkMatch[1].split('_')[1]}`
  }

  // Any other URL — try embedding directly
  if (url.startsWith('http')) {
    return url
  }

  return ''
})
</script>
