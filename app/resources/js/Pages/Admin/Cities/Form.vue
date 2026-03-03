<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.cities.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к городам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ city ? 'Редактировать город' : 'Новый город' }}</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Название *</label>
        <input v-model="form.name" type="text" required class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Название города" />
        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Slug</label>
        <input v-model="form.slug" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm font-mono transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Автоматически из названия" />
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
        <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Описание города" />
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">URL изображения</label>
        <input v-model="form.image" type="url" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="https://..." />
        <div v-if="form.image" class="mt-3 overflow-hidden rounded-xl border border-gray-200">
          <img :src="form.image" class="h-40 w-full object-cover" @error="form.image = ''" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Позиция</label>
          <input v-model.number="form.position" type="number" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
        </div>
        <div class="flex items-end pb-1">
          <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:bg-gray-50">
            <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded-md border-2 border-gray-300 text-[#003274] transition focus:ring-[#003274]/20" />
            <span class="text-sm font-medium text-gray-700">Активен</span>
          </label>
        </div>
      </div>

      <div class="flex gap-3 border-t border-gray-100 pt-6">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-[#003274] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1] disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Сохранить
        </button>
        <Link :href="route('admin.cities.index')" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ city: Object })

const form = useForm({
  name: props.city?.name ?? '',
  slug: props.city?.slug ?? '',
  description: props.city?.description ?? '',
  image: props.city?.image ?? '',
  position: props.city?.position ?? 0,
  is_active: props.city?.is_active ?? true,
})

function submit() {
  props.city
    ? form.put(route('admin.cities.update', props.city.id))
    : form.post(route('admin.cities.store'))
}
</script>
