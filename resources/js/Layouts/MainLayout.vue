<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header
      class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-lg transition-shadow duration-300"
      :class="{ 'shadow-sm': scrolled }"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <Link :href="route('home')" class="flex items-center gap-2.5 transition hover:opacity-80">
            <img src="/images/logo-icon.svg" alt="ГГР" class="h-9 w-auto" />
            <span class="hidden text-lg font-bold text-[#003274] sm:block">Росатом Travel</span>
          </Link>

          <!-- Desktop nav -->
          <nav class="hidden items-center gap-1 md:flex">
            <Link
              :href="route('home')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url === '/' }"
            >
              Главная
            </Link>
            <Link
              :href="route('cities.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/cities') }"
            >
              Города
            </Link>
            <Link
              :href="route('tours.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/tours') }"
            >
              Туры
            </Link>
            <Link
              :href="route('opportunity-tours.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/opportunity-tours') }"
            >
              Туры возможностей
            </Link>
            <Link
              :href="route('education.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/vshgr') }"
            >
              ВШГР
            </Link>
            <Link
              :href="route('research.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/research') }"
            >
              Исследования
            </Link>
            <Link
              :href="route('recipes.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/recipes') }"
            >
              Атомы вкуса
            </Link>
            <Link
              :href="route('blog.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/blog') }"
            >
              Блог
            </Link>
            <Link
              :href="route('vacancies.index')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274]"
              :class="{ 'bg-blue-50 text-[#003274]': $page.url.startsWith('/vacancies') }"
            >
              Вакансии
            </Link>
            <div class="ml-2 h-6 w-px bg-gray-200" />
            <Link
              v-if="$page.props.auth?.user"
              :href="route('admin.dashboard')"
              class="ml-2 rounded-lg bg-[#003274] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#025ea1]"
            >
              Админка
            </Link>
            <Link
              v-else
              :href="route('login')"
              class="ml-2 rounded-lg border border-[#003274] px-4 py-2 text-sm font-medium text-[#003274] transition hover:bg-[#003274] hover:text-white"
            >
              Вход
            </Link>
          </nav>

          <!-- Mobile menu button -->
          <button
            @click="mobileOpen = !mobileOpen"
            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 md:hidden"
          >
            <svg v-if="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="-translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-2 opacity-0"
      >
        <div v-if="mobileOpen" class="border-t border-gray-200 bg-white px-4 pb-4 pt-2 md:hidden">
          <Link :href="route('home')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Главная</Link>
          <Link :href="route('cities.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Города</Link>
          <Link :href="route('tours.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Туры</Link>
          <Link :href="route('opportunity-tours.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Туры возможностей</Link>
          <Link :href="route('education.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">ВШГР</Link>
          <Link :href="route('research.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Исследования</Link>
          <Link :href="route('recipes.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Атомы вкуса</Link>
          <Link :href="route('blog.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Блог</Link>
          <Link :href="route('vacancies.index')" class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100">Вакансии</Link>
          <div class="my-2 border-t border-gray-100" />
          <Link
            v-if="$page.props.auth?.user"
            :href="route('admin.dashboard')"
            class="block rounded-lg bg-[#003274] px-4 py-3 text-center text-white"
          >
            Админка
          </Link>
          <Link
            v-else
            :href="route('login')"
            class="block rounded-lg border border-[#003274] px-4 py-3 text-center text-[#003274]"
          >
            Вход
          </Link>
        </div>
      </Transition>
    </header>

    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-gray-200 bg-white">
      <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-3">
          <div>
            <div class="flex items-center gap-2.5">
              <img src="/images/logo-icon.svg" alt="ГГР" class="h-9 w-auto" />
              <span class="text-lg font-bold text-[#003274]">Росатом Travel</span>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-gray-500">
              Цифровая экосистема для развития туристического потенциала атомных городов России
            </p>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Навигация</h3>
            <div class="mt-4 flex flex-col gap-3">
              <Link :href="route('home')" class="text-sm text-gray-500 transition hover:text-[#003274]">Главная</Link>
              <Link :href="route('cities.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Города</Link>
              <Link :href="route('tours.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Туры</Link>
              <Link :href="route('opportunity-tours.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Туры возможностей</Link>
              <Link :href="route('education.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">ВШГР</Link>
              <Link :href="route('research.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Исследования</Link>
              <Link :href="route('recipes.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Атомы вкуса</Link>
              <Link :href="route('blog.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Блог</Link>
              <Link :href="route('vacancies.index')" class="text-sm text-gray-500 transition hover:text-[#003274]">Вакансии</Link>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Документы</h3>
            <div class="mt-4 flex flex-col gap-3">
              <Link href="/privacy" class="text-sm text-gray-500 transition hover:text-[#003274]">Политика обработки персональных данных</Link>
              <Link href="/consent" class="text-sm text-gray-500 transition hover:text-[#003274]">Согласие на обработку данных</Link>
            </div>
          </div>
        </div>
        <div class="mt-10 border-t border-gray-100 pt-6">
          <p class="text-center text-sm text-gray-400">
            &copy; {{ new Date().getFullYear() }} Гостеприимные города Росатома. Все права защищены.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const mobileOpen = ref(false)
const scrolled = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 10
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>
