<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Отзывы о турах</h1>
      <p class="mt-1 text-sm text-gray-500">Модерация отзывов пользователей</p>
    </div>

    <!-- Status filter -->
    <div class="mb-6 flex gap-2">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        class="rounded-xl px-4 py-2 text-sm font-medium transition"
        :class="currentStatus === tab.value ? 'bg-[#003274] text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
        @click="filterByStatus(tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Reviews list -->
    <div v-if="reviews.data?.length" class="space-y-4">
      <div v-for="review in reviews.data" :key="review.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#003274]/10 text-sm font-bold text-[#003274]">
                {{ review.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ review.user?.name ?? 'Удалённый пользователь' }}</p>
                <p class="text-xs text-gray-500">{{ review.user?.email }} &middot; {{ formatDate(review.created_at) }}</p>
              </div>
            </div>

            <div class="mt-3 flex items-center gap-3">
              <Link v-if="review.tour" :href="route('admin.tours.edit', review.tour.id)" class="text-sm font-medium text-[#003274] hover:underline">
                {{ review.tour.title }}
              </Link>
              <div class="flex gap-0.5 text-amber-400">
                <span v-for="s in 5" :key="s" :class="s <= review.rating ? '' : 'text-gray-300'">&#9733;</span>
              </div>
              <RBadge :variant="review.is_approved ? 'success' : 'warning'">
                {{ review.is_approved ? 'Одобрен' : 'На модерации' }}
              </RBadge>
            </div>

            <p v-if="review.text" class="mt-3 text-sm leading-relaxed text-gray-700">{{ review.text }}</p>
          </div>

          <div class="flex shrink-0 gap-2">
            <RButton v-if="!review.is_approved" variant="primary" size="sm" @click="approve(review.id)">
              Одобрить
            </RButton>
            <RButton v-else variant="outline" size="sm" @click="reject(review.id)">
              Отклонить
            </RButton>
            <RButton variant="danger" size="sm" icon-only @click="destroy(review.id)">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
              </template>
            </RButton>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center">
      <p class="text-gray-500">Отзывов пока нет</p>
    </div>

    <!-- Pagination -->
    <div v-if="reviews.last_page > 1" class="mt-6 flex justify-center">
      <nav class="flex gap-1">
        <Link
          v-for="link in reviews.links"
          :key="link.label"
          :href="link.url || '#'"
          class="rounded-lg px-3 py-2 text-sm transition"
          :class="link.active ? 'bg-[#003274] text-white' : link.url ? 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' : 'text-gray-400 cursor-not-allowed'"
          v-html="link.label"
          preserve-scroll
        />
      </nav>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  reviews: Object,
  filters: Object,
})

const tabs = [
  { value: '', label: 'Все' },
  { value: 'pending', label: 'На модерации' },
  { value: 'approved', label: 'Одобренные' },
]

const currentStatus = ref(props.filters?.status ?? '')

function filterByStatus(status) {
  currentStatus.value = status
  router.get(route('admin.tour-reviews.index'), status ? { status } : {}, { preserveState: true })
}

function approve(id) {
  router.patch(route('admin.tour-reviews.approve', id), {}, { preserveScroll: true })
}

function reject(id) {
  router.patch(route('admin.tour-reviews.reject', id), {}, { preserveScroll: true })
}

function destroy(id) {
  if (confirm('Удалить этот отзыв?')) {
    router.delete(route('admin.tour-reviews.destroy', id), { preserveScroll: true })
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
