<template>
  <AdminLayout>
    <div class="mb-8">
      <div class="flex items-center gap-3">
        <Link
          :href="route('admin.settings.index')"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Видимость страниц</h1>
          <p class="mt-1 text-sm text-gray-500">Скрытые страницы показывают заглушку «В разработке» для посетителей</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="save">
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/50">
              <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Страница</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">URL</th>
              <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Скрыта</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="page in form.pages" :key="page.slug" class="transition hover:bg-gray-50/50">
              <td class="px-6 py-4">
                <span class="text-sm font-medium text-gray-900">{{ page.label }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="rounded-md bg-gray-100 px-2.5 py-1 font-mono text-xs text-gray-500">{{ page.route_prefix }}</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  type="button"
                  role="switch"
                  :aria-checked="page.hidden"
                  @click="page.hidden = !page.hidden"
                  class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#003274] focus:ring-offset-2"
                  :class="page.hidden ? 'bg-red-500' : 'bg-gray-200'"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200 ease-in-out"
                    :class="page.hidden ? 'translate-x-5' : 'translate-x-0'"
                  />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-6 flex justify-end">
        <button
          type="submit"
          :disabled="form.processing"
          class="inline-flex items-center gap-2 rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1] focus:outline-none focus:ring-2 focus:ring-[#003274] focus:ring-offset-2 disabled:opacity-50"
        >
          <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          Сохранить
        </button>
      </div>
    </form>

    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-y-2 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-2 opacity-0"
    >
      <div
        v-if="$page.props.flash?.success"
        class="fixed bottom-6 right-6 z-50 rounded-xl bg-green-600 px-5 py-3 text-sm font-medium text-white shadow-lg"
      >
        {{ $page.props.flash.success }}
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  pages: Array,
})

const form = useForm({
  pages: props.pages.map(p => ({ ...p })),
})

function save() {
  form.put(route('admin.settings.page-visibility.update'))
}
</script>
