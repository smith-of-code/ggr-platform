<template>
  <MainLayout>
    <Head :title="d.hero_title || 'Исследования'" />

    <!-- Hero -->
    <section class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <p class="reveal text-sm font-semibold uppercase tracking-widest text-white/60">{{ d.hero_title }}</p>
        <h1 class="reveal mt-4 text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ d.hero_subtitle }}</h1>
        <p class="reveal mt-6 max-w-3xl text-lg leading-relaxed text-white/80">{{ d.hero_description }}</p>
      </div>
    </section>

    <!-- Tasks -->
    <section v-if="d.tasks?.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="reveal text-2xl font-bold text-gray-900 sm:text-3xl">{{ d.tasks_title }}</h2>
        <div class="mt-10 grid items-start gap-10 lg:grid-cols-2">
          <!-- Left: task descriptions -->
          <div class="space-y-4">
            <div
              v-for="(task, i) in d.tasks"
              :key="i"
              class="cursor-pointer rounded-2xl border p-5 transition-all duration-300"
              :class="
                activeTask === i
                  ? 'border-[#003274]/30 bg-[#003274]/5 shadow-md'
                  : 'border-gray-100 bg-gray-50 hover:border-gray-200'
              "
              @mouseenter="activeTask = i"
            >
              <div class="flex items-start gap-4">
                <span
                  class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors duration-300"
                  :class="activeTask === i ? 'bg-[#003274] text-white' : 'bg-[#003274]/10 text-[#003274]'"
                >{{ i + 1 }}</span>
                <div class="min-w-0 flex-1">
                  <h3
                    class="font-semibold transition-colors duration-300"
                    :class="activeTask === i ? 'text-[#003274]' : 'text-gray-900'"
                  >{{ task.title }}</h3>
                  <p class="mt-1.5 text-sm leading-relaxed text-gray-600">{{ task.text }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: orbital diagram -->
          <div class="reveal relative hidden lg:flex lg:items-center lg:justify-center" style="min-height: 480px">
            <div class="orbital-container relative" style="width: 420px; height: 420px">
              <!-- Orbit rings -->
              <svg class="absolute inset-0 h-full w-full" viewBox="0 0 420 420" fill="none">
                <ellipse cx="210" cy="210" rx="200" ry="100" stroke="#003274" stroke-opacity="0.08" stroke-width="1" transform="rotate(-30 210 210)"/>
                <ellipse cx="210" cy="210" rx="200" ry="100" stroke="#003274" stroke-opacity="0.08" stroke-width="1" transform="rotate(30 210 210)"/>
                <ellipse cx="210" cy="210" rx="200" ry="100" stroke="#003274" stroke-opacity="0.08" stroke-width="1" transform="rotate(90 210 210)"/>
              </svg>

              <!-- Center logo -->
              <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                <div
                  class="flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-xl shadow-[#003274]/10 ring-2 ring-[#003274]/10 transition-all duration-500"
                  :class="{ 'ring-[#003274]/25 shadow-[#003274]/20': activeTask !== null }"
                >
                  <img src="/images/rosatom-atom.png" alt="Росатом" class="h-16 w-16" />
                </div>
              </div>

              <!-- Task labels on orbits -->
              <div
                v-for="(task, i) in d.tasks"
                :key="'orbit-' + i"
                class="orbital-label absolute cursor-pointer transition-all duration-300"
                :style="orbitalPosition(i, d.tasks.length)"
                @mouseenter="activeTask = i"
              >
                <div
                  class="rounded-xl border px-4 py-2.5 text-center text-xs font-semibold leading-tight shadow-sm transition-all duration-300"
                  :class="[
                    activeTask === i
                      ? 'border-[#003274]/40 bg-[#003274] text-white shadow-lg shadow-[#003274]/30 scale-110'
                      : 'border-gray-200 bg-white text-gray-700 hover:border-[#003274]/20 hover:shadow-md',
                  ]"
                  style="max-width: 140px"
                >
                  {{ task.title }}
                </div>
                <svg
                  class="pointer-events-none absolute left-1/2 top-1/2 transition-opacity duration-300"
                  :class="activeTask === i ? 'opacity-40' : 'opacity-10'"
                  :style="{ transform: `rotate(${connectorAngle(i, d.tasks.length)}deg)`, transformOrigin: '0 50%' }"
                  width="80" height="2"
                >
                  <line x1="0" y1="1" x2="80" y2="1" stroke="#003274" stroke-width="1.5" stroke-dasharray="4 3"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Mobile: simple orbit badges -->
          <div class="flex flex-wrap justify-center gap-2 lg:hidden">
            <button
              v-for="(task, i) in d.tasks"
              :key="'mob-' + i"
              type="button"
              class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all duration-200"
              :class="activeTask === i ? 'border-[#003274] bg-[#003274] text-white' : 'border-gray-200 bg-white text-gray-600'"
              @click="activeTask = activeTask === i ? null : i"
            >
              {{ task.title }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Pilot Cities -->
    <section v-if="d.pilot_cities?.length" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="reveal text-2xl font-bold text-gray-900 sm:text-3xl">{{ d.pilot_cities_title }}</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="(city, i) in d.pilot_cities"
            :key="i"
            class="reveal group overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:border-[#003274]/30 hover:shadow-lg"
            :class="'reveal-delay-' + ((i % 3) + 1)"
          >
            <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
              <img
                v-if="city.image"
                :src="city.image"
                :alt="city.name"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
              />
              <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
                <svg class="h-12 w-12 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
              </div>
              <img
                v-if="city.coat_of_arms"
                :src="city.coat_of_arms"
                :alt="city.name + ' герб'"
                class="absolute bottom-3 right-3 h-14 w-14 rounded-lg bg-white/90 object-contain p-1.5 shadow-lg ring-1 ring-white/50 backdrop-blur-sm"
              />
            </div>
            <div class="p-5">
              <h3 class="text-lg font-bold text-gray-900">{{ city.name }}</h3>
              <p class="mt-1 text-sm font-medium text-[#003274]/70">{{ city.region }}</p>
              <p v-if="city.description" class="mt-3 text-sm leading-relaxed text-gray-600">{{ city.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section v-if="d.stats?.length" class="bg-[#003274] px-4 py-16 text-white sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="reveal text-center text-2xl font-bold sm:text-3xl">{{ d.stats_title }}</h2>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="(stat, i) in d.stats"
            :key="i"
            class="reveal rounded-2xl bg-white/10 px-6 py-8 text-center backdrop-blur-sm transition hover:bg-white/15"
            :class="'reveal-delay-' + ((i % 3) + 1)"
          >
            <p class="text-4xl font-bold lg:text-5xl">{{ stat.value }}</p>
            <p class="mt-3 text-sm leading-relaxed text-white/70">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Results -->
    <section v-if="d.results_title" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="reveal mx-auto max-w-5xl">
        <div :class="d.results_image ? 'grid items-center gap-10 lg:grid-cols-2' : 'mx-auto max-w-3xl text-center'">
          <div :class="{ 'text-center lg:text-left': d.results_image }">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ d.results_title }}</h2>
            <p class="mt-4 text-lg leading-relaxed text-gray-600">{{ d.results_description }}</p>
            <a
              v-if="d.results_button_url"
              :href="d.results_button_url"
              target="_blank"
              rel="noopener noreferrer"
              class="mt-8 inline-flex items-center gap-2 rounded-xl bg-[#003274] px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              {{ d.results_button_text || 'Скачать' }}
            </a>
          </div>
          <div v-if="d.results_image" class="overflow-hidden rounded-2xl shadow-xl">
            <img :src="d.results_image" :alt="d.results_title" class="h-full w-full object-cover" />
          </div>
        </div>
      </div>
    </section>

    <!-- Program Cities -->
    <section v-if="d.program_cities?.length" class="bg-gray-50 px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="reveal text-2xl font-bold text-gray-900 sm:text-3xl">{{ d.program_cities_title }}</h2>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          <div
            v-for="(city, i) in d.program_cities"
            :key="i"
            class="reveal flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-[#003274]/30 hover:shadow-md"
            :class="'reveal-delay-' + ((i % 4) + 1)"
          >
            <img
              v-if="city.coat_of_arms"
              :src="city.coat_of_arms"
              :alt="city.name + ' герб'"
              class="h-12 w-12 shrink-0 rounded-lg object-contain"
            />
            <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#003274]/5">
              <svg class="h-6 w-6 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <h3 class="text-lg font-bold text-gray-900">{{ city.name }}</h3>
              <p class="mt-1 text-sm text-gray-500">{{ city.region }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  pageData: { type: Object, default: () => ({}) },
})

const d = props.pageData
const activeTask = ref(null)

function connectorAngle(index, total) {
  const angle = (360 / total) * index - 90
  return angle + 180
}

function orbitalPosition(index, total) {
  const angleOffset = -90
  const angle = (360 / total) * index + angleOffset
  const rad = (angle * Math.PI) / 180
  const rx = 175
  const ry = 175
  const cx = 210 + rx * Math.cos(rad)
  const cy = 210 + ry * Math.sin(rad)
  return {
    left: `${cx}px`,
    top: `${cy}px`,
    transform: 'translate(-50%, -50%)',
  }
}

</script>
