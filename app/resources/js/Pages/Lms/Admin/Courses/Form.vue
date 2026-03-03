<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.courses.index', event.id)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к курсам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ course ? 'Редактировать курс' : 'Новый курс' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="space-y-6 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="text-base font-bold text-gray-900">Основная информация</h2>
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Название *</label>
            <input v-model="form.title" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
            <input v-model="form.slug" type="text" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-mono text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div class="sm:col-span-2">
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Изображение (URL)</label>
            <input v-model="form.image" type="url" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="https://..." />
          </div>
          <div class="flex items-end">
            <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-300 px-4 py-3 transition hover:bg-gray-50">
              <input v-model="form.sequential" type="checkbox" class="h-5 w-5 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
              <span class="text-sm font-medium text-gray-700">Последовательное прохождение</span>
            </label>
          </div>
          <div class="flex items-end">
            <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-300 px-4 py-3 transition hover:bg-gray-50">
              <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-gray-300 bg-white text-rosatom-600 focus:ring-rosatom-500/20" />
              <span class="text-sm font-medium text-gray-700">Активен</span>
            </label>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h2 class="mb-4 text-base font-bold text-gray-900">Этапы</h2>
        <div class="space-y-4">
          <div v-for="(stage, idx) in form.stages" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Этап {{ idx + 1 }}</span>
              <div class="flex gap-2">
                <button v-if="idx > 0" type="button" @click="moveStage(idx, -1)" class="rounded p-1.5 text-gray-500 hover:bg-gray-200 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                </button>
                <button v-if="idx < form.stages.length - 1" type="button" @click="moveStage(idx, 1)" class="rounded p-1.5 text-gray-500 hover:bg-gray-200 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <button type="button" @click="form.stages.splice(idx, 1)" class="rounded p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <input v-model="stage.title" type="text" required placeholder="Название этапа" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
              </div>
              <div>
                <select v-model="stage.type" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                  <option value="content">Контент</option>
                  <option value="scorm">SCORM</option>
                  <option value="test">Тест</option>
                  <option value="assignment">Задание</option>
                  <option value="video">Видео</option>
                </select>
              </div>
              <div v-if="stage.type === 'content'">
                <textarea v-model="stage.content" rows="3" placeholder="Текст контента" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
              </div>
              <div v-else-if="stage.type === 'scorm'">
                <input v-model="stage.content" type="text" placeholder="URL или путь к SCORM пакету" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400" />
              </div>
              <div v-else-if="stage.type === 'test'" class="space-y-2">
                <label class="block text-xs text-gray-500">Тест</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                  <option value="">— Выберите тест —</option>
                  <option v-for="t in tests" :key="t.id" :value="String(t.id)">{{ t.title }}</option>
                </select>
              </div>
              <div v-else-if="stage.type === 'assignment'" class="space-y-2">
                <label class="block text-xs text-gray-500">Задание</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                  <option value="">— Выберите задание —</option>
                  <option v-for="a in assignments" :key="a.id" :value="String(a.id)">{{ a.title }}</option>
                </select>
              </div>
              <div v-else-if="stage.type === 'video'" class="space-y-2">
                <label class="block text-xs text-gray-500">Видео</label>
                <select v-model="stage.content" class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                  <option value="">— Выберите видео —</option>
                  <option v-for="v in videos" :key="v.id" :value="String(v.id)">{{ v.title }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <button type="button" @click="addStage" class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 py-3 text-sm font-medium text-gray-500 transition hover:border-rosatom-500 hover:text-rosatom-600">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Добавить этап
        </button>
      </div>

      <div class="flex gap-3">
        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50">
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
          Сохранить
        </button>
        <Link :href="route('lms.admin.courses.index', event.id)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
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
    form.transform(data => ({ ...data, stages })).put(route('lms.admin.courses.update', [props.event.id, props.course.id]))
  } else {
    form.transform(data => ({ ...data, stages })).post(route('lms.admin.courses.store', props.event.id))
  }
}
</script>
