<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.tours.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к турам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ tour ? 'Редактировать тур' : 'Новый тур' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left column -->
        <div class="space-y-6 rounded-2xl border border-gray-100 bg-white p-8 shadow-sm lg:col-span-2">
          <h2 class="text-base font-bold text-gray-900">Основная информация</h2>

          <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-gray-700">Название *</label>
              <input v-model="form.title" type="text" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
              <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Slug</label>
              <input v-model="form.slug" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm font-mono transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Город старта</label>
              <input v-model="form.start_city" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
              <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Продолжительность</label>
              <input v-model="form.duration" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="2 дня, 1 ночь" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Цена от (₽)</label>
              <input v-model.number="form.price_from" type="number" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Проект</label>
              <select v-model="form.project" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                <option value="">—</option>
                <option value="start_atomgrad">Старт в Атомград</option>
                <option value="atoms_vkusa">Атомы вкуса</option>
                <option value="llr">Лучшие люди Росатома</option>
              </select>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Участие</label>
              <select v-model="form.participation_type" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                <option value="">—</option>
                <option value="contest">Конкурс</option>
                <option value="paid">За свой счёт</option>
              </select>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Сезон</label>
              <select v-model="form.season" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                <option value="">—</option>
                <option value="winter">Зима</option>
                <option value="spring">Весна</option>
                <option value="summer">Лето</option>
                <option value="autumn">Осень</option>
              </select>
            </div>
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Позиция</label>
              <input v-model.number="form.position" type="number" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
          </div>

          <!-- Checkboxes -->
          <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
            <label v-for="opt in checkboxOptions" :key="opt.key" class="group flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-2.5 transition hover:bg-gray-50" :class="form[opt.key] ? 'border-[#003274]/30 bg-[#003274]/5' : ''">
              <input v-model="form[opt.key]" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]/20" />
              <span class="text-sm font-medium text-gray-700">{{ opt.label }}</span>
            </label>
          </div>
        </div>

        <!-- Right column -->
        <div class="space-y-6">
          <!-- Cities -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-gray-900">Города</h2>
            <div class="space-y-2">
              <label v-for="c in cities" :key="c.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-gray-50" :class="form.city_ids.includes(c.id) ? 'bg-[#003274]/5' : ''">
                <input v-model="form.city_ids" type="checkbox" :value="c.id" class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]/20" />
                <span class="text-sm font-medium text-gray-700">{{ c.name }}</span>
              </label>
            </div>
          </div>

          <!-- Departures -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-gray-900">Даты заездов</h2>
            <div class="space-y-3">
              <div v-for="(dep, i) in form.departures" :key="i" class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                <div class="space-y-2">
                  <input v-model="dep.start_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <input v-model="dep.end_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <div class="flex gap-2">
                    <input v-model.number="dep.price_per_person" type="number" placeholder="Цена ₽" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                    <button v-if="form.departures.length > 1" type="button" @click="form.departures.splice(i, 1)" class="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <button type="button" @click="form.departures.push({ start_date: '', end_date: '', price_per_person: '' })" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-200 py-2.5 text-sm font-medium text-gray-500 transition hover:border-[#003274]/30 hover:text-[#003274]">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              Добавить дату
            </button>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-[#003274] px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1] disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Сохранить
        </button>
        <Link :href="route('admin.tours.index')" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ tour: Object, cities: Array })

const checkboxOptions = [
  { key: 'is_active', label: 'Активен' },
  { key: 'is_featured', label: 'На главной' },
  { key: 'for_children', label: 'С детьми' },
  { key: 'for_foreigners', label: 'Иностранцам' },
  { key: 'closed_city', label: 'Закрытый город' },
]

const form = useForm({
  title: props.tour?.title ?? '',
  slug: props.tour?.slug ?? '',
  description: props.tour?.description ?? '',
  start_city: props.tour?.start_city ?? '',
  duration: props.tour?.duration ?? '',
  project: props.tour?.project ?? '',
  participation_type: props.tour?.participation_type ?? '',
  season: props.tour?.season ?? '',
  price_from: props.tour?.price_from ?? null,
  position: props.tour?.position ?? 0,
  city_ids: props.tour?.cities?.map(c => c.id) ?? [],
  is_active: props.tour?.is_active ?? true,
  is_featured: props.tour?.is_featured ?? false,
  for_children: props.tour?.for_children ?? false,
  for_foreigners: props.tour?.for_foreigners ?? false,
  closed_city: props.tour?.closed_city ?? false,
  departures: props.tour?.departures?.length
    ? props.tour.departures.map(d => ({
        start_date: d.start_date?.slice(0, 10) ?? '',
        end_date: d.end_date?.slice(0, 10) ?? '',
        price_per_person: d.price_per_person ?? '',
      }))
    : [{ start_date: '', end_date: '', price_per_person: '' }],
})

function submit() {
  props.tour
    ? form.put(route('admin.tours.update', props.tour.id))
    : form.post(route('admin.tours.store'))
}
</script>
