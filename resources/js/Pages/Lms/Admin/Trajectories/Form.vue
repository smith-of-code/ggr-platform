<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.trajectories.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к траекториям
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ trajectory ? 'Редактировать траекторию' : 'Новая траектория' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Основное</h2>
        </template>
        <div class="space-y-6 p-8">
          <RInput v-model="form.title" label="Название *" required :error="form.errors.title" />
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Описание</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <RCheckbox v-model="form.is_active" label="Активна" />
        </div>
      </RCard>

      <!-- Blocks template -->
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Блоки траектории</h2>
        </template>
        <div class="space-y-3 p-8">
          <p class="mb-4 text-sm text-gray-500">
            Статические блоки — фиксированные этапы (отбор, очный старт и т.д.).
            Блоки «Задание» — ссылка на задание из LMS.
            Программы и гранты участника подтягиваются автоматически по датам.
            По умолчанию каждый блок виден всем; при снятии «Для всех программ» блок показывается только записанным на выбранные программы.
          </p>
          <div v-for="(block, idx) in form.blocks" :key="idx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-start gap-3">
              <div class="flex-1 space-y-3">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Тип</label>
                    <select v-model="block.type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                      <option value="static">Статический</option>
                      <option value="task">Задание</option>
                    </select>
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Заголовок</label>
                    <input v-model="block.title" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" :placeholder="block.type === 'task' ? 'Название задания' : 'Название блока'" />
                  </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Дата начала</label>
                    <input v-model="block.date_start" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Дата окончания</label>
                    <input v-model="block.date_end" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" />
                  </div>
                </div>

                <div v-if="block.type === 'task'">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Задание</label>
                  <select v-model="block.lms_assignment_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                    <option :value="null">— Выберите задание —</option>
                    <option v-for="a in assignments" :key="a.id" :value="a.id">{{ a.title }}</option>
                  </select>
                </div>

                <div v-if="block.type === 'static'">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Описание</label>
                  <textarea v-model="block.description" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900" placeholder="Описание блока" />
                </div>

                <div v-if="materialSections?.length">
                  <label class="mb-1 block text-xs font-medium text-gray-500">Материал</label>
                  <select v-model="block.material_url" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
                    <option value="">— Не выбран —</option>
                    <option v-for="s in materialSections" :key="s.id" :value="materialUrl(s.id)">{{ s.title }}</option>
                  </select>
                </div>

                <div v-if="courses?.length" class="rounded-lg border border-gray-200 bg-white p-3">
                  <RCheckbox
                    :model-value="isBlockForAllPrograms(block)"
                    label="Для всех программ (по умолчанию)"
                    @update:model-value="(v) => setBlockForAllPrograms(block, v)"
                  />
                  <div v-if="!isBlockForAllPrograms(block)" class="mt-3 space-y-2 border-l-2 border-rosatom-200 pl-3">
                    <p class="text-xs text-gray-500">Блок виден только участникам, записанным на отмеченные программы:</p>
                    <div class="flex flex-col gap-2">
                      <label v-for="c in courses" :key="c.id" class="flex cursor-pointer items-center gap-2 text-sm text-gray-800">
                        <input
                          type="checkbox"
                          class="h-4 w-4 rounded border-gray-300 text-rosatom-600 focus:ring-rosatom-500"
                          :checked="blockCourseSelected(block, c.id)"
                          @change="toggleBlockCourse(block, Number(c.id), $event.target.checked)"
                        />
                        <span>{{ c.title }}</span>
                      </label>
                    </div>
                  </div>
                </div>
                <p v-else class="text-xs text-gray-400">Добавьте к событию хотя бы одну программу, чтобы ограничивать видимость блока по программам.</p>
              </div>
              <div class="flex flex-col gap-1 pt-5">
                <button v-if="idx > 0" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600" @click="moveBlock(idx, -1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                </button>
                <button v-if="idx < form.blocks.length - 1" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600" @click="moveBlock(idx, 1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <button type="button" class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600" @click="form.blocks.splice(idx, 1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="!form.blocks.length" class="py-6 text-center text-sm text-gray-400">
            Блоков пока нет. Добавьте хотя бы один блок.
          </div>
        </div>
        <template #footer>
          <RButton type="button" variant="outline" block @click="addBlock" class="mt-3">
            + Добавить блок
          </RButton>
        </template>
      </RCard>

      <!-- Steps (legacy) -->
      <RCard>
        <template #header>
          <h2 class="text-base font-bold text-gray-900">Этапы (программы)</h2>
        </template>
        <div class="space-y-3 p-8">
          <div v-for="(step, idx) in form.steps" :key="idx" class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
            <select v-model="step.lms_course_id" required class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900">
              <option value="">— Выберите программу —</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
            <RCheckbox v-model="step.is_locked" label="Закрыт" class="shrink-0" />
            <RInput v-model="step.opens_at" type="datetime-local" size="sm" />
            <RButton type="button" variant="ghost" size="sm" iconOnly @click="form.steps.splice(idx, 1)" class="text-red-600 hover:bg-red-50">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
              </template>
            </RButton>
          </div>
        </div>
        <template #footer>
          <RButton type="button" variant="outline" block @click="addStep" class="mt-3">
            + Добавить этап
          </RButton>
        </template>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" :disabled="form.processing" variant="primary">Сохранить</RButton>
        <Link :href="route('lms.admin.trajectories.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, trajectory: Object, courses: Array, assignments: Array, materialSections: Array })

const buildSteps = () => {
  if (props.trajectory?.steps?.length) {
    return props.trajectory.steps.map(s => ({
      lms_course_id: s.lms_course_id ?? s.course?.id,
      is_locked: s.is_locked ?? false,
      opens_at: s.opens_at ? s.opens_at.slice(0, 16) : '',
    }))
  }
  return [{ lms_course_id: '', is_locked: false, opens_at: '' }]
}

const buildBlocks = () => {
  if (props.trajectory?.blocks?.length) {
    return props.trajectory.blocks.map(b => ({
      type: b.type,
      title: b.title ?? '',
      description: b.description ?? '',
      date_label: b.date_label ?? '',
      date_start: b.date_start?.substring(0, 10) ?? '',
      date_end: b.date_end?.substring(0, 10) ?? '',
      lms_assignment_id: b.lms_assignment_id ?? null,
      material_url: b.material_url ?? '',
      visible_course_ids: Array.isArray(b.visible_course_ids) && b.visible_course_ids.length
        ? b.visible_course_ids.map(id => Number(id))
        : [],
    }))
  }
  return []
}

const form = useForm({
  title: props.trajectory?.title ?? '',
  description: props.trajectory?.description ?? '',
  is_active: props.trajectory?.is_active ?? true,
  steps: buildSteps(),
  blocks: buildBlocks(),
})

function addStep() {
  form.steps.push({ lms_course_id: '', is_locked: false, opens_at: '' })
}

function addBlock() {
  form.blocks.push({
    type: 'static',
    title: '',
    description: '',
    date_label: '',
    date_start: '',
    date_end: '',
    lms_assignment_id: null,
    material_url: '',
    visible_course_ids: [],
  })
}

function isBlockForAllPrograms(block) {
  return !(Array.isArray(block.visible_course_ids) && block.visible_course_ids.length > 0)
}

function setBlockForAllPrograms(block, forAll) {
  if (forAll) {
    block.visible_course_ids = []
  } else if (props.courses?.length) {
    block.visible_course_ids = [Number(props.courses[0].id)]
  }
}

function blockCourseSelected(block, courseId) {
  const id = Number(courseId)
  return (block.visible_course_ids || []).map(Number).includes(id)
}

function toggleBlockCourse(block, courseId, checked) {
  const set = new Set((block.visible_course_ids || []).map(Number))
  if (checked) {
    set.add(Number(courseId))
  } else {
    set.delete(Number(courseId))
  }
  block.visible_course_ids = Array.from(set)
}

function materialUrl(sectionId) {
  return route('lms.materials.show', { event: props.event.slug, section: sectionId })
}

function moveBlock(idx, direction) {
  const newIdx = idx + direction
  if (newIdx < 0 || newIdx >= form.blocks.length) return
  const tmp = form.blocks[idx]
  form.blocks[idx] = form.blocks[newIdx]
  form.blocks[newIdx] = tmp
}

function submit() {
  const steps = form.steps.filter(s => s.lms_course_id).map((s, i) => ({ ...s, position: i }))
  const blocks = form.blocks.map((b, i) => ({ ...b, position: i }))
  if (props.trajectory) {
    form.transform(d => ({ ...d, steps, blocks })).put(route('lms.admin.trajectories.update', [props.event.slug, props.trajectory.id]))
  } else {
    form.transform(d => ({ ...d, steps, blocks })).post(route('lms.admin.trajectories.store', props.event.slug))
  }
}
</script>
