<template>
  <MainLayout>
    <Head :title="`${product.title} — ВШГР`" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <Link
        :href="route('education.index')"
        class="inline-flex items-center gap-2 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Каталог программ
      </Link>

      <!-- Hero -->
      <div class="mt-8 overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 via-[#003274] to-[#024a8f] shadow-xl">
        <div class="lg:grid lg:grid-cols-2 lg:gap-0">
          <div class="relative aspect-[16/10] max-h-[22rem] lg:max-h-none lg:min-h-[20rem]">
            <img v-if="product.image" :src="product.image" :alt="product.title" class="h-full w-full object-cover opacity-95 lg:rounded-l-3xl" />
            <div v-else class="flex h-full min-h-[12rem] items-center justify-center bg-white/5 lg:rounded-l-3xl">
              <svg class="h-20 w-20 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
              </svg>
            </div>
          </div>
          <div class="flex flex-col justify-center px-8 py-10 text-white lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-widest text-white/60">{{ heroSubtitle }}</p>
            <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">{{ product.title }}</h1>
            <p v-if="product.description" class="mt-4 text-sm leading-relaxed text-white/80">{{ product.description }}</p>
            <div v-if="productType === 'education'" class="mt-6 flex flex-wrap gap-2">
              <span v-if="product.duration" class="rounded-full bg-white/15 px-4 py-1.5 text-xs font-semibold backdrop-blur-sm">{{ product.duration }}</span>
              <span v-if="product.format" class="rounded-full bg-white px-4 py-1.5 text-xs font-semibold text-[#003274]">{{ product.format }}</span>
              <span v-if="product.price_info" class="rounded-full border border-white/40 bg-white/10 px-4 py-1.5 text-xs font-semibold">{{ product.price_info }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Section navigation (education only) -->
      <nav v-if="productType === 'education' && enabledSections.length > 1" class="sticky top-0 z-10 -mx-4 mt-8 overflow-x-auto bg-white/95 px-4 py-3 shadow-sm backdrop-blur-sm sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="flex gap-2">
          <a
            v-for="s in enabledSections"
            :key="s.slug"
            :href="`#section-${s.slug}`"
            class="shrink-0 rounded-full border border-gray-200 px-4 py-1.5 text-xs font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]"
          >
            {{ s.label }}
          </a>
        </div>
      </nav>

      <!-- Education type -->
      <div v-if="productType === 'education'" class="mx-auto mt-12 max-w-4xl space-y-16">
        <!-- Legacy content fallback -->
        <div v-if="product.content && !hasSection('description_goal')" class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="product.content" />
        <section v-if="product.target_audience && !hasSection('target_audience')" class="rounded-2xl border border-[#003274]/15 bg-[#003274]/[0.04] p-8">
          <h2 class="text-lg font-bold text-[#003274]">Для кого программа</h2>
          <div class="html-content mt-4 max-w-none text-gray-700" v-html="product.target_audience" />
        </section>

        <!-- Sections -->
        <template v-for="s in enabledSections" :key="s.slug">
          <!-- RichText sections -->
          <section
            v-if="s.type === 'richtext'"
            :id="`section-${s.slug}`"
            class="scroll-mt-20"
          >
            <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ s.label }}</h2>
            <div class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="s.content" />
          </section>

          <!-- Experts -->
          <section v-else-if="s.slug === 'experts'" :id="`section-${s.slug}`" class="scroll-mt-20">
            <h2 class="mb-8 text-2xl font-bold text-gray-900">{{ s.label }}</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="(expert, idx) in (sections.experts?.items || [])" :key="idx" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                <div v-if="expert.photo" class="aspect-[4/3] overflow-hidden bg-gray-100">
                  <img :src="expert.photo" :alt="expert.name" class="h-full w-full object-cover" />
                </div>
                <div v-else class="flex aspect-[4/3] items-center justify-center bg-gray-50">
                  <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0" />
                  </svg>
                </div>
                <div class="p-5">
                  <p class="font-bold text-gray-900">{{ expert.name }}</p>
                  <p v-if="expert.position" class="mt-0.5 text-sm text-[#003274]">{{ expert.position }}</p>
                  <p v-if="expert.bio" class="mt-2 text-sm leading-relaxed text-gray-600">{{ expert.bio }}</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Regulation -->
          <section v-else-if="s.slug === 'regulation'" :id="`section-${s.slug}`" class="scroll-mt-20">
            <div class="rounded-2xl bg-[#e9eef4] p-8">
              <h2 class="mb-4 text-2xl font-bold text-gray-900">{{ s.label }}</h2>
              <div v-if="s.content" class="html-content mb-6 max-w-none text-gray-700" v-html="s.content" />
              <a
                v-if="product.regulation_file"
                :href="product.regulation_file"
                target="_blank"
                class="inline-flex items-center gap-2 rounded-xl bg-[#003274] px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-[#025ea1]"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Скачать положение
              </a>
            </div>
          </section>

          <!-- Application form -->
          <section v-else-if="s.slug === 'application_form'" id="application-form" class="scroll-mt-24 border-t border-gray-100 pt-12">
            <div v-if="s.content" class="html-content mb-6 max-w-none text-gray-700" v-html="s.content" />
            <ApplicationForm :product="product" />
          </section>

          <!-- Training request -->
          <section v-else-if="s.slug === 'training_request'" :id="`section-${s.slug}`" class="scroll-mt-20">
            <div class="rounded-2xl bg-gradient-to-br from-[#003274] to-[#024a8f] p-8 text-white">
              <h2 class="mb-4 text-2xl font-bold">{{ s.label }}</h2>
              <div v-if="s.content" class="html-content-light max-w-none text-white/90" v-html="s.content" />
            </div>
          </section>
        </template>

        <!-- Fallback application form if no section enabled -->
        <section v-if="!hasSection('application_form')" id="application-form" class="scroll-mt-24 border-t border-gray-100 pt-12">
          <ApplicationForm :product="product" />
        </section>
      </div>

      <!-- Partner type -->
      <div v-else-if="productType === 'partner'" class="mx-auto mt-12 max-w-3xl space-y-12">
        <section v-if="sectionContent('description_goal')">
          <h2 class="mb-6 text-2xl font-bold text-gray-900">О программе</h2>
          <div class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="sectionContent('description_goal')" />
        </section>

        <section v-if="sectionContent('participation_conditions')" class="rounded-2xl border border-amber-200/50 bg-amber-50/50 p-8">
          <h2 class="mb-4 text-xl font-bold text-gray-900">Условия участия</h2>
          <div class="html-content max-w-none text-gray-700" v-html="sectionContent('participation_conditions')" />
        </section>

        <!-- Legacy content fallback -->
        <div v-if="product.content && !sectionContent('description_goal')" class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="product.content" />
      </div>

      <!-- International type -->
      <div v-else-if="productType === 'international'" class="mx-auto mt-12 max-w-4xl space-y-12">
        <section v-if="sectionContent('description_goal')">
          <h2 class="mb-6 text-2xl font-bold text-gray-900">О программе</h2>
          <div class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="sectionContent('description_goal')" />
        </section>

        <!-- Countries -->
        <div v-if="product.countries?.length" class="space-y-8">
          <div
            v-for="(country, idx) in product.countries"
            :key="country.slug || idx"
            :id="`country-${country.slug || idx}`"
            class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
          >
            <div class="border-b border-gray-100 bg-gradient-to-r from-[#003274]/5 to-transparent px-8 py-5">
              <h3 class="text-xl font-bold text-gray-900">{{ country.name }}</h3>
              <p v-if="country.description" class="mt-1 text-sm text-gray-600">{{ country.description }}</p>
            </div>
            <div v-if="country.content" class="html-content p-8 text-gray-700" v-html="country.content" />
          </div>
        </div>

        <!-- Legacy content fallback -->
        <div v-if="product.content && !sectionContent('description_goal') && !product.countries?.length" class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="product.content" />
      </div>

      <!-- Default fallback (legacy products without type) -->
      <div v-else class="mx-auto mt-12 max-w-3xl">
        <div v-if="product.content" class="html-content max-w-none text-base leading-relaxed text-gray-700" v-html="product.content" />
        <section v-if="product.target_audience" class="mt-12 rounded-2xl border border-[#003274]/15 bg-[#003274]/[0.04] p-8">
          <h2 class="text-lg font-bold text-[#003274]">Для кого программа</h2>
          <div class="html-content mt-4 max-w-none text-gray-700" v-html="product.target_audience" />
        </section>
        <section id="application-form" class="mt-16 scroll-mt-24 border-t border-gray-100 pt-12">
          <ApplicationForm :product="product" />
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import ApplicationForm from './Partials/ApplicationForm.vue'

const SECTION_LABELS = {
  description_goal: 'Описание и цель',
  results: 'Результаты работы',
  streams_formats: 'Потоки / форматы',
  target_audience: 'Целевая аудитория',
  directions: 'Направления',
  what_we_offer: 'Что мы предлагаем',
  selection_criteria: 'Критерии участия / отбора',
  experts: 'Эксперты',
  best_cases: 'Лучшие кейсы',
  regulation: 'Положение',
  application_form: 'Подача заявки',
  training_request: 'Заявка на обучение',
  participation_conditions: 'Условия участия',
}

const SPECIAL_SECTIONS = ['experts', 'regulation', 'application_form', 'training_request']

const props = defineProps({
  product: { type: Object, required: true },
})

const productType = computed(() => props.product.type || 'education')
const sections = computed(() => props.product.sections || {})

const heroSubtitle = computed(() => {
  const map = {
    education: 'Образовательная программа',
    partner: 'Партнёрская программа',
    international: 'Международная программа',
  }
  return map[productType.value] || 'Программа'
})

const enabledSections = computed(() => {
  const result = []
  for (const [slug, data] of Object.entries(sections.value)) {
    if (!data?.enabled) continue
    const isSpecial = SPECIAL_SECTIONS.includes(slug)
    result.push({
      slug,
      label: SECTION_LABELS[slug] || slug,
      content: data.content || '',
      type: isSpecial ? slug : 'richtext',
    })
  }
  return result
})

function hasSection(slug) {
  return sections.value[slug]?.enabled && (sections.value[slug]?.content || slug === 'application_form' || slug === 'experts')
}

function sectionContent(slug) {
  const s = sections.value[slug]
  return s?.enabled ? (s.content || null) : null
}
</script>

<style scoped>
.html-content :deep(p) { margin-bottom: 1rem; }
.html-content :deep(p:last-child) { margin-bottom: 0; }
.html-content :deep(a) { color: #003274; text-decoration: underline; text-underline-offset: 2px; }
.html-content :deep(ul),
.html-content :deep(ol) { margin: 0.75rem 0 1rem; padding-left: 1.25rem; }
.html-content :deep(ul) { list-style-type: disc; }
.html-content :deep(ol) { list-style-type: decimal; }
.html-content :deep(h2) { margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 700; color: rgb(17 24 39); }
.html-content :deep(h3) { margin-top: 1.25rem; margin-bottom: 0.5rem; font-size: 1.125rem; font-weight: 600; color: rgb(31 41 55); }
.html-content :deep(img) { margin-top: 1rem; margin-bottom: 1rem; max-width: 100%; border-radius: 0.75rem; }
.html-content-light :deep(a) { color: white; text-decoration: underline; }
.html-content-light :deep(p) { margin-bottom: 0.75rem; }
</style>
