<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Участники</h1>
        <p class="mt-1 text-sm text-gray-500">Участники события «{{ event.title }}»</p>
      </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
      <select :value="filters?.role ?? ''" @change="(e) => applyFilters({ role: e.target.value, group: filters?.group ?? '' })" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
        <option value="">Все роли</option>
        <option value="participant">Участник</option>
        <option value="curator">Куратор</option>
        <option value="admin">Админ</option>
      </select>
      <select :value="filters?.group ?? ''" @change="(e) => applyFilters({ role: filters?.role ?? '', group: e.target.value })" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
        <option value="">Все группы</option>
        <option v-for="g in groups" :key="g.id" :value="String(g.id)">{{ g.title }}</option>
      </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Группа</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Активность</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="profile in profiles.data" :key="profile.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5">
              <Link :href="route('lms.admin.users.show', [event.id, profile.id])" class="block">
                <p class="text-sm font-medium text-gray-900 hover:text-rosatom-600">{{ profile.user?.name }}</p>
                <p class="text-xs text-gray-500">{{ profile.user?.email }}</p>
              </Link>
            </td>
            <td class="px-5 py-3.5">
              <select
                :value="profile.role"
                @change="updateRole(profile, $event.target.value)"
                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm text-gray-900"
              >
                <option value="participant">Участник</option>
                <option value="curator">Куратор</option>
                <option value="admin">Админ</option>
              </select>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.group?.title ?? '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ profile.last_activity ? formatDate(profile.last_activity) : '—' }}</td>
            <td class="px-5 py-3.5 text-right">
              <button @click="confirmDestroy(profile)" class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79" /></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="profiles.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Участников пока нет</div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, profiles: Object, groups: Array, filters: Object })

function applyFilters(params) {
  const p = { role: params.role ?? '', group: params.group ?? '' }
  if (!p.role) delete p.role
  if (!p.group) delete p.group
  router.get(route('lms.admin.users.index', props.event.id), p, { preserveState: true })
}

function formatDate(str) {
  if (!str) return '—'
  return new Date(str).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function updateRole(profile, role) {
  router.patch(route('lms.admin.users.update', [props.event.id, profile.id]), { role }, { preserveState: true })
}

function confirmDestroy(profile) {
  if (confirm(`Удалить участника "${profile.user?.name}" из события?`)) {
    router.delete(route('lms.admin.users.destroy', [props.event.id, profile.id]))
  }
}
</script>
