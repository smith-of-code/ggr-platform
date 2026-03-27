<template>
  <div class="relative" ref="root">
    <label v-if="label" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ label }}</label>
    <button
      type="button"
      class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left text-sm font-medium transition-all duration-200"
      :class="open
        ? 'border-[#003274] bg-white shadow-[0_0_0_3px_rgba(0,50,116,0.1)]'
        : modelValue
          ? 'border-[#003274]/30 bg-[#003274]/[0.04] text-[#003274] hover:border-[#003274]/50'
          : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-[#003274]/40 hover:bg-white'"
      @click="open = !open"
    >
      <span class="flex items-center gap-2 truncate">
        <span v-if="selectedOption?.icon" class="text-base leading-none">{{ selectedOption.icon }}</span>
        <span :class="modelValue ? 'text-[#003274]' : 'text-gray-500'">{{ selectedOption?.label || placeholder }}</span>
      </span>
      <svg
        class="h-4 w-4 shrink-0 transition-transform duration-200"
        :class="open ? 'rotate-180 text-[#003274]' : 'text-gray-400'"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
      </svg>
    </button>

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
        class="absolute left-0 right-0 z-50 mt-1.5 origin-top overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl shadow-gray-200/50"
      >
        <button
          v-for="opt in options"
          :key="opt.value"
          type="button"
          class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition-colors duration-100"
          :class="String(modelValue) === String(opt.value)
            ? 'bg-[#003274]/[0.06] font-semibold text-[#003274]'
            : 'text-gray-700 hover:bg-gray-50'"
          @click="select(opt.value)"
        >
          <span v-if="opt.icon" class="text-base leading-none">{{ opt.icon }}</span>
          <span class="flex-1 truncate">{{ opt.label }}</span>
          <svg
            v-if="String(modelValue) === String(opt.value)"
            class="h-4 w-4 shrink-0 text-[#003274]"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Выбрать...' },
  options: { type: Array, required: true },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const root = ref(null)

const selectedOption = computed(() =>
  props.options.find(o => String(o.value) === String(props.modelValue)),
)

function select(value) {
  emit('update:modelValue', value)
  open.value = false
}

function onClickOutside(e) {
  if (root.value && !root.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', onClickOutside, true))
onUnmounted(() => document.removeEventListener('click', onClickOutside, true))
</script>
