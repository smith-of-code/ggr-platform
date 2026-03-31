<template>
  <div class="space-y-3">
    <!-- Selected cities -->
    <div v-if="selected.length" class="space-y-2">
      <div
        v-for="(entry, idx) in selected"
        :key="entry.city.id"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
      >
        <div class="flex items-center gap-3 p-3">
          <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#003274]/10 text-xs font-bold text-[#003274]">
            {{ idx + 1 }}
          </span>
          <img
            v-if="entry.city.coat_of_arms"
            :src="entry.city.coat_of_arms"
            :alt="entry.city.name"
            class="h-8 w-8 shrink-0 rounded object-contain"
          />
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900">{{ entry.city.name }}</p>
            <p v-if="entry.city.region" class="text-xs text-gray-500">{{ entry.city.region }}</p>
          </div>
          <button
            type="button"
            @click="remove(idx)"
            class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
            title="Убрать"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div v-if="withDescription" class="border-t border-gray-100 px-3 pb-3 pt-2">
          <label class="mb-1 block text-xs font-medium text-gray-500">Описание</label>
          <textarea
            :value="entry.description"
            @input="updateDescription(idx, $event.target.value)"
            rows="2"
            placeholder="Описание города для этого блока..."
            class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-[#003274] focus:ring-[#003274]/10"
          />
        </div>
      </div>
    </div>

    <!-- Search input -->
    <div class="relative">
      <input
        v-model="search"
        type="text"
        :placeholder="placeholder"
        class="w-full rounded-xl border border-dashed border-gray-300 bg-white px-4 py-2.5 pl-10 text-sm transition focus:border-[#003274] focus:ring-[#003274]/10"
        @focus="open = true"
      />
      <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
      </svg>

      <!-- Dropdown -->
      <div
        v-if="open && filtered.length"
        class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg"
      >
        <button
          v-for="city in filtered"
          :key="city.id"
          type="button"
          class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-[#003274]/5"
          @mousedown.prevent="pick(city)"
        >
          <img
            v-if="city.coat_of_arms"
            :src="city.coat_of_arms"
            :alt="city.name"
            class="h-6 w-6 shrink-0 rounded object-contain"
          />
          <span v-else class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-gray-100 text-xs text-gray-400">—</span>
          <div class="min-w-0 flex-1">
            <p class="font-medium text-gray-900">{{ city.name }}</p>
            <p v-if="city.region" class="text-xs text-gray-500">{{ city.region }}</p>
          </div>
        </button>
      </div>
      <p v-else-if="open && search && !filtered.length" class="absolute left-0 right-0 top-full z-20 mt-1 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-400 shadow-lg">
        Ничего не найдено
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  cities: { type: Array, required: true },
  placeholder: { type: String, default: 'Найти город...' },
  withDescription: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const search = ref('')
const open = ref(false)

function getCityId(item) {
  return props.withDescription ? item.city_id : item
}

const selectedIds = computed(() => new Set(props.modelValue.map(getCityId)))

const selected = computed(() =>
  props.modelValue
    .map(item => {
      const id = getCityId(item)
      const city = props.cities.find(c => c.id === id)
      if (!city) return null
      return { city, description: props.withDescription ? (item.description ?? '') : '' }
    })
    .filter(Boolean)
)

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  return props.cities.filter(c => {
    if (selectedIds.value.has(c.id)) return false
    if (!q) return true
    return c.name.toLowerCase().includes(q) || (c.region && c.region.toLowerCase().includes(q))
  })
})

function pick(city) {
  if (props.withDescription) {
    emit('update:modelValue', [...props.modelValue, { city_id: city.id, description: '' }])
  } else {
    emit('update:modelValue', [...props.modelValue, city.id])
  }
  search.value = ''
}

function remove(idx) {
  const updated = [...props.modelValue]
  updated.splice(idx, 1)
  emit('update:modelValue', updated)
}

function updateDescription(idx, value) {
  const updated = [...props.modelValue]
  updated[idx] = { ...updated[idx], description: value }
  emit('update:modelValue', updated)
}

function handleClickOutside(e) {
  if (!e.target.closest('.relative')) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>
