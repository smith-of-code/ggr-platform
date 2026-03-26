<template>
  <teleport to="body">
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="my-8 w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
          <!-- Header -->
          <div class="sticky top-0 z-10 flex items-center justify-between rounded-t-2xl border-b border-gray-100 bg-white px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900">Предпросмотр</h3>
            </div>
            <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" @click="$emit('close')">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="px-6 py-6">
            <!-- Hero image -->
            <div v-if="image" class="mb-6 overflow-hidden rounded-xl">
              <img :src="image" class="h-64 w-full object-cover" alt="" />
            </div>

            <!-- Title -->
            <h1 v-if="title" class="mb-2 text-2xl font-bold text-gray-900">{{ title }}</h1>

            <!-- Meta badges -->
            <div v-if="meta && meta.length" class="mb-4 flex flex-wrap gap-2">
              <span
                v-for="(m, i) in meta"
                :key="i"
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                :class="m.class || 'bg-gray-100 text-gray-600'"
              >
                {{ m.label }}
              </span>
            </div>

            <!-- Description / excerpt -->
            <p v-if="description" class="mb-6 text-base leading-relaxed text-gray-600">{{ description }}</p>

            <!-- Slot for custom sections -->
            <slot />

            <!-- HTML Content -->
            <div v-if="content" class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-blue-600 prose-img:rounded-xl" v-html="content" />
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  content: { type: String, default: '' },
  image: { type: String, default: '' },
  meta: { type: Array, default: () => [] },
})

defineEmits(['close'])
</script>
