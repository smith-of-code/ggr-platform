<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">{{ grant ? 'Редактирование возможности' : 'Создание возможности' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <RCard elevation="raised">
        <div class="space-y-5">
          <RInput v-model="form.title" label="Название" :error="form.errors.title" />

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Тип</label>
              <select
                v-model="form.type"
                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              >
                <option v-for="(label, key) in typeOptions" :key="key" :value="key">{{ label }}</option>
              </select>
              <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
            </div>
            <MultiSelect
              v-model="form.city"
              :options="cityOptions"
              value-key="value"
              label-key="label"
              label="Города"
              placeholder="Все города"
              :error="form.errors.city"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-500">Описание</label>
            <RichTextEditor v-model="form.description" />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <RInput v-model="form.application_start" label="Начало приёма заявок" type="date" :error="form.errors.application_start" />
            <RInput v-model="form.application_end" label="Окончание приёма заявок" type="date" :error="form.errors.application_end" />
          </div>

          <div class="flex items-center gap-2">
            <RCheckbox v-model="form.is_active" label="Активен" />
          </div>
        </div>
      </RCard>

      <!-- Documents -->
      <RCard elevation="raised">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Документы для скачивания</h2>
        <p class="mb-4 text-sm text-gray-500">Документы, которые участники смогут скачать и ознакомиться.</p>

        <div v-if="existingDocs.length" class="mb-4 space-y-2">
          <div
            v-for="doc in existingDocs"
            :key="doc.id"
            class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3"
          >
            <span class="truncate text-sm text-gray-700">{{ doc.original_name }}</span>
            <button type="button" class="text-sm text-red-500 hover:underline" @click="removeExistingDoc(doc.id)">
              Удалить
            </button>
          </div>
        </div>

        <div>
          <input
            type="file"
            multiple
            ref="fileInput"
            class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-200 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-300"
            @change="onFilesChange"
          />
          <p class="mt-1 text-xs text-gray-400">PDF, DOC, DOCX, JPG, PNG — до 10 МБ каждый</p>
        </div>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" :disabled="form.processing" :loading="form.processing">
          {{ grant ? 'Сохранить' : 'Создать' }}
        </RButton>
        <Link :href="route('lms.admin.grants.index', event.slug)">
          <RButton variant="outline" type="button">Отмена</RButton>
        </Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import MultiSelect from '@/Components/MultiSelect.vue'

const props = defineProps({
  event: Object,
  grant: { type: Object, default: null },
})

const typeOptions = {
  grant: 'Грант',
  subsidy: 'Субсидия',
  credit: 'Кредит',
}

const CITY_NAMES = [
  'Ангарск', 'Байкальск', 'Балаково', 'Билибино', 'Волгодонск',
  'Глазов', 'Десногорск', 'Димитровград', 'Железногорск',
  'Заречный (Пензенская область)', 'Заречный (Свердловская область)',
  'Зеленогорск', 'Краснокаменск', 'Курчатов', 'Лесной', 'Неман',
  'Нововоронеж', 'Новоуральск', 'Обнинск', 'Озёрск', 'Певек',
  'Полярные Зори', 'Саров', 'Северск', 'Снежинск', 'Советск',
  'Сосновый Бор', 'Трёхгорный', 'Удомля', 'Усолье-Сибирское', 'Электросталь',
]
const cityOptions = CITY_NAMES.map(name => ({ value: name, label: name }))

const form = useForm({
  title: props.grant?.title ?? '',
  type: props.grant?.type ?? 'grant',
  city: props.grant?.city ?? [],
  description: props.grant?.description ?? '',
  application_start: props.grant?.application_start?.substring(0, 10) ?? '',
  application_end: props.grant?.application_end?.substring(0, 10) ?? '',
  is_active: props.grant?.is_active ?? true,
  keep_document_ids: (props.grant?.documents || []).map(d => d.id),
  new_documents: [],
})

const existingDocs = ref([...(props.grant?.documents || [])])

function removeExistingDoc(id) {
  existingDocs.value = existingDocs.value.filter(d => d.id !== id)
  form.keep_document_ids = existingDocs.value.map(d => d.id)
}

function onFilesChange(e) {
  form.new_documents = Array.from(e.target.files || [])
}

function submit() {
  const routeName = props.grant ? 'lms.admin.grants.update' : 'lms.admin.grants.store'
  const params = props.grant ? [props.event.slug, props.grant.id] : [props.event.slug]

  if (props.grant) {
    form.transform((data) => ({
      ...data,
      _method: 'put',
    })).post(route(routeName, params), { forceFormData: true })
  } else {
    form.post(route(routeName, params), { forceFormData: true })
  }
}
</script>
