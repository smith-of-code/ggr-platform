<template>
  <RModal
    :model-value="show"
    @update:model-value="v => { if (!v) $emit('close') }"
    :title="modalTitle"
    size="lg"
  >
    <div class="space-y-4">
      <RInput
        ref="searchInput"
        v-model="query"
        :placeholder="searchPlaceholder"
        autocomplete="off"
      />

      <div v-if="loading" class="flex items-center justify-center py-8">
        <svg class="h-6 w-6 animate-spin text-rosatom-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <div v-else-if="error" class="rounded-lg bg-red-50 px-3 py-4 text-center text-sm text-red-600">
        {{ error }}
      </div>

      <div v-else-if="results.length === 0 && query.length >= 2" class="py-8 text-center text-sm text-gray-400">
        Ничего не найдено
      </div>

      <div v-else-if="query.length < 2 && results.length === 0" class="py-8 text-center text-sm text-gray-400">
        Введите минимум 2 символа для поиска
      </div>

      <ul v-else class="max-h-72 space-y-2 overflow-y-auto">
        <li
          v-for="item in results"
          :key="item.id"
          class="cursor-pointer rounded-xl border border-gray-200 p-3 transition hover:border-rosatom-400 hover:bg-rosatom-50/50"
          @click="confirmItem(item)"
        >
          <template v-if="type === 'block'">
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium" :class="blockTypeBadge(item.type)">{{ blockTypeLabel(item.type) }}</span>
              <span class="font-medium text-gray-900">{{ item.stage_title }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500 line-clamp-2">
              <span v-if="item.module_title">Модуль: {{ item.module_title }} · </span>
              Курс: {{ item.course_title ?? '—' }}
              <span v-if="item.type === 'content' && item.content" class="ml-1 text-gray-400">· {{ stripHtml(item.content).slice(0, 80) }}...</span>
            </div>
          </template>
          <template v-else>
            <div class="font-medium text-gray-900">{{ item.title }}</div>
            <div class="mt-0.5 text-xs text-gray-500">
              <template v-if="type === 'module'">
                Курс: {{ item.course?.title ?? '—' }}
                <span v-if="item.stages?.length" class="ml-2">· {{ item.stages.length }} этап(ов)</span>
              </template>
              <template v-else>
                <span v-if="item.module?.title">Модуль: {{ item.module.title }} · </span>
                Курс: {{ item.course?.title ?? '—' }}
              </template>
            </div>
          </template>
        </li>
      </ul>

      <div v-if="pendingItem" class="rounded-xl border border-rosatom-300 bg-rosatom-50 p-4">
        <p class="mb-3 text-sm text-gray-700">
          <template v-if="type === 'block'">
            Скопировать блок <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium" :class="blockTypeBadge(pendingItem.type)">{{ blockTypeLabel(pendingItem.type) }}</span>
            из этапа «<strong>{{ pendingItem.stage_title }}</strong>»?
          </template>
          <template v-else>
            {{ type === 'module' ? 'Скопировать модуль' : 'Скопировать этап' }}
            «<strong>{{ pendingItem.title }}</strong>»?
          </template>
        </p>
        <div class="flex gap-2">
          <RButton variant="primary" size="sm" type="button" @click="selectItem">Копировать</RButton>
          <RButton variant="outline" size="sm" type="button" @click="pendingItem = null">Отмена</RButton>
        </div>
      </div>
    </div>
  </RModal>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  type: { type: String, required: true, validator: v => ['module', 'stage', 'block'].includes(v) },
  eventSlug: { type: String, required: true },
})

const modalTitle = computed(() => {
  if (props.type === 'module') return 'Найти модуль'
  if (props.type === 'block') return 'Найти блок из другого этапа'
  return 'Найти этап'
})

const searchPlaceholder = computed(() => {
  if (props.type === 'module') return 'Название модуля...'
  if (props.type === 'block') return 'Название этапа или текст блока...'
  return 'Название этапа...'
})

const blockTypeLabels = { content: 'Контент', scorm: 'SCORM', test: 'Тест', assignment: 'Задание', video: 'Видео' }
const blockTypeBadges = {
  content: 'bg-blue-100 text-blue-700',
  scorm: 'bg-purple-100 text-purple-700',
  test: 'bg-amber-100 text-amber-700',
  assignment: 'bg-green-100 text-green-700',
  video: 'bg-rose-100 text-rose-700',
}

function blockTypeLabel(type) { return blockTypeLabels[type] || type }
function blockTypeBadge(type) { return blockTypeBadges[type] || 'bg-gray-100 text-gray-700' }
function stripHtml(html) { return html ? html.replace(/<[^>]*>/g, '') : '' }

const emit = defineEmits(['close', 'select'])

const query = ref('')
const results = ref([])
const loading = ref(false)
const error = ref('')
const pendingItem = ref(null)
const searchInput = ref(null)

let debounceTimer = null

watch(() => props.show, (val) => {
  if (val) {
    query.value = ''
    results.value = []
    error.value = ''
    pendingItem.value = null
    nextTick(() => searchInput.value?.$el?.querySelector('input')?.focus())
  }
})

watch(query, (val) => {
  pendingItem.value = null
  error.value = ''
  clearTimeout(debounceTimer)
  if (val.length < 2) {
    results.value = []
    return
  }
  loading.value = true
  debounceTimer = setTimeout(() => search(val), 300)
})

async function search(q) {
  try {
    const routeMap = { module: 'lms.admin.search.modules', stage: 'lms.admin.search.stages', block: 'lms.admin.search.blocks' }
    const routeName = routeMap[props.type] || 'lms.admin.search.stages'
    const url = route(routeName, { event: props.eventSlug })
    const { data } = await axios.get(url, { params: { q } })
    results.value = data
  } catch (err) {
    console.error('[SearchRefModal] search error:', err)
    results.value = []
    error.value = err.response?.data?.message || err.message || 'Ошибка поиска'
  } finally {
    loading.value = false
  }
}

function confirmItem(item) {
  pendingItem.value = item
}

function selectItem() {
  emit('select', pendingItem.value)
  pendingItem.value = null
  emit('close')
}
</script>
