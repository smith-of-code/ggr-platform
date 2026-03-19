<template>
  <div class="relative" ref="containerRef">
    <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700">
      {{ label }}
    </label>

    <!-- Trigger -->
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="flex w-full min-h-[42px] flex-wrap items-center gap-1.5 rounded-xl border bg-white px-3 py-2 text-left text-sm transition focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
      :class="[
        open ? 'border-rosatom-500 ring-2 ring-rosatom-500/20' : 'border-gray-300',
        error ? 'border-red-400' : '',
        disabled ? 'cursor-not-allowed bg-gray-50 opacity-60' : 'cursor-pointer hover:border-gray-400',
      ]"
    >
      <template v-if="selectedItems.length">
        <span
          v-for="item in selectedItems"
          :key="item[valueKey]"
          class="inline-flex items-center gap-1 rounded-lg bg-rosatom-50 px-2 py-0.5 text-xs font-medium text-rosatom-700"
        >
          {{ item[labelKey] }}
          <button
            type="button"
            @click.stop="deselect(item)"
            class="ml-0.5 rounded-full p-0.5 text-rosatom-400 hover:bg-rosatom-100 hover:text-rosatom-600"
          >
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </template>
      <span v-else class="text-gray-400">{{ placeholder }}</span>
      <svg class="ml-auto h-4 w-4 shrink-0 text-gray-400 transition" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="scale-95 opacity-0"
      enter-to-class="scale-100 opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="scale-100 opacity-100"
      leave-to-class="scale-95 opacity-0"
    >
      <div
        v-if="open"
        class="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
      >
        <div class="border-b border-gray-100 p-2">
          <input
            ref="searchInputRef"
            v-model="query"
            type="text"
            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-rosatom-400"
            placeholder="Поиск..."
          />
        </div>
        <ul class="max-h-60 overflow-y-auto py-1">
          <li
            v-for="opt in filteredOptions"
            :key="opt[valueKey]"
            @click="toggleItem(opt)"
            class="flex cursor-pointer items-center gap-3 px-4 py-2.5 text-sm transition hover:bg-rosatom-50"
            :class="{ 'bg-rosatom-50': isSelected(opt) }"
          >
            <div
              class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition"
              :class="isSelected(opt) ? 'border-rosatom-600 bg-rosatom-600' : 'border-gray-300'"
            >
              <svg v-if="isSelected(opt)" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </div>
            <span class="flex-1 truncate">{{ opt[labelKey] }}</span>
          </li>
          <li v-if="filteredOptions.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">Ничего не найдено</li>
        </ul>
      </div>
    </Transition>

    <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'name' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Выберите...' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const containerRef = ref(null)
const searchInputRef = ref(null)
const open = ref(false)
const query = ref('')

const selectedItems = computed(() =>
  props.options.filter(o => props.modelValue.includes(o[props.valueKey]))
)

const filteredOptions = computed(() => {
  if (!query.value) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter(o => String(o[props.labelKey]).toLowerCase().includes(q))
})

function isSelected(opt) {
  return props.modelValue.includes(opt[props.valueKey])
}

function toggleItem(opt) {
  const val = opt[props.valueKey]
  const newVal = isSelected(opt)
    ? props.modelValue.filter(v => v !== val)
    : [...props.modelValue, val]
  emit('update:modelValue', newVal)
}

function deselect(opt) {
  emit('update:modelValue', props.modelValue.filter(v => v !== opt[props.valueKey]))
}

function toggle() {
  if (props.disabled) return
  open.value = !open.value
  if (open.value) {
    query.value = ''
    nextTick(() => searchInputRef.value?.focus())
  }
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>
