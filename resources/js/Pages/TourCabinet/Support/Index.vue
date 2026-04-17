<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 px-4 py-8 font-sans text-slate-900">
    <Head title="Поддержка — ЛК туров" />
    <div class="mx-auto max-w-4xl">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <Link :href="route('tour-cabinet.dashboard')" class="text-sm font-semibold text-rosatom-700 underline decoration-rosatom-300 underline-offset-4 hover:text-rosatom-900">
            ← Кабинет
          </Link>
          <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Поддержка</h1>
          <p class="mt-1 text-sm text-slate-600">История обращений и ответов команды.</p>
          <p v-if="supportContactEmail" class="mt-2 text-sm text-slate-600">
            Также можно написать на
            <a :href="`mailto:${supportContactEmail}`" class="font-semibold text-rosatom-700 underline hover:text-rosatom-900">{{ supportContactEmail }}</a>
          </p>
        </div>
        <Link :href="route('tour-cabinet.support.create')">
          <RButton variant="primary" size="sm">Новое обращение</RButton>
        </Link>
      </div>

      <div v-if="$page.props.flash?.success" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ $page.props.flash.error }}
      </div>

      <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5">
        <template v-if="tickets.data.length">
          <ul class="divide-y divide-slate-100">
            <li v-for="t in tickets.data" :key="t.id">
              <Link :href="route('tour-cabinet.support.show', t.id)" class="block px-5 py-4 transition hover:bg-slate-50/80">
                <div class="flex flex-wrap items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-900">#{{ t.id }} — {{ t.subject }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ t.category_label }} · {{ formatDate(t.last_message_at || t.created_at) }}</p>
                  </div>
                  <span class="inline-flex shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/80">
                    {{ t.status_label }}
                  </span>
                </div>
              </Link>
            </li>
          </ul>
          <div v-if="tickets.prev_page_url || tickets.next_page_url" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 px-4 py-3 text-sm">
            <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="font-medium text-rosatom-700 underline hover:text-rosatom-900" preserve-scroll>Назад</Link>
            <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="font-medium text-rosatom-700 underline hover:text-rosatom-900" preserve-scroll>Вперёд</Link>
          </div>
        </template>
        <p v-else class="px-6 py-12 text-center text-sm text-slate-600">Обращений пока нет. Создайте первое — мы ответим в диалоге и при необходимости на email.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  tickets: { type: Object, required: true },
  categoryOptions: { type: Array, default: () => [] },
  supportContactEmail: { type: String, default: null },
})

function formatDate(iso) {
  if (!iso) return '—'
  try {
    const d = new Date(iso)
    return d.toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return iso
  }
}
</script>
