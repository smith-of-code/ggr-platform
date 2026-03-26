<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ event ? 'Редактировать событие' : 'Новое событие' }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ event ? event.title : 'Добавьте событие в хронологию' }}</p>
      </div>
      <Link :href="route('admin.timeline.index')" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Назад</Link>
    </div>

    <form @submit.prevent="submit">
      <div class="mx-auto max-w-2xl">
        <RCard elevation="raised">
          <div class="space-y-5 p-6">
            <div>
              <RInput v-model="form.title" label="Заголовок *" :error="form.errors.title" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-700">Описание</label>
              <textarea
                v-model="form.description"
                rows="4"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
                placeholder="Краткое описание события..."
              />
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <RInput v-model="form.event_date" label="Дата события *" type="date" :error="form.errors.event_date" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Тип *</label>
                <select
                  v-model="form.type"
                  class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
                >
                  <option value="news">Новость</option>
                  <option value="event">Событие</option>
                  <option value="milestone">Веха</option>
                </select>
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
              </div>
            </div>
            <div>
              <RInput v-model="form.link" label="Ссылка (необязательно)" type="url" placeholder="https://..." :error="form.errors.link" />
            </div>
            <div class="flex items-center gap-3">
              <RCheckbox v-model:checked="form.is_active" />
              <span class="text-sm font-medium text-gray-700">Отображать на сайте</span>
            </div>

            <!-- Preview -->
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Предпросмотр</p>
              <div class="flex items-start gap-3">
                <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-[#003274]" />
                <div>
                  <p class="text-sm font-semibold text-[#003274]">{{ formatDate(form.event_date) || 'Дата' }}</p>
                  <p class="mt-1 text-base font-bold text-gray-900">{{ form.title || 'Заголовок события' }}</p>
                  <p v-if="form.description" class="mt-1 text-sm text-gray-600">{{ form.description }}</p>
                  <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="typeClass(form.type)">
                      {{ typeLabel(form.type) }}
                    </span>
                    <span v-if="form.link" class="text-xs text-[#003274]">Ссылка прикреплена</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </RCard>

        <button
          type="submit"
          :disabled="form.processing"
          class="mt-6 w-full rounded-xl bg-[#003274] px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-[#025ea1] disabled:opacity-60"
        >
          {{ form.processing ? 'Сохранение...' : (event ? 'Сохранить' : 'Создать событие') }}
        </button>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ event: Object })

const form = useForm({
  title: props.event?.title ?? '',
  description: props.event?.description ?? '',
  event_date: props.event?.event_date?.substring(0, 10) ?? '',
  link: props.event?.link ?? '',
  type: props.event?.type ?? 'event',
  is_active: props.event?.is_active ?? true,
})

function typeLabel(t) {
  return { news: 'Новость', event: 'Событие', milestone: 'Веха' }[t] || t
}

function typeClass(t) {
  return {
    news: 'bg-blue-100 text-blue-800',
    event: 'bg-emerald-100 text-emerald-800',
    milestone: 'bg-amber-100 text-amber-900',
  }[t] || 'bg-gray-100 text-gray-600'
}

function formatDate(d) {
  if (!d) return ''
  const date = new Date(d)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function submit() {
  if (props.event) {
    form.put(route('admin.timeline.update', props.event.id))
  } else {
    form.post(route('admin.timeline.store'))
  }
}
</script>
