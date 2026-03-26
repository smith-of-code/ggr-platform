<template>
  <MainLayout>
    <div>
      <!-- Hero -->
      <section class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-24 text-white sm:px-6 sm:py-32 lg:px-8 lg:py-40">
        <img
          src="https://images.unsplash.com/photo-1513326738677-b964603b136d?w=1600&h=600&fit=crop"
          alt=""
          class="absolute inset-0 h-full w-full object-cover opacity-15 mix-blend-luminosity"
        />
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.1),transparent_70%)]" />
        <div class="relative mx-auto max-w-7xl text-center">
          <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
            Гостеприимные города<br class="hidden sm:block" /> Росатома
          </h1>
          <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/85">
            Цифровая экосистема для развития туристического, образовательного и предпринимательского потенциала атомных городов
          </p>
          <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link
              :href="route('tours.index')"
              class="group rounded-xl bg-white px-8 py-3.5 font-semibold text-[#003274] shadow-lg shadow-black/10 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
            >
              Выбрать тур
              <span class="ml-1 inline-block transition group-hover:translate-x-1">&rarr;</span>
            </Link>
            <Link
              :href="route('cities.index')"
              class="rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
            >
              Города
            </Link>
          </div>
        </div>
      </section>

      <!-- Stats -->
      <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <RCard v-for="(stat, i) in statCards" :key="i" elevation="raised" hoverable class="reveal text-center" :class="'reveal-delay-' + (i + 1)">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50">
              <span v-html="stat.icon" />
            </div>
            <p class="mt-4 text-3xl font-bold text-[#003274]">{{ stat.value }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ stat.label }}</p>
          </RCard>
        </div>
      </section>

      <!-- Featured tours -->
      <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Популярные туры</h2>
              <p class="mt-2 text-gray-500">Откройте для себя уникальные маршруты</p>
            </div>
            <Link
              :href="route('tours.index')"
              class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block"
            >
              Все туры &rarr;
            </Link>
          </div>
          <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(tour, i) in featuredTours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="reveal"
              :class="'reveal-delay-' + (i + 1)"
            >
            <RCard elevation="raised" hoverable class="group h-full">
              <template #cover>
                <div class="aspect-video overflow-hidden">
                  <img
                    v-if="tour.image"
                    :src="tour.image"
                    :alt="tour.title"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                  />
                  <div v-else class="h-full w-full bg-gray-200" />
                </div>
              </template>
              <div>
                <div class="flex items-center gap-2">
                  <RBadge variant="info" size="sm">{{ tour.start_city }}</RBadge>
                  <span class="text-xs text-gray-400">{{ tour.duration }}</span>
                </div>
                <h3 class="mt-3 text-lg font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ tour.description }}</p>
                <div class="mt-4 flex items-center justify-between">
                  <p class="text-lg font-bold text-[#003274]">
                    <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                    <template v-else><span class="text-green-600">Бесплатно</span></template>
                  </p>
                  <span class="text-sm font-medium text-[#003274] opacity-0 transition group-hover:opacity-100">Подробнее &rarr;</span>
                </div>
              </div>
            </RCard>
            </Link>
          </div>
          <div v-if="featuredTours.length === 0" class="py-12 text-center text-gray-500">
            Туры скоро появятся
          </div>
          <div class="mt-10 text-center sm:hidden">
            <Link
              :href="route('tours.index')"
              class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3 font-medium text-white transition hover:bg-[#025ea1]"
            >
              Все туры &rarr;
            </Link>
          </div>
        </div>
      </section>

      <!-- Cities -->
      <section v-if="cities?.length" class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Атомные города</h2>
              <p class="mt-2 text-gray-500">Современные города с уникальной историей</p>
            </div>
            <Link
              :href="route('cities.index')"
              class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block"
            >
              Все города &rarr;
            </Link>
          </div>
          <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(city, i) in cities"
              :key="city.id"
              :href="route('cities.show', city.slug)"
              class="reveal group relative overflow-hidden rounded-xl shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
              :class="'reveal-delay-' + (i + 1)"
            >
              <div class="aspect-[4/3] overflow-hidden">
                <img
                  v-if="city.image"
                  :src="city.image"
                  :alt="city.name"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                />
                <div v-else class="h-full w-full bg-gray-200" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
              <div class="absolute bottom-0 left-0 right-0 p-5">
                <h3 class="text-xl font-bold text-white">{{ city.name }}</h3>
                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-white/75">{{ city.description }}</p>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Interactive Yandex Map -->
      <section
        v-if="allCities?.length"
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_30%_20%,rgba(255,255,255,0.12),transparent_55%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">География проекта</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-white/80 sm:text-base">
              Атомные города на карте России — нажмите на маркер, чтобы узнать о городе и перейти на его страницу
            </p>
          </div>
          <div class="reveal mx-auto mt-10 overflow-hidden rounded-2xl shadow-2xl shadow-black/30" style="height: 520px">
            <YandexCityMap :cities="allCities" />
          </div>
        </div>
      </section>

      <!-- Атомы вкуса -->
      <section v-if="latestRecipes?.length" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Книга <span class="text-[#003274]">атомных</span> рецептов</h2>
              <p class="mt-2 max-w-xl text-gray-500">
                Блюда из городов атомной отрасли — откройте для себя кулинарные традиции регионов
              </p>
            </div>
            <Link
              :href="route('recipes.index')"
              class="group flex items-center gap-1.5 text-sm font-semibold text-[#003274] transition hover:text-[#025ea1]"
            >
              Все рецепты
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </Link>
          </div>

          <div class="reveal mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="recipe in latestRecipes"
              :key="recipe.id"
              :href="route('recipes.show', recipe.slug)"
              class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
            >
              <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                <img
                  v-if="recipe.image"
                  :src="recipe.image"
                  :alt="recipe.title"
                  class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
                  <svg class="h-12 w-12 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                  </svg>
                </div>
                <div v-if="recipe.cooking_time" class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm backdrop-blur-sm">
                  {{ recipe.cooking_time }}
                </div>
              </div>
              <div class="p-5">
                <h3 class="text-base font-bold text-gray-900 transition group-hover:text-[#003274]">{{ recipe.title }}</h3>
                <p v-if="recipe.city" class="mt-1 text-xs font-medium text-[#025ea1]">{{ recipe.city.name }}</p>
                <p v-if="recipe.description" class="mt-2 line-clamp-2 text-sm text-gray-500">{{ recipe.description }}</p>
                <div v-if="recipe.difficulty || recipe.servings" class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                  <span v-if="recipe.difficulty">{{ recipe.difficulty }}</span>
                  <span v-if="recipe.servings">{{ recipe.servings }} порц.</span>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Timeline -->
      <section class="bg-slate-50 px-4 py-20 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-5xl">
          <div class="reveal mb-14 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Хронология событий</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-500">
              Ключевые новости, события и вехи развития программы
            </p>
          </div>
          <div
            v-if="sortedTimelineEvents.length"
            class="relative"
          >
            <div
              class="absolute bottom-0 left-4 top-0 w-px bg-gradient-to-b from-[#003274]/25 via-[#003274]/15 to-transparent md:left-1/2 md:-translate-x-1/2"
              aria-hidden="true"
            />
            <ul class="space-y-0">
              <li
                v-for="(event, i) in sortedTimelineEvents"
                :key="event.id ?? i"
                class="relative pb-14 md:grid md:grid-cols-2 md:gap-0"
              >
                <div
                  class="absolute left-4 top-2 z-10 flex h-4 w-4 -translate-x-1/2 items-center justify-center rounded-full border-4 border-slate-50 bg-[#003274] shadow md:left-1/2"
                  aria-hidden="true"
                />
                <template v-if="i % 2 === 0">
                  <div
                    class="reveal pl-12 md:pr-10 md:text-right"
                    :class="'reveal-delay-' + ((i % 5) + 1)"
                  >
                    <article
                      class="inline-block max-w-md rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-md shadow-gray-200/60 md:text-right"
                    >
                      <time class="text-sm font-semibold text-[#003274]">{{ formatEventDate(event.event_date) }}</time>
                      <h3 class="mt-2 text-lg font-bold text-gray-900">{{ event.title }}</h3>
                      <p v-if="event.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ event.description }}</p>
                      <div class="mt-3 flex flex-wrap items-center gap-2 md:justify-end">
                        <span
                          class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                          :class="timelineTypeClass(event.type)"
                        >
                          {{ timelineTypeLabel(event.type) }}
                        </span>
                        <a
                          v-if="event.link"
                          :href="event.link"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center rounded-lg border border-[#003274]/20 bg-white px-3 py-1.5 text-xs font-semibold text-[#003274] transition hover:border-[#003274]/40 hover:bg-[#003274]/5"
                        >
                          Подробнее
                        </a>
                      </div>
                    </article>
                  </div>
                  <div class="hidden md:block" />
                </template>
                <template v-else>
                  <div class="hidden md:block" />
                  <div
                    class="reveal pl-12 md:pl-10"
                    :class="'reveal-delay-' + ((i % 5) + 1)"
                  >
                    <article class="max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-md shadow-gray-200/60">
                      <time class="text-sm font-semibold text-[#003274]">{{ formatEventDate(event.event_date) }}</time>
                      <h3 class="mt-2 text-lg font-bold text-gray-900">{{ event.title }}</h3>
                      <p v-if="event.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ event.description }}</p>
                      <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                          class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                          :class="timelineTypeClass(event.type)"
                        >
                          {{ timelineTypeLabel(event.type) }}
                        </span>
                        <a
                          v-if="event.link"
                          :href="event.link"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center rounded-lg border border-[#003274]/20 bg-white px-3 py-1.5 text-xs font-semibold text-[#003274] transition hover:border-[#003274]/40 hover:bg-[#003274]/5"
                        >
                          Подробнее
                        </a>
                      </div>
                    </article>
                  </div>
                </template>
              </li>
            </ul>
          </div>
          <p
            v-else
            class="reveal rounded-2xl border border-dashed border-gray-200 bg-white py-12 text-center text-gray-500"
          >
            События появятся в ближайшее время
          </p>
        </div>
      </section>

      <!-- CTA -->
      <section class="reveal mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003274] to-[#025ea1] px-8 py-16 text-center text-white shadow-xl sm:px-16">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.1),transparent_60%)]" />
          <div class="relative">
            <h2 class="text-2xl font-bold sm:text-3xl">Хотите узнать подробнее о программе?</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">
              Оставьте заявку, и мы свяжемся с вами в ближайшее время
            </p>
            <Link
              :href="route('tours.index')"
              class="mt-8 inline-flex items-center rounded-xl bg-white px-8 py-3.5 font-semibold text-[#003274] shadow-lg transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
            >
              Выбрать тур
            </Link>
          </div>
        </div>
      </section>

      <!-- Contact -->
      <section
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#024a85] to-[#025ea1] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_80%_0%,rgba(255,255,255,0.12),transparent_50%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">Хочу узнать подробнее</h2>
            <p class="mx-auto mt-3 max-w-2xl text-base text-white/85">
              Заполните форму — мы ответим на вопросы о турах, городах и возможностях программы
            </p>
          </div>
          <div class="reveal grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="flex flex-col justify-center space-y-6 text-white/90">
              <p class="text-lg leading-relaxed">
                Команда проекта поможет подобрать маршрут, расскажет о датах и условиях участия.
              </p>
              <ul class="space-y-3 text-sm text-white/80">
                <li class="flex items-start gap-2">
                  <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300" />
                  Ответ в рабочие дни в течение 1–2 дней
                </li>
                <li class="flex items-start gap-2">
                  <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300" />
                  Консультация без обязательства записи на тур
                </li>
              </ul>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-2xl shadow-black/20 backdrop-blur-md sm:p-8">
              <div
                v-if="flashSuccess"
                role="status"
                class="mb-6 rounded-xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-3 text-sm text-emerald-50"
              >
                {{ flashSuccess }}
              </div>
              <form class="space-y-4" @submit.prevent="submitContact">
                <div>
                  <label for="contact-name" class="mb-1.5 block text-xs font-medium text-white/80">Имя</label>
                  <input
                    id="contact-name"
                    v-model="contactForm.name"
                    type="text"
                    required
                    autocomplete="name"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="Как к вам обращаться"
                  />
                  <p v-if="contactForm.errors.name" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.name }}</p>
                </div>
                <div>
                  <label for="contact-email" class="mb-1.5 block text-xs font-medium text-white/80">E-mail</label>
                  <input
                    id="contact-email"
                    v-model="contactForm.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="name@example.com"
                  />
                  <p v-if="contactForm.errors.email" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.email }}</p>
                </div>
                <div>
                  <label for="contact-phone" class="mb-1.5 block text-xs font-medium text-white/80">Телефон</label>
                  <input
                    id="contact-phone"
                    v-model="contactForm.phone"
                    type="tel"
                    autocomplete="tel"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="+7 …"
                  />
                  <p v-if="contactForm.errors.phone" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.phone }}</p>
                </div>
                <div>
                  <label for="contact-message" class="mb-1.5 block text-xs font-medium text-white/80">Сообщение</label>
                  <textarea
                    id="contact-message"
                    v-model="contactForm.message"
                    required
                    rows="4"
                    class="w-full resize-y rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="Ваш вопрос или пожелание"
                  />
                  <p v-if="contactForm.errors.message" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.message }}</p>
                </div>
                <button
                  type="submit"
                  :disabled="contactForm.processing"
                  class="w-full rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-[#003274] shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {{ contactForm.processing ? 'Отправка…' : 'Отправить' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import YandexCityMap from '@/Components/YandexCityMap.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  featuredTours: Array,
  cities: Array,
  allCities: { type: Array, default: () => [] },
  latestRecipes: { type: Array, default: () => [] },
  stats: Object,
  timelineEvents: {
    type: Array,
    default: () => [],
  },
  userFavorites: {
    type: Object,
    default: null,
  },
})

const page = usePage()

const flashSuccess = computed(() => page.props.flash?.success ?? null)

const sortedTimelineEvents = computed(() => {
  const raw = props.timelineEvents || []
  return [...raw].sort(
    (a, b) => new Date(a.event_date).getTime() - new Date(b.event_date).getTime(),
  )
})

const timelineEventsCount = computed(() => (props.timelineEvents || []).length)

const statCards = computed(() => [
  {
    value: props.stats?.cities ?? 0,
    label: 'Атомных городов',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>',
  },
  {
    value: props.stats?.tours ?? 0,
    label: 'Туров возможностей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>',
  },
  {
    value: timelineEventsCount.value,
    label: 'Событий в хронологии',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>',
  },
  {
    value: '3000+',
    label: 'Гостей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>',
  },
])

const contactForm = useForm({
  name: '',
  email: '',
  phone: '',
  message: '',
})

function submitContact() {
  contactForm.post(route('contact.submit'), {
    preserveScroll: true,
    onSuccess: () => {
      contactForm.reset()
    },
  })
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatEventDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return String(dateStr)
  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(d)
}

function timelineTypeClass(type) {
  switch (type) {
    case 'news':
      return 'bg-blue-100 text-blue-800'
    case 'milestone':
      return 'bg-amber-100 text-amber-900'
    case 'event':
    default:
      return 'bg-emerald-100 text-emerald-900'
  }
}

function timelineTypeLabel(type) {
  switch (type) {
    case 'news':
      return 'Новость'
    case 'milestone':
      return 'Веха'
    case 'event':
    default:
      return 'Событие'
  }
}
</script>
