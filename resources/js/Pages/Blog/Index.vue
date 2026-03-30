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

      <!-- Поиск -->
      <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
          <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Поиск по статьям..."
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
          />
        </div>
        <button
          v-if="filters.search || filters.tag || filters.category"
          type="button"
          @click="clearFilters"
          class="shrink-0 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
        >
          Сбросить фильтры
        </button>
      </div>

      <!-- Категории -->
      <div class="mt-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4">
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

      <!-- Теги -->
      <div v-if="allTags.length" class="mt-4 flex flex-wrap gap-2">
        <button
          v-for="tag in allTags"
          :key="tag"
          type="button"
          @click="filterByTag(tag)"
          :class="[
            'rounded-full px-3 py-1 text-xs font-medium transition-all duration-200',
            filters.tag === tag
              ? 'bg-[#003274] text-white shadow-md shadow-[#003274]/25'
              : 'bg-[#003274]/5 text-[#003274] hover:bg-[#003274]/15',
          ]"
        >
          {{ tag }}
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
              <div v-if="post.tags?.length" class="mt-3 flex flex-wrap gap-1.5">
                <span v-for="tag in post.tags.slice(0, 3)" :key="tag" class="rounded-full bg-[#003274]/10 px-2.5 py-0.5 text-[11px] font-medium text-[#003274]">{{ tag }}</span>
                <span v-if="post.tags.length > 3" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-500">+{{ post.tags.length - 3 }}</span>
              </div>
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
        <p class="mt-4 text-lg font-medium text-gray-600">Ничего не найдено</p>
        <p class="mt-1 text-sm text-gray-400">Попробуйте изменить параметры поиска или фильтры</p>
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

    <!-- Подписка на рассылку -->
    <section class="border-t border-gray-200 bg-gradient-to-br from-[#003274] to-[#025ea1] py-16">
      <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
        <svg class="mx-auto h-10 w-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
        <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">Подпишитесь на рассылку</h2>
        <p class="mt-3 text-base text-white/75">Получайте уведомления о новых статьях блога на вашу почту</p>

        <div v-if="subscribeSuccess" class="mt-6 rounded-xl bg-white/15 px-6 py-4 backdrop-blur">
          <p class="text-sm font-medium text-white">{{ subscribeSuccess }}</p>
        </div>

        <form v-else @submit.prevent="submitSubscribe" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-center">
          <div class="flex-1 sm:max-w-sm">
            <input
              v-model="subscribeForm.email"
              type="email"
              required
              placeholder="Ваш email"
              class="w-full rounded-xl border-0 bg-white/15 px-5 py-3 text-sm text-white placeholder-white/60 backdrop-blur transition focus:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/40"
            />
            <p v-if="subscribeForm.errors.email" class="mt-1 text-left text-xs text-red-300">{{ subscribeForm.errors.email }}</p>
          </div>
          <button
            type="submit"
            :disabled="subscribeForm.processing"
            class="shrink-0 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-[#003274] shadow-lg transition hover:bg-white/90 disabled:opacity-60"
          >
            Подписаться
          </button>
        </form>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  posts: Object,
  categories: Object,
  filters: Object,
  allTags: { type: Array, default: () => [] },
})

const subscribeForm = useForm({ email: '' })
const page = usePage()
const subscribeSuccess = ref(page.props?.flash?.subscribed ?? null)

function submitSubscribe() {
  subscribeForm.post(route('blog.subscribe'), {
    preserveScroll: true,
    onSuccess: () => {
      subscribeSuccess.value = 'Вы подписаны на рассылку!'
      subscribeForm.reset()
    },
  })
}

const searchQuery = ref(props.filters?.search ?? '')
let searchTimeout = null

watch(searchQuery, (val) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters({ search: val || undefined })
  }, 400)
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

function currentParams() {
  const p = {}
  if (props.filters?.category) p.category = props.filters.category
  if (props.filters?.search) p.search = props.filters.search
  if (props.filters?.tag) p.tag = props.filters.tag
  return p
}

function applyFilters(overrides) {
  const params = { ...currentParams(), ...overrides }
  Object.keys(params).forEach((k) => {
    if (!params[k]) delete params[k]
  })
  router.get(route('blog.index'), params, { preserveState: true, preserveScroll: true })
}

function filterByCategory(key) {
  applyFilters({ category: key || undefined })
}

function filterByTag(tag) {
  applyFilters({ tag: props.filters?.tag === tag ? undefined : tag })
}

function clearFilters() {
  searchQuery.value = ''
  router.get(route('blog.index'), {}, { preserveState: true })
}
</script>
