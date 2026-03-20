<template>
  <div class="flex min-h-screen bg-gray-50 font-sans">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-rosatom-900/60 backdrop-blur-sm lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <div :class="[
      'fixed inset-y-0 left-0 z-50 transition-transform duration-300 lg:translate-x-0',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
    ]">
      <RSidebar
        :items="sidebarItems"
        :active-item="activeItemId"
        :collapsed="false"
        @select="onSelect"
      >
        <template #logo>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-base font-bold tracking-wide text-white" style="font-family: var(--font-primary)">ВШГР</p>
              <p class="text-xs" style="color: var(--color-primary-light)">Образовательная платформа</p>
            </div>
            <button
              type="button"
              class="rounded-lg p-1.5 text-gray-400 hover:text-white lg:hidden"
              @click="sidebarOpen = false"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
          <!-- User card -->
          <div class="mt-4 rounded-xl bg-white/10 p-3">
            <div class="flex items-center gap-3">
              <RAvatar :name="user?.name || 'U'" size="sm" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
                <p class="truncate text-xs" style="color: var(--color-primary-light)">{{ roleName }}</p>
              </div>
            </div>
          </div>
        </template>
        <template #footer>
          <RButton variant="ghost" size="sm" block @click="onNavigate('lms.profile.edit')">
            <template #icon><UserCircleIcon class="h-4 w-4" /></template>
            Мой профиль
          </RButton>
          <RButton variant="ghost" size="sm" block @click="logout">
            <template #icon><ArrowRightOnRectangleIcon class="h-4 w-4" /></template>
            Выйти
          </RButton>
        </template>
      </RSidebar>
    </div>

    <!-- Main content -->
    <div class="flex flex-1 flex-col" style="padding-left: 240px">
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
          @click="onNavigate('lms.profile.edit')"
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
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
  Bars3Icon,
  XMarkIcon,
  CheckCircleIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  WrenchScrewdriverIcon,
  Cog6ToothIcon,
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

const icons = {
  home: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>',
  courses: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
  trajectories: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>',
  tests: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>',
  assignments: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>',
  leaderboard: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.52.857m0 0a7.468 7.468 0 0 1-.98 3.172m0 0a7.468 7.468 0 0 1-.98-3.172M12 10.585a6.02 6.02 0 0 1-2.52-.857" /></svg>',
  videos: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>',
  kb: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>',
  materials: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>',
  leader: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
  admin: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" /></svg>',
}

const menuConfig = computed(() => {
  const defaults = { courses: true, trajectories: true, tests: true, assignments: true, leaderboard: true, videos: true, kb: true, materials: true }
  return { ...defaults, ...(props.event?.menu_config || {}) }
})

const sidebarItems = computed(() => {
  const mc = menuConfig.value
  const items = [
    { id: 'lms.dashboard', label: 'Главная', icon: icons.home },
  ]

  if (mc.courses) items.push({ id: 'lms.courses', label: 'Курсы', icon: icons.courses })
  if (mc.trajectories) items.push({ id: 'lms.trajectories', label: 'Траектории', icon: icons.trajectories })
  if (mc.tests) items.push({ id: 'lms.tests', label: 'Тестирование', icon: icons.tests })
  if (mc.assignments) items.push({ id: 'lms.assignments', label: 'Задания', icon: icons.assignments })
  if (mc.leaderboard) items.push({ id: 'lms.gamification.leaderboard', label: 'Рейтинг', icon: icons.leaderboard })
  if (mc.videos) items.push({ id: 'lms.videos', label: 'Видеоматериалы', icon: icons.videos })
  if (mc.kb) items.push({ id: 'lms.kb', label: 'База знаний', icon: icons.kb })
  if (mc.materials) items.push({ id: 'lms.materials', label: 'Материалы', icon: icons.materials })

  if (showLeaderCabinet.value) {
    items.push({ id: 'lms.leader.dashboard', label: 'Кабинет лидера', icon: icons.leader })
  }
  if (props.profile?.role === 'admin') {
    items.push({ id: 'lms.admin', label: 'Админ-панель LMS', icon: icons.admin })
  }

  return items
})

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
  'lms.profile.edit': 'lms.profile.edit',
}

function onSelect(itemId) {
  onNavigate(itemId)
  sidebarOpen.value = false
}

function onNavigate(itemId) {
  const routeName = routeMap[itemId]
  if (routeName) {
    router.visit(route(routeName, { event: props.event?.slug }))
  }
}

function logout() {
  router.post(route('lms.logout', { event: props.event?.slug }))
}
</script>
