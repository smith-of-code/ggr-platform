<template>
  <LmsAdminLayout :events="events.data">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">События</h1>
        <p class="mt-1 text-sm text-gray-500">Управление событиями LMS</p>
      </div>
      <Link :href="route('lms.admin.events.create')" class="inline-flex items-center gap-2 rounded-xl bg-rosatom-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Создать событие
      </Link>
    </div>

    <RCard flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Slug</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Авторизация</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="evt in events.data" :key="evt.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5 text-sm font-medium">
              <Link :href="route('lms.admin.courses.index', evt.slug)" class="text-rosatom-700 underline decoration-rosatom-300 underline-offset-2 transition hover:text-rosatom-900 hover:decoration-rosatom-500">{{ evt.title }}</Link>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500 font-mono">{{ evt.slug }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ evt.auth_method === 'sso' ? 'SSO' : 'Email' }}</td>
            <td class="px-5 py-3.5 text-center">
              <span class="cursor-pointer" @click="toggleActive(evt)">
                <RBadge :variant="evt.is_active ? 'primary' : 'neutral'" :dot="true">
                  {{ evt.is_active ? 'Активно' : 'Скрыто' }}
                </RBadge>
              </span>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('lms.admin.courses.index', evt.slug)" class="inline-flex items-center gap-1.5 rounded-lg bg-rosatom-50 px-3 py-1.5 text-xs font-semibold text-rosatom-600 transition hover:bg-rosatom-100">
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                  Управлять
                </Link>
                <Link :href="route('lms.admin.events.edit', evt.slug)" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <RButton variant="danger" size="sm" icon-only @click="confirmDestroy(evt)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="events.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Событий пока нет</div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

defineProps({ events: Object })

function toggleActive(evt) {
  router.patch(route('lms.admin.events.update', evt.slug), {
    title: evt.title,
    slug: evt.slug,
    description: evt.description ?? '',
    auth_method: evt.auth_method ?? 'email',
    sso_provider_url: evt.sso_provider_url ?? '',
    is_active: !evt.is_active,
  }, { preserveState: true })
}

function confirmDestroy(evt) {
  if (confirm(`Удалить событие "${evt.title}"?`)) {
    router.delete(route('lms.admin.events.destroy', evt.slug))
  }
}
</script>
