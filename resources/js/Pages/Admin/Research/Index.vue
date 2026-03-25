<template>
  <AdminLayout>
    <Head title="Исследования" />

    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Исследования</h1>
        <p class="mt-1 text-sm text-gray-500">Управление исследованиями по городам</p>
      </div>
      <Link
        :href="route('admin.research.create')"
        class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Новое исследование
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Город</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Год</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="item in researches.data" :key="item.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ item.title }}</p>
              <p class="mt-0.5 font-mono text-xs text-gray-400">{{ item.slug }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ item.city?.name ?? '—' }}
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ item.year ?? '—' }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <RBadge v-if="item.is_published" variant="success" size="sm">Опубликовано</RBadge>
              <RBadge v-else variant="neutral" size="sm">Черновик</RBadge>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link
                  :href="route('admin.research.edit', item.id)"
                  class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]"
                  title="Редактировать"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                    />
                  </svg>
                </Link>
                <RButton variant="danger" size="sm" icon-only title="Удалить" @click="confirmDestroy(item)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                      />
                    </svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="researches.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">
        Исследований пока нет
      </div>

      <div v-if="researches.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">
          {{ researches.from }}–{{ researches.to }} из {{ researches.total }}
        </p>
        <div class="flex gap-1">
          <button
            v-for="link in researches.links"
            :key="link.label"
            type="button"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="
              link.active
                ? 'bg-[#003274] text-white'
                : 'text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30'
            "
            @click="link.url && router.visit(link.url, { preserveState: true })"
            v-html="link.label"
          />
        </div>
      </div>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ researches: Object })

function confirmDestroy(item) {
  if (confirm(`Удалить исследование «${item.title}»?`)) {
    router.delete(route('admin.research.destroy', item.id))
  }
}
</script>
