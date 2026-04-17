<template>
  <AdminLayout>
    <Head :title="`Обращение #${ticket.id}`" />
    <div class="mb-4">
      <Link :href="route('admin.tour-cabinet.support.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">← Все обращения</Link>
    </div>

    <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
      {{ $page.props.flash.success }}
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">#{{ ticket.id }} — {{ ticket.subject }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ ticket.category_label }} · {{ ticket.status_label }}</p>
        <div class="mt-4 text-sm text-gray-700">
          <p><span class="font-medium">Участник:</span> {{ ticket.user.email }}</p>
          <p v-if="ticket.user.name"><span class="font-medium">Имя:</span> {{ ticket.user.name }}</p>
          <p v-if="ticket.user.phone"><span class="font-medium">Телефон:</span> {{ ticket.user.phone }}</p>
        </div>
      </div>
      <form class="flex flex-wrap items-end gap-2" @submit.prevent="updateStatus">
        <div>
          <label class="mb-1.5 block text-xs font-medium text-gray-500">Статус</label>
          <select v-model="statusForm.status" class="cursor-pointer rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm">
            <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <RButton type="submit" variant="outline" size="sm" :loading="statusForm.processing">Сохранить статус</RButton>
      </form>
    </div>

    <div class="space-y-4">
      <article
        v-for="m in messages"
        :key="m.id"
        class="rounded-2xl border px-5 py-4"
        :class="m.is_admin ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-200 bg-white'"
      >
        <div class="flex flex-wrap justify-between gap-2 border-b border-gray-100 pb-2 text-xs text-gray-500">
          <span class="font-semibold text-gray-800">{{ m.author_label }}</span>
          <time :datetime="m.created_at">{{ formatDate(m.created_at) }}</time>
        </div>
        <p class="mt-3 whitespace-pre-wrap text-sm text-gray-800">{{ m.body }}</p>
        <ul v-if="m.attachments?.length" class="mt-3 flex flex-wrap gap-2">
          <li v-for="a in m.attachments" :key="a.id">
            <a
              :href="a.url"
              class="inline-flex rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-[#003274] hover:bg-white"
              target="_blank"
              rel="noopener"
            >
              {{ a.name }}
            </a>
          </li>
        </ul>
      </article>
    </div>

    <form class="mt-8 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="sendReply">
      <h2 class="text-lg font-bold text-gray-900">Ответ участнику</h2>
      <textarea
        v-model="replyForm.body"
        rows="5"
        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
        placeholder="Текст ответа…"
        required
      />
      <p v-if="replyForm.errors.body" class="text-sm text-red-600">{{ replyForm.errors.body }}</p>
      <div>
        <label class="mb-1.5 block text-xs font-medium text-gray-600">Вложения</label>
        <input type="file" multiple class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2" @change="onFiles" />
        <p v-if="replyForm.errors.attachments" class="mt-1 text-sm text-red-600">{{ replyForm.errors.attachments }}</p>
      </div>
      <RButton type="submit" variant="primary" :loading="replyForm.processing">Отправить ответ</RButton>
    </form>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  ticket: { type: Object, required: true },
  messages: { type: Array, required: true },
  statusOptions: { type: Array, required: true },
})

const statusForm = useForm({
  status: props.ticket.status,
})

const replyForm = useForm({
  body: '',
  attachments: [],
})

function formatDate(iso) {
  if (!iso) return '—'
  try {
    return new Date(iso).toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' })
  } catch {
    return iso
  }
}

function onFiles(e) {
  replyForm.attachments = Array.from(e.target.files || [])
}

function updateStatus() {
  statusForm.patch(route('admin.tour-cabinet.support.status.update', props.ticket.id), { preserveScroll: true })
}

function sendReply() {
  replyForm.post(route('admin.tour-cabinet.support.messages.store', props.ticket.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      replyForm.reset()
      replyForm.clearErrors()
    },
  })
}
</script>
