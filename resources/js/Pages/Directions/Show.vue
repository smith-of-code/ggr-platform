<template>
  <MainLayout>
    <Head :title="direction.title" />

    <!-- Hero -->
    <section class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ direction.title }}</h1>
        <p v-if="direction.description" class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-white/80">
          {{ direction.description }}
        </p>
      </div>
    </section>

    <!-- Поднаправления -->
    <section v-if="direction.sub_directions?.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 v-if="direction.sub_directions_title" class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ direction.sub_directions_title }}</h2>
        <p v-if="direction.sub_directions_description" class="mt-2 max-w-2xl text-gray-500">{{ direction.sub_directions_description }}</p>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div
            v-for="(sd, i) in direction.sub_directions"
            :key="i"
            class="rounded-2xl border border-gray-200 p-8 transition hover:border-[#003274]/30 hover:shadow-lg"
          >
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#003274] text-lg font-bold text-white">
              {{ i + 1 }}
            </div>
            <h3 class="mt-4 text-xl font-bold text-gray-900">{{ sd.name }}</h3>
            <p class="mt-3 leading-relaxed text-gray-600">{{ sd.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Целевые аудитории -->
    <section v-if="direction.target_audiences?.length" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Для кого</h2>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div
            v-for="ta in direction.target_audiences"
            :key="ta.number"
            class="relative rounded-2xl border border-gray-200 bg-white p-8"
          >
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#003274] text-xl font-bold text-white">
              {{ ta.number }}
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900">{{ ta.title }}</h3>
            <p class="mt-3 leading-relaxed text-gray-600">{{ ta.description }}</p>
          </div>
        </div>
        <p v-if="direction.target_audience_note" class="mt-6 text-sm italic text-gray-500">
          * {{ direction.target_audience_note }}
        </p>
      </div>
    </section>

    <!-- Твой билет в атомный город -->
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Твой билет в атомный город</h2>
        <div class="mt-12 grid gap-8 lg:grid-cols-2">
          <!-- Бесплатно: конкурс -->
          <div v-if="direction.free_participation_steps?.length" class="rounded-2xl border-2 border-[#003274]/20 bg-gradient-to-b from-[#003274]/5 to-transparent p-8">
            <div class="mb-6 flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Победить в конкурсе</h3>
            </div>
            <div class="space-y-4">
              <div v-for="(step, i) in direction.free_participation_steps" :key="i" class="flex gap-4">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#003274] text-xs font-bold text-white">{{ i + 1 }}</div>
                <div>
                  <p class="font-semibold text-gray-900">{{ step.title }}</p>
                  <p class="mt-1 text-sm text-gray-600">{{ step.description }}</p>
                </div>
              </div>
            </div>
            <div class="mt-8 text-center">
              <Link
                :href="route('login')"
                class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3.5 font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#025ea1] hover:shadow-xl"
              >
                Личный кабинет
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
              </Link>
            </div>
          </div>

          <!-- Платно -->
          <div v-if="direction.paid_participation_steps?.length" class="rounded-2xl border-2 border-amber-200 bg-gradient-to-b from-amber-50 to-transparent p-8">
            <div class="mb-6 flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">За свой счёт</h3>
            </div>
            <div class="space-y-4">
              <div v-for="(step, i) in direction.paid_participation_steps" :key="i" class="flex gap-4">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">{{ i + 1 }}</div>
                <div>
                  <p class="font-semibold text-gray-900">{{ step.title }}</p>
                  <p class="mt-1 text-sm text-gray-600">{{ step.description }}</p>
                </div>
              </div>
            </div>
            <div class="mt-8 text-center">
              <Link
                :href="route('applications.store')"
                method="get"
                as="button"
                class="inline-flex items-center rounded-xl bg-amber-500 px-8 py-3.5 font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-xl"
                @click.prevent="scrollToTours"
              >
                Оставить заявку
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Конкурсные детали -->
    <section v-if="details" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-4xl">
        <div class="grid gap-8 lg:grid-cols-2">
          <!-- Развёрнутые ответы -->
          <div v-if="details.questions?.length" class="rounded-2xl bg-white p-8 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">Развёрнутые ответы</h3>
            <p class="mt-2 text-sm text-gray-500">Участнику в личном кабинете необходимо дать подробные ответы на следующие вопросы:</p>
            <ul class="mt-6 space-y-4">
              <li v-for="(q, i) in details.questions" :key="i" class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#003274]/10 text-xs font-bold text-[#003274]">{{ i + 1 }}</span>
                <p class="text-sm leading-relaxed text-gray-700">{{ q }}</p>
              </li>
            </ul>
          </div>

          <!-- Проверочное задание -->
          <div v-if="details.challenge_description" class="rounded-2xl bg-white p-8 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">{{ details.challenge_title || 'Проверочное задание' }}</h3>
            <p class="mt-4 leading-relaxed text-gray-600">{{ details.challenge_description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Туры направления -->
    <section v-if="featuredTours.length" ref="toursSection" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <div class="flex items-end justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Туры направления</h2>
            <p class="mt-2 text-gray-500">Выберите тур и отправляйтесь в путешествие</p>
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
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
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
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
          </button>
          <button
            @click="scrollTours(1)"
            class="absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
          >
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
          </button>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  direction: { type: Object, required: true },
  featuredTours: { type: Array, default: () => [] },
})

const details = computed(() => props.direction.free_participation_details)

const toursSlider = ref(null)
const toursSection = ref(null)

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function scrollTours(direction) {
  if (!toursSlider.value) return
  const cardWidth = toursSlider.value.firstElementChild?.offsetWidth ?? 300
  toursSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

function scrollToTours() {
  toursSection.value?.scrollIntoView({ behavior: 'smooth' })
}
</script>
