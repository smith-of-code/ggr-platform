<template>
  <LmsAdminLayout :event="event">
    <div
      v-if="showPortalTourCabinetBanner"
      class="mb-6 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-950"
    >
      <p class="font-medium">Вы в конструкторе форм из настроек ЛК туров портала.</p>
      <p class="mt-1 text-sky-900/90">
        Чтобы вернуться к разделу «Формы и этап 1» на портале, используйте ссылку ниже или кнопку «В настройки ЛК туров» внизу бокового меню.
      </p>
      <Link
        :href="`${route('admin.tour-cabinet.index', {}, false)}#tour-cabinet-admin-forms`"
        class="mt-3 inline-flex font-semibold text-[#003274] underline decoration-2 underline-offset-2 hover:text-[#025ea1]"
      >
        В настройки ЛК туров (портал)
      </Link>
    </div>

    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Формы и опросы</h1>
        <p class="mt-1 text-sm text-gray-500">Конструктор анкет, опросов и форм обратной связи</p>
      </div>
      <Link :href="route(routeNames.create, event.slug, false)">
        <RButton variant="primary">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          </template>
          Создать форму
        </RButton>
      </Link>
    </div>

    <div v-if="forms?.data?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="form in forms.data"
        :key="form.id"
        class="group rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-rosatom-300 hover:shadow-md"
      >
        <div class="mb-3 flex items-start justify-between">
          <div>
            <h3 class="font-bold text-gray-900">{{ form.title }}</h3>
            <p v-if="form.description" class="mt-1 text-xs text-gray-400 line-clamp-2">{{ form.description }}</p>
          </div>
          <RBadge :variant="form.is_active ? 'success' : 'neutral'" size="sm">
            {{ form.is_active ? 'Активна' : 'Неактивна' }}
          </RBadge>
        </div>

        <div class="mb-4 flex items-center gap-4 text-xs text-gray-400">
          <span class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            {{ form.submissions_count }} ответов
          </span>
          <span v-if="form.is_anonymous" class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
            Анонимная
          </span>
          <span v-if="form.allow_embed" class="flex items-center gap-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
            Embed
          </span>
        </div>

        <div class="flex items-center gap-2">
          <Link :href="route(routeNames.stats, [event.slug, form.id], false)" class="min-w-0 flex-1">
            <RButton variant="outline" size="sm" block>Статистика</RButton>
          </Link>
          <Link :href="route(routeNames.edit, [event.slug, form.id], false)">
            <RButton variant="ghost" size="sm">Редактировать</RButton>
          </Link>
          <RButton
            variant="ghost"
            size="sm"
            icon-only
            :loading="duplicatingFormId === form.id"
            :disabled="duplicatingFormId === form.id || deletingFormId === form.id"
            title="Дублировать"
            aria-label="Дублировать"
            @click="duplicateForm(form)"
          >
            <template #icon>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
            </template>
          </RButton>
          <RButton
            variant="danger"
            size="sm"
            icon-only
            :loading="deletingFormId === form.id"
            :disabled="duplicatingFormId === form.id || deletingFormId === form.id"
            title="Удалить"
            aria-label="Удалить"
            @click="deleteForm(form)"
          >
            <template #icon>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </template>
          </RButton>
        </div>
      </div>
    </div>

    <RCard v-else>
      <div class="py-12 text-center text-sm text-gray-400">
        Форм пока нет. Создайте первую форму или опрос.
      </div>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import { defaultLmsAdminFormRouteNames } from '@/constants/lmsAdminFormRoutes.js'

const props = defineProps({
  event: Object,
  forms: Object,
  lmsFormsRouteNames: { type: Object, default: null },
})

const page = usePage()
const showPortalTourCabinetBanner = computed(() => (page.url || '').startsWith('/admin/tour-cabinet/lms/'))

const routeNames = computed(() => ({ ...defaultLmsAdminFormRouteNames, ...props.lmsFormsRouteNames }))

const duplicatingFormId = ref(null)
const deletingFormId = ref(null)

function duplicateForm(form) {
  if (duplicatingFormId.value === form.id) return
  duplicatingFormId.value = form.id
  router.post(
    route(routeNames.value.duplicate, [props.event.slug, form.id], false),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        duplicatingFormId.value = null
      },
    },
  )
}

function deleteForm(form) {
  if (deletingFormId.value === form.id) return
  const submissions = Number(form.submissions_count ?? 0)
  const message = submissions > 0
    ? `Удалить форму «${form.title}»? Будут удалены все ответы (${submissions}).`
    : `Удалить форму «${form.title}»?`
  if (!confirm(message)) return

  deletingFormId.value = form.id
  router.delete(
    route(routeNames.value.destroy, [props.event.slug, form.id], false),
    {
      preserveScroll: true,
      onFinish: () => {
        deletingFormId.value = null
      },
    },
  )
}
</script>
