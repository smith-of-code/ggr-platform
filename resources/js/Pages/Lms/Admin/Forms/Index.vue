<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Формы и опросы</h1>
        <p class="mt-1 text-sm text-gray-500">Конструктор анкет, опросов и форм обратной связи</p>
      </div>
      <Link :href="route('lms.admin.forms.create', event.slug)">
        <RButton variant="primary">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          </template>
          Создать форму
        </RButton>
      </Link>
    </div>

    <div v-if="forms?.data?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="form in forms.data"
        :key="form.id"
        class="group rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-rosatom-300 hover:shadow-md"
      >
        <div class="mb-3 flex items-start justify-between">
          <div>
            <h3 class="font-bold text-gray-900">{{ form.title }}</h3>
            <p v-if="form.description" class="mt-1 text-xs text-gray-400 line-clamp-2">{{ form.description }}</p>
          </div>
          <RBadge :variant="form.is_active ? 'success' : 'neutral'" size="sm">
            {{ form.is_active ? 'Активна' : 'Неактивна' }}
          </RBadge>
        </div>

        <div class="mb-4 flex items-center gap-4 text-xs text-gray-400">
          <span class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            {{ form.submissions_count }} ответов
          </span>
          <span v-if="form.is_anonymous" class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
            Анонимная
          </span>
          <span v-if="form.allow_embed" class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
            Embed
          </span>
        </div>

        <div class="flex gap-2">
          <Link :href="route('lms.admin.forms.stats', [event.slug, form.id])" class="flex-1">
            <RButton variant="outline" size="sm" block>Статистика</RButton>
          </Link>
          <Link :href="route('lms.admin.forms.edit', [event.slug, form.id])">
            <RButton variant="ghost" size="sm">Редактировать</RButton>
          </Link>
        </div>
      </div>
    </div>

    <RCard v-else>
      <div class="py-12 text-center text-sm text-gray-400">
        Форм пока нет. Создайте первую форму или опрос.
      </div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

defineProps({ event: Object, forms: Object })
</script>
