<template>
  <div class="relative" ref="containerRef">
    <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700">
      {{ label }} <span v-if="required" class="text-red-400">*</span>
    </label>

    <!-- Trigger -->
    <button
      type="button"
      @click="toggle"
      :disabled="disabled"
      class="flex w-full items-center justify-between rounded-xl border bg-white px-4 py-2.5 text-left text-sm transition focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
      :class="[
        open ? 'border-rosatom-500 ring-2 ring-rosatom-500/20' : 'border-gray-300',
        error ? 'border-red-400' : '',
        disabled ? 'cursor-not-allowed bg-gray-50 opacity-60' : 'cursor-pointer hover:border-gray-400',
      ]"
    >
      <span v-if="selectedLabel" class="truncate text-gray-900">{{ selectedLabel }}</span>
      <span v-else class="truncate text-gray-400">{{ placeholder }}</span>
      <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400 transition" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
        <!-- Search input -->
        <div v-if="searchable" class="border-b border-gray-100 p-2">
          <input
            ref="searchInputRef"
            v-model="query"
            type="text"
            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-rosatom-400"
            :placeholder="searchPlaceholder"
          />
        </div>

        <!-- Options -->
        <ul class="max-h-60 overflow-y-auto py-1">
          <li
            v-if="clearable"
            @click="select(null)"
            class="flex cursor-pointer items-center px-4 py-2.5 text-sm text-gray-400 transition hover:bg-gray-50"
          >
            {{ clearLabel }}
          </li>
          <li
            v-for="opt in filteredOptions"
            :key="opt[valueKey]"
            @click="select(opt)"
            class="flex cursor-pointer items-center gap-3 px-4 py-2.5 text-sm transition hover:bg-rosatom-50"
            :class="{ 'bg-rosatom-50 font-medium text-rosatom-700': isSelected(opt) }"
          >
            <span class="flex-1 truncate">{{ opt[labelKey] }}</span>
            <svg v-if="isSelected(opt)" class="h-4 w-4 shrink-0 text-rosatom-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </li>
          <li v-if="filteredOptions.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
            Ничего не найдено
          </li>
        </ul>
      </div>
    </Transition>

    <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number, null], default: null },
  options: { type: Array, default: () => [] },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'name' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Выберите...' },
  searchPlaceholder: { type: String, default: 'Поиск...' },
  clearLabel: { type: String, default: '— Не выбрано —' },
  searchable: { type: Boolean, default: true },
  clearable: { type: Boolean, default: true },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const containerRef = ref(null)
const searchInputRef = ref(null)
const open = ref(false)
const query = ref('')

const selectedLabel = computed(() => {
  if (props.modelValue == null || props.modelValue === '') return ''
  const opt = props.options.find(o => String(o[props.valueKey]) === String(props.modelValue))
  return opt ? opt[props.labelKey] : ''
})

const filteredOptions = computed(() => {
  if (!query.value) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter(o => String(o[props.labelKey]).toLowerCase().includes(q))
})

function isSelected(opt) {
  return String(opt[props.valueKey]) === String(props.modelValue)
}

function toggle() {
  if (props.disabled) return
  open.value = !open.value
  if (open.value) {
    query.value = ''
    nextTick(() => searchInputRef.value?.focus())
  }
}

function select(opt) {
  emit('update:modelValue', opt ? opt[props.valueKey] : null)
  open.value = false
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>
