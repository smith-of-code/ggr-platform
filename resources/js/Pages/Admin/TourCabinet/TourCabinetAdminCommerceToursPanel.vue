<template>
  <div>
    <RCard id="tour-cabinet-admin-commerce-tours" class="mb-8 scroll-mt-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Блок «Коммерческие туры»</h2>
      <p class="mt-2 text-sm text-gray-600">
        Отдельный блок на дашборде ЛК туров под блоком «Конкурс». Включается флагом ниже. Текст экрана этапа 3 (заголовок и тело) показывается участнику после прохождения анкеты доп. данных.
      </p>

      <form class="mt-6 space-y-5" @submit.prevent="submitStage3">
        <label class="flex items-start gap-3">
          <input
            type="checkbox"
            class="mt-1 h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
            :checked="stage3Form.enabled"
            @change="(e) => { stage3Form.enabled = e.target.checked }"
          />
          <span class="text-sm text-gray-800">
            Блок активен
            <span class="block text-xs text-gray-500">Снимите галочку, чтобы временно скрыть блок у всех пользователей.</span>
          </span>
        </label>
        <p v-if="stage3Form.errors.enabled" class="-mt-3 text-xs text-red-600">{{ stage3Form.errors.enabled }}</p>

        <div>
          <label class="block text-sm font-medium text-gray-700">Заголовок этапа 3</label>
          <input
            v-model="stage3Form.subject"
            type="text"
            maxlength="255"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
            :class="stage3Form.errors.subject ? 'border-red-400' : ''"
          />
          <p v-if="stage3Form.errors.subject" class="mt-1 text-xs text-red-600">{{ stage3Form.errors.subject }}</p>
          <p v-else class="mt-1 text-xs text-gray-500">Если оставить пустым — будет использован вариант по умолчанию.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Текст этапа 3</label>
          <textarea
            v-model="stage3Form.body"
            rows="6"
            maxlength="20000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#003274] focus:ring-[#003274] sm:text-sm"
            :class="stage3Form.errors.body ? 'border-red-400' : ''"
          />
          <p v-if="stage3Form.errors.body" class="mt-1 text-xs text-red-600">{{ stage3Form.errors.body }}</p>
          <p v-else class="mt-1 text-xs text-gray-500">Поддерживаются переносы строк. HTML не интерпретируется — только текст.</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="stage3Form.processing" :disabled="stage3Form.processing">
            Сохранить
          </RButton>
        </div>
      </form>
    </RCard>

    <RCard class="mb-8" elevation="raised">
      <h2 class="text-lg font-semibold text-gray-900">Анкета этапа 2 — какие формы открывать по городам</h2>
      <p class="mt-2 text-sm text-gray-600">
        Для каждого города задайте slug LMS-формы, которая откроется на этапе 2 после выбора тура. Если города нет в списке — добавьте его в каталог городов и привяжите хотя бы один активный тур.
      </p>

      <div v-if="cityForms.length" class="mt-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead>
            <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
              <th class="px-3 py-2">Город</th>
              <th class="px-3 py-2">Форма (slug)</th>
              <th class="px-3 py-2 text-right">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="row in cityForms" :key="row.id">
              <td class="px-3 py-3 align-top text-gray-900">
                {{ row.city_name || `#${row.city_id}` }}
              </td>
              <td class="px-3 py-3 align-top">
                <select
                  class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
                  :value="editSlug[row.id] ?? row.lms_form_slug"
                  @change="(e) => { editSlug[row.id] = e.target.value }"
                >
                  <option v-if="!availableForms.length" value="" disabled>Нет активных форм</option>
                  <option v-for="opt in availableForms" :key="opt.slug" :value="opt.slug">
                    {{ opt.title }} ({{ opt.slug }})
                  </option>
                </select>
              </td>
              <td class="px-3 py-3 text-right align-top">
                <div class="inline-flex flex-wrap gap-2">
                  <RButton
                    type="button"
                    variant="primary"
                    size="sm"
                    :disabled="(editSlug[row.id] ?? row.lms_form_slug) === row.lms_form_slug"
                    @click="updateRow(row)"
                  >
                    Сохранить
                  </RButton>
                  <RButton type="button" variant="outline" size="sm" @click="destroyRow(row)">
                    Удалить
                  </RButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="mt-6 text-sm text-gray-500">Привязок пока нет. Добавьте первую с помощью формы ниже.</p>

      <form class="mt-8 space-y-4 rounded-xl border border-dashed border-gray-300 p-4" @submit.prevent="submitCreate">
        <h3 class="text-sm font-semibold text-gray-800">Добавить привязку</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Город</label>
            <select
              v-model="createForm.city_id"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
              :class="createForm.errors.city_id ? 'border-red-400' : ''"
            >
              <option :value="null" disabled>Выберите город</option>
              <option
                v-for="opt in unboundCityOptions"
                :key="opt.id"
                :value="opt.id"
              >
                {{ opt.name }}
              </option>
            </select>
            <p v-if="createForm.errors.city_id" class="mt-1 text-xs text-red-600">{{ createForm.errors.city_id }}</p>
            <p v-else class="mt-1 text-xs text-gray-500">Список — города с хотя бы одним активным туром, без существующей привязки.</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Форма (slug)</label>
            <select
              v-model="createForm.lms_form_slug"
              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#003274] focus:ring-[#003274]"
              :class="createForm.errors.lms_form_slug ? 'border-red-400' : ''"
            >
              <option value="" disabled>Выберите форму</option>
              <option v-for="opt in availableForms" :key="opt.slug" :value="opt.slug">
                {{ opt.title }} ({{ opt.slug }})
              </option>
            </select>
            <p v-if="createForm.errors.lms_form_slug" class="mt-1 text-xs text-red-600">{{ createForm.errors.lms_form_slug }}</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-3">
          <RButton type="submit" variant="primary" :loading="createForm.processing" :disabled="createForm.processing || !createForm.city_id || !createForm.lms_form_slug">
            Добавить
          </RButton>
        </div>
      </form>
    </RCard>
  </div>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  enabled: { type: Boolean, default: false },
  stage3: {
    type: Object,
    default: () => ({ subject: '', body: '' }),
  },
  cityForms: { type: Array, default: () => [] },
  availableCities: { type: Array, default: () => [] },
  availableForms: { type: Array, default: () => [] },
})

const stage3Form = useForm({
  enabled: !!props.enabled,
  subject: props.stage3?.subject ?? '',
  body: props.stage3?.body ?? '',
})

watch(
  () => [props.enabled, props.stage3],
  () => {
    stage3Form.enabled = !!props.enabled
    stage3Form.subject = props.stage3?.subject ?? ''
    stage3Form.body = props.stage3?.body ?? ''
  },
  { deep: true },
)

function submitStage3() {
  stage3Form.put(route('admin.tour-cabinet.commerce-tours.stage3-notification.update'), {
    preserveScroll: true,
  })
}

const editSlug = reactive({})

const createForm = useForm({
  city_id: null,
  lms_form_slug: '',
})

const boundCityIds = computed(() => new Set(props.cityForms.map((r) => r.city_id)))
const unboundCityOptions = computed(() => props.availableCities.filter((c) => !boundCityIds.value.has(c.id)))

function submitCreate() {
  createForm.post(route('admin.tour-cabinet.commerce-tours.city-forms.store'), {
    preserveScroll: true,
    onSuccess: () => {
      createForm.reset('city_id', 'lms_form_slug')
      createForm.city_id = null
      createForm.lms_form_slug = ''
    },
  })
}

function updateRow(row) {
  const newSlug = editSlug[row.id] ?? row.lms_form_slug
  if (newSlug === row.lms_form_slug) {
    return
  }
  router.patch(
    route('admin.tour-cabinet.commerce-tours.city-forms.update', row.id),
    { lms_form_slug: newSlug },
    {
      preserveScroll: true,
      onSuccess: () => {
        delete editSlug[row.id]
      },
    },
  )
}

function destroyRow(row) {
  if (!window.confirm(`Удалить привязку «${row.city_name || `#${row.city_id}`}» → ${row.lms_form_slug}?`)) {
    return
  }
  router.delete(route('admin.tour-cabinet.commerce-tours.city-forms.destroy', row.id), {
    preserveScroll: true,
  })
}
</script>
