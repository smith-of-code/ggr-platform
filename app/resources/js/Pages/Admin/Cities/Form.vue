<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.cities.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к городам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ city ? 'Редактировать город' : 'Новый город' }}</h1>
    </div>

    <RCard elevation="raised" class="max-w-2xl">
    <form @submit.prevent="submit" class="space-y-6">
      <RInput v-model="form.name" label="Название *" placeholder="Название города" :error="form.errors.name" required />
      <RInput v-model="form.slug" label="Slug" placeholder="Автоматически из названия" />
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
        <textarea v-model="form.description" rows="4" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Описание города" />
      </div>
      <div>
        <RInput v-model="form.image" type="url" label="URL изображения" placeholder="https://..." />
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
          <RCheckbox v-model="form.is_active" label="Активен" />
        </div>
      </div>

      <div class="flex gap-3 border-t border-gray-100 pt-6">
        <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить
        </RButton>
        <Link :href="route('admin.cities.index')" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
    </RCard>
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
