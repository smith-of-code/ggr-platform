<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.kb.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к базе знаний
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ section ? 'Редактировать раздел' : 'Новый раздел' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">Основное</h2>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
          <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Родительский раздел</label>
          <select v-model="form.parent_id" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
            <option :value="null">— Нет —</option>
            <option v-for="p in parentSections" :key="p.id" :value="p.id">{{ p.title }}</option>
          </select>
        </div>
        <div class="flex gap-4">
          <label class="flex cursor-pointer items-center gap-2">
            <input v-model="form.in_menu" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
            <span class="text-sm text-gray-700">В меню</span>
          </label>
          <div>
            <label class="mb-1 block text-xs text-gray-500">Позиция</label>
            <input v-model.number="form.position" type="number" class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
          </div>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Группы</label>
          <div class="space-y-2">
            <label v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-50" :class="form.group_ids.includes(g.id) ? 'bg-rosatom-50' : ''">
              <input v-model="form.group_ids" type="checkbox" :value="g.id" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
              <span class="text-sm text-gray-900">{{ g.title }}</span>
            </label>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="mb-4 text-base font-bold text-gray-900">Элементы</h2>
        <div class="space-y-3">
          <div v-for="(item, idx) in form.items" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex justify-between gap-2">
              <input v-model="item.title" type="text" required placeholder="Название" class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
              <select v-model="item.type" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                <option value="text">Текст</option>
                <option value="url">Ссылка</option>
                <option value="file">Файл</option>
              </select>
              <button type="button" @click="form.items.splice(idx, 1)" class="rounded p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">×</button>
            </div>
            <input v-if="item.type === 'url'" v-model="item.url" type="url" placeholder="URL" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
            <textarea v-else-if="item.type === 'text'" v-model="item.content" rows="2" placeholder="Контент" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
            <input v-else v-model="item.file_path" type="text" placeholder="Путь к файлу" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
          </div>
        </div>
        <button type="button" @click="form.items.push({ title: '', type: 'text', content: '', url: '', file_path: '', position: form.items.length })" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 py-3 text-sm text-gray-500 hover:border-rosatom-500 hover:text-rosatom-600">
          + Добавить элемент
        </button>
      </div>

      <div class="flex gap-3">
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white hover:bg-rosatom-700 disabled:opacity-50">Сохранить</button>
        <Link :href="route('lms.admin.kb.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, section: Object, parentSections: Array, groups: Array })

const buildItems = () => {
  if (props.section?.items?.length) {
    return props.section.items.map(i => ({
      title: i.title,
      type: i.type ?? 'text',
      content: i.content ?? '',
      url: i.url ?? '',
      file_path: i.file_path ?? '',
      position: i.position ?? 0,
    }))
  }
  return [{ title: '', type: 'text', content: '', url: '', file_path: '', position: 0 }]
}

const form = useForm({
  title: props.section?.title ?? '',
  description: props.section?.description ?? '',
  parent_id: props.section?.parent_id ?? null,
  in_menu: props.section?.in_menu ?? false,
  position: props.section?.position ?? 0,
  group_ids: props.section?.groups?.map(g => g.id) ?? [],
  items: buildItems(),
})

function submit() {
  const items = form.items.filter(i => i.title).map((i, idx) => ({ ...i, position: idx }))
  if (props.section) {
    form.transform(d => ({ ...d, items })).put(route('lms.admin.kb.update', [props.event.id, props.section.id]))
  } else {
    form.transform(d => ({ ...d, items })).post(route('lms.admin.kb.store', props.event.id))
  }
}
</script>
