<template>
  <MainLayout>
    <Head :title="recipe.title" />

    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-amber-50/60 via-transparent to-gray-50" />

      <div class="relative mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <Link
          :href="route('recipes.index')"
          class="reveal inline-flex items-center gap-2 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          К рецептам
        </Link>

        <header class="reveal mt-8">
          <div class="flex flex-wrap items-center gap-2">
            <RBadge v-if="recipe.city" variant="primary">{{ recipe.city.name }}</RBadge>
            <span v-if="recipe.published_at" class="text-sm text-gray-500">{{ formatDate(recipe.published_at) }}</span>
          </div>
          <h1 class="mt-4 text-3xl font-bold text-gray-900 sm:text-4xl">{{ recipe.title }}</h1>
        </header>

        <div v-if="recipe.image" class="reveal mt-8 overflow-hidden rounded-2xl border border-amber-100/80 bg-amber-50/30 shadow-md">
          <div class="aspect-video">
            <img :src="recipe.image" :alt="recipe.title" class="h-full w-full object-cover" />
          </div>
        </div>

        <div class="reveal mt-8 flex flex-wrap gap-3 rounded-2xl border border-amber-100/80 bg-white/90 p-4 shadow-sm sm:gap-6 sm:p-5">
          <div v-if="recipe.cooking_time" class="flex items-center gap-2 text-sm text-gray-700">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-800">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </span>
            <div>
              <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Время</div>
              <div class="font-semibold text-gray-900">{{ recipe.cooking_time }}</div>
            </div>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-800">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3" />
              </svg>
            </span>
            <div>
              <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Сложность</div>
              <div :class="['font-semibold', difficultyTextClass(recipe.difficulty)]">{{ difficultyLabel(recipe.difficulty) }}</div>
            </div>
          </div>
          <div v-if="recipe.servings != null" class="flex items-center gap-2 text-sm text-gray-700">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-800">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
              </svg>
            </span>
            <div>
              <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Порции</div>
              <div class="font-semibold text-gray-900">{{ servingsLabel(recipe.servings) }}</div>
            </div>
          </div>
        </div>

        <section v-if="normalizedIngredients.length" class="reveal mt-10">
          <h2 class="text-xl font-bold text-gray-900">Ингредиенты</h2>
          <ul class="mt-4 space-y-2 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
            <li v-for="(ing, idx) in normalizedIngredients" :key="idx" class="flex gap-3 text-gray-700">
              <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#003274]" />
              <span>{{ ing }}</span>
            </li>
          </ul>
        </section>

        <section v-if="recipe.content" class="reveal mt-10">
          <h2 class="text-xl font-bold text-gray-900">Приготовление</h2>
          <div
            class="prose prose-gray mt-4 max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-[#003274]"
            v-html="recipe.content"
          />
        </section>

        <div v-if="recipe.city?.slug" class="reveal mt-12 border-t border-amber-100/80 pt-8">
          <p class="text-sm text-gray-500">Город рецепта</p>
          <Link
            :href="route('cities.show', recipe.city.slug)"
            class="mt-2 inline-flex items-center gap-2 text-base font-semibold text-[#003274] transition hover:text-[#025ea1]"
          >
            {{ recipe.city.name }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  recipe: Object,
})

const normalizedIngredients = computed(() => {
  const raw = props.recipe?.ingredients
  if (!raw || !Array.isArray(raw)) return []
  return raw.map((item) => {
    if (typeof item === 'string') return item
    if (item && typeof item === 'object') {
      const name = item.name ?? item.title ?? ''
      const amount = item.amount ?? item.qty ?? item.quantity
      if (name && amount) return `${name} — ${amount}`
      return name || String(amount || '')
    }
    return String(item)
  }).filter(Boolean)
})

function formatDate(value) {
  if (!value) return ''
  const d = typeof value === 'string' ? new Date(value) : value
  return new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }).format(d)
}

function difficultyLabel(key) {
  const map = { easy: 'Легко', medium: 'Средне', hard: 'Сложно' }
  return map[key] || key
}

function difficultyTextClass(key) {
  const map = {
    easy: 'text-green-700',
    medium: 'text-amber-700',
    hard: 'text-red-700',
  }
  return map[key] || 'text-gray-900'
}

function servingsLabel(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return `${n} порций`
  if (y > 1 && y < 5) return `${n} порции`
  if (y === 1) return `${n} порция`
  return `${n} порций`
}
</script>
