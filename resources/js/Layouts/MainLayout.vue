<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header
      class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-lg transition-shadow duration-300"
      :class="{ 'shadow-sm': scrolled }"
    >
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <Link :href="route('home')" class="flex shrink-0 items-center gap-2 transition hover:opacity-80">
            <img src="/images/logo-icon.svg" alt="ГГР" class="h-9 w-auto" />
            <span class="hidden text-lg font-bold text-[#003274] lg:block">Росатом Travel</span>
          </Link>

          <!-- Desktop nav -->
          <nav class="hidden flex-1 items-center justify-center lg:flex">
            <div class="flex items-center gap-0.5">
              <template v-for="item in navItems" :key="item.slug">
                <a
                  v-if="isLmsFullPageUrl(item.href)"
                  :href="item.href"
                  class="whitespace-nowrap rounded-lg px-2.5 py-1.5 text-[13px] font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274] xl:px-3"
                  :class="{ 'bg-[#003274]/5 text-[#003274] font-semibold': item.active }"
                >{{ item.label }}</a>
                <Link
                  v-else
                  :href="item.href"
                  class="whitespace-nowrap rounded-lg px-2.5 py-1.5 text-[13px] font-medium text-gray-600 transition hover:bg-gray-100 hover:text-[#003274] xl:px-3"
                  :class="{ 'bg-[#003274]/5 text-[#003274] font-semibold': item.active }"
                >
                  {{ item.label }}
                </Link>
              </template>
            </div>
          </nav>

          <!-- Auth button (desktop) -->
          <div class="hidden shrink-0 items-center gap-2 lg:flex">
            <a
              v-if="$page.props.auth?.user && isLmsFullPageUrl(cabinetUrl)"
              :href="cabinetUrl"
              class="rounded-lg bg-[#003274] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#025ea1]"
            >
              Личный кабинет
            </a>
            <Link
              v-else-if="$page.props.auth?.user"
              :href="cabinetUrl"
              class="rounded-lg bg-[#003274] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#025ea1]"
            >
              Личный кабинет
            </Link>
            <template v-else>
              <Link
                :href="route('tour-cabinet.register')"
                class="whitespace-nowrap rounded-lg bg-[#003274] px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-[#025ea1] active:scale-[0.99]"
              >
                Зарегистрироваться
              </Link>
              <Link
                :href="route('login')"
                class="whitespace-nowrap rounded-lg border border-[#003274] bg-white px-4 py-2 text-sm font-medium text-[#003274] transition hover:bg-[#003274]/5"
              >
                Вход
              </Link>
            </template>
          </div>

          <!-- Mobile menu button -->
          <button
            @click="mobileOpen = !mobileOpen"
            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 lg:hidden"
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
        <div v-if="mobileOpen" class="border-t border-gray-200 bg-white px-4 pb-4 pt-2 lg:hidden">
          <template v-for="item in navItems" :key="item.slug">
            <a
              v-if="isLmsFullPageUrl(item.href)"
              :href="item.href"
              class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100"
            >{{ item.label }}</a>
            <Link
              v-else
              :href="item.href"
              class="block rounded-lg px-4 py-3 text-gray-700 hover:bg-gray-100"
            >{{ item.label }}</Link>
          </template>
          <div class="my-2 border-t border-gray-100" />
          <a
            v-if="$page.props.auth?.user && isLmsFullPageUrl(cabinetUrl)"
            :href="cabinetUrl"
            class="block rounded-lg bg-[#003274] px-4 py-3 text-center text-white"
          >
            Личный кабинет
          </a>
          <Link
            v-else-if="$page.props.auth?.user"
            :href="cabinetUrl"
            class="block rounded-lg bg-[#003274] px-4 py-3 text-center text-white"
          >
            Личный кабинет
          </Link>
          <template v-else>
            <Link
              :href="route('tour-cabinet.register')"
              class="mb-2 block rounded-lg bg-[#003274] px-4 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1] active:scale-[0.99]"
            >
              Зарегистрироваться
            </Link>
            <Link
              :href="route('tour-cabinet.login')"
              class="block rounded-lg px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Вход — участник конкурса
            </Link>
            <Link
              :href="route('login')"
              class="mt-1 block rounded-lg border border-[#003274] bg-white px-4 py-3 text-center text-sm font-medium text-[#003274] transition hover:bg-[#003274]/5"
            >
              Вход на сайт
            </Link>
          </template>
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
              <template v-for="item in navItems" :key="item.slug">
                <a
                  v-if="isLmsFullPageUrl(item.href)"
                  :href="item.href"
                  class="text-sm text-gray-500 transition hover:text-[#003274]"
                >{{ item.label }}</a>
                <Link
                  v-else
                  :href="item.href"
                  class="text-sm text-gray-500 transition hover:text-[#003274]"
                >{{ item.label }}</Link>
              </template>
              <Link :href="route('tour-cabinet.register')" class="text-sm text-gray-500 transition hover:text-[#003274]">
                Зарегистрироваться (личный кабинет туров)
              </Link>
              <Link :href="route('tour-cabinet.login')" class="text-sm text-gray-500 transition hover:text-[#003274]">
                Вход — личный кабинет туров
              </Link>
            </div>
          </div>
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Документы</h3>
            <div class="mt-4 flex flex-col gap-3">
              <Link href="/privacy" class="text-sm text-gray-500 transition hover:text-[#003274]">Политика обработки персональных данных</Link>
              <Link href="/consent" class="text-sm text-gray-500 transition hover:text-[#003274]">Согласие на обработку данных</Link>
              <Link href="/consent-third-party" class="text-sm text-gray-500 transition hover:text-[#003274]">Согласие на передачу данных третьим лицам</Link>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { isLmsFullPageUrl } from '@/composables/useLmsFullPageNav'

const page = usePage()
const mobileOpen = ref(false)
const scrolled = ref(false)
const cabinetUrl = computed(() => {
  if (page.props.auth?.user?.is_admin) {
    return route('admin.dashboard')
  }
  if (page.props.lmsEntryUrl) {
    return page.props.lmsEntryUrl
  }
  if (page.props.tourCabinetUrl) {
    return page.props.tourCabinetUrl
  }

  return route('home')
})

const hiddenPages = computed(() => page.props.hiddenPages || [])

const allNavItems = computed(() => [
  { slug: 'home', label: 'Главная', href: route('home'), active: page.url === '/' },
  { slug: 'cities', label: 'Города', href: route('cities.index'), active: page.url.startsWith('/cities') },
  { slug: 'tours', label: 'Каталог туров', href: route('tours.index'), active: page.url.startsWith('/tours') },
  { slug: 'opportunity-tours', label: 'Туры возможностей', href: route('opportunity-tours.index'), active: page.url.startsWith('/opportunity-tours') },
  { slug: 'education', label: 'ВШГР', href: route('education.index'), active: page.url.startsWith('/vshgr') || page.url.startsWith('/lms/') },
  { slug: 'research', label: 'Исследования', href: route('research.index'), active: page.url.startsWith('/research') },
  { slug: 'atomy-vkusa', label: 'Атомы вкуса', href: route('directions.show', 'atomy-vkusa'), active: page.url.startsWith('/directions/atomy-vkusa') },
  { slug: 'blog', label: 'Блог', href: route('blog.index'), active: page.url.startsWith('/blog') },
  { slug: 'vacancies', label: 'Вакансии', href: route('vacancies.index'), active: page.url.startsWith('/vacancies') },
])

const navItems = computed(() =>
  allNavItems.value.filter(item => !hiddenPages.value.includes(item.slug))
)

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
