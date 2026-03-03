<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`${section?.title} – База знаний`" />
    <div class="space-y-6">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm">
        <Link
          :href="route('lms.kb.index', { event: event?.slug })"
          class="text-gray-500 hover:text-rosatom-600"
        >
          База знаний
        </Link>
        <ChevronRightIcon class="h-4 w-4 text-gray-400" />
        <span class="text-gray-700">{{ section?.title }}</span>
      </nav>

      <div>
        <h1 class="font-brand text-2xl font-bold text-gray-900">{{ section?.title }}</h1>
        <p v-if="section?.description" class="mt-2 text-gray-500">{{ section.description }}</p>
      </div>

      <!-- Child sections -->
      <div v-if="(section?.children || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Подразделы</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <Link
            v-for="child in section.children"
            :key="child.id"
            :href="route('lms.kb.show', { event: event?.slug, section: child.id })"
            class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white shadow-sm p-4 transition hover:border-gray-300"
          >
            <FolderIcon class="h-6 w-6 shrink-0 text-rosatom-500" />
            <div class="min-w-0 flex-1">
              <p class="font-medium text-gray-900">{{ child.title }}</p>
              <p v-if="child.description" class="mt-0.5 truncate text-sm text-gray-400">{{ child.description }}</p>
            </div>
            <ChevronRightIcon class="h-5 w-5 shrink-0 text-gray-400" />
          </Link>
        </div>
      </div>

      <!-- Items -->
      <div v-if="(section?.items || []).length > 0">
        <h2 class="font-brand mb-4 text-lg font-semibold text-gray-900">Материалы</h2>
        <div class="space-y-2">
          <div
            v-for="item in section.items"
            :key="item.id"
            class="rounded-xl border border-gray-200 bg-white shadow-sm"
          >
            <button
              v-if="item.type === 'text'"
              type="button"
              class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-gray-50"
              @click="expandedItemId = expandedItemId === item.id ? null : item.id"
            >
              <DocumentTextIcon class="h-5 w-5 shrink-0 text-rosatom-500" />
              <span class="flex-1 font-medium text-gray-900">{{ item.title }}</span>
              <ChevronDownIcon
                :class="['h-5 w-5 shrink-0 transition', expandedItemId === item.id ? 'rotate-180' : '']"
              />
            </button>
            <a
              v-else-if="item.type === 'url' && item.url"
              :href="item.url"
              target="_blank"
              rel="noopener noreferrer"
              class="flex items-center gap-3 p-4 transition hover:bg-gray-50"
            >
              <LinkIcon class="h-5 w-5 shrink-0 text-rosatom-600" />
              <span class="flex-1 font-medium text-gray-900">{{ item.title }}</span>
              <ArrowTopRightOnSquareIcon class="h-5 w-5 shrink-0 text-gray-400" />
            </a>
            <a
              v-else-if="(item.type === 'file' || item.file_path) && item.file_path"
              :href="item.file_path"
              target="_blank"
              rel="noopener noreferrer"
              class="flex items-center gap-3 p-4 transition hover:bg-gray-50"
            >
              <DocumentIcon class="h-5 w-5 shrink-0 text-accent-yellow" />
              <span class="flex-1 font-medium text-gray-900">{{ item.title }}</span>
              <ArrowDownTrayIcon class="h-5 w-5 shrink-0 text-gray-400" />
            </a>
            <div v-else class="flex items-center gap-3 p-4">
              <DocumentTextIcon class="h-5 w-5 shrink-0 text-gray-400" />
              <span class="font-medium text-gray-500">{{ item.title }}</span>
            </div>
            <div
              v-if="item.type === 'text' && expandedItemId === item.id && item.content"
              class="border-t border-gray-200 p-4"
            >
              <div
                class="prose max-w-none text-sm text-gray-700"
                v-html="item.content"
              />
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!(section?.children?.length) && !(section?.items?.length)"
        class="rounded-xl border border-gray-200 bg-white py-12 text-center text-gray-400 shadow-sm"
      >
        В этом разделе пока нет материалов
      </div>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import {
  ChevronRightIcon,
  ChevronDownIcon,
  FolderIcon,
  DocumentTextIcon,
  LinkIcon,
  DocumentIcon,
  ArrowTopRightOnSquareIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, default: () => ({}) },
  profile: { type: Object, default: () => ({}) },
  section: { type: Object, required: true },
})

const user = computed(() => props.user || props.event?.user || usePage().props.auth?.user || {})
const expandedItemId = ref(null)
</script>
