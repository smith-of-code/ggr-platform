<template>
  <MainLayout>
    <Head :title="post.title" />

    <!-- Hero -->
    <section class="relative min-h-[min(55vh,28rem)] w-full overflow-hidden">
      <div
        v-if="post.image"
        class="absolute inset-0 bg-cover bg-center transition-transform duration-700"
        :style="{ backgroundImage: `url(${post.image})` }"
      />
      <div
        v-else
        class="absolute inset-0 bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#003d8f]"
      />
      <div
        class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/55 to-gray-900/30"
      />
      <div class="relative mx-auto flex min-h-[min(55vh,28rem)] max-w-7xl flex-col justify-end px-4 pb-12 pt-24 sm:px-6 lg:px-8">
        <Link
          :href="route('blog.index')"
          class="mb-6 inline-flex w-fit items-center gap-2 text-sm font-medium text-white/90 transition hover:text-white"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          К блогу
        </Link>
        <div class="flex flex-wrap items-center gap-3">
          <span class="rounded-lg bg-[#003274] px-3 py-1 text-xs font-semibold text-white shadow-lg">
            {{ categoryLabel(post.category) }}
          </span>
          <time :datetime="post.published_at" class="text-sm text-white/85">
            {{ formatDate(post.published_at) }}
          </time>
        </div>
        <h1 class="mt-4 max-w-4xl text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
          {{ post.title }}
        </h1>
      </div>
    </section>

    <!-- Контент -->
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
      <div
        class="prose prose-lg max-w-none text-gray-700 prose-headings:font-bold prose-headings:text-gray-900 prose-a:text-[#003274] prose-a:no-underline hover:prose-a:underline prose-strong:text-gray-900 prose-img:rounded-xl"
        v-html="post.content"
      />

      <!-- Видео -->
      <div v-if="postVideos.length" class="mt-10 border-t border-gray-200 pt-8">
        <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Видео</p>
        <div class="grid gap-6" :class="postVideos.length > 1 ? 'sm:grid-cols-2' : ''">
          <div v-for="(video, vi) in postVideos" :key="vi" class="aspect-video overflow-hidden rounded-xl bg-gray-100">
            <iframe
              :src="embedUrl(video)"
              class="h-full w-full"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            />
          </div>
        </div>
      </div>

      <div v-if="tags.length" class="mt-10 border-t border-gray-200 pt-8">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Теги</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="tag in tags"
            :key="tag"
            class="rounded-full bg-[#003274]/10 px-3 py-1 text-xs font-medium text-[#003274]"
          >
            {{ tag }}
          </span>
        </div>
      </div>

      <div class="mt-10">
        <Link
          :href="route('blog.index')"
          class="inline-flex items-center gap-2 text-sm font-semibold text-[#003274] transition hover:text-[#025ea1]"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          Все публикации
        </Link>
      </div>
    </article>

    <!-- Похожие материалы -->
    <section v-if="relatedPosts.length" class="border-t border-gray-200 bg-gray-50/80 py-14">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Читайте также</h2>
        <p class="mt-1 text-sm text-gray-500">Другие материалы в этой категории</p>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="related in relatedPosts"
            :key="related.id"
            class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-md shadow-gray-200/60 ring-1 ring-gray-100 transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:ring-[#003274]/15"
          >
            <Link :href="route('blog.show', related.slug)" class="flex flex-1 flex-col">
              <div class="relative aspect-[16/9] overflow-hidden bg-gray-100">
                <img
                  v-if="related.image"
                  :src="related.image"
                  :alt="related.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200"
                >
                  <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"
                    />
                  </svg>
                </div>
                <span
                  class="absolute left-3 top-3 rounded-lg bg-[#003274] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white"
                >
                  {{ categoryLabel(related.category) }}
                </span>
              </div>
              <div class="flex flex-1 flex-col p-4">
                <h3 class="font-bold text-gray-900 transition group-hover:text-[#003274]">
                  {{ related.title }}
                </h3>
                <p v-if="related.excerpt" class="mt-2 line-clamp-2 text-sm text-gray-600">
                  {{ related.excerpt }}
                </p>
                <time :datetime="related.published_at" class="mt-auto pt-3 text-xs text-gray-400">
                  {{ formatDate(related.published_at) }}
                </time>
              </div>
            </Link>
          </article>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

/** Дублирует App\Models\Post::CATEGORIES для подписи без отдельного prop */
const CATEGORY_LABELS = {
  news: 'Новости программы',
  announcements: 'Анонсы',
  partner_articles: 'Статьи партнёров',
  atoms_vkusa: 'Атомы вкуса',
}

const props = defineProps({
  post: Object,
  relatedPosts: Array,
})

const tags = computed(() => {
  const t = props.post?.tags
  if (!t) return []
  return Array.isArray(t) ? t : []
})

const postVideos = computed(() => {
  const v = props.post?.videos
  if (!v) return []
  return Array.isArray(v) ? v.filter(Boolean) : []
})

function embedUrl(url) {
  if (!url) return ''
  // YouTube
  const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/)
  if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`
  // RuTube
  const rtMatch = url.match(/rutube\.ru\/video\/([a-zA-Z0-9]+)/)
  if (rtMatch) return `https://rutube.ru/play/embed/${rtMatch[1]}`
  return url
}

function categoryLabel(key) {
  return CATEGORY_LABELS[key] ?? key ?? ''
}

function formatDate(value) {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
