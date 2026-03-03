<template>
  <div class="flex min-h-screen bg-gray-50 font-sans">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-rosatom-900">
      <!-- Logo -->
      <div class="flex h-16 items-center gap-3 px-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
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
      <nav class="lms-scrollbar flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
        <Link
          v-if="!event"
          :href="route('lms.admin.events.index')"
          :class="[
            isActive('lms.admin.events')
              ? 'bg-white/15 text-white font-semibold'
              : 'text-rosatom-300 hover:bg-white/8 hover:text-white',
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150',
          ]"
        >
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          События
        </Link>

        <template v-if="event">
          <div class="pb-1 pt-2">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-widest text-rosatom-500">{{ event.title }}</p>
          </div>
          <Link
            v-for="item in eventNav"
            :key="item.route"
            :href="route(item.routeName, event.id)"
            :class="[
              isActive(item.route)
                ? 'bg-white/15 text-white font-semibold'
                : 'text-rosatom-300 hover:bg-white/8 hover:text-white',
              'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150',
            ]"
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" />
            {{ item.label }}
          </Link>
        </template>

        <div class="border-t border-white/10 pt-4">
          <Link
            :href="route('admin.dashboard')"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-rosatom-400 transition hover:bg-white/8 hover:text-white"
          >
            <ArrowLeftIcon class="h-5 w-5 shrink-0" />
            В админ-панель
          </Link>
        </div>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="ml-64 flex-1">
      <header class="sticky top-0 z-20 flex h-16 items-center border-b border-gray-200 bg-white/95 px-8 backdrop-blur-sm">
        <div class="flex-1" />
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
import { Link, usePage } from '@inertiajs/vue3'
import {
  BookOpenIcon,
  ClipboardDocumentListIcon,
  DocumentTextIcon,
  MapIcon,
  VideoCameraIcon,
  GlobeAltIcon,
  FolderIcon,
  UserGroupIcon,
  UsersIcon,
  TrophyIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import { h } from 'vue'

defineProps({
  event: { type: Object, default: null },
  currentRoute: { type: String, default: '' },
})

const eventNav = [
  { route: 'lms.admin.courses', routeName: 'lms.admin.courses.index', label: 'Курсы', icon: BookOpenIcon },
  { route: 'lms.admin.tests', routeName: 'lms.admin.tests.index', label: 'Тесты', icon: ClipboardDocumentListIcon },
  { route: 'lms.admin.assignments', routeName: 'lms.admin.assignments.index', label: 'Задания', icon: DocumentTextIcon },
  { route: 'lms.admin.trajectories', routeName: 'lms.admin.trajectories.index', label: 'Траектории', icon: MapIcon },
  { route: 'lms.admin.videos', routeName: 'lms.admin.videos.index', label: 'Видео', icon: VideoCameraIcon },
  { route: 'lms.admin.kb', routeName: 'lms.admin.kb.index', label: 'База знаний', icon: GlobeAltIcon },
  { route: 'lms.admin.materials', routeName: 'lms.admin.materials.index', label: 'Материалы', icon: FolderIcon },
  { route: 'lms.admin.groups', routeName: 'lms.admin.groups.index', label: 'Группы', icon: UserGroupIcon },
  { route: 'lms.admin.users', routeName: 'lms.admin.users.index', label: 'Участники', icon: UsersIcon },
  { route: 'lms.admin.gamification', routeName: 'lms.admin.gamification.index', label: 'Геймификация', icon: TrophyIcon },
]

const page = usePage()

function isActive(routePrefix) {
  const url = page.url
  if (routePrefix === 'lms.admin.events') {
    return url.startsWith('/lms-admin/events') && !/\/lms-admin\/\d+/.test(url)
  }
  const slug = routePrefix.replace('lms.admin.', '')
  return url.includes(`/${slug}`)
}
</script>
