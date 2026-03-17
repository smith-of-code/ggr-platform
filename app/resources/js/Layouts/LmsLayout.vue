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
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-5">
        <div>
          <p class="font-brand text-base font-bold tracking-wide text-white">ВШГР</p>
          <p class="text-xs text-rosatom-300">Образовательная платформа</p>
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
          <RAvatar :name="user?.name || 'U'" size="sm" />
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
            <p class="truncate text-xs text-rosatom-300">{{ roleName }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="lms-scrollbar mt-4 flex-1 space-y-0.5 overflow-y-auto px-3 pb-4">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Обучение</p>

        <button
          v-for="item in mainNav"
          :key="item.id"
          :class="[
            'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
            activeItemId === item.id
              ? 'bg-white/15 text-white shadow-sm'
              : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
          ]"
          @click="onNavigate(item.id)"
        >
          <component
            :is="item.icon"
            :class="[
              'h-5 w-5 shrink-0 transition-colors',
              activeItemId === item.id ? 'text-accent-yellow' : 'text-rosatom-400 group-hover:text-rosatom-300',
            ]"
          />
          {{ item.label }}
        </button>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Ресурсы</p>

        <button
          v-for="item in resourceNav"
          :key="item.id"
          :class="[
            'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
            activeItemId === item.id
              ? 'bg-white/15 text-white shadow-sm'
              : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
          ]"
          @click="onNavigate(item.id)"
        >
          <component
            :is="item.icon"
            :class="[
              'h-5 w-5 shrink-0 transition-colors',
              activeItemId === item.id ? 'text-accent-yellow' : 'text-rosatom-400 group-hover:text-rosatom-300',
            ]"
          />
          {{ item.label }}
        </button>

        <!-- Leader & Admin -->
        <template v-if="showLeaderCabinet || profile?.role === 'admin'">
          <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Управление</p>

          <button
            v-if="showLeaderCabinet"
            :class="[
              'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
              activeItemId === 'lms.leader.dashboard'
                ? 'bg-accent-orange/20 text-accent-orange'
                : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
            ]"
            @click="onNavigate('lms.leader.dashboard')"
          >
            <Cog6ToothIcon class="h-5 w-5 shrink-0 text-rosatom-400" />
            Кабинет лидера
          </button>

          <button
            v-if="profile?.role === 'admin'"
            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rosatom-200 transition-all duration-150 hover:bg-accent-yellow/20 hover:text-accent-yellow"
            @click="onNavigate('lms.admin')"
          >
            <WrenchScrewdriverIcon class="h-5 w-5 shrink-0 text-rosatom-400 group-hover:text-accent-yellow" />
            Админ-панель LMS
          </button>
        </template>
      </nav>

      <!-- Bottom actions -->
      <div class="border-t border-white/10 p-3 space-y-1">
        <button
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
          @click="navigateTo('lms.profile.edit', { event: event?.slug })"
        >
          <UserCircleIcon class="h-5 w-5 shrink-0" />
          Мой профиль
        </button>
        <button
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
          @click="logout"
        >
          <ArrowRightOnRectangleIcon class="h-5 w-5 shrink-0" />
          Выйти
        </button>
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

        <button
          class="hidden items-center gap-2 rounded-lg px-2 py-1.5 transition hover:bg-gray-100 lg:flex"
          @click="navigateTo('lms.profile.edit', { event: event?.slug })"
        >
          <RAvatar :name="user?.name || 'U'" size="sm" />
        </button>
      </header>

      <main class="flex-1 p-4 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
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
  WrenchScrewdriverIcon,
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
  { id: 'lms.dashboard', label: 'Главная', icon: HomeIcon },
  { id: 'lms.courses', label: 'Курсы', icon: BookOpenIcon },
  { id: 'lms.trajectories', label: 'Траектории', icon: MapIcon },
  { id: 'lms.tests', label: 'Тестирование', icon: ClipboardDocumentListIcon },
  { id: 'lms.assignments', label: 'Задания', icon: PencilSquareIcon },
  { id: 'lms.gamification.leaderboard', label: 'Рейтинг', icon: TrophyIcon },
]

const resourceNav = [
  { id: 'lms.videos', label: 'Видеоматериалы', icon: PlayIcon },
  { id: 'lms.kb', label: 'База знаний', icon: CircleStackIcon },
  { id: 'lms.materials', label: 'Материалы', icon: FolderIcon },
]

const activeItemId = computed(() => {
  const url = usePage().url
  const slug = props.event?.slug
  if (!slug) return ''
  const base = `/lms/${slug}`

  if (url === base || url === `${base}/`) return 'lms.dashboard'
  if (url.startsWith(`${base}/courses`)) return 'lms.courses'
  if (url.startsWith(`${base}/trajectories`)) return 'lms.trajectories'
  if (url.startsWith(`${base}/tests`)) return 'lms.tests'
  if (url.startsWith(`${base}/assignments`)) return 'lms.assignments'
  if (url.startsWith(`${base}/videos`)) return 'lms.videos'
  if (url.startsWith(`${base}/knowledge`)) return 'lms.kb'
  if (url.startsWith(`${base}/materials`)) return 'lms.materials'
  if (url.startsWith(`${base}/leaderboard`)) return 'lms.gamification.leaderboard'
  if (url.startsWith(`${base}/leader`)) return 'lms.leader.dashboard'
  return ''
})

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
  'lms.leader.dashboard': 'lms.leader.dashboard',
  'lms.admin': 'lms.admin.courses.index',
}

function onNavigate(itemId) {
  const routeName = routeMap[itemId]
  if (routeName) {
    router.visit(route(routeName, { event: props.event?.slug }))
    sidebarOpen.value = false
  }
}

function navigateTo(routeName, params = {}) {
  router.visit(route(routeName, params))
}

function logout() {
  router.post(route('lms.logout', { event: props.event?.slug }))
}
</script>
