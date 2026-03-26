<template>
  <MainLayout>
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
      <!-- Back -->
      <Link :href="route('vacancies.index')" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Все вакансии
      </Link>

      <!-- Image -->
      <div v-if="vacancy.image" class="mb-8 overflow-hidden rounded-2xl">
        <img :src="vacancy.image" :alt="vacancy.title" class="h-64 w-full object-cover sm:h-80" />
      </div>

      <!-- Header -->
      <div class="mb-8">
        <div class="flex flex-wrap items-center gap-2">
          <RBadge v-if="vacancy.city" variant="info">{{ vacancy.city.name }}</RBadge>
          <RBadge v-if="vacancy.employment_type" variant="neutral">{{ typeLabel(vacancy.employment_type) }}</RBadge>
        </div>
        <h1 class="mt-4 text-3xl font-bold text-gray-900">{{ vacancy.title }}</h1>
        <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500">
          <span v-if="vacancy.company" class="flex items-center gap-1.5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
            {{ vacancy.company }}
          </span>
          <span v-if="vacancy.city" class="flex items-center gap-1.5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
            {{ vacancy.city.name }}
          </span>
        </div>
        <p v-if="vacancy.salary" class="mt-4 text-2xl font-bold text-[#003274]">{{ vacancy.salary }}</p>
      </div>

      <!-- Content sections -->
      <div class="space-y-8">
        <section v-if="vacancy.description">
          <h2 class="mb-3 text-lg font-bold text-gray-900">О вакансии</h2>
          <div class="prose prose-sm max-w-none text-gray-600" v-html="vacancy.description" />
        </section>

        <section v-if="vacancy.responsibilities">
          <h2 class="mb-3 text-lg font-bold text-gray-900">Обязанности</h2>
          <div class="prose prose-sm max-w-none text-gray-600" v-html="vacancy.responsibilities" />
        </section>

        <section v-if="vacancy.requirements">
          <h2 class="mb-3 text-lg font-bold text-gray-900">Требования</h2>
          <div class="prose prose-sm max-w-none text-gray-600" v-html="vacancy.requirements" />
        </section>

        <section v-if="vacancy.conditions">
          <h2 class="mb-3 text-lg font-bold text-gray-900">Условия</h2>
          <div class="prose prose-sm max-w-none text-gray-600" v-html="vacancy.conditions" />
        </section>

        <!-- Contacts -->
        <section v-if="vacancy.contact_email || vacancy.contact_phone" class="rounded-2xl bg-blue-50 p-6">
          <h2 class="mb-3 text-lg font-bold text-gray-900">Контакты</h2>
          <div class="flex flex-wrap gap-6">
            <a v-if="vacancy.contact_email" :href="`mailto:${vacancy.contact_email}`" class="flex items-center gap-2 text-sm font-medium text-[#003274] hover:underline">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
              {{ vacancy.contact_email }}
            </a>
            <a v-if="vacancy.contact_phone" :href="`tel:${vacancy.contact_phone}`" class="flex items-center gap-2 text-sm font-medium text-[#003274] hover:underline">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
              {{ vacancy.contact_phone }}
            </a>
          </div>
        </section>
      </div>

      <!-- Related -->
      <div v-if="relatedVacancies.length" class="mt-12 border-t border-gray-200 pt-10">
        <h2 class="mb-6 text-xl font-bold text-gray-900">Другие вакансии</h2>
        <div class="grid gap-6 sm:grid-cols-3">
          <Link v-for="rv in relatedVacancies" :key="rv.id" :href="route('vacancies.show', rv.slug)" class="group">
            <RCard elevation="raised" hoverable>
              <div>
                <h3 class="font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ rv.title }}</h3>
                <p v-if="rv.company" class="mt-1 text-sm text-gray-500">{{ rv.company }}</p>
                <p v-if="rv.salary" class="mt-2 font-bold text-[#003274]">{{ rv.salary }}</p>
              </div>
            </RCard>
          </Link>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

defineProps({ vacancy: Object, relatedVacancies: Array })

const types = { full_time: 'Полная занятость', part_time: 'Частичная', remote: 'Удалённо', internship: 'Стажировка', contract: 'Подряд' }
function typeLabel(t) { return types[t] || t }
</script>
