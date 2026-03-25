<template>
  <AdminLayout>
    <Head title="Блог — Управление статьями" />

    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Блог — Управление статьями</h1>
        <p class="mt-1 text-sm text-gray-500">Создание и публикация материалов</p>
      </div>
      <Link
        :href="route('admin.blog.create')"
        class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Новая статья
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Превью</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Заголовок</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Категория</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Статус</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Дата</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="post in posts.data" :key="post.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5">
              <div v-if="post.image" class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img :src="post.image" :alt="post.title" class="h-full w-full object-cover" />
              </div>
              <div v-else class="flex h-10 w-14 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                —
              </div>
            </td>
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ post.title }}</p>
              <p class="mt-0.5 font-mono text-xs text-gray-400">{{ post.slug }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ categoryLabel(post.category) }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <RBadge v-if="post.is_published" variant="success" size="sm">Опубликовано</RBadge>
              <RBadge v-else variant="neutral" size="sm">Черновик</RBadge>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-500">
              {{ formatPostDate(post) }}
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link
                  :href="route('admin.blog.edit', post.id)"
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
                <button
                  type="button"
                  :title="post.is_published ? 'Снять с публикации' : 'Опубликовать'"
                  class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-[#003274]"
                  @click="togglePublish(post)"
                >
                  <svg v-if="post.is_published" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21" />
                  </svg>
                  <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </button>
                <RButton variant="danger" size="sm" icon-only title="Удалить" @click="confirmDestroy(post)">
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
      <div v-if="posts.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">Статей пока нет</div>

      <div v-if="posts.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">
          {{ posts.from }}–{{ posts.to }} из {{ posts.total }}
        </p>
        <div class="flex gap-1">
          <button
            v-for="link in posts.links"
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

const CATEGORIES = {
  news: 'Новости',
  announcements: 'Анонсы',
  partner_articles: 'Статьи партнёров',
}

defineProps({ posts: Object })

function categoryLabel(key) {
  return CATEGORIES[key] ?? key ?? '—'
}

function formatPostDate(post) {
  const raw = post.is_published && post.published_at ? post.published_at : post.updated_at ?? post.created_at
  if (!raw) return '—'
  return new Date(raw).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

function togglePublish(post) {
  router.patch(route('admin.blog.togglePublish', post.id), {}, { preserveState: true })
}

function confirmDestroy(post) {
  if (confirm(`Удалить статью «${post.title}»?`)) {
    router.delete(route('admin.blog.destroy', post.id))
  }
}
</script>
