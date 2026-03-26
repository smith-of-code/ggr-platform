<template>
  <AdminLayout>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ vacancy ? 'Редактировать вакансию' : 'Новая вакансия' }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ vacancy ? vacancy.title : 'Заполните данные вакансии' }}</p>
      </div>
      <div class="flex gap-3">
        <button v-if="vacancy" type="button" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="showPreview = true">Предпросмотр</button>
        <Link :href="route('admin.vacancies.index')" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Назад</Link>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <div>
                <RInput v-model="form.title" label="Название вакансии *" :error="form.errors.title" />
              </div>
              <div>
                <RInput v-model="form.slug" label="Slug (URL)" :error="form.errors.slug" />
              </div>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <RInput v-model="form.company" label="Компания / предприятие" :error="form.errors.company" />
                </div>
                <div>
                  <RInput v-model="form.salary" label="Зарплата" placeholder="от 80 000 ₽" :error="form.errors.salary" />
                </div>
              </div>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Город</label>
                  <select v-model="form.city_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10">
                    <option :value="null">— Не указан —</option>
                    <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="mb-1.5 block text-sm font-semibold text-gray-700">Тип занятости</label>
                  <select v-model="form.employment_type" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10">
                    <option value="">— Не указан —</option>
                    <option value="full_time">Полная занятость</option>
                    <option value="part_time">Частичная занятость</option>
                    <option value="remote">Удалённо</option>
                    <option value="internship">Стажировка</option>
                    <option value="contract">Договор подряда</option>
                  </select>
                </div>
              </div>

              <RichTextEditor v-model="form.description" label="Описание вакансии" />
              <RichTextEditor v-model="form.responsibilities" label="Обязанности" />
              <RichTextEditor v-model="form.requirements" label="Требования" />
              <RichTextEditor v-model="form.conditions" label="Условия" />
            </div>
          </RCard>
        </div>

        <div class="space-y-6">
          <RCard elevation="raised">
            <div class="space-y-5 p-6">
              <ImageUploadCrop v-model="form.image" label="Изображение" :upload-url="route('admin.upload.image')" />
              <div>
                <RInput v-model="form.contact_email" label="Контактный email" type="email" />
              </div>
              <div>
                <RInput v-model="form.contact_phone" label="Контактный телефон" />
              </div>
              <div>
                <RInput v-model="form.position" label="Позиция сортировки" type="number" />
              </div>
              <div class="flex items-center gap-3">
                <RCheckbox v-model:checked="form.is_published" />
                <span class="text-sm font-medium text-gray-700">Опубликована</span>
              </div>
            </div>
          </RCard>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-[#003274] px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-[#025ea1] disabled:opacity-60"
          >
            {{ form.processing ? 'Сохранение...' : (vacancy ? 'Сохранить' : 'Создать') }}
          </button>
        </div>
      </div>
    </form>

    <ContentPreview
      :open="showPreview"
      :title="form.title"
      :content="form.description"
      :image="form.image"
      :meta="previewMeta"
      @close="showPreview = false"
    >
      <div v-if="form.responsibilities" class="border-t border-gray-100 px-6 py-4">
        <h4 class="mb-2 text-sm font-semibold text-gray-900">Обязанности</h4>
        <div class="prose prose-sm max-w-none text-gray-600" v-html="form.responsibilities" />
      </div>
      <div v-if="form.requirements" class="border-t border-gray-100 px-6 py-4">
        <h4 class="mb-2 text-sm font-semibold text-gray-900">Требования</h4>
        <div class="prose prose-sm max-w-none text-gray-600" v-html="form.requirements" />
      </div>
      <div v-if="form.conditions" class="border-t border-gray-100 px-6 py-4">
        <h4 class="mb-2 text-sm font-semibold text-gray-900">Условия</h4>
        <div class="prose prose-sm max-w-none text-gray-600" v-html="form.conditions" />
      </div>
    </ContentPreview>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'

const props = defineProps({ vacancy: Object, cities: Array })

const showPreview = ref(false)

const form = useForm({
  title: props.vacancy?.title ?? '',
  slug: props.vacancy?.slug ?? '',
  city_id: props.vacancy?.city_id ?? null,
  company: props.vacancy?.company ?? '',
  employment_type: props.vacancy?.employment_type ?? '',
  salary: props.vacancy?.salary ?? '',
  description: props.vacancy?.description ?? '',
  responsibilities: props.vacancy?.responsibilities ?? '',
  requirements: props.vacancy?.requirements ?? '',
  conditions: props.vacancy?.conditions ?? '',
  contact_email: props.vacancy?.contact_email ?? '',
  contact_phone: props.vacancy?.contact_phone ?? '',
  image: props.vacancy?.image ?? '',
  is_published: props.vacancy?.is_published ?? false,
  position: props.vacancy?.position ?? 0,
})

const types = { full_time: 'Полная занятость', part_time: 'Частичная', remote: 'Удалённо', internship: 'Стажировка', contract: 'Подряд' }

const previewMeta = computed(() => {
  const m = []
  const city = props.cities?.find(c => c.id === form.city_id)
  if (city) m.push(city.name)
  if (form.employment_type) m.push(types[form.employment_type] || form.employment_type)
  if (form.salary) m.push(form.salary)
  if (form.company) m.push(form.company)
  return m
})

function submit() {
  if (props.vacancy) {
    form.put(route('admin.vacancies.update', props.vacancy.id))
  } else {
    form.post(route('admin.vacancies.store'))
  }
}
</script>
