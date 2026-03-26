<template>
  <LmsLayout :event="event" :user="$page.props.user" :profile="$page.props.profile">
    <Head :title="`${grant?.title} – ${event?.title}`" />
    <div class="mx-auto max-w-4xl space-y-6">
      <RButton variant="ghost" size="sm" @click="router.visit(route('lms.grants.index', { event: event?.slug }))">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </template>
        Все гранты
      </RButton>

      <RCard elevation="raised">
        <div class="p-6 lg:p-8">
          <div class="flex items-start justify-between gap-4">
            <h1 class="font-brand text-2xl font-bold text-gray-900">{{ grant?.title }}</h1>
            <RBadge v-if="enrolled" variant="success">Выбран</RBadge>
          </div>

          <div v-if="grant?.description" class="prose prose-sm mt-4 max-w-none" v-html="grant.description" />

          <!-- Application dates -->
          <div v-if="grant?.application_start || grant?.application_end" class="mt-6 rounded-xl bg-rosatom-50 px-5 py-4">
            <div class="flex items-center gap-2 text-sm font-medium text-rosatom-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
              Приём заявок
            </div>
            <p class="mt-1 text-sm text-rosatom-600">
              <span v-if="grant.application_start">{{ formatDateFull(grant.application_start) }}</span>
              <span v-if="grant.application_start && grant.application_end"> — </span>
              <span v-if="grant.application_end">{{ formatDateFull(grant.application_end) }}</span>
            </p>
          </div>

          <!-- Documents -->
          <div v-if="documents?.length" class="mt-6">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-400">Документы</h2>
            <div class="space-y-2">
              <a
                v-for="doc in documents"
                :key="doc.id"
                :href="doc.url"
                target="_blank"
                class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700">{{ doc.original_name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
              </a>
            </div>
          </div>

          <!-- Enroll action -->
          <div class="mt-8">
            <template v-if="enrolled">
              <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm font-medium text-green-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                  </svg>
                  Вы выбрали этот грант
                </div>
                <RButton variant="outline" size="sm" type="button" @click="unenroll">
                  Отменить выбор
                </RButton>
              </div>
            </template>
            <template v-else>
              <div v-if="!isProfileComplete" class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3">
                <p class="text-sm font-medium text-amber-800">Для выбора гранта необходимо заполнить профиль</p>
                <Link :href="route('lms.profile.edit', { event: event?.slug })" class="mt-1 inline-block text-sm font-medium text-rosatom-600 hover:underline">
                  Перейти в личный кабинет
                </Link>
              </div>
              <RButton v-else variant="primary" @click="enroll">
                Выбрать грант
              </RButton>
            </template>
          </div>
        </div>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: Object,
  grant: Object,
  documents: Array,
  enrolled: Boolean,
  isProfileComplete: { type: Boolean, default: false },
})

function enroll() {
  router.post(route('lms.grants.enroll', { event: props.event?.slug, grant: props.grant?.id }))
}

function unenroll() {
  router.delete(route('lms.grants.unenroll', { event: props.event?.slug, grant: props.grant?.id }))
}

function formatDateFull(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
