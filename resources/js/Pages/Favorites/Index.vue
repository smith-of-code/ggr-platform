<template>
  <MainLayout>
    <Head title="Избранное" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Избранное</h1>
      <p class="mt-2 text-gray-500">Города и туры, которые вы сохранили</p>

      <section class="mt-12">
        <h2 class="text-lg font-semibold text-gray-900">Избранные города</h2>
        <div
          v-if="citiesList.length"
          class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
          <article
            v-for="city in citiesList"
            :key="city.id"
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          >
            <Link :href="route('cities.show', city.slug)" class="block">
              <div class="aspect-video overflow-hidden bg-gray-100">
                <img
                  v-if="city.image"
                  :src="city.image"
                  :alt="city.name"
                  class="h-full w-full object-cover"
                />
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-900 transition hover:text-[#003274]">{{ city.name }}</h3>
              </div>
            </Link>
            <div class="border-t border-gray-50 px-4 py-3">
              <button
                type="button"
                class="text-sm font-medium text-[#003274] transition hover:text-[#025ea1] disabled:opacity-50"
                :disabled="removing === `city-${city.id}`"
                @click="removeFavorite('city', city.id)"
              >
                Убрать из избранного
              </button>
            </div>
          </article>
        </div>
        <p v-else class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-10 text-center text-sm text-gray-500">
          У вас пока нет избранных городов. Добавляйте их со страницы города.
        </p>
      </section>

      <section class="mt-14">
        <h2 class="text-lg font-semibold text-gray-900">Избранные туры</h2>
        <div
          v-if="toursList.length"
          class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
          <article
            v-for="tour in toursList"
            :key="tour.id"
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          >
            <Link :href="route('tours.show', tour.slug)" class="block">
              <div class="aspect-video overflow-hidden bg-gray-100">
                <img
                  v-if="tour.image"
                  :src="tour.image"
                  :alt="tour.title"
                  class="h-full w-full object-cover"
                />
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-900 transition hover:text-[#003274]">{{ tour.title }}</h3>
                <div v-if="tour.cities?.length" class="mt-1.5 flex flex-wrap gap-1.5">
                  <RBadge v-for="city in tour.cities" :key="city.id" variant="info" size="md">{{ city.name }}</RBadge>
                </div>
                <p v-if="tour.start_city" class="mt-1 text-sm text-gray-500">
                  <span class="font-medium text-gray-600">Логистические точки:</span> {{ tour.start_city }}
                </p>
              </div>
            </Link>
            <div class="border-t border-gray-50 px-4 py-3">
              <button
                type="button"
                class="text-sm font-medium text-[#003274] transition hover:text-[#025ea1] disabled:opacity-50"
                :disabled="removing === `tour-${tour.id}`"
                @click="removeFavorite('tour', tour.id)"
              >
                Убрать из избранного
              </button>
            </div>
          </article>
        </div>
        <p v-else class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-10 text-center text-sm text-gray-500">
          У вас пока нет избранных туров. Добавляйте их со страницы тура.
        </p>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  favorites: {
    type: Object,
    default: () => ({ cities: [], tours: [] }),
  },
})

const citiesList = computed(() => props.favorites?.cities ?? [])
const toursList = computed(() => props.favorites?.tours ?? [])

const removing = ref(null)

function removeFavorite(type, id) {
  const key = `${type}-${id}`
  removing.value = key
  router.post(
    route('favorites.toggle', { type, id }),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        removing.value = null
      },
    },
  )
}
</script>
