<template>
  <MainLayout>
    <Head title="Блог" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Блог</h1>
        <p class="mt-3 max-w-2xl text-lg text-gray-500">
          Новости, анонсы и материалы информационного портала
        </p>
      </div>

      <!-- Категории -->
      <div class="mt-10 flex flex-wrap gap-2 border-b border-gray-200 pb-4">
        <button
          type="button"
          @click="filterByCategory(null)"
          :class="tabClass(!filters.category)"
        >
          Все
        </button>
        <button
          v-for="(label, key) in categories"
          :key="key"
          type="button"
          @click="filterByCategory(key)"
          :class="tabClass(filters.category === key)"
        >
          {{ label }}
        </button>
      </div>

      <!-- Сетка карточек -->
      <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="post in posts.data"
          :key="post.id"
          class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-md shadow-gray-200/60 ring-1 ring-gray-100 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-gray-300/50 hover:ring-[#003274]/15"
        >
          <Link :href="route('blog.show', post.slug)" class="flex flex-1 flex-col">
            <div class="relative aspect-[16/9] overflow-hidden bg-gray-100">
              <img
                v-if="post.image"
                :src="post.image"
                :alt="post.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
              />
              <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200"
              >
                <svg class="h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"
                  />
                </svg>
              </div>
              <span
                class="absolute left-3 top-3 rounded-lg bg-[#003274] px-2.5 py-1 text-xs font-semibold text-white shadow-md"
              >
                {{ categories[post.category] ?? post.category }}
              </span>
            </div>

            <div class="flex flex-1 flex-col p-5">
              <h2
                class="text-lg font-bold leading-snug text-gray-900 transition group-hover:text-[#003274] sm:text-xl"
              >
                {{ post.title }}
              </h2>
              <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-gray-600">
                {{ post.excerpt }}
              </p>
              <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                <time :datetime="post.published_at" class="text-xs text-gray-500">
                  {{ formatDate(post.published_at) }}
                </time>
                <span
                  class="text-sm font-semibold text-[#003274] opacity-90 transition group-hover:opacity-100"
                >
                  Читать
                  <span class="inline-block transition group-hover:translate-x-0.5">→</span>
                </span>
              </div>
            </div>
          </Link>
        </article>
      </div>

      <div v-if="posts.data.length === 0" class="py-20 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
          <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
            />
          </svg>
        </div>
        <p class="mt-4 text-lg font-medium text-gray-600">Пока нет записей</p>
        <p class="mt-1 text-sm text-gray-400">Попробуйте выбрать другую категорию</p>
      </div>

      <!-- Пагинация -->
      <div v-if="posts.data.length > 0 && posts.last_page > 1" class="mt-12 flex flex-wrap justify-center gap-2">
        <template v-for="(link, i) in posts.links" :key="i">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm font-medium transition-all duration-200',
              link.active
                ? 'bg-[#003274] text-white shadow-lg shadow-[#003274]/20'
                : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-900',
            ]"
          >
            <span v-html="link.label" />
          </Link>
          <span
            v-else
            :class="[
              'flex h-10 min-w-[40px] items-center justify-center rounded-xl px-3 text-sm',
              link.active ? 'bg-[#003274] text-white' : 'cursor-default bg-gray-50 text-gray-300',
            ]"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

defineProps({
  posts: Object,
  categories: Object,
  filters: Object,
})

function formatDate(value) {
  if (!value) return '—'
  const d = typeof value === 'string' ? new Date(value) : value
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function tabClass(active) {
  return [
    'rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200',
    active
      ? 'bg-[#003274] text-white shadow-md shadow-[#003274]/25'
      : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-[#003274]',
  ]
}

function filterByCategory(key) {
  const params = key ? { category: key } : {}
  router.get(route('blog.index'), params, { preserveState: true })
}
</script>
