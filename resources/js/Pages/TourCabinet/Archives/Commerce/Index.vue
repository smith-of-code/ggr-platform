<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 font-sans text-slate-900">
    <Head title="Архив коммерческих туров — ЛК туров" />
    <TourCabinetHeader max-width-class="max-w-4xl">
      <template #title>
        <h1 class="text-xl font-bold leading-tight tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">Архив коммерческих туров</h1>
      </template>
      <template #subtitle>
        <p class="text-sm leading-relaxed text-slate-600">Отправленные заявки по коммерческим турам. Только для чтения.</p>
      </template>
      <template #toolbar>
        <div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
          <Link :href="route('tour-cabinet.dashboard')" class="w-full sm:w-auto">
            <RButton type="button" variant="outline" size="sm" class="w-full min-h-[2.75rem] sm:min-h-0 sm:w-auto">
              Личный кабинет
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
      <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5">
        <template v-if="archives.data.length">
          <ul class="divide-y divide-slate-100">
            <li v-for="a in archives.data" :key="a.id">
              <Link
                :href="route('tour-cabinet.archives.commerce.show', a.id)"
                class="block px-4 py-4 transition hover:bg-slate-50/80 sm:px-5"
              >
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <ArchiveBoxIcon class="h-5 w-5 shrink-0 text-rosatom-600" aria-hidden="true" />
                      <p class="font-semibold text-slate-900">Заявка №{{ a.id }}</p>
                    </div>
                    <p v-if="a.tour_title" class="mt-1 text-sm text-slate-700">{{ a.tour_title }}</p>
                    <p v-if="a.city_name" class="mt-0.5 text-xs text-slate-500">Город: {{ a.city_name }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                      <ClockIcon class="h-3.5 w-3.5 shrink-0 text-slate-400" aria-hidden="true" />
                      Отправлено: {{ formatDateTime(a.submitted_at) }}
                    </p>
                  </div>
                  <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 ring-1 ring-emerald-200">
                    <CheckCircleIcon class="h-3.5 w-3.5 text-emerald-700" aria-hidden="true" />
                    Отправлено
                  </span>
                </div>
              </Link>
            </li>
          </ul>
          <div v-if="archives.prev_page_url || archives.next_page_url" class="flex flex-wrap justify-end gap-2 border-t border-slate-100 px-4 py-3 text-sm">
            <Link v-if="archives.prev_page_url" :href="archives.prev_page_url" class="font-medium text-rosatom-700 underline hover:text-rosatom-900" preserve-scroll>Назад</Link>
            <Link v-if="archives.next_page_url" :href="archives.next_page_url" class="font-medium text-rosatom-700 underline hover:text-rosatom-900" preserve-scroll>Вперёд</Link>
          </div>
        </template>
        <p v-else class="px-6 py-12 text-center text-sm text-slate-600">Архивных заявок по коммерческим турам пока нет.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArchiveBoxIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline'
import { Head, Link, router } from '@inertiajs/vue3'
import TourCabinetHeader from '@/Components/TourCabinet/TourCabinetHeader.vue'

defineProps({
  archives: { type: Object, required: true },
})

function formatDateTime(iso) {
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
