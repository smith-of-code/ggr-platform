<template>
  <AdminLayout>
    <Head :title="`Клиенты — ${user.display_name}`" />
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

    <!-- Конкурс и заявки -->
    <section class="mb-10 space-y-5">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Конкурс (этапы 1–3) и заявки на туры</h2>

      <RCard v-if="contest.progress" elevation="raised" class="p-5">
        <h3 class="text-base font-semibold text-gray-900">Прогресс и направление</h3>
        <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium text-gray-500">Направление</dt>
            <dd class="text-gray-900">{{ contest.progress.direction_label || '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500">Текущий этап</dt>
            <dd class="text-gray-900">{{ contest.progress.current_stage }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-medium text-gray-500">Города в выборе (этап 1)</dt>
            <dd class="text-gray-900">
              <span v-if="contest.progress.selected_cities?.length">{{ contest.progress.selected_cities.map((c) => c.name).join(', ') }}</span>
              <span v-else class="text-gray-400">—</span>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500">Этап 2 отправлен</dt>
            <dd class="text-gray-900">{{ formatIso(contest.progress.stage2_submitted_at) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500">Обновлено</dt>
            <dd class="text-gray-900">{{ formatIso(contest.progress.updated_at) }}</dd>
          </div>
        </dl>
      </RCard>
      <RCard v-else elevation="raised" class="p-5 text-sm text-gray-500">
        Нет записи прогресса конкурса (участник мог не начинать конкурс в ЛК).
      </RCard>

      <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-800">Этап 1 — анкеты по городам</h3>
        <div v-if="contest.stage1_city_forms?.length" class="space-y-4">
          <RCard v-for="(block, idx) in contest.stage1_city_forms" :key="idx" elevation="raised" class="p-5">
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-gray-100 pb-3">
              <p class="text-base font-semibold text-gray-900">{{ block.city_name }}</p>
              <p class="text-xs text-gray-500">ID города: {{ block.city_id }} · ответ формы: {{ formatIso(block.submitted_at) }}</p>
            </div>
            <dl v-if="block.responses?.length" class="mt-4 space-y-3">
              <div v-for="(r, j) in block.responses" :key="j">
                <dt class="text-xs font-medium text-gray-500">{{ r.label }}</dt>
                <dd class="mt-0.5 whitespace-pre-wrap text-sm text-gray-900">{{ r.value || '—' }}</dd>
              </div>
            </dl>
            <p v-else class="mt-3 text-sm text-gray-400">Нет полей ответа (форма не привязана или пустая).</p>
          </RCard>
        </div>
        <RCard v-else elevation="raised" class="p-4 text-sm text-gray-500">Нет отправленных анкет по городам.</RCard>
      </div>

      <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-800">Этап 2 — ответы на вопросы</h3>
        <RCard v-if="contest.stage2_qa?.length" elevation="raised" flush class="divide-y divide-gray-100">
          <div v-for="(qa, idx) in contest.stage2_qa" :key="idx" class="p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Вопрос</p>
            <p class="mt-1 whitespace-pre-wrap text-sm font-medium text-gray-900">{{ qa.question_body }}</p>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-500">Ответ</p>
            <p class="mt-1 whitespace-pre-wrap text-sm text-gray-800">{{ qa.answer_text || '—' }}</p>
            <p v-if="qa.updated_at" class="mt-2 text-xs text-gray-400">Обновлено: {{ formatIso(qa.updated_at) }}</p>
          </div>
        </RCard>
        <RCard v-else elevation="raised" class="p-4 text-sm text-gray-500">Нет ответов этапа 2.</RCard>
      </div>

      <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-800">Этап 3 — проверочное задание</h3>
        <RCard v-if="contest.stage3" elevation="raised" class="p-5">
          <p class="text-sm font-semibold text-gray-900">{{ contest.stage3.assignment_title }}</p>
          <p v-if="contest.stage3.task_body" class="mt-2 whitespace-pre-wrap text-sm text-gray-700">{{ contest.stage3.task_body }}</p>
          <p class="mt-2 text-xs text-gray-500">Формат ответа: {{ stage3FormatLabel(contest.stage3.response_format) }}</p>
          <div class="mt-4 space-y-3 text-sm">
            <div v-if="contest.stage3.text">
              <p class="text-xs font-medium text-gray-500">Текст / ссылка</p>
              <p class="mt-1 whitespace-pre-wrap text-gray-900">{{ contest.stage3.text }}</p>
            </div>
            <div v-if="contest.stage3.video_url">
              <p class="text-xs font-medium text-gray-500">Видео</p>
              <a :href="contest.stage3.video_url" class="mt-1 break-all text-[#003274] underline" target="_blank" rel="noopener">{{ contest.stage3.video_url }}</a>
            </div>
            <div v-if="contest.stage3.attachment_original_name || contest.stage3.has_attachment">
              <p class="text-xs font-medium text-gray-500">Файл</p>
              <p class="mt-1 text-gray-900">{{ contest.stage3.attachment_original_name || 'вложение' }}</p>
              <a
                v-if="contest.stage3.attachment_download_url"
                :href="contest.stage3.attachment_download_url"
                class="mt-2 inline-flex text-sm font-medium text-[#003274] underline"
              >
                Скачать вложение (как в ответах этапа 3)
              </a>
            </div>
          </div>
        </RCard>
        <RCard v-else elevation="raised" class="p-4 text-sm text-gray-500">Нет данных этапа 3.</RCard>
      </div>

      <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-800">Заявки на туры (портал, по email)</h3>
        <RCard v-if="contest.tour_applications?.length" elevation="raised" flush>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100 bg-gray-50/80 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                  <th class="px-4 py-3">ID</th>
                  <th class="px-4 py-3">Тур</th>
                  <th class="px-4 py-3">Статус</th>
                  <th class="px-4 py-3">Имя в заявке</th>
                  <th class="px-4 py-3">Телефон</th>
                  <th class="px-4 py-3">Даты</th>
                  <th class="px-4 py-3">Создана</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="app in contest.tour_applications" :key="app.id">
                  <td class="px-4 py-3 font-mono text-gray-600">#{{ app.id }}</td>
                  <td class="px-4 py-3 text-gray-900">{{ app.tour_title }}</td>
                  <td class="px-4 py-3">{{ app.status_label }}</td>
                  <td class="px-4 py-3 text-gray-700">{{ app.name }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ app.phone || '—' }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ app.date_range || '—' }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ app.created_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <details v-for="app in contest.tour_applications" :key="'d-' + app.id" class="border-t border-gray-100 px-4 py-3">
            <summary class="cursor-pointer text-xs font-medium text-[#003274]">Доп. данные заявки #{{ app.id }} (JSON)</summary>
            <pre class="mt-2 max-h-48 overflow-auto rounded-lg bg-gray-50 p-3 text-xs text-gray-800">{{ app.data_json }}</pre>
          </details>
        </RCard>
        <RCard v-else elevation="raised" class="p-4 text-sm text-gray-500">Заявок на тур с этим email не найдено.</RCard>
      </div>
    </section>

    <section class="mb-8 space-y-4">
      <div>
        <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Архив конкурсы</h2>
        <p class="mt-1 text-xs text-gray-500">Иммутабельные снапшоты отправленных заявок. Не удаляются при reset прогресса конкурса.</p>
      </div>
      <RCard v-if="contestArchives.length === 0" elevation="raised" class="p-4 text-sm text-gray-500">Архивных заявок на конкурс нет.</RCard>
      <details v-for="a in contestArchives" :key="'ca-' + a.id" class="rounded-2xl border border-gray-200 bg-white">
        <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900">Заявка №{{ a.id }}<span v-if="a.direction_label"> · {{ a.direction_label }}</span></p>
            <p class="mt-0.5 text-xs text-gray-500">Отправлено: {{ formatIso(a.submitted_at) }}</p>
          </div>
          <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200">Отправлено</span>
        </summary>
        <div class="border-t border-gray-100 px-4 py-4 text-sm">
          <div v-if="a.payload?.progress" class="mb-3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Прогресс</p>
            <p class="mt-1 text-gray-900">Этап на момент архивации: {{ a.payload.progress.current_stage }}</p>
            <p v-if="a.payload.progress.selected_cities?.length" class="text-gray-700">Города: {{ a.payload.progress.selected_cities.map((c) => c.name).join(', ') }}</p>
          </div>
          <div v-if="a.payload?.stage1_city_forms?.length" class="mb-3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Этап 1 — анкеты по городам</p>
            <div v-for="(block, i) in a.payload.stage1_city_forms" :key="i" class="mt-2 rounded-lg border border-gray-100 bg-gray-50 p-3">
              <p class="font-semibold text-gray-900">{{ block.city_name }}</p>
              <ul class="mt-1 space-y-1">
                <li v-for="(r, j) in block.responses" :key="j" class="text-gray-800"><span class="text-gray-500">{{ r.label }}:</span> {{ r.value || '—' }}</li>
              </ul>
            </div>
          </div>
          <div v-if="a.payload?.stage2_qa?.length" class="mb-3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Этап 2 — ответы</p>
            <div v-for="(qa, i) in a.payload.stage2_qa" :key="i" class="mt-2 rounded-lg border border-gray-100 bg-gray-50 p-3">
              <p class="text-xs text-gray-600">Вопрос: {{ qa.question_body }}</p>
              <p class="mt-1 whitespace-pre-wrap text-gray-900">{{ qa.answer_text || '—' }}</p>
            </div>
          </div>
          <div v-if="a.payload?.stage3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Этап 3</p>
            <p v-if="a.payload.stage3.text" class="mt-1 whitespace-pre-wrap text-gray-900">{{ a.payload.stage3.text }}</p>
            <p v-if="a.payload.stage3.video_url" class="text-gray-700">Видео: <a :href="a.payload.stage3.video_url" target="_blank" rel="noopener" class="text-[#003274] underline">{{ a.payload.stage3.video_url }}</a></p>
            <p v-if="a.payload.stage3.attachment_original_name || a.payload.meta?.stage3_attachment_original_name" class="text-gray-700">Файл: {{ a.payload.stage3.attachment_original_name || a.payload.meta?.stage3_attachment_original_name }}</p>
          </div>
        </div>
      </details>
    </section>

    <section class="mb-8 space-y-4">
      <div>
        <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Архив коммерческих туров</h2>
        <p class="mt-1 text-xs text-gray-500">Иммутабельные снапшоты отправленных заявок по коммерческим турам.</p>
      </div>
      <RCard v-if="commerceArchives.length === 0" elevation="raised" class="p-4 text-sm text-gray-500">Архивных коммерческих заявок нет.</RCard>
      <details v-for="a in commerceArchives" :key="'cma-' + a.id" class="rounded-2xl border border-gray-200 bg-white">
        <summary class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3 text-sm">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900">Заявка №{{ a.id }}<span v-if="a.tour_title"> · {{ a.tour_title }}</span></p>
            <p class="mt-0.5 text-xs text-gray-500">{{ a.city_name ? `Город: ${a.city_name} · ` : '' }}Отправлено: {{ formatIso(a.submitted_at) }}</p>
          </div>
          <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200">Отправлено</span>
        </summary>
        <div class="border-t border-gray-100 px-4 py-4 text-sm">
          <div class="mb-3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Выбор</p>
            <p class="mt-1 text-gray-900">Город: {{ a.payload?.city?.name || '—' }} · Тур: {{ a.payload?.tour?.title || '—' }}</p>
          </div>
          <div v-if="a.payload?.lms_form?.responses?.length" class="mb-3">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Этап 2 — анкета</p>
            <p v-if="a.payload.lms_form.title" class="mt-1 text-gray-700">{{ a.payload.lms_form.title }}</p>
            <ul class="mt-1 space-y-1">
              <li v-for="(r, j) in a.payload.lms_form.responses" :key="j" class="text-gray-800"><span class="text-gray-500">{{ r.label }}:</span> {{ r.value || '—' }}</li>
            </ul>
          </div>
          <div v-if="a.payload?.stage3_notification">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Этап 3 — уведомление</p>
            <p class="mt-1 font-medium text-gray-900">{{ a.payload.stage3_notification.subject || '—' }}</p>
            <p v-if="a.payload.stage3_notification.body" class="mt-1 whitespace-pre-wrap text-gray-700">{{ a.payload.stage3_notification.body }}</p>
          </div>
        </div>
      </details>
    </section>

    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Документы ЛК</h2>
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
  contest: { type: Object, default: () => ({}) },
  contestArchives: { type: Array, default: () => [] },
  commerceArchives: { type: Array, default: () => [] },
})

const annulComments = reactive({})
for (const row of props.documentRows) {
  annulComments[row.type] = ''
}

const approveProcessing = ref(null)
const annulProcessing = ref(null)

function formatIso(iso) {
  if (!iso) return '—'
  try {
    return new Date(iso).toLocaleString('ru-RU')
  } catch {
    return iso
  }
}

function stage3FormatLabel(format) {
  if (format === 'file_upload') return 'Текст + файл'
  if (format === 'video_link') return 'Текст + ссылка на видео'
  return format || '—'
}

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
