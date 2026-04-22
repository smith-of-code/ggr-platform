<template>
  <header
    class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-[0_1px_0_rgba(15,23,42,0.04)] backdrop-blur-md"
  >
    <div :class="['mx-auto px-4 sm:px-5 lg:px-6', maxWidthClass]">
      <div
        class="flex flex-col gap-4 py-4 sm:gap-5 sm:py-5 lg:flex-row lg:items-center lg:justify-between lg:gap-8"
      >
        <div class="min-w-0 flex-1">
          <slot name="breadcrumb" />
          <slot name="title" />
          <div v-if="$slots.subtitle" class="mt-1.5 max-w-2xl text-pretty">
            <slot name="subtitle" />
          </div>
        </div>
        <div
          v-if="$slots.toolbar"
          class="flex flex-col gap-3 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:gap-4 sm:border-t-0 sm:pt-0 lg:shrink-0"
        >
          <TourCabinetQuickNav />
          <div
            class="flex w-full shrink-0 flex-col gap-2 border-l-0 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:border-l sm:border-slate-200 sm:pl-4 lg:pl-5"
          >
            <slot name="toolbar" />
          </div>
        </div>
        <div v-else class="border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0 lg:shrink-0">
          <TourCabinetQuickNav />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import TourCabinetQuickNav from '@/Components/TourCabinet/TourCabinetQuickNav.vue'

defineProps({
  maxWidthClass: {
    type: String,
    default: 'max-w-6xl',
  },
})
</script>
