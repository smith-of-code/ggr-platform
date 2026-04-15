<template>
  <AdminLayout>
    <Head title="Формы ЛК туров" />
    <div class="mb-4">
      <Link :href="route('admin.tour-cabinet.index')" class="text-sm font-medium text-[#003274] hover:text-[#025ea1]">
        ← ЛК туров
      </Link>
    </div>
    <div v-if="page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ page.props.flash.error }}
    </div>

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Формы личного кабинета туров</h1>
        <p class="mt-1 text-sm text-gray-500">
          Те же анкеты, что в LMS Admin для события
          <span class="font-mono text-gray-700">{{ configSlug }}</span>
          (настройка: <code class="rounded bg-gray-100 px-1 text-xs">TOUR_CABINET_LMS_EVENT_SLUG</code>).
        </p>
      </div>
      <div v-if="lmsEvent" class="flex shrink-0 flex-wrap gap-2">
        <Link
          :href="route('lms.admin.forms.index', lmsEvent.slug)"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
          Все формы в LMS Admin
        </Link>
        <Link
          :href="route('lms.admin.forms.create', lmsEvent.slug)"
          class="inline-flex items-center justify-center rounded-lg bg-[#003274] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#025ea1]"
        >
          Создать форму
        </Link>
      </div>
    </div>

    <RCard v-if="lmsEvent" class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Конкурс, этап 1 — какие формы открывать</h2>
      <form class="mt-6 space-y-5" @submit.prevent="submitSlugs">
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <SearchSelect
              :model-value="slugForm.contest_stage1_form_slug_standard || null"
              label="Стандартная анкета (города без «больше данных»)"
              :options="formSelectOptions"
              value-key="slug"
              label-key="label"
              placeholder="Выберите форму"
              clear-label="—"
              :searchable="formSelectOptions.length > 5"
              :error="slugForm.errors.contest_stage1_form_slug_standard || ''"
              :hint="`Итог: «${contestFormSlugsEffective.standard || '—'}»`"
              @update:model-value="(v) => { slugForm.contest_stage1_form_slug_standard = v ?? '' }"
            />
          </div>
          <div>
            <SearchSelect
              :model-value="slugForm.contest_stage1_form_slug_more_data || null"
              label="Анкета «нужно больше данных»"
              :options="formSelectOptions"
              value-key="slug"
              label-key="label"
              placeholder="Выберите форму"
              clear-label="—"
              :searchable="formSelectOptions.length > 5"
              :error="slugForm.errors.contest_stage1_form_slug_more_data || ''"
              :hint="`Итог: «${contestFormSlugsEffective.more_data || '—'}»`"
              @update:model-value="(v) => { slugForm.contest_stage1_form_slug_more_data = v ?? '' }"
            />
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="slugForm.processing" :disabled="slugForm.processing">
            Сохранить привязку форм
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
            <Link :href="route('forms.public.show', form.slug)" class="flex-1 min-w-[7rem]">
              <RButton variant="outline" size="sm" block>Публичная страница</RButton>
            </Link>
            <Link :href="route('lms.admin.forms.stats', [lmsEvent.slug, form.id])">
              <RButton variant="outline" size="sm">Статистика</RButton>
            </Link>
            <Link :href="route('lms.admin.forms.edit', [lmsEvent.slug, form.id])">
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
            <Link :href="route('lms.admin.forms.index', lmsEvent.slug)" class="font-medium text-[#003274] underline hover:text-[#025ea1]">LMS Admin → формы</Link>.
          </p>
        </div>
      </RCard>
    </template>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'

const page = usePage()

const props = defineProps({
  lmsEvent: { type: Object, default: null },
  forms: { type: Array, default: () => [] },
  configSlug: { type: String, default: '' },
  contestFormSlugOverrides: {
    type: Object,
    default: () => ({ standard: '', more_data: '' }),
  },
  contestFormSlugsEffective: {
    type: Object,
    default: () => ({ standard: null, more_data: null }),
  },
  formOptions: { type: Array, default: () => [] },
})

const formSelectOptions = computed(() =>
  props.formOptions.map((opt) => ({
    slug: opt.slug,
    label: `${opt.title} (${opt.slug})${opt.is_active ? '' : ' — неактивна'}`,
  })),
)

const slugForm = useForm({
  contest_stage1_form_slug_standard: props.contestFormSlugOverrides.standard ?? '',
  contest_stage1_form_slug_more_data: props.contestFormSlugOverrides.more_data ?? '',
})

function submitSlugs() {
  slugForm.put(route('admin.tour-cabinet.forms.contest-form-slugs.update'), { preserveScroll: true })
}
</script>
