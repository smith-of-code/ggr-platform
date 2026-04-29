<template>
  <div>
    <div v-if="lmsEvent" class="mb-6 flex flex-wrap justify-end gap-2">
      <Link
        :href="sameOriginHref(route('admin.tour-cabinet.lms.forms.index', lmsEvent.slug, false))"
        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Все формы в LMS Admin
      </Link>
      <Link
        :href="sameOriginHref(route('admin.tour-cabinet.lms.forms.create', lmsEvent.slug, false))"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center justify-center rounded-lg bg-[#003274] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#025ea1]"
      >
        Создать форму
      </Link>
    </div>

    <RCard class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Дашборд: Стандартная анкета</h2>
      <p class="mt-2 text-sm text-gray-600">
        Отдельный блок выше блока «Конкурс» в ЛК туров. Кнопка «Заполнить» открывает выбранную форму.
        Можно привязать <strong>любую активную</strong> форму платформы — без ограничения по событию.
      </p>
      <form class="mt-6 space-y-5" @submit.prevent="submitDashboardStandardForm">
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <SearchSelect
              :model-value="dashboardForm.dashboard_standard_form_slug || null"
              label="Форма для блока «Стандартная анкета»"
              :options="allFormsSelectOptions"
              value-key="slug"
              label-key="label"
              placeholder="Выберите форму"
              clear-label="— (блок скрыт)"
              :searchable="allFormsSelectOptions.length > 5"
              :error="dashboardForm.errors.dashboard_standard_form_slug || ''"
              @update:model-value="(v) => { dashboardForm.dashboard_standard_form_slug = v ?? '' }"
            />
            <p class="mt-2 text-xs text-gray-500">
              Если значение не выбрано — блок «Стандартная анкета» в ЛК клиента не отображается.
            </p>
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="dashboardForm.processing" :disabled="dashboardForm.processing">
            Сохранить
          </RButton>
        </div>
      </form>
    </RCard>

    <RCard id="tour-cabinet-admin-completion-notification" class="mb-8 scroll-mt-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Уведомление о завершении конкурса</h2>
      <p class="mt-2 text-sm text-gray-600">
        Письмо отправляется участнику автоматически после успешного прохождения <strong>всех трёх этапов</strong> конкурса
        (один раз на участника). Если отправка выключена — письмо не уходит, даже когда участник завершил этап 3.
      </p>
      <form class="mt-6 space-y-5" @submit.prevent="submitCompletionNotification">
        <label class="flex items-start gap-3">
          <input
            type="checkbox"
            class="mt-1 h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
            :checked="completionForm.enabled"
            @change="(e) => { completionForm.enabled = e.target.checked }"
          />
          <span class="text-sm text-gray-800">
            Отправка активна
            <span class="block text-xs text-gray-500">Снимите галочку, чтобы временно отключить рассылку.</span>
          </span>
        </label>
        <p v-if="completionForm.errors.enabled" class="-mt-3 text-xs text-red-600">{{ completionForm.errors.enabled }}</p>

        <div>
          <label class="block text-sm font-medium text-gray-700">Тема письма</label>
          <input
            v-model="completionForm.subject"
            type="text"
            maxlength="255"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
            :class="completionForm.errors.subject ? 'border-red-400' : ''"
          />
          <p v-if="completionForm.errors.subject" class="mt-1 text-xs text-red-600">{{ completionForm.errors.subject }}</p>
          <p v-else class="mt-1 text-xs text-gray-500">Если оставить пустым — будет использован вариант по умолчанию.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Тело письма</label>
          <textarea
            v-model="completionForm.body"
            rows="6"
            maxlength="20000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
            :class="completionForm.errors.body ? 'border-red-400' : ''"
          />
          <p v-if="completionForm.errors.body" class="mt-1 text-xs text-red-600">{{ completionForm.errors.body }}</p>
          <p v-else class="mt-1 text-xs text-gray-500">Поддерживаются переносы строк. HTML не интерпретируется — только текст.</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="completionForm.processing" :disabled="completionForm.processing">
            Сохранить
          </RButton>
        </div>
      </form>
    </RCard>

    <RCard v-if="!lmsEvent" elevation="raised">
      <p class="text-sm text-amber-800">
        Событие LMS со slug «{{ configSlug }}» не найдено. Проверьте конфигурацию или создайте событие в LMS Admin.
      </p>
    </RCard>

    <template v-else>
      <div v-if="forms.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="form in forms"
          :key="form.id"
          class="group rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-[#003274]/40 hover:shadow-md"
        >
          <div class="mb-3 flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="font-bold text-gray-900">{{ form.title }}</h3>
              <p v-if="form.description" class="mt-1 line-clamp-2 text-xs text-gray-500">{{ form.description }}</p>
              <p class="mt-2 font-mono text-xs text-gray-400">/forms/{{ form.slug }}</p>
            </div>
            <RBadge :variant="form.is_active ? 'success' : 'neutral'" size="sm">
              {{ form.is_active ? 'Активна' : 'Неактивна' }}
            </RBadge>
          </div>

          <div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-gray-500">
            <span class="flex items-center gap-1">
              <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              {{ form.submissions_count }} ответов
            </span>
            <span v-if="form.is_anonymous" class="text-gray-400">Анонимная</span>
            <span v-if="form.allow_embed" class="text-gray-400">Embed</span>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="sameOriginHref(route('forms.public.show', form.slug, false))" class="min-w-[7rem] flex-1">
              <RButton variant="outline" size="sm" block>Публичная страница</RButton>
            </Link>
            <Link
              :href="sameOriginHref(route('admin.tour-cabinet.lms.forms.stats', [lmsEvent.slug, form.id], false))"
              target="_blank"
              rel="noopener noreferrer"
            >
              <RButton variant="outline" size="sm">Статистика</RButton>
            </Link>
            <Link :href="sameOriginHref(route('admin.tour-cabinet.lms.forms.edit', [lmsEvent.slug, form.id], false))">
              <RButton variant="ghost" size="sm">Редактировать</RButton>
            </Link>
          </div>
        </div>
      </div>

      <RCard v-else elevation="raised">
        <div class="py-10 text-center text-sm text-gray-500">
          <p>Форм для этого события пока нет.</p>
          <p class="mt-2">
            Создайте их в
            <Link :href="sameOriginHref(route('admin.tour-cabinet.lms.forms.index', lmsEvent.slug, false))" class="font-medium text-[#003274] underline hover:text-[#025ea1]">LMS Admin → формы</Link>.
          </p>
        </div>
      </RCard>
    </template>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import SearchSelect from '@/Components/SearchSelect.vue'
import { sameOriginHref } from '@/utils/sameOriginHref.js'

const props = defineProps({
  lmsEvent: { type: Object, default: null },
  forms: { type: Array, default: () => [] },
  configSlug: { type: String, default: '' },
  dashboardStandardFormSlug: { type: String, default: '' },
  allFormsOptions: { type: Array, default: () => [] },
  contestCompletionNotification: {
    type: Object,
    default: () => ({ enabled: false, subject: '', body: '' }),
  },
})

const allFormsSelectOptions = computed(() =>
  props.allFormsOptions
    .filter((opt) => opt.is_active)
    .map((opt) => ({
      slug: opt.slug,
      label: `${opt.title} (${opt.slug})`,
    })),
)

const dashboardForm = useForm({
  dashboard_standard_form_slug: props.dashboardStandardFormSlug ?? '',
})

const completionForm = useForm({
  enabled: !!props.contestCompletionNotification?.enabled,
  subject: props.contestCompletionNotification?.subject ?? '',
  body: props.contestCompletionNotification?.body ?? '',
})

watch(
  () => props.dashboardStandardFormSlug,
  (v) => {
    dashboardForm.dashboard_standard_form_slug = v ?? ''
  },
)

watch(
  () => props.contestCompletionNotification,
  (v) => {
    completionForm.enabled = !!v?.enabled
    completionForm.subject = v?.subject ?? ''
    completionForm.body = v?.body ?? ''
  },
  { deep: true },
)

function submitDashboardStandardForm() {
  dashboardForm.put(sameOriginHref(route('admin.tour-cabinet.dashboard-form.update', {}, false)), { preserveScroll: true })
}

function submitCompletionNotification() {
  completionForm.put(sameOriginHref(route('admin.tour-cabinet.contest-completion-notification.update', {}, false)), { preserveScroll: true })
}
</script>
