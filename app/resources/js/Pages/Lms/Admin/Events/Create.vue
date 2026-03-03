<template>
  <LmsAdminLayout>
    <div class="mb-8">
      <Link :href="route('lms.admin.events.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к событиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">Новое событие</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
        <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Название события" @blur="generateSlug" />
        <p v-if="form.errors.title" class="mt-1.5 text-xs text-red-600">{{ form.errors.title }}</p>
      </div>
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
        <input v-model="form.slug" type="text" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="Автоматически из названия" />
        <p v-if="form.errors.slug" class="mt-1.5 text-xs text-red-600">{{ form.errors.slug }}</p>
      </div>
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
      <div v-if="form.auth_method === 'sso'">
        <label class="mb-2 block text-sm font-medium text-gray-700">URL SSO провайдера</label>
        <input v-model="form.sso_provider_url" type="url" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="https://..." />
        <p v-if="form.errors.sso_provider_url" class="mt-1.5 text-xs text-red-600">{{ form.errors.sso_provider_url }}</p>
      </div>
      <div>
        <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-300 px-4 py-3 transition hover:bg-gray-50">
          <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded-md border-2 border-gray-300 bg-white text-rosatom-600 transition focus:ring-rosatom-500/20" />
          <span class="text-sm font-medium text-gray-700">Активно</span>
        </label>
      </div>

      <div class="flex gap-3 border-t border-gray-200 pt-6">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Создать
        </button>
        <Link :href="route('lms.admin.events.index')" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
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
