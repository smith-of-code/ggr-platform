<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.materials.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к материалам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ material ? 'Редактировать раздел' : 'Новый раздел' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.title"
          label="Название *"
          required
          :error="form.errors.title"
        />
        <RichTextEditor v-model="form.content" label="Контент" :upload-url="route('lms.admin.upload.image', event.slug)" :media-picker-url="route('admin.media.index')" collection="lms_materials" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
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
        <div class="flex gap-4">
          <RCheckbox v-model="form.in_menu" label="В меню" />
          <div class="w-24">
            <label class="mb-1 block text-xs text-gray-500">Позиция</label>
            <RInput v-model.number="form.position" type="number" size="sm" />
          </div>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Прикреплённые файлы</label>
          <div v-if="form.files.length" class="space-y-3">
            <div
              v-for="(file, idx) in form.files"
              :key="idx"
              class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-3"
            >
              <div class="flex-1 space-y-2">
                <RInput
                  v-model="file.title"
                  placeholder="Название файла"
                  size="sm"
                />
                <div v-if="file.file_path && !file._uploading" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                  <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                  <span class="flex-1 truncate text-sm text-gray-700">{{ file.file_name || file.file_path }}</span>
                  <button type="button" class="cursor-pointer text-xs font-medium text-rosatom-600 hover:underline" @click="openMatFilePicker(idx)">Заменить</button>
                  <button type="button" class="cursor-pointer text-gray-400 hover:text-red-500" @click="file.file_path = ''; file.file_name = ''">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <button v-else type="button" class="group flex w-full cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-3 transition hover:border-rosatom-400 hover:bg-rosatom-50/30" @click="openMatFilePicker(idx)">
                  <svg class="h-5 w-5 text-gray-400 group-hover:text-rosatom-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                  <span class="text-sm text-gray-500 group-hover:text-gray-700">Выберите файл</span>
                </button>
              </div>
              <button type="button" class="mt-1 cursor-pointer text-gray-400 hover:text-red-500" @click="form.files.splice(idx, 1)">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
              </button>
            </div>
          </div>
          <button
            type="button"
            class="mt-3 flex cursor-pointer items-center gap-1.5 text-sm font-medium text-rosatom-600 hover:text-rosatom-800"
            @click="addFile"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Добавить файл
          </button>
        </div>
        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.materials.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>

    <MediaPickerModal
      :show="matFilePicker.show"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.file')"
      accept="*"
      file-type="all"
      upload-field="file"
      collection="lms_materials"
      :entity-type="mediaEntityType"
      :entity-id="mediaEntityId"
      @close="matFilePicker.show = false"
      @select="onMatFilePickerSelect"
    />
  </LmsAdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const props = defineProps({ event: Object, material: Object, groups: Array })
const mediaEntityType = 'App\\Models\\Lms\\LmsMaterialSection'
const mediaEntityId = props.material?.id || null
const matFilePicker = ref({ show: false, idx: -1 })

function buildFiles() {
  if (props.material?.files?.length) {
    return props.material.files.map(f => ({
      id: f.id,
      title: f.title,
      file_path: f.file_path,
      file_name: f.file_name,
      file_size: f.file_size,
      _uploading: false,
    }))
  }
  return []
}

const form = useForm({
  title: props.material?.title ?? '',
  content: props.material?.content ?? '',
  in_menu: props.material?.in_menu ?? false,
  position: props.material?.position ?? 0,
  group_ids: props.material?.groups?.map(g => g.id) ?? [],
  files: buildFiles(),
})

function addFile() {
  form.files.push({ id: null, title: '', file_path: '', file_name: '', file_size: null, _uploading: false })
}

function openMatFilePicker(idx) {
  matFilePicker.value = { show: true, idx }
}

function onMatFilePickerSelect(url, name) {
  const { idx } = matFilePicker.value
  if (idx >= 0) {
    const entry = form.files[idx]
    entry.file_path = url
    entry.file_name = name || url.split('/').pop()
    entry.file_size = null
    if (!entry.title) entry.title = entry.file_name
  }
  matFilePicker.value = { show: false, idx: -1 }
}

function submit() {
  const files = form.files
    .filter(f => f.file_path)
    .map((f, i) => ({ id: f.id, title: f.title, file_path: f.file_path, file_name: f.file_name, file_size: f.file_size, position: i }))

  if (props.material) {
    form.transform(d => ({ ...d, files })).put(route('lms.admin.materials.update', [props.event.slug, props.material.id]))
  } else {
    form.transform(d => ({ ...d, files })).post(route('lms.admin.materials.store', props.event.slug))
  }
}
</script>
