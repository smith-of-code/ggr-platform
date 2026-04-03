<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.kb.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к базе знаний
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ section ? 'Редактировать раздел' : 'Новый раздел' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основное</h2>
        </template>
        <div class="space-y-6 p-8">
          <RInput
            v-model="form.title"
            label="Название *"
            required
            :error="form.errors.title"
          />
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
            <RCheckbox v-model="form.in_menu" label="В меню" />
            <div class="w-24">
              <label class="mb-1 block text-xs text-gray-500">Позиция</label>
              <RInput v-model.number="form.position" type="number" size="sm" />
            </div>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Группы</label>
            <div class="space-y-2">
              <div v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-50" :class="form.group_ids.includes(g.id) ? 'bg-rosatom-50' : ''">
                <RCheckbox
                  :model-value="form.group_ids.includes(g.id)"
                  @update:model-value="(v) => { if (v) form.group_ids.push(g.id); else form.group_ids = form.group_ids.filter(id => id !== g.id) }"
                  :label="g.title"
                />
              </div>
            </div>
          </div>
        </div>
      </RCard>

      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Элементы</h2>
        </template>
        <div class="space-y-3 p-8">
          <div v-for="(item, idx) in form.items" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex justify-between gap-2">
              <RInput v-model="item.title" placeholder="Название" required size="sm" class="flex-1" />
              <select v-model="item.type" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                <option value="text">Текст</option>
                <option value="link">Ссылка</option>
                <option value="video">Видео</option>
                <option value="file">Файл</option>
              </select>
              <RButton type="button" variant="ghost" size="sm" iconOnly @click="form.items.splice(idx, 1)" class="text-red-600 hover:bg-red-50">
                <template #icon>×</template>
              </RButton>
            </div>

            <!-- Текст -->
            <textarea v-if="item.type === 'text'" v-model="item.content" rows="2" placeholder="Контент" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />

            <!-- Ссылка -->
            <RInput v-else-if="item.type === 'link'" v-model="item.url" type="url" placeholder="URL" size="sm" class="mt-2" />

            <!-- Видео / Файл -->
            <div v-else class="mt-3 space-y-3">
              <div class="flex gap-2">
                <button
                  type="button"
                  :class="['rounded-lg px-3 py-1.5 text-xs font-medium transition', item._mode === 'url' ? 'bg-rosatom-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"
                  @click="item._mode = 'url'"
                >
                  По ссылке
                </button>
                <button
                  type="button"
                  :class="['rounded-lg px-3 py-1.5 text-xs font-medium transition', item._mode === 'upload' ? 'bg-rosatom-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300']"
                  @click="item._mode = 'upload'"
                >
                  Загрузить
                </button>
              </div>

              <RInput
                v-if="item._mode === 'url'"
                v-model="item.url"
                type="url"
                :placeholder="item.type === 'video' ? 'Ссылка на видео (YouTube, Rutube, прямая ссылка)' : 'Прямая ссылка на файл'"
                size="sm"
              />

              <div v-else>
                <div v-if="item.file_path && !item._uploading" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                  <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                  <span class="flex-1 truncate text-sm text-gray-700">{{ item._fileName || item.file_path }}</span>
                  <button type="button" class="text-xs font-medium text-rosatom-600 hover:underline" @click="openKbFilePicker(idx)">Заменить</button>
                  <button type="button" class="text-gray-400 hover:text-red-500" @click="item.file_path = ''; item._fileName = ''">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>

                <button v-else type="button" class="group flex w-full cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-3 transition hover:border-rosatom-400 hover:bg-rosatom-50/30" @click="openKbFilePicker(idx)">
                  <svg class="h-5 w-5 text-gray-400 group-hover:text-rosatom-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                  <span class="text-sm text-gray-500 group-hover:text-gray-700">Выберите файл</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        <template #footer>
          <RButton type="button" variant="outline" block class="mt-3" @click="addItem">
            + Добавить элемент
          </RButton>
        </template>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" :disabled="form.processing" variant="primary">
          Сохранить
        </RButton>
        <Link :href="route('lms.admin.kb.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>

    <MediaPickerModal
      :show="kbFilePicker.show"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.file')"
      :accept="form.items[kbFilePicker.idx]?.type === 'video' ? 'video/*' : '*'"
      :file-type="form.items[kbFilePicker.idx]?.type === 'video' ? 'video' : 'all'"
      upload-field="file"
      @close="kbFilePicker.show = false"
      @select="onKbFilePickerSelect"
    />
  </LmsAdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const props = defineProps({ event: Object, section: Object, parentSections: Array, groups: Array })
const kbFilePicker = ref({ show: false, idx: -1 })

function makeItem(overrides = {}) {
  return {
    title: '', type: 'text', content: '', url: '', file_path: '', position: 0,
    _mode: 'url', _fileName: '', _uploading: false,
    ...overrides,
  }
}

const buildItems = () => {
  if (props.section?.items?.length) {
    return props.section.items.map(i => makeItem({
      title: i.title,
      type: i.type === 'url' ? 'link' : (i.type ?? 'text'),
      content: i.content ?? '',
      url: i.url ?? '',
      file_path: i.file_path ?? '',
      position: i.position ?? 0,
      _mode: i.file_path ? 'upload' : 'url',
      _fileName: '',
    }))
  }
  return [makeItem()]
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

function addItem() {
  form.items.push(makeItem({ position: form.items.length }))
}

function openKbFilePicker(idx) {
  kbFilePicker.value = { show: true, idx }
}

function onKbFilePickerSelect(url, name) {
  const { idx } = kbFilePicker.value
  if (idx >= 0) {
    const item = form.items[idx]
    item.file_path = url
    item._fileName = name || url.split('/').pop()
  }
  kbFilePicker.value = { show: false, idx: -1 }
}

function submit() {
  const items = form.items.filter(i => i.title).map((i, idx) => ({
    title: i.title, type: i.type, content: i.content,
    url: i.url, file_path: i.file_path, position: idx,
  }))
  if (props.section) {
    form.transform(d => ({ ...d, items })).put(route('lms.admin.kb.update', [props.event.slug, props.section.id]))
  } else {
    form.transform(d => ({ ...d, items })).post(route('lms.admin.kb.store', props.event.slug))
  }
}
</script>
