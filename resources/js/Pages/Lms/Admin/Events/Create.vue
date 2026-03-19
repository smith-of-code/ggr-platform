<template>
  <LmsAdminLayout>
    <div class="mb-8">
      <Link :href="route('lms.admin.events.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к событиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">Новое событие</h1>
    </div>

    <RCard class="max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6">
        <RInput
          v-model="form.title"
          label="Название *"
          placeholder="Название события"
          required
          :error="form.errors.title"
          @blur="generateSlug"
        />
        <RInput
          v-model="form.slug"
          label="Slug"
          placeholder="Автоматически из названия"
          :error="form.errors.slug"
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Описание события" />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Способ авторизации</label>
          <select v-model="form.auth_method" class="w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
            <option value="email">Email</option>
            <option value="sso">SSO</option>
          </select>
        </div>
        <RInput
          v-if="form.auth_method === 'sso'"
          v-model="form.sso_provider_url"
          label="URL SSO провайдера"
          type="url"
          placeholder="https://..."
          :error="form.errors.sso_provider_url"
        />
        <RCheckbox v-model="form.is_active" label="Активно" />

        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
            Создать
          </RButton>
          <Link :href="route('lms.admin.events.index')" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const form = useForm({
  title: '',
  slug: '',
  description: '',
  auth_method: 'email',
  sso_provider_url: '',
  is_active: true,
})

function slugify(text) {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
}

function generateSlug() {
  if (form.title && !form.slug) {
    form.slug = slugify(form.title)
  }
}

function submit() {
  form.post(route('lms.admin.events.store'))
}
</script>
