<template>
  <LmsLayout :event="event" :user="page.props.user" :profile="page.props.profile">
    <Head :title="`Видео – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Видеоматериалы</h1>

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
        <RCard
          v-for="video in (videos?.data || videos || [])"
          :key="video.id"
          elevation="raised"
          hoverable
          class="group"
          @click="router.visit(route('lms.videos.show', { event: event?.slug, video: video.id }))"
        >
          <template #cover>
          <div class="relative aspect-video overflow-hidden bg-gray-900">
            <img
              v-if="getThumbnail(video)"
              :src="getThumbnail(video)"
              :alt="video.title"
              class="h-full w-full object-cover transition group-hover:scale-105"
            />
            <div v-else class="flex h-full items-center justify-center">
              <VideoCameraIcon class="h-14 w-14 text-gray-600" />
            </div>
            <!-- Play button overlay -->
            <div class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition group-hover:opacity-100">
              <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 shadow-lg">
                <PlayIcon class="h-7 w-7 text-rosatom-700" />
              </div>
            </div>
            <RBadge v-if="video.is_recording" variant="error" size="sm" class="absolute right-2 top-2">
              Запись
            </RBadge>
          </div>
          </template>

          <div>
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ video.title }}</h3>
            <p v-if="video.description" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ video.description }}</p>
            <p v-if="getSourceLabel(video)" class="mt-2 text-xs text-gray-400">{{ getSourceLabel(video) }}</p>
          </div>
        </RCard>
      </div>

      <div v-if="!(videos?.data?.length || videos?.length)" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <VideoCameraIcon class="mx-auto h-10 w-10 text-gray-300" />
        <p class="mt-3 text-sm text-gray-400">Видеоматериалы не найдены</p>
      </div>

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
import { VideoCameraIcon, PlayIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  videos: { type: [Object, Array], default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const items = computed(() => {
  const v = props.videos
  if (v?.data && typeof v.last_page === 'number') return v
  return { last_page: 1, from: 0, to: 0, total: 0, links: [] }
})

let searchTimeout = null
function debouncedSearch(e) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('lms.videos.index', { event: props.event.slug }), { search: e.target.value || undefined }, { preserveState: true })
  }, 400)
}

function getThumbnail(video) {
  if (video.thumbnail) return video.thumbnail

  const url = video.url || ''

  // Rutube thumbnail
  const rutubeMatch = url.match(/rutube\.ru\/video\/([a-zA-Z0-9]+)/)
  if (rutubeMatch) {
    return `https://pic.rutube.ru/video/${rutubeMatch[1]}`
  }

  // YouTube thumbnail
  const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/)
  if (ytMatch) {
    return `https://img.youtube.com/vi/${ytMatch[1]}/hqdefault.jpg`
  }

  return null
}

function getSourceLabel(video) {
  const url = video.url || ''
  if (url.includes('rutube.ru')) return 'Rutube'
  if (url.includes('youtube.com') || url.includes('youtu.be')) return 'YouTube'
  if (url.includes('vk.com')) return 'VK Видео'
  if (video.source === 'upload') return 'Загружено'
  return ''
}
</script>
