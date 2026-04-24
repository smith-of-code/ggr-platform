<template>
  <AdminLayout>
    <Head :title="`Документы — ${user.display_name}`" />
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ user.display_name }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ user.email }}<span v-if="user.phone"> · {{ user.phone }}</span></p>
      </div>
      <Link :href="route('admin.tour-cabinet.tour-users.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">← К списку клиентов</Link>
    </div>

    <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ $page.props.flash.error }}
    </div>
    <div v-if="page.props.errors?.comment" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ page.props.errors.comment }}
    </div>

    <div class="space-y-4">
      <RCard v-for="row in documentRows" :key="row.type" elevation="raised" class="p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0 flex-1">
            <h2 class="text-base font-semibold text-gray-900">{{ row.type_label }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ row.status_label }}</p>
            <p v-if="row.original_name && row.has_file" class="mt-2 text-sm text-gray-700">
              Файл: <span class="font-mono text-xs">{{ row.original_name }}</span>
            </p>
            <p v-if="row.admin_comment && row.status === 'annulled'" class="mt-2 whitespace-pre-wrap rounded-lg bg-red-50 px-3 py-2 text-sm text-red-900">
              {{ row.admin_comment }}
            </p>
          </div>
          <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
            <a
              v-if="row.id && row.has_file"
              :href="route('admin.tour-cabinet.tour-users.documents.download', { user: user.id, document: row.id })"
              class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-50"
            >
              Скачать
            </a>
            <RButton
              v-if="row.can_approve"
              variant="primary"
              size="sm"
              :disabled="approveProcessing === row.id"
              @click="approve(row.id)"
            >
              Подтвердить
            </RButton>
          </div>
        </div>

        <div v-if="row.can_annul && row.id" class="mt-4 border-t border-gray-100 pt-4">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Запросить правку / отклонить</p>
          <p class="mt-1 text-xs text-gray-500">Комментарий увидит участник в ЛК и на email. Файл будет снят, потребуется повторная загрузка.</p>
          <textarea
            v-model="annulComments[row.type]"
            rows="3"
            class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
            placeholder="Например: плохо читается штамп, нужен цветной скан разворота…"
          />
          <div class="mt-2">
            <RButton
              variant="outline"
              size="sm"
              :disabled="annulProcessing === row.id"
              @click="annul(row)"
            >
              Отклонить с комментарием
            </RButton>
          </div>
        </div>
      </RCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const page = usePage()

const props = defineProps({
  user: { type: Object, required: true },
  documentRows: { type: Array, required: true },
})

const annulComments = reactive({})
for (const row of props.documentRows) {
  annulComments[row.type] = ''
}

const approveProcessing = ref(null)
const annulProcessing = ref(null)

function approve(documentId) {
  approveProcessing.value = documentId
  router.post(
    route('admin.tour-cabinet.tour-users.documents.approve', { user: props.user.id, document: documentId }),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        approveProcessing.value = null
      },
    },
  )
}

function annul(row) {
  if (!row.id) return
  annulProcessing.value = row.id
  router.post(
    route('admin.tour-cabinet.tour-users.documents.annul', { user: props.user.id, document: row.id }),
    { comment: annulComments[row.type] || '' },
    {
      preserveScroll: true,
      onFinish: () => {
        annulProcessing.value = null
      },
    },
  )
}
</script>
