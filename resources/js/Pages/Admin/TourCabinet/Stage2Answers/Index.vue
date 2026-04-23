<template>
  <AdminLayout>
    <Head title="Ответы этапа 2 конкурса" />
    <div class="mx-auto max-w-6xl">
      <div class="mb-4">
        <Link
          :href="`${route('admin.tour-cabinet.index')}#tour-cabinet-admin-stage2`"
          class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
        >
          ← ЛК туров
        </Link>
      </div>
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Конкурс ЛК туров: ответы этапа 2</h1>
        <p class="mt-2 text-sm text-gray-600">
          Здесь видны ответы после того, как участник нажал «Отправить» в личном кабинете (или ранее завершил этап 2 по старой схеме).
        </p>
      </div>

      <div v-if="answers.data?.length" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 font-semibold text-gray-900">Участник</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Вопрос</th>
              <th class="px-4 py-3 font-semibold text-gray-900">Ответ</th>
              <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-900">Отправлено</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="row in answers.data" :key="row.id" class="align-top">
              <td class="px-4 py-3 text-gray-800">
                <p class="font-medium">{{ row.user_display }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ row.user_email }}</p>
              </td>
              <td class="max-w-xs px-4 py-3 text-gray-700">{{ row.question_body }}</td>
              <td class="max-w-md px-4 py-3 whitespace-pre-wrap text-gray-800">{{ row.answer_text }}</td>
              <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ formatSubmitted(row.submitted_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center">
        <p class="text-gray-500">Пока нет отправленных ответов по этапу 2.</p>
      </div>

      <div v-if="answers.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex flex-wrap justify-center gap-1">
          <Link
            v-for="link in answers.links"
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
  answers: { type: Object, required: true },
})

function formatSubmitted(iso) {
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
