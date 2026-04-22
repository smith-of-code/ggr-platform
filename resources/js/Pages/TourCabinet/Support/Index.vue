<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 font-sans text-slate-900">
    <Head title="Поддержка — ЛК туров" />
    <TourCabinetHeader max-width-class="max-w-4xl">
      <template #breadcrumb>
        <Link
          :href="route('tour-cabinet.dashboard')"
          class="mb-2 inline-flex items-center gap-1.5 text-sm font-semibold text-rosatom-700 transition hover:text-rosatom-900"
        >
          <span aria-hidden="true">←</span>
          Личный кабинет
        </Link>
      </template>
      <template #title>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Поддержка</h1>
      </template>
      <template #subtitle>
        <p class="text-sm text-slate-600">История обращений и ответов команды.</p>
        <p v-if="supportContactEmail" class="mt-2 text-sm text-slate-600">
          Также можно написать на
          <a :href="`mailto:${supportContactEmail}`" class="font-semibold text-rosatom-700 underline hover:text-rosatom-900">{{ supportContactEmail }}</a>
        </p>
      </template>
      <template #toolbar>
        <div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
          <Link :href="route('tour-cabinet.support.create')" class="w-full sm:w-auto">
            <RButton variant="primary" size="sm" class="w-full min-h-[2.75rem] sm:min-h-0 sm:w-auto">
              Новое обращение
            </RButton>
          </Link>
          <form @submit.prevent="logout" class="w-full sm:w-auto">
            <RButton type="submit" variant="outline" size="sm" class="w-full min-h-[2.75rem] sm:min-h-0 sm:w-auto">
              Выйти
            </RButton>
          </form>
        </div>
      </template>
    </TourCabinetHeader>

    <div class="mx-auto max-w-4xl px-3 pb-10 pt-4 sm:px-4 lg:px-6 sm:pt-6">
      <div
        v-if="$page.props.flash?.success"
        class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 sm:mb-6"
      >
        {{ $page.props.flash.success }}
      </div>
      <div
        v-if="$page.props.flash?.error"
        class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mb-6"
      >
        {{ $page.props.flash.error }}
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5">
        <template v-if="tickets.data.length">
          <ul class="divide-y divide-slate-100">
            <li v-for="t in tickets.data" :key="t.id">
              <Link :href="route('tour-cabinet.support.show', t.id)" class="block px-4 py-4 transition hover:bg-slate-50/80 sm:px-5">
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
import TourCabinetHeader from '@/Components/TourCabinet/TourCabinetHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'

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

function logout() {
  router.post(route('tour-cabinet.logout'))
}
</script>
