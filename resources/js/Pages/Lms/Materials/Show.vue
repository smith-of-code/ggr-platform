<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${section?.title} – Материалы`" />
    <div class="space-y-6">
      <Link
        :href="route('lms.materials.index', { event: event?.slug })"
        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-rosatom-700"
      >
        <ArrowLeftIcon class="h-4 w-4" />
        Назад к материалам
      </Link>

      <RCard>
        <template #default>
          <h1 class="font-brand text-2xl font-bold text-gray-900">{{ section?.title }}</h1>
          <div
            v-if="section?.content"
            class="material-content prose mt-6 max-w-none text-gray-700 prose-headings:text-gray-900 prose-a:text-rosatom-600 prose-a:no-underline hover:prose-a:underline"
            v-html="section.content"
          />
          <p v-else class="mt-6 text-gray-400">Контент отсутствует</p>

          <div v-if="section?.files?.length" class="mt-8 border-t border-gray-200 pt-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Прикреплённые файлы</h2>
            <ul class="space-y-2">
              <li v-for="file in section.files" :key="file.id">
                <a
                  :href="file.file_path"
                  target="_blank"
                  class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-rosatom-300 hover:bg-rosatom-50/30"
                >
                  <DocumentArrowDownIcon class="h-5 w-5 shrink-0 text-rosatom-500" />
                  <span class="flex-1 text-sm font-medium text-gray-800">{{ file.title || file.file_name }}</span>
                  <span v-if="file.file_size" class="text-xs text-gray-400">{{ formatSize(file.file_size) }}</span>
                </a>
              </li>
            </ul>
          </div>
        </template>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  section: { type: Object, required: true },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})

function formatSize(bytes) {
  if (!bytes) return ''
  const units = ['Б', 'КБ', 'МБ', 'ГБ']
  let i = 0
  let size = bytes
  while (size >= 1024 && i < units.length - 1) { size /= 1024; i++ }
  return `${size.toFixed(i > 0 ? 1 : 0)} ${units[i]}`
}
</script>
