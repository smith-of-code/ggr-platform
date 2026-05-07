<template>
  <LmsAdminLayout :event="event">
    <Head :title="`Участники — ${grant.title}`" />

    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
      <div>
        <Link
          :href="route('lms.admin.grants.index', event.slug)"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900"
        >
          ← Назад к возможностям
        </Link>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Участники: {{ grant.title }}</h1>
        <div class="mt-2 flex flex-wrap gap-2 text-xs">
          <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">{{ grant.type_label }}</span>
          <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">Приём: {{ grant.application_period }}</span>
          <span class="rounded-full bg-rosatom-50 px-3 py-1 text-rosatom-700">{{ grant.application_status }}</span>
          <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">Всего: {{ enrollments.total }}</span>
        </div>
      </div>

      <a :href="route('lms.admin.grants.export', { event: event.slug, grant_id: grant.id })">
        <RButton variant="outline">Выгрузить Excel</RButton>
      </a>
    </div>

    <RCard elevation="raised" flush>
      <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px] text-left text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
              <th class="px-5 py-3 font-medium">#</th>
              <th class="px-5 py-3 font-medium">ФИО</th>
              <th class="px-5 py-3 font-medium">Email</th>
              <th class="px-5 py-3 font-medium">Телефон</th>
              <th class="px-5 py-3 font-medium">Проект</th>
              <th class="px-5 py-3 font-medium">Город</th>
              <th class="px-5 py-3 font-medium">Должность</th>
              <th class="px-5 py-3 font-medium">Организация</th>
              <th class="px-5 py-3 font-medium">Дата</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(e, idx) in enrollments.data" :key="e.id" class="transition hover:bg-gray-50/50">
              <td class="px-5 py-4 text-gray-400">{{ rowNumber(idx) }}</td>
              <td class="px-5 py-4 font-medium text-gray-900">{{ fullName(e.user) }}</td>
              <td class="px-5 py-4 text-gray-500">{{ e.user?.email || '—' }}</td>
              <td class="px-5 py-4 text-gray-500">{{ e.user?.phone || '—' }}</td>
              <td class="max-w-md px-5 py-4 text-gray-600">{{ e.profile?.project_description || '—' }}</td>
              <td class="px-5 py-4 text-gray-500">{{ e.profile?.city || '—' }}</td>
              <td class="px-5 py-4 text-gray-500">{{ e.profile?.position || '—' }}</td>
              <td class="px-5 py-4 text-gray-500">{{ e.profile?.organization || '—' }}</td>
              <td class="px-5 py-4 text-gray-400">{{ e.created_at || '—' }}</td>
            </tr>
            <tr v-if="!enrollments.data.length">
              <td colspan="9" class="px-5 py-16 text-center text-sm text-gray-400">Нет записавшихся участников</td>
            </tr>
          </tbody>
        </table>
      </div>
    </RCard>

    <div v-if="enrollments.last_page > 1" class="mt-6 flex items-center justify-between">
      <p class="text-xs text-gray-500">{{ enrollments.from }}–{{ enrollments.to }} из {{ enrollments.total }}</p>
      <div class="flex gap-1">
        <Link
          v-for="link in enrollments.links"
          :key="link.label"
          :href="link.url || '#'"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-medium transition',
            link.active ? 'bg-rosatom-600 text-white' : 'text-gray-500 hover:bg-gray-100',
            link.url ? '' : 'pointer-events-none opacity-30',
          ]"
          v-html="link.label"
        />
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({
  event: Object,
  grant: Object,
  enrollments: Object,
})

function fullName(user) {
  if (!user) return '—'
  return [user.last_name, user.first_name, user.patronymic].filter(Boolean).join(' ') || user.name || '—'
}

function rowNumber(idx) {
  return (props.enrollments.from || 1) + idx
}
</script>
