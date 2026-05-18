<template>
  <nav
    class="-mx-1 flex flex-nowrap items-center gap-x-0 overflow-x-auto px-1 [-ms-overflow-style:none] [scrollbar-width:none] sm:mx-0 sm:gap-x-0 sm:overflow-visible sm:px-0 [&::-webkit-scrollbar]:hidden"
    aria-label="Навигация по разделам"
  >
    <slot name="before" />
    <Link :href="route('home')" :class="itemClass(false)">
      <HomeIcon :class="iconClass(false)" aria-hidden="true" />
      <span class="whitespace-nowrap">На сайт</span>
    </Link>
    <span class="shrink-0 select-none px-1.5 text-slate-300 sm:px-2" aria-hidden="true">|</span>
    <Link :href="route('tour-cabinet.support.index')" :class="itemClass(isSupportSection)">
      <LifebuoyIcon :class="iconClass(isSupportSection)" aria-hidden="true" />
      <span class="whitespace-nowrap">Поддержка</span>
    </Link>
    <span class="shrink-0 select-none px-1.5 text-slate-300 sm:px-2" aria-hidden="true">|</span>
    <Link :href="route('tour-cabinet.archives.contest.index')" :class="itemClass(isContestArchiveSection)">
      <ArchiveBoxIcon :class="iconClass(isContestArchiveSection)" aria-hidden="true" />
      <span class="whitespace-nowrap">Архив конкурсы</span>
    </Link>
    <span class="shrink-0 select-none px-1.5 text-slate-300 sm:px-2" aria-hidden="true">|</span>
    <Link :href="route('tour-cabinet.archives.commerce.index')" :class="itemClass(isCommerceArchiveSection)">
      <ArchiveBoxIcon :class="iconClass(isCommerceArchiveSection)" aria-hidden="true" />
      <span class="whitespace-nowrap">Архив коммерческих туров</span>
    </Link>
    <template v-if="lmsEntryUrl">
      <span class="shrink-0 select-none px-1.5 text-slate-300 sm:px-2" aria-hidden="true">|</span>
      <a :href="lmsEntryUrl" :class="itemClass(false)">
        <AcademicCapIcon :class="iconClass(false)" aria-hidden="true" />
        <span class="whitespace-nowrap">ВШГР — обучение</span>
        <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5 shrink-0 opacity-50 transition group-hover:opacity-100 sm:h-4 sm:w-4" aria-hidden="true" />
      </a>
    </template>
  </nav>
</template>

<script setup>
import {
  AcademicCapIcon,
  ArchiveBoxIcon,
  ArrowTopRightOnSquareIcon,
  HomeIcon,
  LifebuoyIcon,
} from '@heroicons/vue/24/outline'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const lmsEntryUrl = computed(() => page.props.lmsEntryUrl || null)

const isSupportSection = computed(() => {
  const u = page.url || ''
  return u.startsWith('/tour-cabinet/support')
})

const isContestArchiveSection = computed(() => {
  const u = page.url || ''
  return u.startsWith('/tour-cabinet/archives/contest')
})

const isCommerceArchiveSection = computed(() => {
  const u = page.url || ''
  return u.startsWith('/tour-cabinet/archives/commerce')
})

function itemClass(active) {
  const base =
    'group inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003274]/25 focus-visible:ring-offset-2 sm:gap-2 sm:px-2.5'
  if (active) {
    return `${base} bg-[#003274] text-white font-semibold shadow-sm ring-1 ring-[#003274]/30 hover:bg-[#002357]`
  }
  return `${base} text-slate-600 hover:bg-slate-100 hover:text-slate-900`
}

function iconClass(active) {
  const base = 'h-4 w-4 shrink-0 transition sm:h-5 sm:w-5'
  if (active) {
    return `${base} text-white`
  }
  return `${base} text-slate-400 group-hover:text-slate-600`
}
</script>
