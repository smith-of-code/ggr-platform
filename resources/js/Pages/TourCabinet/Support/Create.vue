<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 px-4 py-8 font-sans text-slate-900">
    <Head title="Новое обращение — ЛК туров" />
    <div class="mx-auto max-w-2xl">
      <Link :href="route('tour-cabinet.support.index')" class="text-sm font-semibold text-rosatom-700 underline decoration-rosatom-300 underline-offset-4 hover:text-rosatom-900">
        ← Все обращения
      </Link>
      <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">Новое обращение</h1>
      <p class="mt-2 text-sm text-slate-600">
        Опишите вопрос. Файлы — скриншоты, PDF, документы (до 5 файлов по 5 МБ). Документы уровня паспорта в этот канал загружать не стоит.
      </p>
      <p v-if="supportContactEmail" class="mt-2 text-sm text-slate-600">
        Альтернатива:
        <a :href="`mailto:${supportContactEmail}`" class="font-semibold text-rosatom-700 underline hover:text-rosatom-900">{{ supportContactEmail }}</a>
      </p>

      <form class="mt-8 space-y-5 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5" @submit.prevent="submit">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Тема</label>
          <input
            v-model="form.subject"
            type="text"
            maxlength="255"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-rosatom-600 focus:outline-none focus:ring-2 focus:ring-rosatom-600/15"
            required
          />
          <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Категория</label>
          <select
            v-model="form.category"
            class="w-full cursor-pointer rounded-xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-rosatom-600 focus:outline-none focus:ring-2 focus:ring-rosatom-600/15"
            required
          >
            <option v-for="c in categoryOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
          </select>
          <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Текст</label>
          <textarea
            v-model="form.body"
            rows="6"
            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm transition focus:border-rosatom-600 focus:outline-none focus:ring-2 focus:ring-rosatom-600/15"
            required
          />
          <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">{{ form.errors.body }}</p>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700">Вложения (необязательно)</label>
          <input type="file" multiple class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-medium file:text-slate-800 hover:file:bg-slate-200" @change="onFiles" />
          <p v-if="form.errors.attachments" class="mt-1 text-sm text-red-600">{{ form.errors.attachments }}</p>
          <p v-for="(err, i) in attachmentErrors" :key="i" class="mt-1 text-sm text-red-600">{{ err }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">Отправить</RButton>
          <Link :href="route('tour-cabinet.support.index')">
            <RButton type="button" variant="outline" :disabled="form.processing">Отмена</RButton>
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  categoryOptions: { type: Array, required: true },
  supportContactEmail: { type: String, default: null },
})

const form = useForm({
  subject: '',
  category: props.categoryOptions[0]?.value ?? 'other',
  body: '',
  attachments: [],
})

const attachmentErrors = computed(() => {
  const e = form.errors
  const keys = Object.keys(e).filter((k) => k.startsWith('attachments.'))
  return keys.map((k) => e[k]).filter(Boolean)
})

function onFiles(e) {
  const files = Array.from(e.target.files || [])
  form.attachments = files
}

function submit() {
  form.post(route('tour-cabinet.support.store'), {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>
