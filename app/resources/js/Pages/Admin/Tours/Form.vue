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
        <RCard elevation="raised" class="lg:col-span-2">
          <div class="space-y-6">
          <h2 class="text-base font-bold text-gray-900">Основная информация</h2>

          <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <RInput v-model="form.title" label="Название *" :error="form.errors.title" required />
            </div>
            <RInput v-model="form.slug" label="Slug" />
            <RInput v-model="form.start_city" label="Город старта" />
            <div class="sm:col-span-2">
              <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
              <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
            </div>
            <RInput v-model="form.duration" label="Продолжительность" placeholder="2 дня, 1 ночь" />
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
            <RCheckbox v-for="opt in checkboxOptions" :key="opt.key" v-model="form[opt.key]" :label="opt.label" />
          </div>
          </div>
        </RCard>

        <!-- Right column -->
        <div class="space-y-6">
          <!-- Cities -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Города</h2>
            <div class="space-y-2">
              <label v-for="c in cities" :key="c.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-gray-50" :class="form.city_ids.includes(c.id) ? 'bg-[#003274]/5' : ''">
                <input v-model="form.city_ids" type="checkbox" :value="c.id" class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]/20" />
                <span class="text-sm font-medium text-gray-700">{{ c.name }}</span>
              </label>
            </div>
          </RCard>

          <!-- Departures -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Даты заездов</h2>
            <div class="space-y-3">
              <div v-for="(dep, i) in form.departures" :key="i" class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                <div class="space-y-2">
                  <input v-model="dep.start_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <input v-model="dep.end_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <div class="flex gap-2">
                    <input v-model.number="dep.price_per_person" type="number" placeholder="Цена ₽" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                    <RButton v-if="form.departures.length > 1" variant="danger" size="sm" icon-only @click="form.departures.splice(i, 1)">
                      <template #icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                      </template>
                    </RButton>
                  </div>
                </div>
              </div>
            </div>
            <RButton variant="outline" block class="mt-3" @click="form.departures.push({ start_date: '', end_date: '', price_per_person: '' })">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              </template>
              Добавить дату
            </RButton>
          </RCard>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить
        </RButton>
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
