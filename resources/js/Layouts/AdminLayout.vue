<template>
  <div class="flex min-h-screen bg-gray-50">
    <ToastNotifications />

    <div
      v-show="sidebarOpen"
      class="fixed inset-0 z-20 bg-gray-900/40 backdrop-blur-[1px] lg:hidden"
      aria-hidden="true"
      @click="sidebarOpen = false"
    />

    <aside
      id="admin-sidebar"
      class="fixed inset-y-0 left-0 z-40 max-lg:!h-dvh max-lg:shadow-2xl transition-transform duration-200 ease-out lg:z-30 lg:translate-x-0 lg:shadow-none"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <AppSidebar
        :items="sidebarItems"
        :active-item="activeItemId"
        :collapsed="false"
        theme="light"
        @select="onSelect"
      >
        <template #logo>
          <div class="flex items-start justify-end gap-2">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#003274]">
                  <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-gray-900">Росатом Travel</p>
                  <p class="truncate text-xs text-gray-400">Админ-панель</p>
                </div>
              </div>
            </div>
            <button
              type="button"
              class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 lg:hidden"
              aria-label="Закрыть меню"
              @click="sidebarOpen = false"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <div class="mt-4 rounded-xl bg-[#003274]/5 p-3">
            <div class="flex items-center gap-3">
              <RAvatar :name="page.props.auth?.user?.name || 'A'" size="sm" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">{{ page.props.auth?.user?.name }}</p>
                <p class="truncate text-xs text-gray-500">{{ page.props.auth?.user?.email }}</p>
              </div>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="space-y-0.5">
            <a
              v-if="canAccessPortalAdmin"
              :href="route('admin.dashboard')"
              class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
              Админка портала
            </a>
            <a
              v-if="canAccessLmsAdmin"
              :href="route('lms.admin.events.index')"
              class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
              Админка LMS
            </a>
            <a
              v-if="lmsEntryUrl"
              :href="lmsEntryUrl"
              class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
              ЛК LMS
            </a>
            <a
              v-if="tourCabinetUrl"
              :href="tourCabinetUrl"
              class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
              ЛК туров
            </a>

            <div class="my-2 border-t border-gray-100"></div>

            <Link
              :href="route('home')"
              class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
              На сайт
            </Link>
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-600 transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
            >
              <svg class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
              Выход
            </Link>
          </div>
        </template>
      </AppSidebar>
    </aside>

    <div class="min-w-0 flex-1 lg:ml-60">
      <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-2 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-lg sm:px-6 lg:px-8">
        <button
          type="button"
          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 lg:hidden"
          :aria-label="sidebarOpen ? 'Закрыть меню' : 'Открыть меню'"
          :aria-expanded="sidebarOpen"
          aria-controls="admin-sidebar"
          @click="sidebarOpen = !sidebarOpen"
        >
          <svg v-if="!sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
          <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
        <div class="flex-1" />
        <div v-if="page.props.flash?.success" class="flex items-center gap-2 rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
          {{ page.props.flash.success }}
        </div>
      </header>

      <main class="min-w-0 p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import ToastNotifications from '@/Components/ToastNotifications.vue'
import AppSidebar from '@/Components/AppSidebar.vue'

const sidebarOpen = ref(false)

const page = usePage()
const canAccessPortalAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin))
const canAccessLmsAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin || page.props.hasAnyLmsAdminAccess))
const lmsEntryUrl = computed(() => page.props.lmsEntryUrl || null)
const tourCabinetUrl = computed(() => page.props.tourCabinetUrl || null)

const ICON_ATTRS = 'fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:20px;height:20px"'
const icon = path => `<svg ${ICON_ATTRS}>${path}</svg>`

const ICON_DASHBOARD = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />')
const ICON_APPLICATIONS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />')
const ICON_CITIES = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />')
const ICON_TOURS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />')
const ICON_PROMO = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />')
const ICON_TC = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />')
const ICON_TC_USERS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 18H18a2.25 2.25 0 0 0 2.25-2.25V8.25A2.25 2.25 0 0 0 18 6H6a2.25 2.25 0 0 0-2.25 2.25v12A2.25 2.25 0 0 0 6 22.5h2.25m9-9H9m3.75-9v9m3-3H9" />')
const ICON_SUPPORT = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />')
const ICON_REVIEWS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />')
const ICON_DIRECTIONS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />')
const ICON_ATOMS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />')
const ICON_BLOG = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />')
const ICON_SUBSCRIBERS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />')
const ICON_EDU = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />')
const ICON_RECIPES = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />')
const ICON_HOMEPAGE = icon('<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />')
const ICON_OPP_TOURS = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />')
const ICON_VSHGR_PAGE = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.01 50.01 0 0 1 12 0c1.607 0 3.158.112 4.656.326" />')
const ICON_RESEARCH = icon('<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />')
const ICON_TIMELINE = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />')
const ICON_VACANCIES = icon('<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />')

const ROUTE_MAP = {
  'admin.dashboard': 'admin.dashboard',
  'admin.applications': 'admin.applications.index',
  'admin.cities': 'admin.cities.index',
  'admin.tours': 'admin.tours.index',
  'admin.promocodes': 'admin.promocodes.index',
  'admin.tour-cabinet': 'admin.tour-cabinet.index',
  'admin.tour-cabinet.tour-users': 'admin.tour-cabinet.tour-users.index',
  'admin.tour-cabinet.support': 'admin.tour-cabinet.support.index',
  'admin.tour-reviews': 'admin.tour-reviews.index',
  'admin.directions': 'admin.directions.index',
  'admin.atoms-vkusa': 'admin.atoms-vkusa.edit',
  'admin.blog': 'admin.blog.index',
  'admin.blog-subscribers': 'admin.blog-subscribers.index',
  'admin.education-products': 'admin.education-products.index',
  'admin.recipes': 'admin.recipes.index',
  'admin.main-page': 'admin.main-page.index',
  'admin.opportunity-tours-page': 'admin.opportunity-tours-page.index',
  'admin.vshgr-page': 'admin.vshgr-page.index',
  'admin.research-page': 'admin.research-page.index',
  'admin.timeline': 'admin.timeline.index',
  'admin.vacancies': 'admin.vacancies.index',
}

const sidebarItems = computed(() => [
  { id: 'admin.dashboard', label: 'Дашборд', icon: ICON_DASHBOARD },
  { id: 'admin.applications', label: 'Заявки', icon: ICON_APPLICATIONS },

  { id: 'sec.content', type: 'section', label: 'Контент портала' },
  { id: 'admin.cities', label: 'Города', icon: ICON_CITIES },
  { id: 'admin.tours', label: 'Каталог туров', icon: ICON_TOURS },
  { id: 'admin.promocodes', label: 'Промокоды', icon: ICON_PROMO },
  { id: 'admin.tour-cabinet', label: 'ЛК туров', icon: ICON_TC },
  { id: 'admin.tour-cabinet.tour-users', label: 'Клиенты', icon: ICON_TC_USERS },
  { id: 'admin.tour-cabinet.support', label: 'Обращения ЛК туров', icon: ICON_SUPPORT },
  { id: 'admin.tour-reviews', label: 'Отзывы', icon: ICON_REVIEWS },
  { id: 'admin.directions', label: 'Направления', icon: ICON_DIRECTIONS },
  { id: 'admin.atoms-vkusa', label: 'Атомы вкуса', icon: ICON_ATOMS },
  { id: 'admin.blog', label: 'Блог', icon: ICON_BLOG },
  { id: 'admin.blog-subscribers', label: 'Подписчики', icon: ICON_SUBSCRIBERS },
  { id: 'admin.education-products', label: 'Продукты ВШГР', icon: ICON_EDU },
  { id: 'admin.recipes', label: 'Рецепты', icon: ICON_RECIPES },

  { id: 'sec.site', type: 'section', label: 'Сайт' },
  { id: 'admin.main-page', label: 'Главная страница', icon: ICON_HOMEPAGE },
  { id: 'admin.opportunity-tours-page', label: 'Туры возможностей', icon: ICON_OPP_TOURS },
  { id: 'admin.vshgr-page', label: 'Страница ВШГР', icon: ICON_VSHGR_PAGE },
  { id: 'admin.research-page', label: 'Исследования', icon: ICON_RESEARCH },
  { id: 'admin.timeline', label: 'Хронология', icon: ICON_TIMELINE },

  { id: 'sec.hr', type: 'section', label: 'HR' },
  { id: 'admin.vacancies', label: 'Вакансии', icon: ICON_VACANCIES },
])

const activeItemId = computed(() => {
  const url = page.url
  if (url === '/admin' || url === '/admin/') return 'admin.dashboard'
  if (url.startsWith('/admin/applications')) return 'admin.applications'
  if (url.startsWith('/admin/cities')) return 'admin.cities'
  if (url.startsWith('/admin/tours')) return 'admin.tours'
  if (url.startsWith('/admin/promocodes')) return 'admin.promocodes'
  if (url.startsWith('/admin/tour-cabinet/support')) return 'admin.tour-cabinet.support'
  if (url.startsWith('/admin/tour-cabinet/tour-users')) return 'admin.tour-cabinet.tour-users'
  if (url.startsWith('/admin/tour-cabinet')) return 'admin.tour-cabinet'
  if (url.startsWith('/admin/tour-reviews')) return 'admin.tour-reviews'
  if (url.startsWith('/admin/directions')) return 'admin.directions'
  if (url.startsWith('/admin/atoms-vkusa')) return 'admin.atoms-vkusa'
  if (url.startsWith('/admin/blog-subscribers')) return 'admin.blog-subscribers'
  if (url.startsWith('/admin/blog')) return 'admin.blog'
  if (url.startsWith('/admin/education-products')) return 'admin.education-products'
  if (url.startsWith('/admin/recipes')) return 'admin.recipes'
  if (url.startsWith('/admin/main-page')) return 'admin.main-page'
  if (url.startsWith('/admin/opportunity-tours-page')) return 'admin.opportunity-tours-page'
  if (url.startsWith('/admin/vshgr-page')) return 'admin.vshgr-page'
  if (url.startsWith('/admin/research-page')) return 'admin.research-page'
  if (url.startsWith('/admin/timeline')) return 'admin.timeline'
  if (url.startsWith('/admin/vacancies')) return 'admin.vacancies'
  return ''
})

function onSelect(itemId) {
  const routeName = ROUTE_MAP[itemId]
  if (!routeName) return
  router.visit(route(routeName))
  if (typeof window !== 'undefined' && !window.matchMedia('(min-width: 1024px)').matches) {
    sidebarOpen.value = false
  }
}

let removeRouterListener

function onEscapeKey(ev) {
  if (ev.key === 'Escape') sidebarOpen.value = false
}

onMounted(() => {
  removeRouterListener = router.on('start', () => {
    sidebarOpen.value = false
  })
  window.addEventListener('keydown', onEscapeKey)
})

onUnmounted(() => {
  removeRouterListener?.()
  window.removeEventListener('keydown', onEscapeKey)
})
</script>
