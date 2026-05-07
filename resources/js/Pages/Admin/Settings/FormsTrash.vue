<template>
  <AdminLayout>
    <Head title="Корзина форм" />

    <div class="mb-8">
      <div class="flex items-center gap-3">
        <Link
          :href="route('admin.settings.index')"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Корзина форм</h1>
          <p class="mt-1 text-sm text-gray-500">
            Удалённые формы LMS / ЛК туров. Можно восстановить (вернуть в обычные списки) или удалить навсегда (вместе с полями, отправками и ответами — без возврата).
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="page.props.flash?.success"
      class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
    >
      {{ page.props.flash.success }}
    </div>
    <div
      v-if="page.props.flash?.error"
      class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
    >
      {{ page.props.flash.error }}
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div v-if="forms.data.length === 0" class="px-6 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
        </svg>
        <p class="mt-3 text-sm font-medium text-gray-700">Корзина пуста</p>
        <p class="mt-1 text-xs text-gray-400">Все актуальные формы — в LMS Admin или на «ЛК туров → Формы».</p>
      </div>

      <table v-else class="w-full">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <th class="px-6 py-3.5">Форма</th>
            <th class="px-6 py-3.5">Событие LMS</th>
            <th class="px-6 py-3.5 text-right">Полей</th>
            <th class="px-6 py-3.5 text-right">Отправок</th>
            <th class="px-6 py-3.5">Удалена</th>
            <th class="px-6 py-3.5 text-right">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="form in forms.data" :key="form.id" class="transition hover:bg-gray-50/50">
            <td class="px-6 py-4">
              <div class="text-sm font-semibold text-gray-900">{{ form.title }}</div>
              <div class="mt-1 font-mono text-xs text-gray-400">/forms/{{ form.slug }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-700">{{ form.event?.title || '—' }}</div>
              <div v-if="form.event?.slug" class="mt-1 font-mono text-xs text-gray-400">{{ form.event.slug }}</div>
            </td>
            <td class="px-6 py-4 text-right text-sm tabular-nums text-gray-700">{{ form.fields_count }}</td>
            <td class="px-6 py-4 text-right text-sm tabular-nums text-gray-700">{{ form.submissions_count }}</td>
            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(form.deleted_at) }}</td>
            <td class="px-6 py-4">
              <div class="flex justify-end gap-2">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="restoringId === form.id || forceDeletingId === form.id"
                  @click="restoreForm(form)"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                  {{ restoringId === form.id ? 'Восстановление…' : 'Восстановить' }}
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="restoringId === form.id || forceDeletingId === form.id"
                  @click="forceDeleteForm(form)"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                  </svg>
                  {{ forceDeletingId === form.id ? 'Удаление…' : 'Удалить навсегда' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="forms.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-6 py-3">
        <p class="text-xs text-gray-500">{{ forms.from }}–{{ forms.to }} из {{ forms.total }}</p>
        <div class="flex gap-1">
          <button
            v-for="link in forms.links"
            :key="link.label"
            type="button"
            :disabled="!link.url"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
            :class="link.active ? 'bg-[#003274] text-white' : 'text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30'"
            @click="link.url && router.visit(link.url, { preserveState: true })"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  forms: {
    type: Object,
    required: true,
  },
})

const page = usePage()

const restoringId = ref(null)
const forceDeletingId = ref(null)

function formatDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('ru-RU', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function restoreForm(form) {
  if (restoringId.value === form.id || forceDeletingId.value === form.id) return
  if (!confirm(`Восстановить форму «${form.title}»? Она снова появится в обычных списках админки.`)) return

  restoringId.value = form.id
  router.post(
    route('admin.settings.forms-trash.restore', form.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        restoringId.value = null
      },
    },
  )
}

function forceDeleteForm(form) {
  if (restoringId.value === form.id || forceDeletingId.value === form.id) return
  const submissions = Number(form.submissions_count ?? 0)
  const fields = Number(form.fields_count ?? 0)
  const message = `Удалить навсегда форму «${form.title}»?\n\nЭто действие нельзя отменить. Будут безвозвратно удалены: ${fields} полей, ${submissions} отправок и все связанные ответы.`
  if (!confirm(message)) return

  forceDeletingId.value = form.id
  router.delete(
    route('admin.settings.forms-trash.destroy', form.id),
    {
      preserveScroll: true,
      onFinish: () => {
        forceDeletingId.value = null
      },
    },
  )
}
</script>
