<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="$emit('close')">
      <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900">Медиа-библиотека</h3>
          <button type="button" class="rounded-lg p-1 text-gray-400 hover:text-gray-600" @click="$emit('close')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-3">
          <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Поиск по имени файла..."
              class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm focus:border-[#003274] focus:ring-[#003274]/20"
              @input="debouncedFetch"
            />
          </div>
          <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-[#003274] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#004090]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
            </svg>
            {{ uploading ? 'Загрузка...' : 'Загрузить' }}
            <input type="file" :accept="accept" :multiple="multiple" class="hidden" :disabled="uploading" @change="uploadFile" />
          </label>
        </div>

        <!-- Scope tabs -->
        <div v-if="hasTabs" class="flex items-center gap-1 border-b border-gray-100 px-6 py-2">
          <button
            v-if="entityId"
            type="button"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
            :class="activeScope === 'entity' ? 'bg-[#003274] text-white' : 'text-gray-600 hover:bg-gray-100'"
            @click="switchScope('entity')"
          >
            {{ entityTabLabel }} <span class="ml-1 opacity-70">({{ counts.entity ?? 0 }})</span>
          </button>
          <button
            v-if="collection"
            type="button"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
            :class="activeScope === 'collection' ? 'bg-[#003274] text-white' : 'text-gray-600 hover:bg-gray-100'"
            @click="switchScope('collection')"
          >
            {{ collectionLabel }} <span class="ml-1 opacity-70">({{ counts.collection ?? 0 }})</span>
          </button>
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
            :class="activeScope === 'all' ? 'bg-[#003274] text-white' : 'text-gray-600 hover:bg-gray-100'"
            @click="switchScope('all')"
          >
            Вся библиотека <span class="ml-1 opacity-70">({{ counts.all ?? 0 }})</span>
          </button>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
          <div v-if="loading && !items.length" class="flex items-center justify-center py-16">
            <svg class="h-8 w-8 animate-spin text-[#003274]" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
          </div>
          <div v-else-if="!items.length" class="py-16 text-center text-sm text-gray-400">
            {{ search ? 'Ничего не найдено' : `Библиотека пуста. Загрузите первый ${typeLabel}.` }}
          </div>
          <div v-else class="grid grid-cols-4 gap-3 sm:grid-cols-5 lg:grid-cols-6">
            <button
              v-for="item in items"
              :key="item.id"
              type="button"
              class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg border-2 transition"
              :class="isSelected(item.url) ? 'border-[#003274] ring-2 ring-[#003274]/30' : 'border-gray-200 hover:border-[#003274]/40'"
              @click="toggleItem(item.url)"
              @dblclick="confirmSelect"
            >
              <img v-if="isImage(item.mime_type)" :src="item.url" :alt="item.original_name" class="h-full w-full object-cover" loading="lazy" />
              <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 bg-gray-50 p-2">
                <svg v-if="isVideo(item.mime_type)" class="h-10 w-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <svg v-else class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="max-w-full truncate text-[10px] text-gray-500">{{ item.original_name }}</span>
              </div>
              <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/10" />
              <div v-if="isSelected(item.url)" class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#003274] text-xs font-bold text-white">
                <template v-if="multiple">{{ selectionOrder(item.url) }}</template>
                <svg v-else class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex items-center justify-center gap-1 border-t border-gray-100 px-6 py-3">
          <button
            v-for="p in lastPage"
            :key="p"
            type="button"
            class="rounded-lg px-3 py-1.5 text-sm transition"
            :class="p === page ? 'bg-[#003274] text-white' : 'text-gray-600 hover:bg-gray-100'"
            @click="goToPage(p)"
          >
            {{ p }}
          </button>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4">
          <p v-if="multiple && multiSelected.length" class="truncate text-sm text-gray-500">
            Выбрано: {{ multiSelected.length }}
          </p>
          <p v-else-if="!multiple && selected" class="truncate text-sm text-gray-500">{{ selectedName }}</p>
          <p v-else class="text-sm text-gray-400">
            {{ multiple ? `Выберите один или несколько файлов` : `Выберите ${typeLabel} или загрузите новый` }}
          </p>
          <div class="flex gap-3">
            <button
              type="button"
              class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
              @click="$emit('close')"
            >
              Отмена
            </button>
            <button
              type="button"
              class="rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#004090] disabled:opacity-40"
              :disabled="!canConfirm"
              @click="confirmSelect"
            >
              {{ multiple && multiSelected.length > 1 ? `Выбрать (${multiSelected.length})` : 'Выбрать' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const collectionLabels = {
  cities: 'Все города',
  tours: 'Все туры',
  blog: 'Все статьи',
  vacancies: 'Все вакансии',
  recipes: 'Все рецепты',
  education_products: 'Все образ. продукты',
  directions: 'Все направления',
  research_page: 'Исследования',
  atoms_vkusa: 'Атомы вкуса',
  opportunity_tours: 'Возможности',
  lms_courses: 'Все программы',
  lms_materials: 'Все материалы',
  lms_grants: 'Все гранты',
}

const props = defineProps({
  show: { type: Boolean, default: false },
  apiUrl: { type: String, required: true },
  uploadUrl: { type: String, default: '/admin/upload/image' },
  collection: { type: String, default: '' },
  entityType: { type: String, default: '' },
  entityId: { type: [Number, String], default: null },
  multiple: { type: Boolean, default: false },
  accept: { type: String, default: 'image/*' },
  fileType: { type: String, default: 'image' },
  uploadField: { type: String, default: 'image' },
})

const emit = defineEmits(['close', 'select'])

const items = ref([])
const search = ref('')
const page = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const uploading = ref(false)
const selected = ref(null)
const multiSelected = ref([])
const selectedName = ref('')
const counts = ref({ all: 0 })
const entityLabel = ref(null)

function isSelected(url) {
  return props.multiple ? multiSelected.value.includes(url) : selected.value === url
}

function toggleItem(url) {
  if (props.multiple) {
    const idx = multiSelected.value.indexOf(url)
    if (idx >= 0) {
      multiSelected.value.splice(idx, 1)
    } else {
      multiSelected.value.push(url)
    }
  } else {
    selected.value = url
  }
}

function selectionOrder(url) {
  return multiSelected.value.indexOf(url) + 1
}

const canConfirm = computed(() => props.multiple ? multiSelected.value.length > 0 : !!selected.value)

function isImage(mime) { return mime && mime.startsWith('image/') }
function isVideo(mime) { return mime && mime.startsWith('video/') }

const typeLabel = computed(() => {
  if (props.fileType === 'video') return 'видео'
  if (props.fileType === 'document') return 'документ'
  if (props.fileType === 'all') return 'файл'
  return 'изображение'
})

const hasTabs = computed(() => !!(props.entityId || props.collection))
const activeScope = ref('all')

const entityTabLabel = computed(() => entityLabel.value || 'Этот объект')
const collectionLabel = computed(() => collectionLabels[props.collection] || props.collection || 'Коллекция')

let debounceTimer = null

watch(() => props.show, (val) => {
  if (val) {
    selected.value = null
    multiSelected.value = []
    selectedName.value = ''
    search.value = ''
    page.value = 1
    activeScope.value = props.entityId ? 'entity' : (props.collection ? 'collection' : 'all')
    fetchMedia()
  }
})

function switchScope(scope) {
  activeScope.value = scope
  page.value = 1
  fetchMedia()
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    page.value = 1
    fetchMedia()
  }, 300)
}

async function fetchMedia() {
  loading.value = true
  try {
    const params = { page: page.value, scope: activeScope.value }
    if (search.value) params.search = search.value
    if (props.collection) params.collection = props.collection
    if (props.entityType) params.entity_type = props.entityType
    if (props.entityId) params.entity_id = props.entityId
    if (props.fileType && props.fileType !== 'all') params.type = props.fileType
    const { data } = await axios.get(props.apiUrl, { params })
    items.value = data.data
    lastPage.value = data.last_page
    if (data.counts) counts.value = data.counts
    if (data.entity_label) entityLabel.value = data.entity_label
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
}

function goToPage(p) {
  page.value = p
  fetchMedia()
}

async function uploadFile(e) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  e.target.value = ''

  uploading.value = true
  try {
    for (const file of files) {
      const fd = new FormData()
      fd.append(props.uploadField, file)
      if (props.collection) fd.append('collection', props.collection)
      if (props.entityType) fd.append('entity_type', props.entityType)
      if (props.entityId) fd.append('entity_id', props.entityId)
      const { data } = await axios.post(props.uploadUrl, fd)
      if (props.multiple) {
        multiSelected.value.push(data.url)
      } else {
        selected.value = data.url
        selectedName.value = file.name
      }
    }
    page.value = 1
    await fetchMedia()
  } catch {
    // silent
  } finally {
    uploading.value = false
  }
}

function confirmSelect() {
  if (props.multiple) {
    if (multiSelected.value.length) {
      const urls = [...multiSelected.value]
      const names = multiSelected.value.map(url => {
        const item = items.value.find(i => i.url === url)
        return item?.original_name || url.split('/').pop()
      })
      emit('select', urls, names)
      emit('close')
    }
  } else if (selected.value) {
    emit('select', selected.value, selectedName.value)
    emit('close')
  }
}

watch(
  () => selected.value,
  (url) => {
    if (!url) return
    const item = items.value.find(i => i.url === url)
    selectedName.value = item?.original_name || url.split('/').pop()
  },
)
</script>
