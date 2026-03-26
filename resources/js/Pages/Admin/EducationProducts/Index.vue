<template>
  <AdminLayout>
    <Head title="Образовательные продукты" />

    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Образовательные продукты</h1>
        <p class="mt-1 text-sm text-gray-500">ВШГР и программы обучения</p>
      </div>
      <Link
        :href="route('admin.education-products.create')"
        class="flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Новый продукт
      </Link>
    </div>

    <RCard elevation="raised" flush>
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Длительность</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Формат</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Активен</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Позиция</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="product in products.data" :key="product.id" class="transition hover:bg-gray-50/50">
            <td class="px-5 py-3.5">
              <p class="text-sm font-medium text-gray-900">{{ product.title }}</p>
              <p class="mt-0.5 font-mono text-xs text-gray-400">{{ product.slug }}</p>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ product.duration ?? '—' }}
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ product.format ?? '—' }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <RBadge v-if="product.is_active" variant="success" size="sm">Да</RBadge>
              <RBadge v-else variant="neutral" size="sm">Нет</RBadge>
            </td>
            <td class="px-5 py-3.5 text-sm text-gray-600">
              {{ product.position ?? 0 }}
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link
                  :href="route('admin.education-products.edit', product.id)"
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
                <RButton variant="danger" size="sm" icon-only title="Удалить" @click="confirmDestroy(product)">
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
      <div v-if="products.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-400">Продуктов пока нет</div>

      <div v-if="products.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-500">
          {{ products.from }}–{{ products.to }} из {{ products.total }}
        </p>
        <div class="flex gap-1">
          <button
            v-for="link in products.links"
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

    <!-- LMS Courses -->
    <div v-if="lmsCourses && lmsCourses.length" class="mt-10">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Курсы LMS ВШГР</h2>
        <p class="mt-1 text-sm text-gray-500">Курсы из системы дистанционного обучения</p>
      </div>

      <RCard elevation="raised" flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Курс</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Slug</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Записаны</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Даты</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Публикация</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="course in lmsCourses" :key="course.id" class="transition hover:bg-gray-50/50">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div v-if="course.image" class="h-10 w-10 shrink-0 overflow-hidden rounded-lg">
                    <img :src="course.image" class="h-full w-full object-cover" alt="" />
                  </div>
                  <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                    <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ course.title }}</p>
                    <p v-if="course.description" class="mt-0.5 line-clamp-1 text-xs text-gray-400">{{ stripTags(course.description) }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5">
                <span class="font-mono text-xs text-gray-400">{{ course.slug }}</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                  </svg>
                  {{ course.enrollments_count }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-center text-xs text-gray-500">
                <template v-if="course.starts_at || course.ends_at">
                  {{ formatDate(course.starts_at) }} — {{ formatDate(course.ends_at) }}
                </template>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                  :class="course.is_active
                    ? 'bg-green-50 text-green-700 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                  @click="toggleCourse(course)"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="course.is_active ? 'bg-green-500' : 'bg-gray-400'" />
                  {{ course.is_active ? 'Опубликован' : 'Скрыт' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </RCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ products: Object, lmsCourses: { type: Array, default: () => [] } })

function stripTags(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '')
}

function confirmDestroy(product) {
  if (confirm(`Удалить продукт «${product.title}»?`)) {
    router.delete(route('admin.education-products.destroy', product.id))
  }
}

function toggleCourse(course) {
  router.patch(route('admin.education-products.toggleCourse', course.id), {}, {
    preserveScroll: true,
  })
}

function formatDate(dateStr) {
  if (!dateStr) return '...'
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
