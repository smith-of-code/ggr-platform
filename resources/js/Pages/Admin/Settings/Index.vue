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
]
</script>
