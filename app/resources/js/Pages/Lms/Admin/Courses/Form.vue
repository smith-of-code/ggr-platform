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
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <RInput v-model="form.image" label="Изображение (URL)" type="url" placeholder="https://..." />
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.sequential" label="Последовательное прохождение" />
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.is_active" label="Активен" />
          </div>
        </div>
      </RCard>

      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Этапы</h2>
        </template>
        <div class="space-y-4">
          <div v-for="(stage, idx) in form.stages" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Этап {{ idx + 1 }}</span>
              <div class="flex gap-2">
                <RButton v-if="idx > 0" variant="ghost" size="sm" icon-only type="button" @click="moveStage(idx, -1)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                  </template>
                </RButton>
                <RButton v-if="idx < form.stages.length - 1" variant="ghost" size="sm" icon-only type="button" @click="moveStage(idx, 1)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </template>
                </RButton>
                <RButton variant="danger" size="sm" icon-only type="button" @click="form.stages.splice(idx, 1)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                  </template>
                </RButton>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <RInput v-model="stage.title" placeholder="Название этапа" required />
              </div>
              <div>
                <select v-model="stage.type" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
                  <option value="content">Контент</option>
                  <option value="scorm">SCORM</option>
                  <option value="test">Тест</option>
                  <option value="assignment">Задание</option>
                  <option value="video">Видео</option>
                </select>
              </div>
              <div v-if="stage.type === 'content'">
                <textarea v-model="stage.content" rows="3" placeholder="Текст контента" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
              </div>
              <div v-else-if="stage.type === 'scorm'" class="space-y-3">
                <div
                  class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center transition hover:border-rosatom-400"
                  :class="{ 'border-rosatom-500 bg-rosatom-50': stage.uploading }"
                >
                  <template v-if="stage.uploading">
                    <svg class="mx-auto mb-2 h-8 w-8 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                    <p class="text-sm font-medium text-rosatom-600">Загрузка SCORM-пакета...</p>
                  </template>
                  <template v-else-if="stage.scormFilename">
                    <svg class="mx-auto mb-2 h-8 w-8 text-accent-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <p class="text-sm font-medium text-gray-900">{{ stage.scormFilename }}</p>
                    <p class="mt-1 text-xs text-gray-500">SCORM-пакет загружен</p>
                    <button type="button" class="mt-2 text-xs font-medium text-rosatom-600 hover:underline" @click="clearScorm(idx)">Заменить файл</button>
                  </template>
                  <template v-else>
                    <svg class="mx-auto mb-2 h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    <p class="text-sm font-medium text-gray-700">Загрузить SCORM-пакет (.zip)</p>
                    <p class="mt-1 text-xs text-gray-400">до 100 МБ, ZIP с imsmanifest.xml</p>
                    <input
                      type="file"
                      accept=".zip"
                      class="absolute inset-0 cursor-pointer opacity-0"
                      @change="handleScormUpload($event, idx)"
                    />
                  </template>
                </div>
                <div v-if="stage.scormError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ stage.scormError }}</div>
                <RInput v-if="stage.content" v-model="stage.content" label="SCORM URL (авто)" disabled />
              </div>
              <div v-else-if="stage.type === 'test'" class="space-y-2">
                <label class="block text-xs text-gray-500">Тест</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
                  <option value="">— Выберите тест —</option>
                  <option v-for="t in tests" :key="t.id" :value="String(t.id)">{{ t.title }}</option>
                </select>
              </div>
              <div v-else-if="stage.type === 'assignment'" class="space-y-2">
                <label class="block text-xs text-gray-500">Задание</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
                  <option value="">— Выберите задание —</option>
                  <option v-for="a in assignments" :key="a.id" :value="String(a.id)">{{ a.title }}</option>
                </select>
              </div>
              <div v-else-if="stage.type === 'video'" class="space-y-2">
                <label class="block text-xs text-gray-500">Видео</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
                  <option value="">— Выберите видео —</option>
                  <option v-for="v in videos" :key="v.id" :value="String(v.id)">{{ v.title }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <RButton variant="outline" block type="button" class="mt-4" @click="addStage">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          </template>
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
import { Link, useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, course: Object, tests: Array, assignments: Array, videos: Array })

const tests = props.tests ?? []
const assignments = props.assignments ?? []
const videos = props.videos ?? []

const buildStages = () => {
  if (props.course?.stages?.length) {
    return props.course.stages.map(s => ({
      title: s.title,
      type: s.type || 'content',
      content: s.content ?? '',
      position: s.position ?? 0,
    }))
  }
  return [{ title: '', type: 'content', content: '', position: 0 }]
}

const form = useForm({
  title: props.course?.title ?? '',
  slug: props.course?.slug ?? '',
  description: props.course?.description ?? '',
  image: props.course?.image ?? '',
  sequential: props.course?.sequential ?? true,
  is_active: props.course?.is_active ?? true,
  stages: buildStages(),
})

function addStage() {
  form.stages.push({ title: '', type: 'content', content: '', position: form.stages.length })
}

async function handleScormUpload(event, stageIdx) {
  const file = event.target.files?.[0]
  if (!file) return

  const stage = form.stages[stageIdx]
  stage.uploading = true
  stage.scormError = null

  const fd = new FormData()
  fd.append('scorm_file', file)

  try {
    const { data } = await axios.post(
      route('lms.admin.scorm.upload', props.event.slug),
      fd,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    stage.content = data.url
    stage.scormFilename = data.filename
  } catch (err) {
    stage.scormError = err.response?.data?.error || err.response?.data?.message || 'Ошибка загрузки'
  } finally {
    stage.uploading = false
  }
}

function clearScorm(stageIdx) {
  const stage = form.stages[stageIdx]
  stage.content = ''
  stage.scormFilename = null
  stage.scormError = null
}

function moveStage(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= form.stages.length) return
  const arr = [...form.stages]
  ;[arr[idx], arr[newIdx]] = [arr[newIdx], arr[idx]]
  form.stages = arr
}

function submit() {
  const stages = form.stages.map((s, i) => ({ ...s, position: i }))
  if (props.course) {
    form.transform(data => ({ ...data, stages })).put(route('lms.admin.courses.update', [props.event.slug, props.course.id]))
  } else {
    form.transform(data => ({ ...data, stages })).post(route('lms.admin.courses.store', props.event.slug))
  }
}
</script>
