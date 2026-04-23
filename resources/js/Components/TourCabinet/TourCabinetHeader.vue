<template>
  <header
    class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-[0_1px_0_rgba(15,23,42,0.04)] backdrop-blur-md"
  >
    <div :class="['mx-auto px-4 sm:px-5 lg:px-6', maxWidthClass]">
      <div
        v-if="$slots.breadcrumb"
        class="border-b border-slate-100/90 pb-3 pt-4 sm:pb-4 sm:pt-5"
      >
        <slot name="breadcrumb" />
      </div>

      <div class="flex flex-col gap-4 pt-3 pb-4 sm:gap-5 sm:pt-4 sm:pb-5 lg:gap-6">
        <div class="min-w-0 flex-1 lg:pr-6">
          <slot name="title" />
          <div v-if="$slots.subtitle" class="mt-3 max-w-2xl space-y-2 sm:mt-3.5">
            <slot name="subtitle" />
          </div>
        </div>
        <div
          v-if="$slots.toolbar"
          class="flex w-full flex-col gap-3 border-t border-slate-100 pt-3 sm:flex-row sm:items-start sm:gap-6 sm:border-t-0 sm:pt-0 lg:w-auto lg:shrink-0"
        >
          <div class="min-w-0 shrink-0 lg:pt-1">
            <TourCabinetQuickNav />
          </div>
          <div
            class="flex w-full shrink-0 flex-col gap-2 border-l-0 sm:ml-auto sm:w-auto sm:flex-row sm:flex-wrap sm:items-stretch sm:justify-end sm:border-l sm:border-slate-200 sm:pl-6"
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
