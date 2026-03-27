<template>
  <MainLayout>
    <Head title="Туры возможностей" />

    <!-- Цифры проекта -->
    <section class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Туры возможностей</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-white/80">
          Программа развития внутреннего туризма в атомных городах России
        </p>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="stat in stats"
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
          <a
            v-for="project in projects"
            :key="project.title"
            :href="project.link"
            class="group overflow-hidden rounded-2xl border border-gray-200 transition hover:border-[#003274]/30 hover:shadow-lg"
          >
            <div class="aspect-[16/9] overflow-hidden">
              <img
                :src="project.image"
                :alt="project.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
              />
            </div>
            <div class="p-6">
              <h3 class="text-xl font-bold text-[#003274] transition group-hover:text-[#025ea1]">
                {{ project.title }}
                <span class="ml-1 inline-block transition group-hover:translate-x-1">&rarr;</span>
              </h3>
              <p class="mt-3 leading-relaxed text-gray-600">{{ project.description }}</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- Видео туров -->
    <section class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
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
              v-for="(video, i) in videos"
              :key="i"
              class="w-full flex-shrink-0 snap-center sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]"
            >
              <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="aspect-video">
                  <iframe
                    :src="video.embedUrl"
                    class="h-full w-full"
                    frameborder="0"
                    allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                    allowfullscreen
                  />
                </div>
                <div class="p-4">
                  <h3 class="font-semibold text-gray-900">{{ video.title }}</h3>
                </div>
              </div>
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

    <!-- Как принять участие -->
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Как принять участие</h2>
        <p class="mt-2 text-gray-500">Три простых шага для участия в программе</p>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div v-for="(step, i) in participationSteps" :key="i" class="relative rounded-2xl border border-gray-200 p-8">
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
    <section class="bg-gradient-to-r from-[#003274] to-[#025ea1] px-4 py-16 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h2 class="text-2xl font-bold sm:text-3xl">Счётчик эмоций</h2>
        <p class="mx-auto mt-2 max-w-xl text-white/70">Впечатления участников наших туров</p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
          <div
            v-for="emotion in emotions"
            :key="emotion.label"
            class="rounded-2xl bg-white/10 px-4 py-6 backdrop-blur-sm"
          >
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white/15" v-html="emotion.icon" />
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
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Мы в социальных сетях</h2>
        <p class="mt-2 text-gray-500">Следите за нашими новостями</p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
          <a
            v-for="social in socials"
            :key="social.name"
            :href="social.url"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-3 rounded-xl border border-gray-200 px-6 py-4 transition hover:border-[#003274]/30 hover:shadow-md"
          >
            <span class="text-2xl" v-html="social.icon" />
            <span class="font-medium text-gray-700">{{ social.name }}</span>
          </a>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-3xl">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Ответы на популярные вопросы</h2>
        <div class="mt-10 space-y-3">
          <div
            v-for="(item, i) in faq"
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
                <p class="px-6 pb-5 leading-relaxed text-gray-600">{{ item.answer }}</p>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </section>

    <!-- Партнёры -->
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl text-center">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Партнёры</h2>
        <p class="mt-2 text-gray-500">Организации, поддерживающие программу</p>
        <div class="mt-10 grid grid-cols-2 items-center gap-8 sm:grid-cols-3 lg:grid-cols-6">
          <div
            v-for="partner in partners"
            :key="partner.name"
            class="flex flex-col items-center gap-3 rounded-xl px-4 py-6 transition hover:bg-gray-50"
          >
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-2xl font-bold text-[#003274]">
              {{ partner.name.charAt(0) }}
            </div>
            <span class="text-sm font-medium text-gray-700">{{ partner.name }}</span>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  featuredTours: { type: Array, default: () => [] },
})

const stats = [
  { value: '47', label: 'Туров реализовано' },
  { value: '12 000+', label: 'Гостей посетило атомные города' },
  { value: '15', label: 'Городов участвовало в 2025 году' },
  { value: '20', label: 'Городов участвует в 2026 году' },
]

const projects = [
  {
    image: 'https://loremflickr.com/800/450/city,architecture',
    title: 'Старт в Атомград',
    description: 'Программа знакомства с атомными городами России. Уникальная возможность увидеть современные технологии и богатую историю городов атомной промышленности.',
    link: '#',
  },
  {
    image: 'https://loremflickr.com/800/450/food,cooking',
    title: 'Атомы вкуса',
    description: 'Гастрономический проект, раскрывающий кулинарные традиции атомных городов. Авторские рецепты, мастер-классы и дегустации от лучших шеф-поваров.',
    link: '#',
  },
  {
    image: 'https://loremflickr.com/800/450/team,professionals',
    title: 'Лучшие люди Росатома',
    description: 'Истории людей, которые создают будущее. Встречи с учёными, инженерами и руководителями, посвятившими жизнь развитию атомной отрасли.',
    link: '#',
  },
]

const videos = [
  { title: 'Тур в Саров', embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239000&hd=2' },
  { title: 'Тур в Обнинск', embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239001&hd=2' },
  { title: 'Тур в Озёрск', embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239002&hd=2' },
  { title: 'Тур в Северск', embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239003&hd=2' },
]

const participationSteps = [
  {
    title: 'Зарегистрируйтесь',
    description: 'Создайте личный кабинет на платформе, заполните профиль и укажите свои интересы для подбора подходящего тура.',
  },
  {
    title: 'Выберите тур',
    description: 'Ознакомьтесь с каталогом доступных туров, выберите подходящие даты и направление. Подайте заявку на участие.',
  },
  {
    title: 'Отправляйтесь в путешествие',
    description: 'Получите подтверждение участия, подготовьтесь к поездке по нашим рекомендациям и наслаждайтесь незабываемым путешествием.',
  },
]

const emotions = [
  {
    icon: '<svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>',
    count: '8 540', label: 'Нравится',
  },
  {
    icon: '<svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
    count: '3 210', label: 'Удивление',
  },
  {
    icon: '<svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>',
    count: '5 780', label: 'Огонь',
  },
  {
    icon: '<svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V3a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m7.348-7.052a9.024 9.024 0 0 0-5.197-2.353M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" /></svg>',
    count: '4 120', label: 'Круто',
  },
  {
    icon: '<svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>',
    count: '6 350', label: 'Восторг',
  },
]

function projectLabel(key) {
  const labels = { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }
  return labels[key] || key || ''
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

const socials = [
  { name: 'ВКонтакте', url: 'https://vk.com/rosatom_travel', icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.391 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.12-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.847 2.49 2.27 4.675 2.862 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.204.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.15-3.574 2.15-3.574.119-.254.322-.491.762-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>' },
  { name: 'Telegram', url: 'https://t.me/rosatom_travel', icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>' },
  { name: 'YouTube', url: 'https://youtube.com/@rosatom_travel', icon: '<svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>' },
]

const faq = [
  {
    question: 'Кто может принять участие в турах?',
    answer: 'Участие в турах открыто для всех желающих старше 18 лет. Для некоторых туров в закрытые города может потребоваться предварительное оформление пропуска, о чём мы информируем заранее.',
  },
  {
    question: 'Как подать заявку на тур?',
    answer: 'Зарегистрируйтесь на платформе, перейдите в каталог туров, выберите интересующий тур и нажмите кнопку «Оставить заявку». Наш менеджер свяжется с вами для уточнения деталей.',
  },
  {
    question: 'Включено ли проживание в стоимость тура?',
    answer: 'В большинстве туров проживание включено в стоимость. Точная информация о том, что входит в стоимость, указана в описании каждого конкретного тура.',
  },
  {
    question: 'Можно ли участвовать с детьми?',
    answer: 'Да, некоторые туры адаптированы для семейного посещения. В каталоге используйте фильтр «Для детей», чтобы найти подходящие программы.',
  },
  {
    question: 'Как отменить или перенести участие?',
    answer: 'Вы можете отменить участие не позднее чем за 7 дней до начала тура через личный кабинет или связавшись с нашей службой поддержки. Перенос на другие даты возможен при наличии свободных мест.',
  },
  {
    question: 'Предоставляется ли трансфер?',
    answer: 'В большинстве туров трансфер от вокзала/аэропорта до места размещения включён. Подробности указаны в описании каждого тура.',
  },
]

const partners = [
  { name: 'Росатом' },
  { name: 'ТВЭЛ' },
  { name: 'АРМЗ' },
  { name: 'АСЭ' },
  { name: 'РАСУ' },
  { name: 'Атомэнергопроект' },
]

const openFaq = ref(null)
const videoSlider = ref(null)
const toursSlider = ref(null)

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
</script>
