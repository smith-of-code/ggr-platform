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
            <input type="file" accept="image/*" class="hidden" :disabled="uploading" @change="uploadFile" />
          </label>
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
            {{ search ? 'Ничего не найдено' : 'Библиотека пуста. Загрузите первое изображение.' }}
          </div>
          <div v-else class="grid grid-cols-4 gap-3 sm:grid-cols-5 lg:grid-cols-6">
            <button
              v-for="item in items"
              :key="item.id"
              type="button"
              class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg border-2 transition"
              :class="selected === item.url ? 'border-[#003274] ring-2 ring-[#003274]/30' : 'border-gray-200 hover:border-[#003274]/40'"
              @click="selected = item.url"
              @dblclick="confirmSelect"
            >
              <img :src="item.url" :alt="item.original_name" class="h-full w-full object-cover" loading="lazy" />
              <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/10" />
              <div v-if="selected === item.url" class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#003274] text-white">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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
          <p v-if="selected" class="truncate text-sm text-gray-500">{{ selectedName }}</p>
          <p v-else class="text-sm text-gray-400">Выберите изображение или загрузите новое</p>
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
              :disabled="!selected"
              @click="confirmSelect"
            >
              Выбрать
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  apiUrl: { type: String, required: true },
  uploadUrl: { type: String, default: '/admin/upload/image' },
})

const emit = defineEmits(['close', 'select'])

const items = ref([])
const search = ref('')
const page = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const uploading = ref(false)
const selected = ref(null)
const selectedName = ref('')

let debounceTimer = null

watch(() => props.show, (val) => {
  if (val) {
    selected.value = null
    selectedName.value = ''
    search.value = ''
    page.value = 1
    fetchMedia()
  }
})

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
    const params = { page: page.value }
    if (search.value) params.search = search.value
    const { data } = await axios.get(props.apiUrl, { params })
    items.value = data.data
    lastPage.value = data.last_page
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
  const file = e.target.files?.[0]
  if (!file) return
  e.target.value = ''

  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('image', file)
    const { data } = await axios.post(props.uploadUrl, fd)
    selected.value = data.url
    selectedName.value = file.name
    page.value = 1
    await fetchMedia()
  } catch {
    // silent
  } finally {
    uploading.value = false
  }
}

function confirmSelect() {
  if (selected.value) {
    emit('select', selected.value)
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
