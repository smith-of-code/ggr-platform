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
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  featuredTours: Array,
  cities: Array,
  stats: Object,
})

const statCards = computed(() => [
  {
    value: props.stats.cities,
    label: 'Атомных городов',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>',
  },
  {
    value: props.stats.tours,
    label: 'Туров возможностей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>',
  },
  {
    value: '3',
    label: 'Направления',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" /></svg>',
  },
  {
    value: '3000+',
    label: 'Гостей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>',
  },
])

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}
</script>
