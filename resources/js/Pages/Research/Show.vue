<template>
  <MainLayout>
    <Head :title="research.title" />

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
      <Link
        :href="route('research.index')"
        class="reveal inline-flex items-center gap-2 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        К исследованиям
      </Link>

      <header class="reveal mt-8">
        <div class="flex flex-wrap items-center gap-2">
          <RBadge v-if="research.city" variant="primary">{{ research.city.name }}</RBadge>
          <RBadge v-if="research.year" variant="neutral">{{ research.year }}</RBadge>
          <span v-if="research.published_at" class="text-sm text-gray-500">
            {{ formatDate(research.published_at) }}
          </span>
        </div>
        <h1 class="mt-4 text-3xl font-bold text-gray-900 sm:text-4xl">{{ research.title }}</h1>
      </header>

      <div v-if="research.image" class="reveal mt-8 overflow-hidden rounded-2xl bg-gray-100 shadow-sm">
        <div class="aspect-video">
          <img :src="research.image" :alt="research.title" class="h-full w-full object-cover" />
        </div>
      </div>

      <section v-if="research.methodology" class="reveal mt-10">
        <h2 class="text-xl font-bold text-gray-900">Методология</h2>
        <p class="mt-4 whitespace-pre-wrap text-base leading-relaxed text-gray-700">{{ research.methodology }}</p>
      </section>

      <section v-if="research.content" class="reveal mt-10">
        <h2 class="text-xl font-bold text-gray-900">Материал</h2>
        <div class="prose prose-gray mt-4 max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-[#003274]" v-html="research.content" />
      </section>

      <section v-if="research.results_summary" class="reveal mt-10 rounded-2xl border border-[#003274]/15 bg-[#003274]/[0.04] p-6 sm:p-8">
        <h2 class="text-xl font-bold text-[#003274]">Ключевые выводы</h2>
        <div class="prose prose-gray mt-4 max-w-none text-gray-700" v-html="research.results_summary" />
      </section>

      <div v-if="research.pdf_file" class="reveal mt-10">
        <a
          :href="storageUrl(research.pdf_file)"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#003274]/25 transition hover:bg-[#025ea1]"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
          </svg>
          Скачать PDF
        </a>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

defineProps({
  research: Object,
})

function formatDate(value) {
  if (!value) return ''
  const d = typeof value === 'string' ? new Date(value) : value
  return new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }).format(d)
}

function storageUrl(path) {
  if (!path) return '#'
  if (String(path).startsWith('http')) return path
  const p = String(path).replace(/^\//, '')
  return `/storage/${p}`
}
</script>
