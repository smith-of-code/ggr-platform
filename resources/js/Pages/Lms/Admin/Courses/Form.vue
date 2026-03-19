<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.courses.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к курсам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ course ? 'Редактировать курс' : 'Новый курс' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основная информация</h2>
        </template>
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <RInput
              v-model="form.title"
              label="Название *"
              required
              :error="form.errors.title"
            />
          </div>
          <div>
            <RInput v-model="form.slug" label="Slug" />
          </div>
          <div class="sm:col-span-2">
            <RichTextEditor v-model="form.description" label="Описание" :upload-url="route('lms.admin.upload.image', event.slug)" />
          </div>
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Изображение курса</label>
            <div class="flex items-start gap-4">
              <div
                v-if="imagePreview"
                class="relative h-32 w-48 shrink-0 overflow-hidden rounded-xl border border-gray-200"
              >
                <img :src="imagePreview" class="h-full w-full object-cover" alt="Превью" />
                <button
                  type="button"
                  class="absolute right-1.5 top-1.5 rounded-full bg-white/80 p-1 text-gray-500 shadow transition hover:bg-white hover:text-red-500"
                  @click="removeImage"
                >
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </div>
              <div
                class="relative flex flex-1 flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 px-4 py-6 text-center transition hover:border-rosatom-400"
                :class="{ 'border-rosatom-500 bg-rosatom-50': imageUploading }"
              >
                <template v-if="imageUploading">
                  <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                  <p class="text-sm text-rosatom-600">Загрузка...</p>
                </template>
                <template v-else>
                  <ArrowUpTrayIcon class="mx-auto mb-1 h-6 w-6 text-gray-400" />
                  <p class="text-sm font-medium text-gray-600">Загрузить изображение</p>
                  <p class="mt-0.5 text-xs text-gray-400">JPG, PNG, WebP до 5 МБ</p>
                </template>
                <input
                  type="file"
                  accept="image/*"
                  class="absolute inset-0 cursor-pointer opacity-0"
                  @change="handleImageUpload"
                />
              </div>
            </div>
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.sequential" label="Последовательное прохождение" />
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.is_active" label="Активен" />
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.requires_approval" label="Требуется одобрение для записи" />
          </div>
        </div>
      </RCard>

      <!-- Модули -->
      <RCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Модули</h2>
            <RButton variant="outline" size="sm" type="button" @click="addModule">
              <template #icon><PlusIcon class="h-4 w-4" /></template>
              Добавить модуль
            </RButton>
          </div>
        </template>

        <div v-if="form.modules.length === 0" class="py-8 text-center text-sm text-gray-400">
          Модулей пока нет. Нажмите «Добавить модуль», чтобы структурировать курс.
        </div>

        <div class="space-y-6">
          <div
            v-for="(mod, mIdx) in form.modules"
            :key="mIdx"
            class="rounded-2xl border border-rosatom-200 bg-rosatom-50/30 p-5"
          >
            <div class="mb-4 flex items-start justify-between gap-3">
              <div class="flex-1 space-y-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-rosatom-500 text-xs font-bold text-white">{{ mIdx + 1 }}</span>
                  <RInput v-model="mod.title" placeholder="Название модуля *" required class="flex-1" />
                </div>
                <RInput v-model="mod.description" placeholder="Описание модуля (необязательно)" />
                <div class="grid gap-3 sm:grid-cols-2">
                  <RInput v-model="mod.available_from" label="Открытие" type="datetime-local" />
                  <RInput v-model="mod.available_to" label="Закрытие" type="datetime-local" />
                </div>
              </div>
              <div class="flex shrink-0 gap-1 pt-1">
                <RButton v-if="mIdx > 0" variant="ghost" size="sm" icon-only type="button" @click="moveModule(mIdx, -1)">
                  <template #icon><ChevronUpIcon class="h-4 w-4" /></template>
                </RButton>
                <RButton v-if="mIdx < form.modules.length - 1" variant="ghost" size="sm" icon-only type="button" @click="moveModule(mIdx, 1)">
                  <template #icon><ChevronDownIcon class="h-4 w-4" /></template>
                </RButton>
                <RButton variant="danger" size="sm" icon-only type="button" @click="removeModule(mIdx)">
                  <template #icon><XMarkIcon class="h-4 w-4" /></template>
                </RButton>
              </div>
            </div>

            <!-- Этапы внутри модуля -->
            <div class="ml-4 border-l-2 border-rosatom-200 pl-4">
              <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Этапы модуля</h4>
              <div class="space-y-3">
                <StageEditor
                  v-for="(stage, sIdx) in mod.stages"
                  :key="sIdx"
                  :stage="stage"
                  :index="sIdx"
                  :total="mod.stages.length"
                  :tests="tests"
                  :assignments="assignments"
                  :videos="videos"
                  :event-slug="event.slug"
                  @move="(delta) => moveModuleStage(mIdx, sIdx, delta)"
                  @remove="removeModuleStage(mIdx, sIdx)"
                />
              </div>
              <RButton variant="ghost" size="sm" type="button" class="mt-3" @click="addModuleStage(mIdx)">
                <template #icon><PlusIcon class="h-4 w-4" /></template>
                Добавить этап
              </RButton>
            </div>
          </div>
        </div>
      </RCard>

      <!-- Свободные этапы (без модуля) -->
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Этапы без модуля</h2>
        </template>
        <div class="space-y-4">
          <StageEditor
            v-for="(stage, idx) in form.stages"
            :key="idx"
            :stage="stage"
            :index="idx"
            :total="form.stages.length"
            :tests="tests"
            :assignments="assignments"
            :videos="videos"
            :event-slug="event.slug"
            @move="(delta) => moveStage(idx, delta)"
            @remove="form.stages.splice(idx, 1)"
          />
        </div>
        <RButton variant="outline" block type="button" class="mt-4" @click="addStage">
          <template #icon><PlusIcon class="h-4 w-4" /></template>
          Добавить этап
        </RButton>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
          Сохранить
        </RButton>
        <Link :href="route('lms.admin.courses.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import StageEditor from './StageEditor.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import { PlusIcon, XMarkIcon, ChevronUpIcon, ChevronDownIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline'
import axios from 'axios'

const props = defineProps({ event: Object, course: Object, tests: Array, assignments: Array, videos: Array })

const tests = props.tests ?? []
const assignments = props.assignments ?? []
const videos = props.videos ?? []

function emptyStage() {
  return { title: '', type: 'content', content: '', position: 0 }
}

function emptyModule() {
  return { title: '', description: '', available_from: '', available_to: '', stages: [emptyStage()] }
}

function buildModules() {
  if (props.course?.modules?.length) {
    return props.course.modules.map(m => ({
      title: m.title ?? '',
      description: m.description ?? '',
      available_from: m.available_from ? m.available_from.slice(0, 16) : '',
      available_to: m.available_to ? m.available_to.slice(0, 16) : '',
      stages: (m.stages || []).map(s => ({
        title: s.title,
        type: s.type || 'content',
        content: s.content ?? '',
        position: s.position ?? 0,
      })),
    }))
  }
  return []
}

function buildOrphanStages() {
  if (props.course?.stages?.length) {
    return props.course.stages
      .filter(s => !s.lms_course_module_id)
      .map(s => ({
        title: s.title,
        type: s.type || 'content',
        content: s.content ?? '',
        position: s.position ?? 0,
      }))
  }
  return [emptyStage()]
}

const form = useForm({
  title: props.course?.title ?? '',
  slug: props.course?.slug ?? '',
  description: props.course?.description ?? '',
  image: props.course?.image ?? '',
  sequential: props.course?.sequential ?? true,
  is_active: props.course?.is_active ?? true,
  requires_approval: props.course?.requires_approval ?? false,
  modules: buildModules(),
  stages: buildOrphanStages(),
})

const imageUploading = ref(false)
const imagePreview = computed(() => form.image || null)

async function handleImageUpload(e) {
  const file = e.target.files?.[0]
  if (!file) return
  imageUploading.value = true
  try {
    const fd = new FormData()
    fd.append('image', file)
    const { data } = await axios.post(route('lms.admin.upload.image', props.event.slug), fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    if (data.url) form.image = data.url
  } catch (err) {
    alert(err.response?.data?.message || 'Ошибка загрузки')
  } finally {
    imageUploading.value = false
    e.target.value = ''
  }
}

function removeImage() {
  form.image = ''
}

function addModule() {
  form.modules.push(emptyModule())
}
function removeModule(idx) {
  form.modules.splice(idx, 1)
}
function moveModule(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= form.modules.length) return
  const arr = [...form.modules]
  ;[arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]]
  form.modules = arr
}

function addModuleStage(mIdx) {
  form.modules[mIdx].stages.push(emptyStage())
}
function removeModuleStage(mIdx, sIdx) {
  form.modules[mIdx].stages.splice(sIdx, 1)
}
function moveModuleStage(mIdx, sIdx, delta) {
  const stages = form.modules[mIdx].stages
  const newIdx = sIdx + delta
  if (newIdx < 0 || newIdx >= stages.length) return
  ;[stages[sIdx], stages[newIdx]] = [stages[newIdx], stages[sIdx]]
}

function addStage() {
  form.stages.push(emptyStage())
}
function moveStage(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= form.stages.length) return
  const arr = [...form.stages]
  ;[arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]]
  form.stages = arr
}

function submit() {
  const modules = form.modules.map((m, mi) => ({
    ...m,
    position: mi,
    stages: m.stages.map((s, si) => ({ ...s, position: si })),
  }))
  const stages = form.stages.map((s, i) => ({ ...s, position: i }))

  if (props.course) {
    form.transform(data => ({ ...data, modules, stages })).put(route('lms.admin.courses.update', [props.event.slug, props.course.id]))
  } else {
    form.transform(data => ({ ...data, modules, stages })).post(route('lms.admin.courses.store', props.event.slug))
  }
}
</script>
