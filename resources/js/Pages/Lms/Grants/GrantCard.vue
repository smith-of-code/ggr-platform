<template>
  <div
    class="group cursor-pointer overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:shadow-md"
    :class="badgeVariant === 'expired' ? 'border-gray-200 opacity-75' : 'border-gray-100'"
    @click="router.visit(route('lms.grants.show', { event: event?.slug, grant: item.grant.id }))"
  >
    <div class="p-5">
      <div class="mb-3 flex items-start justify-between gap-2">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rosatom-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rosatom-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h4.5a2.5 2.5 0 0 1 0 5H9V8Zm0 5v3m0 0v2m0-2H7m2 0h4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <div class="flex items-center gap-2">
          <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="typeBadgeClass">
            {{ typeLabel }}
          </span>
          <RBadge v-if="badgeVariant === 'enrolled'" variant="success" size="sm">Участвую</RBadge>
          <RBadge v-else-if="badgeVariant === 'expired'" variant="danger" size="sm">Истёк срок</RBadge>
        </div>
      </div>
      <h3 class="font-semibold text-gray-900 group-hover:text-rosatom-600">{{ item.grant.title }}</h3>
      <p v-if="item.grant.city?.length" class="mt-1 text-xs text-gray-400">{{ item.grant.city.join(', ') }}</p>
      <p v-if="item.grant.description" class="mt-2 line-clamp-3 text-sm text-gray-500">{{ stripTags(item.grant.description) }}</p>
      <div v-if="item.grant.application_start || item.grant.application_end" class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <span>Приём заявок: {{ formatDateRange(item.grant) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  item: { type: Object, required: true },
  event: { type: Object, required: true },
  badgeVariant: { type: String, default: '' },
})

const TYPE_LABELS = { grant: 'Грант', subsidy: 'Субсидия', credit: 'Кредит' }
const TYPE_CLASSES = {
  grant: 'bg-blue-50 text-blue-700',
  subsidy: 'bg-violet-50 text-violet-700',
  credit: 'bg-amber-50 text-amber-700',
}

const typeLabel = computed(() => TYPE_LABELS[props.item.grant.type] || props.item.grant.type)
const typeBadgeClass = computed(() => TYPE_CLASSES[props.item.grant.type] || 'bg-gray-50 text-gray-700')

function stripTags(html) {
  if (!html) return ''
  return new DOMParser().parseFromString(html, 'text/html').body.textContent || ''
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateRange(grant) {
  const parts = []
  if (grant.application_start) parts.push(formatDate(grant.application_start))
  if (grant.application_end) parts.push(formatDate(grant.application_end))
  return parts.join(' – ')
}
</script>
