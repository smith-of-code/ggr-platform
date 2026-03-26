<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`Гранты – ${event?.title}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Гранты</h1>

      <div v-if="!isProfileComplete" class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div>
          <p class="font-semibold text-amber-800">Заполните профиль</p>
          <p class="mt-1 text-sm text-amber-700">Для выбора гранта необходимо заполнить личный кабинет.</p>
          <Link :href="route('lms.profile.edit', { event: event?.slug })" class="mt-2 inline-block text-sm font-medium text-rosatom-600 hover:underline">
            Перейти в личный кабинет
          </Link>
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="item in grants"
          :key="item.grant.id"
          class="group cursor-pointer overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          @click="router.visit(route('lms.grants.show', { event: event?.slug, grant: item.grant.id }))"
        >
          <div class="p-5">
            <div class="mb-3 flex items-start justify-between gap-2">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rosatom-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rosatom-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
              </div>
              <RBadge v-if="item.enrolled" variant="success" size="sm">Выбран</RBadge>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ item.grant.title }}</h3>
            <p v-if="item.grant.description" class="mt-2 line-clamp-3 text-sm text-gray-500">{{ stripTags(item.grant.description) }}</p>
            <div v-if="item.grant.application_start || item.grant.application_end" class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
              <span>Приём заявок: {{ formatDateRange(item.grant) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!grants?.length" class="rounded-xl border border-dashed border-gray-200 bg-white py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <p class="mt-3 text-sm text-gray-400">Гранты пока не добавлены</p>
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: Object,
  grants: Array,
  isProfileComplete: { type: Boolean, default: false },
})

function stripTags(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '')
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

function formatDateRange(grant) {
  const parts = []
  if (grant.application_start) parts.push(formatDate(grant.application_start))
  if (grant.application_end) parts.push(formatDate(grant.application_end))
  return parts.join(' – ')
}
</script>
