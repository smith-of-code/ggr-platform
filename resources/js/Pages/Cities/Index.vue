<template>
  <MainLayout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="reveal">
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Атомные города</h1>
        <p class="mt-3 max-w-2xl text-lg text-gray-500">Каталог городов программы «Гостеприимные города Росатома»</p>
      </div>

      <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="(city, i) in cities"
          :key="city.id"
          :href="route('cities.show', city.slug)"
          class="reveal group overflow-hidden rounded-xl bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
          :class="'reveal-delay-' + ((i % 3) + 1)"
        >
          <div class="aspect-video overflow-hidden">
            <img
              v-if="city.image"
              :src="city.image"
              :alt="city.name"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
            />
            <div v-else class="h-full w-full bg-gray-200" />
          </div>
          <div class="p-5">
            <h2 class="text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ city.name }}</h2>
            <p v-if="city.description" class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">
              {{ city.description }}
            </p>
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-[#003274] transition group-hover:gap-2">
              Подробнее
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </span>
          </div>
        </Link>
      </div>

      <div v-if="cities.length === 0" class="py-16 text-center text-gray-500">
        Города скоро появятся
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
  cities: Array,
})
</script>
