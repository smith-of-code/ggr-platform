<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 font-sans text-slate-900">
    <Head :title="`Обращение #${ticket.id} — ЛК туров`" />
    <TourCabinetHeader max-width-class="max-w-3xl">
      <template #breadcrumb>
        <Link
          :href="route('tour-cabinet.support.index')"
          class="mb-2 inline-flex items-center gap-1.5 text-sm font-semibold text-rosatom-700 transition hover:text-rosatom-900"
        >
          <span aria-hidden="true">←</span>
          Все обращения
        </Link>
      </template>
      <template #title>
        <h1 class="text-lg font-bold leading-snug text-slate-900 sm:text-xl">
          #{{ ticket.id }} — {{ ticket.subject }}
        </h1>
      </template>
      <template #subtitle>
        <p class="text-sm text-slate-600">{{ ticket.category_label }} · {{ ticket.status_label }}</p>
        <p v-if="supportContactEmail" class="mt-2 text-sm text-slate-600">
          Дополнительно:
          <a :href="`mailto:${supportContactEmail}`" class="font-semibold text-rosatom-700 underline hover:text-rosatom-900">{{ supportContactEmail }}</a>
        </p>
      </template>
      <template #toolbar>
        <form @submit.prevent="logout" class="w-full sm:w-auto">
          <RButton type="submit" variant="outline" size="sm" class="w-full min-h-[2.75rem] sm:min-h-0 sm:w-auto">
            Выйти
          </RButton>
        </form>
      </template>
    </TourCabinetHeader>

    <div class="mx-auto max-w-3xl px-3 pb-10 pt-4 sm:px-4 lg:px-6 sm:pt-6">
      <div
        v-if="$page.props.flash?.success"
        class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 sm:mb-6"
      >
        {{ $page.props.flash.success }}
      </div>
      <div
        v-if="$page.props.flash?.error"
        class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mb-6"
      >
        {{ $page.props.flash.error }}
      </div>

      <div class="space-y-4">
        <article
          v-for="m in messages"
          :key="m.id"
          class="rounded-2xl border px-4 py-4 shadow-sm sm:px-5"
          :class="m.is_admin ? 'border-emerald-200/80 bg-emerald-50/40' : 'border-slate-200/90 bg-white ring-1 ring-slate-900/5'"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/60 pb-2 text-xs text-slate-500">
            <span class="font-semibold text-slate-800">{{ m.author_label }}</span>
            <time :datetime="m.created_at">{{ formatDate(m.created_at) }}</time>
          </div>
          <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-800">{{ m.body }}</p>
          <ul v-if="m.attachments?.length" class="mt-3 flex flex-wrap gap-2">
            <li v-for="a in m.attachments" :key="a.id">
              <a
                :href="a.url"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-rosatom-800 transition hover:border-rosatom-300 hover:bg-white"
                target="_blank"
                rel="noopener"
              >
                {{ a.name }}
              </a>
            </li>
          </ul>
        </article>
      </div>

      <form v-if="ticket.can_reply" class="mt-8 space-y-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:p-6" @submit.prevent="sendReply">
        <h2 class="text-base font-bold text-slate-900">Ваше сообщение</h2>
        <textarea
          v-model="replyForm.body"
          rows="4"
          class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-rosatom-600 focus:outline-none focus:ring-2 focus:ring-rosatom-600/15"
          placeholder="Текст сообщения…"
          required
        />
        <p v-if="replyForm.errors.body" class="text-sm text-red-600">{{ replyForm.errors.body }}</p>
        <div>
          <label class="mb-1.5 block text-xs font-medium text-slate-600">Вложения</label>
          <input type="file" multiple class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-medium file:text-slate-800 hover:file:bg-slate-200" @change="onReplyFiles" />
          <p v-if="replyForm.errors.attachments" class="mt-1 text-sm text-red-600">{{ replyForm.errors.attachments }}</p>
        </div>
        <RButton type="submit" variant="primary" class="min-h-[2.75rem] w-full sm:w-auto" :loading="replyForm.processing" :disabled="replyForm.processing">
          Отправить
        </RButton>
      </form>
      <p v-else class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-4 py-4 text-center text-sm text-slate-600">Обращение закрыто для новых сообщений.</p>
    </div>
  </div>
</template>

<script setup>
import TourCabinetHeader from '@/Components/TourCabinet/TourCabinetHeader.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  ticket: { type: Object, required: true },
  messages: { type: Array, required: true },
  supportContactEmail: { type: String, default: null },
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

function onReplyFiles(e) {
  replyForm.attachments = Array.from(e.target.files || [])
}

function sendReply() {
  replyForm.post(route('tour-cabinet.support.messages.store', props.ticket.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      replyForm.reset()
      replyForm.clearErrors()
    },
  })
}

function logout() {
  router.post(route('tour-cabinet.logout'))
}
</script>
