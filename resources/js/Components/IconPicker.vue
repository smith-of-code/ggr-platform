<template>
  <div class="relative">
    <button
      type="button"
      class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition hover:border-gray-300 hover:bg-gray-50"
      @click="open = true"
    >
      <span v-html="currentIcon" />
      <span class="text-gray-600">{{ currentLabel }}</span>
      <svg class="ml-auto h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-150"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150"
        leave-to-class="opacity-0"
      >
        <div
          v-if="open"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
          @click.self="open = false"
        >
          <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
              <h3 class="text-sm font-semibold text-gray-800">Выберите иконку</h3>
              <button type="button" @click="open = false" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <div class="px-5 pt-3">
              <input
                v-model="search"
                type="text"
                placeholder="Поиск..."
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none transition focus:border-[#003274] focus:ring-1 focus:ring-[#003274]/20"
              />
            </div>
            <div class="grid max-h-72 grid-cols-6 gap-1 overflow-y-auto p-4">
              <button
                v-for="key in filteredKeys"
                :key="key"
                type="button"
                class="flex flex-col items-center gap-1 rounded-xl p-2.5 transition"
                :class="modelValue === key ? 'bg-[#003274]/10 ring-2 ring-[#003274]/30' : 'hover:bg-gray-100'"
                :title="labels[key] || key"
                @click="select(key)"
              >
                <span class="text-[#003274]" v-html="icons[key]" />
                <span class="max-w-full truncate text-[10px] text-gray-500">{{ labels[key] || key }}</span>
              </button>
              <p v-if="!filteredKeys.length" class="col-span-6 py-6 text-center text-sm text-gray-400">Ничего не найдено</p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { iconLibrary, iconKeys, iconLabels } from '@/utils/iconLibrary'

const props = defineProps({
  modelValue: { type: String, default: 'star' },
})
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const search = ref('')

const icons = iconLibrary
const labels = iconLabels

const currentIcon = computed(() => icons[props.modelValue] || icons.star)
const currentLabel = computed(() => labels[props.modelValue] || props.modelValue)

const filteredKeys = computed(() => {
  if (!search.value) return iconKeys
  const q = search.value.toLowerCase()
  return iconKeys.filter(k => {
    const label = (labels[k] || '').toLowerCase()
    return k.includes(q) || label.includes(q)
  })
})

function select(key) {
  emit('update:modelValue', key)
  open.value = false
}
</script>
