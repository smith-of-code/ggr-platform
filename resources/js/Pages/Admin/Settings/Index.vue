<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Настройки</h1>
      <p class="mt-1 text-sm text-gray-500">Управление конфигурацией платформы</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <Link
        v-for="section in sections"
        :key="section.route"
        :href="route(section.route)"
        class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 hover:border-[#003274]/20 hover:shadow-lg hover:shadow-[#003274]/5"
      >
        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl transition-colors" :class="section.iconBg">
          <svg class="h-5 w-5 transition-colors" :class="section.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" :d="section.icon" />
          </svg>
        </div>

        <h3 class="text-sm font-bold text-gray-900 group-hover:text-[#003274]">{{ section.title }}</h3>
        <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ section.description }}</p>

        <svg class="absolute right-5 top-6 h-4 w-4 text-gray-300 transition-all group-hover:translate-x-0.5 group-hover:text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
      </Link>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const sections = [
  {
    route: 'admin.settings.mail',
    title: 'Почта (SMTP)',
    description: 'Настройки SMTP-сервера, отправителя и параметров отправки электронной почты',
    icon: 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
    iconBg: 'bg-blue-50 group-hover:bg-blue-100',
    iconColor: 'text-blue-600',
  },
  {
    route: 'admin.settings.page-visibility',
    title: 'Видимость страниц',
    description: 'Управление видимостью разделов сайта. Скрытые страницы показывают заглушку «В разработке»',
    icon: 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88',
    iconBg: 'bg-amber-50 group-hover:bg-amber-100',
    iconColor: 'text-amber-600',
  },
]
</script>
