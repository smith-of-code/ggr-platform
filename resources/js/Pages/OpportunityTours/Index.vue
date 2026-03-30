<template>
  <MainLayout>
    <Head :title="heroTitle" />

    <!-- Цифры проекта -->
    <section class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ heroTitle }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-white/80">{{ heroDescription }}</p>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="stat in statsData"
            :key="stat.label"
            class="rounded-2xl bg-white/10 px-6 py-8 backdrop-blur-sm transition hover:bg-white/15"
          >
            <p class="text-4xl font-bold">{{ stat.value }}</p>
            <p class="mt-2 text-sm text-white/70">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Проекты программы -->
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Проекты программы</h2>
        <p class="mt-2 text-gray-500">Три направления для развития туризма в атомных городах</p>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <Link
            v-for="d in directionsOrFallback"
            :key="d.slug || d.title"
            :href="d.slug ? route('directions.show', d.slug) : (d.link || '#')"
            class="group overflow-hidden rounded-2xl border border-gray-200 transition hover:border-[#003274]/30 hover:shadow-lg"
          >
            <div class="aspect-[16/9] overflow-hidden">
              <img
                v-if="d.image"
                :src="d.image"
                :alt="d.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
              />
              <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                </svg>
              </div>
            </div>
            <div class="p-6">
              <h3 class="text-xl font-bold text-[#003274] transition group-hover:text-[#025ea1]">
                {{ d.title }}
                <span class="ml-1 inline-block transition group-hover:translate-x-1">&rarr;</span>
              </h3>
              <p class="mt-3 leading-relaxed text-gray-600">{{ d.description }}</p>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Видео туров -->
    <section v-if="videosData.length" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Видео туров</h2>
        <p class="mt-2 text-gray-500">Смотрите как проходят наши туры</p>
        <div class="relative mt-10">
          <div
            ref="videoSlider"
            class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4"
            style="scrollbar-width: none"
          >
            <div
              v-for="(video, i) in videosData"
              :key="i"
              class="w-full flex-shrink-0 snap-center sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]"
            >
              <button
                type="button"
                class="group w-full overflow-hidden rounded-2xl bg-white text-left shadow-sm transition hover:shadow-lg"
                @click="openVideoModal(video)"
              >
                <div class="relative aspect-video bg-gray-200">
                  <img
                    v-if="video.thumbnail"
                    :src="video.thumbnail"
                    :alt="video.title"
                    class="h-full w-full object-cover"
                  />
                  <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                  </div>
                  <div class="absolute inset-0 flex items-center justify-center bg-black/20 transition group-hover:bg-black/30">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 shadow-lg transition group-hover:scale-110">
                      <svg class="ml-1 h-6 w-6 text-[#003274]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z" />
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="p-4">
                  <h3 class="font-semibold text-gray-900">{{ video.title }}</h3>
                </div>
              </button>
            </div>
          </div>
          <button
            @click="scrollVideos(-1)"
            class="absolute -left-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
          >
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="scrollVideos(1)"
            class="absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
          >
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </section>

    <!-- Модальное окно видео -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-200"
        leave-to-class="opacity-0"
      >
        <div
          v-if="activeVideo"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
          @click.self="closeVideoModal"
        >
          <Transition
            enter-active-class="transition duration-200"
            enter-from-class="scale-95 opacity-0"
            leave-active-class="transition duration-200"
            leave-to-class="scale-95 opacity-0"
          >
            <div v-if="activeVideo" class="relative w-full max-w-4xl">
              <button
                type="button"
                @click="closeVideoModal"
                class="absolute -right-2 -top-10 rounded-full p-1.5 text-white/80 transition hover:text-white"
              >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
              <div class="overflow-hidden rounded-2xl bg-black">
                <div class="aspect-video">
                  <video
                    v-if="activeVideo.videoFile"
                    :src="activeVideo.videoFile"
                    controls
                    autoplay
                    class="h-full w-full"
                    :poster="activeVideo.thumbnail || undefined"
                  />
                  <iframe
                    v-else-if="activeVideo.embedUrl"
                    :src="activeVideo.embedUrl"
                    class="h-full w-full"
                    frameborder="0"
                    allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                    allowfullscreen
                  />
                </div>
              </div>
              <p v-if="activeVideo.title" class="mt-3 text-center text-lg font-semibold text-white">{{ activeVideo.title }}</p>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Как принять участие -->
    <section v-if="stepsData.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Как принять участие</h2>
        <p class="mt-2 text-gray-500">Три простых шага для участия в программе</p>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div v-for="(step, i) in stepsData" :key="i" class="relative rounded-2xl border border-gray-200 p-8">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#003274] text-lg font-bold text-white">
              {{ i + 1 }}
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900">{{ step.title }}</h3>
            <p class="mt-2 leading-relaxed text-gray-600">{{ step.description }}</p>
          </div>
        </div>
        <div class="mt-10 text-center">
          <Link
            :href="route('login')"
            class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3.5 font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#025ea1] hover:shadow-xl"
          >
            Личный кабинет
            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
        </div>
      </div>
    </section>

    <!-- Счётчик эмоций -->
    <section v-if="emotionsData.length" class="bg-gradient-to-r from-[#003274] to-[#025ea1] px-4 py-16 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h2 class="text-2xl font-bold sm:text-3xl">Счётчик эмоций</h2>
        <p class="mx-auto mt-2 max-w-xl text-white/70">Впечатления участников наших туров</p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
          <div
            v-for="emotion in emotionsData"
            :key="emotion.label"
            class="rounded-2xl bg-white/10 px-4 py-6 backdrop-blur-sm"
          >
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white/15" v-html="emotionIconSvg(emotion.icon)" />
            <p class="mt-3 text-2xl font-bold">{{ emotion.count }}</p>
            <p class="mt-1 text-sm text-white/70">{{ emotion.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Популярные туры -->
    <section class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <div class="flex items-end justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Популярные туры</h2>
            <p class="mt-2 text-gray-500">Самые востребованные направления программы</p>
          </div>
          <Link
            :href="route('tours.index')"
            class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block"
          >
            Все туры &rarr;
          </Link>
        </div>
        <div class="relative mt-10">
          <div
            ref="toursSlider"
            class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4"
            style="scrollbar-width: none"
          >
            <Link
              v-for="tour in featuredTours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="w-full flex-shrink-0 snap-center sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)]"
            >
              <RCard elevation="raised" hoverable class="group h-full">
                <template #cover>
                  <div class="aspect-video overflow-hidden bg-gray-100">
                    <img
                      v-if="tour.image"
                      :src="tour.image"
                      :alt="tour.title"
                      class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                      <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                      </svg>
                    </div>
                  </div>
                </template>
                <div>
                  <div class="flex items-center gap-2">
                    <RBadge variant="primary" size="sm">{{ projectLabel(tour.project) }}</RBadge>
                    <span class="flex items-center gap-1 text-xs text-gray-400">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                      {{ tour.duration }}
                    </span>
                  </div>
                  <h3 class="mt-3 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                  <p class="mt-1.5 flex items-center gap-1 text-sm text-gray-500">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{ tour.start_city }}
                  </p>
                  <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
                    <p class="text-lg font-bold text-[#003274]">
                      <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                      <template v-else><span class="font-semibold text-green-600">Бесплатно</span></template>
                    </p>
                    <span class="flex items-center gap-1 text-sm font-medium text-[#003274] opacity-0 transition-all duration-300 group-hover:opacity-100">
                      Подробнее
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                      </svg>
                    </span>
                  </div>
                </div>
              </RCard>
            </Link>
          </div>
          <button
            @click="scrollTours(-1)"
            class="absolute -left-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
          >
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="scrollTours(1)"
            class="absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
          >
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        <div v-if="featuredTours.length === 0" class="py-12 text-center text-gray-500">
          Туры скоро появятся
        </div>
        <div class="mt-8 text-center sm:hidden">
          <Link
            :href="route('tours.index')"
            class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3 font-medium text-white transition hover:bg-[#025ea1]"
          >
            Все туры &rarr;
          </Link>
        </div>
      </div>
    </section>

    <!-- Социальные сети -->
    <section v-if="socialsData.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Мы в социальных сетях</h2>
        <p class="mt-2 text-gray-500">Следите за нашими новостями</p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
          <a
            v-for="social in socialsData"
            :key="social.name"
            :href="social.url"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-3 rounded-xl border border-gray-200 px-6 py-4 transition hover:border-[#003274]/30 hover:shadow-md"
          >
            <span class="text-2xl" v-html="socialIconSvg(social.icon)" />
            <span class="font-medium text-gray-700">{{ social.name }}</span>
          </a>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section v-if="faqData.length" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-3xl">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Ответы на популярные вопросы</h2>
        <div class="mt-10 space-y-3">
          <div
            v-for="(item, i) in faqData"
            :key="i"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white"
          >
            <button
              @click="toggleFaq(i)"
              class="flex w-full items-center justify-between px-6 py-5 text-left transition hover:bg-gray-50"
            >
              <span class="pr-4 font-semibold text-gray-900">{{ item.question }}</span>
              <svg
                class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200"
                :class="{ 'rotate-180': openFaq === i }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <Transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-96 opacity-100"
              leave-active-class="transition duration-150 ease-in"
              leave-from-class="max-h-96 opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div v-if="openFaq === i" class="overflow-hidden">
                <div class="faq-answer px-6 pb-5 leading-relaxed text-gray-600" v-html="item.answer" />
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </section>

    <!-- Партнёры — бесконечная карусель -->
    <section v-if="partnersData.length" class="overflow-hidden bg-white py-16">
      <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Партнёры</h2>
        <p class="mt-2 text-gray-500">Организации, поддерживающие программу</p>
      </div>
      <div class="partners-carousel mt-10">
        <div class="partners-track">
          <component
            v-for="(partner, i) in [...partnersData, ...partnersData]"
            :key="'p' + i"
            :is="partner.url ? 'a' : 'div'"
            :href="partner.url || undefined"
            :target="partner.url ? '_blank' : undefined"
            :rel="partner.url ? 'noopener noreferrer' : undefined"
            class="flex h-32 w-[320px] flex-shrink-0 items-center justify-center px-6 transition hover:opacity-60"
            :title="partner.name"
          >
            <img
              v-if="partner.logo"
              :src="partner.logo"
              :alt="partner.name"
              class="max-h-full max-w-full object-contain"
            />
            <span v-else class="text-lg font-semibold text-gray-400">{{ partner.name }}</span>
          </component>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { emotionIcon, socialIcon } from '@/utils/opportunityToursIcons'

const props = defineProps({
  featuredTours: { type: Array, default: () => [] },
  directions: { type: Array, default: () => [] },
  pageData: { type: Object, default: () => ({}) },
})

const d = props.pageData

const heroTitle = computed(() => d.hero_title || 'Туры возможностей')
const heroDescription = computed(() => d.hero_description || 'Программа развития внутреннего туризма в атомных городах России')
const statsData = computed(() => d.stats?.length ? d.stats : defaultStats)
const emotionsData = computed(() => d.emotions?.length ? d.emotions : defaultEmotions)
const partnersData = computed(() => d.partners?.length ? d.partners : defaultPartners)
const socialsData = computed(() => d.socials?.length ? d.socials : defaultSocials)
const faqData = computed(() => d.faq?.length ? d.faq : defaultFaq)
const videosData = computed(() => d.videos?.length ? d.videos : defaultVideos)
const stepsData = computed(() => d.participation_steps?.length ? d.participation_steps : defaultSteps)

const directionsOrFallback = computed(() => {
  const projects = d.projects
  if (projects?.length) {
    return projects
      .map(p => {
        if (p.type === 'direction') {
          const dir = props.directions.find(dd => dd.id === Number(p.direction_id))
          return dir || null
        }
        return {
          title: p.title,
          description: p.description,
          image: p.image || null,
          slug: null,
          link: p.link || null,
        }
      })
      .filter(Boolean)
  }
  if (props.directions.length) return props.directions
  return defaultProjects
})

const defaultStats = [
  { value: '47', label: 'Туров реализовано' },
  { value: '12 000+', label: 'Гостей посетило атомные города' },
  { value: '15', label: 'Городов участвовало в 2025 году' },
  { value: '20', label: 'Городов участвует в 2026 году' },
]

const defaultEmotions = [
  { icon: 'heart', count: '8 540', label: 'Нравится' },
  { icon: 'eye', count: '3 210', label: 'Удивление' },
  { icon: 'fire', count: '5 780', label: 'Огонь' },
  { icon: 'thumbs-up', count: '4 120', label: 'Круто' },
  { icon: 'star', count: '6 350', label: 'Восторг' },
]

const defaultProjects = [
  { title: 'Старт в Атомград', description: 'Программа знакомства с атомными городами России.' },
  { title: 'Атомы вкуса', description: 'Гастрономический проект, раскрывающий кулинарные традиции.' },
  { title: 'Лучшие люди Росатома', description: 'Истории людей, которые создают будущее.' },
]

const defaultPartners = [
  { name: 'Росатом', url: 'https://rosatom.ru', logo: null },
  { name: 'ТВЭЛ', url: 'https://tvel.ru', logo: null },
  { name: 'АРМЗ', url: 'https://armz.ru', logo: null },
]

const defaultSocials = [
  { name: 'ВКонтакте', url: 'https://vk.com/rosatom_travel', icon: 'vk' },
  { name: 'Telegram', url: 'https://t.me/rosatom_travel', icon: 'telegram' },
]

const defaultFaq = [
  { question: 'Кто может принять участие в турах?', answer: 'Участие открыто для всех желающих старше 18 лет.' },
]

const defaultVideos = [
  { title: 'Тур в Саров', embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239000&hd=2' },
]

const defaultSteps = [
  { title: 'Зарегистрируйтесь', description: 'Создайте личный кабинет на платформе.' },
  { title: 'Выберите тур', description: 'Ознакомьтесь с каталогом доступных туров.' },
  { title: 'Отправляйтесь в путешествие', description: 'Получите подтверждение и наслаждайтесь путешествием.' },
]

function emotionIconSvg(key) {
  return emotionIcon(key, 'h-6 w-6 text-white')
}

function socialIconSvg(key) {
  return socialIcon(key, 'h-6 w-6')
}

function projectLabel(key) {
  const labels = { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }
  return labels[key] || key || ''
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

const openFaq = ref(null)
const videoSlider = ref(null)
const toursSlider = ref(null)
const activeVideo = ref(null)

function toggleFaq(index) {
  openFaq.value = openFaq.value === index ? null : index
}

function scrollVideos(direction) {
  if (!videoSlider.value) return
  const cardWidth = videoSlider.value.firstElementChild?.offsetWidth ?? 400
  videoSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

function scrollTours(direction) {
  if (!toursSlider.value) return
  const cardWidth = toursSlider.value.firstElementChild?.offsetWidth ?? 300
  toursSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

function openVideoModal(video) {
  if (!video.videoFile && !video.embedUrl) return
  activeVideo.value = video
  document.body.style.overflow = 'hidden'
}

function closeVideoModal() {
  activeVideo.value = null
  document.body.style.overflow = ''
}
</script>

<style scoped>
.faq-answer :deep(a) {
  color: #003274;
  text-decoration: underline;
  transition: color 0.15s;
}
.faq-answer :deep(a:hover) {
  color: #025ea1;
}

.partners-carousel {
  width: 100%;
  overflow: hidden;
  mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
}
.partners-track {
  display: flex;
  width: max-content;
  animation: scroll-partners 25s linear infinite;
}
.partners-track:hover {
  animation-play-state: paused;
}
@keyframes scroll-partners {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
</style>
