<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">Роли</h1>
      <Link :href="route('lms.admin.roles.create', event.slug)">
        <RButton variant="primary" size="sm">
          <template #icon><PlusIcon class="h-4 w-4" /></template>
          Новая роль
        </RButton>
      </Link>
    </div>

    <RCard v-if="roles.length">
      <div class="divide-y divide-gray-100">
        <div
          v-for="role in roles"
          :key="role.id"
          class="flex items-center justify-between px-6 py-4 transition hover:bg-gray-50"
        >
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rosatom-100 text-rosatom-600">
                <ShieldCheckIcon class="h-5 w-5" />
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ role.name }}</p>
                <p class="text-xs text-gray-500">{{ role.slug }}</p>
              </div>
            </div>
            <p v-if="role.description" class="ml-12 mt-1 text-sm text-gray-500">{{ role.description }}</p>
          </div>
          <div class="flex items-center gap-4">
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
              {{ role.profiles_count }} {{ pluralUsers(role.profiles_count) }}
            </span>
            <div class="flex gap-1">
              <Link :href="route('lms.admin.roles.edit', [event.slug, role.id])">
                <RButton variant="ghost" size="sm" icon-only title="Редактировать">
                  <template #icon><PencilSquareIcon class="h-4 w-4" /></template>
                </RButton>
              </Link>
              <RButton
                variant="danger"
                size="sm"
                icon-only
                title="Удалить"
                @click="confirmDelete(role)"
              >
                <template #icon><TrashIcon class="h-4 w-4" /></template>
              </RButton>
            </div>
          </div>
        </div>
      </div>
    </RCard>

    <RCard v-else>
      <div class="py-12 text-center">
        <ShieldCheckIcon class="mx-auto h-12 w-12 text-gray-300" />
        <p class="mt-3 text-sm text-gray-500">Ролей пока нет</p>
        <Link :href="route('lms.admin.roles.create', event.slug)" class="mt-4 inline-block">
          <RButton variant="primary" size="sm">Создать первую роль</RButton>
        </Link>
      </div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { PlusIcon, PencilSquareIcon, TrashIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ event: Object, roles: Array })

function pluralUsers(n) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return 'участник'
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return 'участника'
  return 'участников'
}

function confirmDelete(role) {
  if (confirm(`Удалить роль «${role.name}»?`)) {
    router.delete(route('lms.admin.roles.destroy', [props.event.slug, role.id]), {
      preserveScroll: true,
    })
  }
}
</script>
