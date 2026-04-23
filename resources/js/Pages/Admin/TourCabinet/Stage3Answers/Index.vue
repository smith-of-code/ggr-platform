<template>
  <AdminLayout>
    <Head title="Ответы этапа 3 конкурса" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-stage3`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Конкурс ЛК туров: ответы этапа 3</h1>
        <p class="mt-2 text-sm text-gray-600">Сохранённые ответы участников на проверочное задание.</p>
      </div>

      <div v-if="rows.data?.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 font-semibold text-gray-900">Участник</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Направление</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Задание</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Формат</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Текст</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Видео / файл</th>
              <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900">Обновлено</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in rows.data" :key="row.id" class="align-top">
              <td class="px-4 py-3 text-gray-800">
                <p class="font-medium">{{ row.user_display }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ row.user_email }}</p>
              </td>
              <td class="px-4 py-3 text-gray-700">{{ row.direction_label }}</td>
              <td class="max-w-[10rem] px-4 py-3 text-gray-700">{{ row.assignment_title }}</td>
              <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ formatLabel(row.response_format) }}</td>
              <td class="max-w-md px-4 py-3 whitespace-pre-wrap text-gray-800">{{ row.stage3_text }}</td>
              <td class="max-w-xs px-4 py-3 text-gray-700">
                <a
                  v-if="row.stage3_video_url"
                  :href="row.stage3_video_url"
                  class="break-all text-[#003274] hover:underline"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Ссылка
                </a>
                <a
                  v-else-if="row.stage3_has_attachment && row.attachment_download_url"
                  :href="row.attachment_download_url"
                  class="text-[#003274] hover:underline"
                >
                  {{ row.stage3_attachment_original_name || 'Скачать файл' }}
                </a>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ formatDate(row.updated_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center">
        <p class="text-gray-500">Пока нет сохранённых ответов по этапу 3.</p>
      </div>

      <div v-if="rows.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex flex-wrap justify-center gap-1">
          <Link
            v-for="link in rows.links"
            :key="link.label"
            :href="link.url || '#'"
            class="rounded-lg px-3 py-2 text-sm transition"
            :class="
              link.active
                ? 'bg-[#003274] text-white'
                : link.url
                  ? 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                  : 'cursor-not-allowed text-gray-400'
            "
            preserve-scroll
            v-html="link.label"
          />
        </nav>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  rows: { type: Object, required: true },
})

function formatLabel(format) {
  if (format === 'file_upload') {
    return 'текст + файл'
  }
  return 'текст + видео'
}

function formatDate(iso) {
  if (!iso) {
    return '—'
  }
  try {
    const d = new Date(iso)
    if (Number.isNaN(d.getTime())) {
      return '—'
    }
    return d.toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return '—'
  }
}
</script>
