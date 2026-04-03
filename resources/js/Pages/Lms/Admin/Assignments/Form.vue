<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.assignments.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к заданиям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ assignment ? 'Редактировать задание' : 'Новое задание' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основная информация</h2>
        </template>
        <div class="max-w-2xl space-y-6 p-8">
          <RInput
            v-model="form.title"
            label="Название *"
            required
            :error="form.errors.title"
          />
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>

          <!-- Template file upload -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Шаблон задания (файл)</label>
            <div v-if="form.template_file && !templateUploading" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
              <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
              <a :href="form.template_file" target="_blank" class="flex-1 truncate text-sm text-rosatom-600 hover:underline">
                {{ form.template_file_name || 'Шаблон' }}
              </a>
              <button type="button" class="text-gray-400 hover:text-red-500" @click="removeTemplate">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <button v-else type="button" class="group flex w-full cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-3 transition hover:border-rosatom-400 hover:bg-rosatom-50/30" @click="openTplPicker('main')">
              <svg class="h-5 w-5 text-gray-400 group-hover:text-rosatom-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
              <span class="text-sm text-gray-500 group-hover:text-gray-700">Выберите файл шаблона</span>
            </button>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Режим выполнения</label>
            <select v-model="form.completion_mode" class="w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
              <option value="on_review">По рецензии</option>
              <option value="on_submit">При отправке</option>
            </select>
          </div>
          <RInput
            v-model="form.deadline"
            label="Дедлайн"
            type="datetime-local"
          />
          <RCheckbox v-model="form.is_active" label="Активно" />
        </div>
      </RCard>

      <!-- Sub-tasks -->
      <RCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Подзадания</h2>
            <RButton variant="outline" size="sm" type="button" @click="addTask">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              </template>
              Добавить подзадание
            </RButton>
          </div>
        </template>

        <div v-if="form.tasks.length === 0" class="px-8 py-12 text-center text-sm text-gray-400">
          Подзаданий нет. Участник увидит стандартную форму отправки (текст + ссылка + файл).
        </div>

        <div class="space-y-4 p-8">
          <div v-for="(task, idx) in form.tasks" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1 space-y-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rosatom-500 text-xs font-bold text-white">{{ idx + 1 }}</span>
                  <RInput v-model="task.title" placeholder="Название подзадания *" required class="flex-1" />
                </div>
                <textarea
                  v-model="task.description"
                  rows="2"
                  placeholder="Описание (необязательно)"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400"
                />
                <div class="flex flex-wrap items-end gap-4">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Формат ответа</label>
                    <select v-model="task.response_type" class="cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                      <option value="text">Текст</option>
                      <option value="link">Ссылка</option>
                      <option value="file">Файл</option>
                    </select>
                  </div>
                  <div class="flex-1">
                    <label class="mb-1 block text-xs font-medium text-gray-500">Шаблон для подзадания</label>
                    <div v-if="task.template_file && !task._uploading" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                      <svg class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                      <a :href="task.template_file" target="_blank" class="flex-1 truncate text-xs text-rosatom-600 hover:underline">
                        {{ task.template_file_name || 'Шаблон' }}
                      </a>
                      <button type="button" class="text-gray-400 hover:text-red-500" @click="task.template_file = ''; task.template_file_name = ''">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                    <button v-else type="button" class="group flex w-full cursor-pointer items-center gap-1.5 rounded-lg border-2 border-dashed border-gray-300 bg-white px-3 py-2 text-xs transition hover:border-rosatom-400 hover:bg-rosatom-50/30" @click="openTplPicker('task', idx)">
                      <svg class="h-4 w-4 text-gray-400 group-hover:text-rosatom-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                      <span class="text-gray-500">Загрузить шаблон</span>
                    </button>
                  </div>
                </div>
              </div>
              <div class="flex shrink-0 flex-col gap-1 pt-1">
                <RButton v-if="idx > 0" variant="ghost" size="sm" icon-only type="button" @click="moveTask(idx, -1)">
                  <template #icon><ChevronUpIcon class="h-4 w-4" /></template>
                </RButton>
                <RButton v-if="idx < form.tasks.length - 1" variant="ghost" size="sm" icon-only type="button" @click="moveTask(idx, 1)">
                  <template #icon><ChevronDownIcon class="h-4 w-4" /></template>
                </RButton>
                <RButton variant="danger" size="sm" icon-only type="button" @click="form.tasks.splice(idx, 1)">
                  <template #icon><XMarkIcon class="h-4 w-4" /></template>
                </RButton>
              </div>
            </div>
          </div>
        </div>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" :disabled="form.processing" :loading="form.processing" variant="primary">
          Сохранить
        </RButton>
        <Link :href="route('lms.admin.assignments.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>

    <MediaPickerModal
      :show="tplPicker.show"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.file')"
      accept="*"
      file-type="all"
      upload-field="file"
      @close="tplPicker.show = false"
      @select="onTplPickerSelect"
    />
  </LmsAdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'
import { ChevronUpIcon, ChevronDownIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({ event: Object, assignment: Object })

const tplPicker = ref({ show: false, target: 'main', idx: -1 })

function emptyTask() {
  return { title: '', description: '', response_type: 'file', template_file: '', template_file_name: '', position: 0, _uploading: false }
}

function buildTasks() {
  if (props.assignment?.tasks?.length) {
    return props.assignment.tasks.map(t => ({
      id: t.id,
      title: t.title ?? '',
      description: t.description ?? '',
      response_type: t.response_type ?? 'file',
      template_file: t.template_file ?? '',
      template_file_name: t.template_file_name ?? '',
      position: t.position ?? 0,
      _uploading: false,
    }))
  }
  return []
}

const form = useForm({
  title: props.assignment?.title ?? '',
  description: props.assignment?.description ?? '',
  template_file: props.assignment?.template_file ?? '',
  template_file_name: props.assignment?.template_file_name ?? '',
  completion_mode: props.assignment?.completion_mode ?? 'on_review',
  deadline: props.assignment?.deadline ? props.assignment.deadline.slice(0, 16) : '',
  is_active: props.assignment?.is_active ?? true,
  tasks: buildTasks(),
})

function addTask() {
  form.tasks.push(emptyTask())
}

function moveTask(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= form.tasks.length) return
  const arr = [...form.tasks]
  ;[arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]]
  form.tasks = arr
}

function removeTemplate() {
  form.template_file = ''
  form.template_file_name = ''
}

function openTplPicker(target, idx = -1) {
  tplPicker.value = { show: true, target, idx }
}

function onTplPickerSelect(url, name) {
  const resolvedName = name || url.split('/').pop()
  if (tplPicker.value.target === 'main') {
    form.template_file = url
    form.template_file_name = resolvedName
  } else if (tplPicker.value.idx >= 0) {
    const task = form.tasks[tplPicker.value.idx]
    task.template_file = url
    task.template_file_name = resolvedName
  }
  tplPicker.value = { show: false, target: 'main', idx: -1 }
}

function submit() {
  const tasks = form.tasks
    .filter(t => t.title?.trim())
    .map((t, i) => ({
      id: t.id || undefined,
      title: t.title,
      description: t.description,
      response_type: t.response_type,
      template_file: t.template_file,
      template_file_name: t.template_file_name,
      position: i,
    }))

  if (props.assignment) {
    form.transform(data => ({ ...data, tasks })).put(route('lms.admin.assignments.update', [props.event.slug, props.assignment.id]))
  } else {
    form.transform(data => ({ ...data, tasks })).post(route('lms.admin.assignments.store', props.event.slug))
  }
}
</script>
