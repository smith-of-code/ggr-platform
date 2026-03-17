<template>
  <div class="flex min-h-screen bg-gray-50 font-sans">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-rosatom-800">
      <!-- Logo -->
      <div class="flex items-center gap-3 px-5 py-5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10">
          <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
          </svg>
        </div>
        <div>
          <p class="font-brand text-sm font-bold text-white">ВШГР LMS</p>
          <p class="text-xs text-rosatom-400">Админ-панель</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="lms-scrollbar mt-2 flex-1 space-y-0.5 overflow-y-auto px-3 pb-4">
        <!-- Events always visible -->
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">Общее</p>

        <button
          :class="[
            'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
            activeItemId === 'events'
              ? 'bg-white/15 text-white shadow-sm'
              : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
          ]"
          @click="router.visit(route('lms.admin.events.index'))"
        >
          <CalendarIcon :class="['h-5 w-5 shrink-0', activeItemId === 'events' ? 'text-accent-yellow' : 'text-rosatom-400']" />
          События
        </button>

        <!-- Event sections (visible when event is selected) -->
        <template v-if="event">
          <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">
            {{ event.title }}
          </p>

          <button
            v-for="item in eventNav"
            :key="item.id"
            :class="[
              'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
              activeItemId === item.id
                ? 'bg-white/15 text-white shadow-sm'
                : 'text-rosatom-200 hover:bg-white/8 hover:text-white',
            ]"
            @click="onEventNavigate(item.id)"
          >
            <component
              :is="item.icon"
              :class="['h-5 w-5 shrink-0', activeItemId === item.id ? 'text-accent-yellow' : 'text-rosatom-400 group-hover:text-rosatom-300']"
            />
            {{ item.label }}
          </button>
        </template>

        <!-- Event list (when no event is selected) -->
        <template v-else-if="events?.length">
          <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-400">
            Перейти к событию
          </p>

          <button
            v-for="evt in events"
            :key="evt.slug"
            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rosatom-200 transition-all duration-150 hover:bg-white/8 hover:text-white"
            @click="router.visit(route('lms.admin.courses.index', evt.slug))"
          >
            <CalendarIcon class="h-5 w-5 shrink-0 text-rosatom-400" />
            {{ evt.title }}
          </button>
        </template>
      </nav>

      <!-- Bottom actions -->
      <div class="border-t border-white/10 p-3 space-y-1">
        <button
          v-if="event"
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
          @click="navigateTo('lms.dashboard', { event: event.slug })"
        >
          <ArrowLeftIcon class="h-5 w-5 shrink-0" />
          Вернуться в LMS
        </button>
        <button
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rosatom-300 transition hover:bg-white/8 hover:text-white"
          @click="navigateTo('admin.dashboard')"
        >
          <Cog6ToothIcon class="h-5 w-5 shrink-0" />
          Основная админ-панель
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="ml-64 flex-1">
      <header class="sticky top-0 z-20 flex h-16 items-center border-b border-gray-200 bg-white/95 px-8 backdrop-blur-sm">
        <div class="flex-1">
          <p v-if="event" class="font-brand text-sm font-bold text-rosatom-700">{{ event.title }}</p>
        </div>
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
        >
          <div
            v-if="$page.props.flash?.success"
            class="flex items-center gap-2 rounded-lg bg-accent-green/10 px-4 py-2 text-sm font-medium text-accent-green"
          >
            <CheckCircleIcon class="h-4 w-4" />
            {{ $page.props.flash.success }}
          </div>
        </Transition>
      </header>

      <main class="p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  CalendarIcon,
  BookOpenIcon,
  ClipboardDocumentListIcon,
  PencilSquareIcon,
  MapIcon,
  PlayIcon,
  CircleStackIcon,
  FolderIcon,
  UserGroupIcon,
  UsersIcon,
  TrophyIcon,
  ArrowLeftIcon,
  Cog6ToothIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, default: null },
  events: { type: Array, default: null },
  currentRoute: { type: String, default: '' },
})

const page = usePage()

const eventNav = [
  { id: 'courses', label: 'Курсы', icon: BookOpenIcon },
  { id: 'tests', label: 'Тесты', icon: ClipboardDocumentListIcon },
  { id: 'assignments', label: 'Задания', icon: PencilSquareIcon },
  { id: 'trajectories', label: 'Траектории', icon: MapIcon },
  { id: 'videos', label: 'Видео', icon: PlayIcon },
  { id: 'kb', label: 'База знаний', icon: CircleStackIcon },
  { id: 'materials', label: 'Материалы', icon: FolderIcon },
  { id: 'groups', label: 'Группы', icon: UserGroupIcon },
  { id: 'users', label: 'Участники', icon: UsersIcon },
  { id: 'gamification', label: 'Геймификация', icon: TrophyIcon },
]

const activeItemId = computed(() => {
  const url = page.url
  if (url.startsWith('/lms-admin/events') && !props.event) return 'events'
  if (!props.event) return ''

  if (url.includes('/courses')) return 'courses'
  if (url.includes('/tests')) return 'tests'
  if (url.includes('/assignments')) return 'assignments'
  if (url.includes('/trajectories')) return 'trajectories'
  if (url.includes('/videos')) return 'videos'
  if (url.includes('/kb') || url.includes('/knowledge')) return 'kb'
  if (url.includes('/materials')) return 'materials'
  if (url.includes('/groups')) return 'groups'
  if (url.includes('/users')) return 'users'
  if (url.includes('/gamification')) return 'gamification'
  return 'events'
})

const eventRouteMap = {
  courses: 'lms.admin.courses.index',
  tests: 'lms.admin.tests.index',
  assignments: 'lms.admin.assignments.index',
  trajectories: 'lms.admin.trajectories.index',
  videos: 'lms.admin.videos.index',
  kb: 'lms.admin.kb.index',
  materials: 'lms.admin.materials.index',
  groups: 'lms.admin.groups.index',
  users: 'lms.admin.users.index',
  gamification: 'lms.admin.gamification.index',
}

function onEventNavigate(itemId) {
  const routeName = eventRouteMap[itemId]
  if (routeName && props.event) {
    router.visit(route(routeName, props.event.slug))
  }
}

function navigateTo(routeName, params = {}) {
  router.visit(route(routeName, params))
}
</script>
