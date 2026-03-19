<template>
  <MainLayout>
    <div>
      <!-- Hero image -->
      <div class="relative h-72 overflow-hidden bg-gray-200 sm:h-80 lg:h-[28rem]">
        <img
          v-if="city.image"
          :src="city.image"
          :alt="city.name"
          class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent" />
        <div class="absolute bottom-0 left-0 right-0 mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
          <h1 class="text-4xl font-bold text-white sm:text-5xl">{{ city.name }}</h1>
        </div>
      </div>

      <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div v-if="city.description" class="reveal max-w-3xl text-lg leading-relaxed text-gray-600" v-html="city.description" />

        <section v-if="city.tours?.length" class="mt-16">
          <h2 class="reveal text-2xl font-bold text-gray-900">Туры в городе</h2>
          <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(tour, i) in city.tours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="reveal group overflow-hidden rounded-xl bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
              :class="'reveal-delay-' + (i + 1)"
            >
              <div class="aspect-video overflow-hidden bg-gray-200">
                <img
                  v-if="tour.image"
                  :src="tour.image"
                  :alt="tour.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                />
              </div>
              <div class="p-5">
                <h3 class="font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ tour.duration }}</p>
                <p class="mt-3 text-lg font-bold text-[#003274]">
                  <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                  <template v-else><span class="text-green-600">Бесплатно</span></template>
                </p>
              </div>
            </Link>
          </div>
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

defineProps({
  city: Object,
})

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}
</script>
