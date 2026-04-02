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
            <RichTextEditor v-model="form.description" label="Описание" :upload-url="route('lms.admin.upload.image', event.slug)" :media-picker-url="route('admin.media.index')" collection="lms_courses" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
          </div>
          <div class="sm:col-span-2">
            <ImageUploadCrop
              v-model="form.image"
              label="Изображение курса"
              :upload-url="route('lms.admin.upload.image', event.slug)"
              :media-picker-url="route('admin.media.index')" collection="lms_courses" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
              :skip-crop="true"
              preview-class="h-32 w-full object-cover"
            />
          </div>
          <div>
            <RInput v-model="form.starts_at" label="Дата начала" type="date" :error="form.errors.starts_at" />
          </div>
          <div>
            <RInput v-model="form.ends_at" label="Дата окончания" type="date" :error="form.errors.ends_at" />
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
          <div class="flex items-end">
            <RCheckbox v-model="form.is_mandatory" label="Обязательный курс (автозапись при регистрации)" />
          </div>
          <div class="flex items-end">
            <RCheckbox v-model="form.unlocks_gamification" label="Открывает геймификацию (баллы, рейтинг)" />
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
          <template v-for="(mod, mIdx) in form.modules" :key="mIdx">
            <div
              :ref="el => { if (el) moduleRefs[mIdx] = el }"
              class="rounded-2xl border border-rosatom-200 bg-rosatom-50/30 p-5"
            >
              <div class="mb-4 flex items-start justify-between gap-3">
                <div class="flex-1 space-y-3">
                  <button
                    v-if="!mod.title"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-rosatom-600 transition hover:bg-rosatom-50 hover:text-rosatom-700"
                    @click="openModuleSearch(mIdx)"
                  >
                    <MagnifyingGlassIcon class="h-3.5 w-3.5" />
                    Найти модуль из другого курса
                  </button>
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
                    @search="openStageSearch(mIdx, sIdx)"
                    @search-block="openBlockSearch(mIdx, sIdx)"
                  />
                </div>
                <RButton variant="ghost" size="sm" type="button" class="mt-3" @click="addModuleStage(mIdx)">
                  <template #icon><PlusIcon class="h-4 w-4" /></template>
                  Добавить этап
                </RButton>
              </div>
            </div>

            <div class="flex justify-center">
              <RButton variant="outline" size="sm" type="button" @click="insertModuleAfter(mIdx)">
                <template #icon><PlusIcon class="h-4 w-4" /></template>
                Добавить модуль
              </RButton>
            </div>
          </template>
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
            @search="openStageSearch(null, idx)"
            @search-block="openBlockSearch(null, idx)"
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

    <SearchRefModal
      :show="showModuleSearch"
      type="module"
      :event-slug="event.slug"
      @close="showModuleSearch = false"
      @select="handleModuleSelect"
    />

    <SearchRefModal
      :show="showStageSearch"
      type="stage"
      :event-slug="event.slug"
      @close="showStageSearch = false"
      @select="handleStageSelect"
    />

    <SearchRefModal
      :show="showBlockSearch"
      type="block"
      :event-slug="event.slug"
      @close="showBlockSearch = false"
      @select="handleBlockSelect"
    />
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import StageEditor from './StageEditor.vue'
import SearchRefModal from './SearchRefModal.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import { PlusIcon, XMarkIcon, ChevronUpIcon, ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import axios from 'axios'

const props = defineProps({ event: Object, course: Object, tests: Array, assignments: Array, videos: Array })
const mediaEntityType = 'App\\Models\\Lms\\LmsCourse'
const mediaEntityId = props.course?.id || null

const tests = props.tests ?? []
const assignments = props.assignments ?? []
const videos = props.videos ?? []

const showModuleSearch = ref(false)
const showStageSearch = ref(false)
const showBlockSearch = ref(false)
const moduleSearchIdx = ref(null)
const stageSearchCtx = ref({ moduleIdx: null, stageIdx: 0 })
const blockSearchCtx = ref({ moduleIdx: null, stageIdx: 0 })

function openModuleSearch(mIdx) {
  moduleSearchIdx.value = mIdx
  showModuleSearch.value = true
}

function handleModuleSelect(mod) {
  const idx = moduleSearchIdx.value
  if (idx === null) return
  const target = form.modules[idx]
  target.title = mod.title ?? ''
  target.description = mod.description ?? ''
  target.available_from = mod.available_from ? mod.available_from.slice(0, 16) : ''
  target.available_to = mod.available_to ? mod.available_to.slice(0, 16) : ''
  target.source_module_id = mod.id
  target.stages = (mod.stages || []).map(s => ({
    title: s.title,
    type: s.type || 'content',
    content: s.content ?? '',
    position: s.position ?? 0,
    source_stage_id: s.id,
    blocks: stageBlocksFromServer(s),
  }))
}

function openStageSearch(moduleIdx, stageIdx) {
  stageSearchCtx.value = { moduleIdx, stageIdx }
  showStageSearch.value = true
}

function handleStageSelect(stage) {
  const { moduleIdx, stageIdx } = stageSearchCtx.value
  const target = moduleIdx !== null
    ? form.modules[moduleIdx].stages[stageIdx]
    : form.stages[stageIdx]
  if (!target) return
  target.title = stage.title
  target.type = stage.type || 'content'
  target.content = stage.content ?? ''
  target.source_stage_id = stage.id
  target.blocks = stageBlocksFromServer(stage)
}

function openBlockSearch(moduleIdx, stageIdx) {
  blockSearchCtx.value = { moduleIdx, stageIdx }
  showBlockSearch.value = true
}

function handleBlockSelect(block) {
  const { moduleIdx, stageIdx } = blockSearchCtx.value
  const target = moduleIdx !== null
    ? form.modules[moduleIdx]?.stages?.[stageIdx]
    : form.stages[stageIdx]
  if (!target) return

  if (!target.blocks) target.blocks = []

  target.blocks.push({
    type: block.type || 'content',
    content: block.content ?? '',
    position: target.blocks.length,
    scheduled_at: normalizeScheduledAt(block.scheduled_at),
  })
}

function normalizeScheduledAt(val) {
  if (!val) return ''
  return val.length > 16 ? val.slice(0, 16) : val
}

function emptyBlock() {
  return { type: 'content', content: '', position: 0, scheduled_at: '' }
}

function emptyStage() {
  return { title: '', type: 'content', content: '', position: 0, blocks: [emptyBlock()] }
}

function emptyModule() {
  return { title: '', description: '', available_from: '', available_to: '', stages: [emptyStage()] }
}

function stageBlocksFromServer(s) {
  if (s.blocks?.length) {
    return s.blocks.map(b => ({
      type: b.type || 'content',
      content: b.content ?? '',
      position: b.position ?? 0,
      scheduled_at: normalizeScheduledAt(b.scheduled_at),
    }))
  }
  return [{ type: s.type || 'content', content: s.content ?? '', position: 0, scheduled_at: '' }]
}

function buildModules() {
  if (props.course?.modules?.length) {
    return props.course.modules.map(m => ({
      title: m.title ?? '',
      description: m.description ?? '',
      available_from: m.available_from ? m.available_from.slice(0, 16) : '',
      available_to: m.available_to ? m.available_to.slice(0, 16) : '',
      source_module_id: m.source_module_id ?? null,
      stages: (m.stages || []).map(s => ({
        title: s.title,
        type: s.type || 'content',
        content: s.content ?? '',
        position: s.position ?? 0,
        source_stage_id: s.source_stage_id ?? null,
        blocks: stageBlocksFromServer(s),
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
        source_stage_id: s.source_stage_id ?? null,
        blocks: stageBlocksFromServer(s),
      }))
  }
  return [emptyStage()]
}

const form = useForm({
  title: props.course?.title ?? '',
  slug: props.course?.slug ?? '',
  description: props.course?.description ?? '',
  image: props.course?.image ?? '',
  starts_at: props.course?.starts_at?.substring(0, 10) ?? '',
  ends_at: props.course?.ends_at?.substring(0, 10) ?? '',
  sequential: props.course?.sequential ?? true,
  is_active: props.course?.is_active ?? true,
  requires_approval: props.course?.requires_approval ?? false,
  is_mandatory: props.course?.is_mandatory ?? false,
  unlocks_gamification: props.course?.unlocks_gamification ?? false,
  modules: buildModules(),
  stages: buildOrphanStages(),
})

const moduleRefs = ref({})

function addModule() {
  form.modules.push(emptyModule())
  nextTick(() => {
    const idx = form.modules.length - 1
    moduleRefs.value[idx]?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  })
}
function insertModuleAfter(mIdx) {
  form.modules.splice(mIdx + 1, 0, emptyModule())
  nextTick(() => {
    moduleRefs.value[mIdx + 1]?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  })
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
  const filterStages = (arr) => arr
    .filter(s => s.title?.trim())
    .map((s, i) => ({
      ...s,
      position: i,
      blocks: (s.blocks || []).map((b, bi) => ({ ...b, position: bi })),
    }))

  const modules = form.modules
    .filter(m => m.title?.trim())
    .map((m, mi) => ({
      ...m,
      position: mi,
      stages: filterStages(m.stages),
    }))
  const stages = filterStages(form.stages)

  if (props.course) {
    form.transform(data => ({ ...data, modules, stages })).put(route('lms.admin.courses.update', [props.event.slug, props.course.id]))
  } else {
    form.transform(data => ({ ...data, modules, stages })).post(route('lms.admin.courses.store', props.event.slug))
  }
}
</script>
