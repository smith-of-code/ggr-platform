<template>
  <div class="flex min-h-screen bg-gray-50 font-sans">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-rosatom-900/60 backdrop-blur-sm lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-rosatom-800 transition-transform duration-300 lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <!-- Logo & branding -->
      <div class="flex h-20 items-center gap-4 px-6">
        <img src="/images/logo-compact.png" alt="ГГР" class="h-12 w-auto brightness-0 invert" />
        <div class="min-w-0 flex-1">
          <p class="font-brand text-sm font-bold tracking-wide text-white">ВШГР</p>
          <p class="truncate text-xs text-rosatom-300">Образовательная платформа</p>
        </div>
        <button
          type="button"
          class="rounded-lg p-1.5 text-rosatom-300 hover:bg-white/10 hover:text-white lg:hidden"
          @click="sidebarOpen = false"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- User card -->
      <div class="mx-4 rounded-xl bg-white/10 p-3">
        <div class="flex items-center gap-3">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-yellow font-brand text-sm font-bold text-rosatom-900"
          >
            {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
            <p class="truncate text-xs text-rosatom-300">{{ roleName }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="lms-scrollbar mt-4 flex-1 space-y-0.5 overflow-y-auto px-3 pb-4">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Обучение</p>

        <Link
          v-for="item in mainNav"
          :key="item.route"
          :href="navHref(item.route)"
          :class="[
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
            isActive(item.route)
              ? 'bg-white/15 text-white shadow-sm'
              : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
          ]"
        >
          <component
            :is="item.icon"
            :class="[
              'h-5 w-5 shrink-0 transition-colors',
              isActive(item.route) ? 'text-accent-yellow' : 'text-rosatom-400 group-hover:text-rosatom-300',
            ]"
          />
          {{ item.label }}
        </Link>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Ресурсы</p>

        <Link
          v-for="item in resourceNav"
          :key="item.route"
          :href="navHref(item.route)"
          :class="[
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
            isActive(item.route)
              ? 'bg-white/15 text-white shadow-sm'
              : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
          ]"
        >
          <component
            :is="item.icon"
            :class="[
              'h-5 w-5 shrink-0 transition-colors',
              isActive(item.route) ? 'text-accent-yellow' : 'text-rosatom-400 group-hover:text-rosatom-300',
            ]"
          />
          {{ item.label }}
        </Link>

        <!-- Leader cabinet -->
        <template v-if="showLeaderCabinet">
          <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Управление</p>
          <Link
            :href="route('lms.leader.dashboard', { event: event?.slug })"
            :class="[
              'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
              isActive('lms.leader')
                ? 'bg-accent-orange/20 text-accent-orange'
                : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
            ]"
          >
            <Cog6ToothIcon
              :class="[
                'h-5 w-5 shrink-0',
                isActive('lms.leader') ? 'text-accent-orange' : 'text-rosatom-400',
              ]"
            />
            Кабинет лидера
          </Link>
        </template>
      </nav>

      <!-- Bottom actions -->
      <div class="border-t border-white/10 p-3">
        <Link
          :href="route('lms.profile.edit', { event: event?.slug })"
          class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
        >
          <UserCircleIcon class="h-5 w-5 shrink-0" />
          Мой профиль
        </Link>
        <Link
          :href="route('lms.logout', { event: event?.slug })"
          method="post"
          as="button"
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
        >
          <ArrowRightOnRectangleIcon class="h-5 w-5 shrink-0" />
          Выйти
        </Link>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex flex-1 flex-col lg:pl-72">
      <!-- Top bar -->
      <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/95 px-4 backdrop-blur-sm lg:px-8">
        <button
          type="button"
          class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-rosatom-700 lg:hidden"
          @click="sidebarOpen = true"
        >
          <Bars3Icon class="h-6 w-6" />
        </button>

        <div class="min-w-0 flex-1">
          <h1 class="truncate font-brand text-lg font-bold text-rosatom-800">
            {{ event?.title || event?.name || 'ВШГР' }}
          </h1>
        </div>

        <!-- Flash messages -->
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="translate-y-[-8px] opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="$page.props.flash?.success"
            class="flex items-center gap-2 rounded-lg bg-accent-green/10 px-4 py-2 text-sm font-medium text-accent-green"
          >
            <CheckCircleIcon class="h-4 w-4" />
            {{ $page.props.flash.success }}
          </div>
        </Transition>

        <!-- User avatar (desktop) -->
        <Link
          :href="route('lms.profile.edit', { event: event?.slug })"
          class="hidden items-center gap-2 rounded-lg px-2 py-1.5 transition hover:bg-gray-100 lg:flex"
        >
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-rosatom-600 text-xs font-bold text-white">
            {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
          </div>
        </Link>
      </header>

      <main class="flex-1 p-4 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
  HomeIcon,
  BookOpenIcon,
  MapIcon,
  ClipboardDocumentListIcon,
  PencilSquareIcon,
  PlayIcon,
  CircleStackIcon,
  FolderIcon,
  TrophyIcon,
  Bars3Icon,
  XMarkIcon,
  Cog6ToothIcon,
  CheckCircleIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, required: true },
  profile: { type: Object, default: () => ({}) },
})

const sidebarOpen = ref(false)

const roleName = computed(() => {
  const map = { participant: 'Участник', curator: 'Куратор', leader: 'Лидер', admin: 'Администратор' }
  return map[props.profile?.role] || 'Участник'
})

const showLeaderCabinet = computed(() => {
  const role = props.profile?.role || ''
  return ['leader', 'curator', 'admin'].includes(role)
})

const mainNav = [
  { route: 'lms.dashboard', label: 'Главная', icon: HomeIcon },
  { route: 'lms.courses', label: 'Курсы', icon: BookOpenIcon },
  { route: 'lms.trajectories', label: 'Траектории', icon: MapIcon },
  { route: 'lms.tests', label: 'Тестирование', icon: ClipboardDocumentListIcon },
  { route: 'lms.assignments', label: 'Задания', icon: PencilSquareIcon },
  { route: 'lms.gamification.leaderboard', label: 'Рейтинг', icon: TrophyIcon },
]

const resourceNav = [
  { route: 'lms.videos', label: 'Видеоматериалы', icon: PlayIcon },
  { route: 'lms.kb', label: 'База знаний', icon: CircleStackIcon },
  { route: 'lms.materials', label: 'Материалы', icon: FolderIcon },
]

function navHref(routePrefix) {
  const slug = props.event?.slug
  const routeMap = {
    'lms.dashboard': 'lms.dashboard',
    'lms.courses': 'lms.courses.index',
    'lms.trajectories': 'lms.trajectories.index',
    'lms.tests': 'lms.tests.index',
    'lms.assignments': 'lms.assignments.index',
    'lms.videos': 'lms.videos.index',
    'lms.kb': 'lms.kb.index',
    'lms.materials': 'lms.materials.index',
    'lms.gamification.leaderboard': 'lms.gamification.leaderboard',
  }
  return route(routeMap[routePrefix] || routePrefix, { event: slug })
}

function isActive(routePrefix) {
  const url = usePage().url
  const slug = props.event?.slug
  if (!slug) return false
  const base = `/lms/${slug}`
  if (routePrefix === 'lms.dashboard') return url === base || url === `${base}/`
  if (routePrefix === 'lms.courses') return url.startsWith(`${base}/courses`)
  if (routePrefix === 'lms.trajectories') return url.startsWith(`${base}/trajectories`)
  if (routePrefix === 'lms.tests') return url.startsWith(`${base}/tests`)
  if (routePrefix === 'lms.assignments') return url.startsWith(`${base}/assignments`)
  if (routePrefix === 'lms.videos') return url.startsWith(`${base}/videos`)
  if (routePrefix === 'lms.kb') return url.startsWith(`${base}/knowledge`)
  if (routePrefix === 'lms.materials') return url.startsWith(`${base}/materials`)
  if (routePrefix === 'lms.gamification.leaderboard') return url.startsWith(`${base}/leaderboard`)
  if (routePrefix === 'lms.leader') return url.startsWith(`${base}/leader`)
  return false
}
</script>
