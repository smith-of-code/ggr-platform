<template>
  <AdminLayout>
    <Head :title="product ? 'Редактировать продукт' : 'Новый продукт'" />

    <div class="mb-8">
      <Link
        :href="route('admin.education-products.index')"
        class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Назад к списку
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ product ? 'Редактировать продукт' : 'Новый продукт' }}</h1>
    </div>

    <RCard elevation="raised" class="max-w-4xl">
      <form class="space-y-6" @submit.prevent="submit">
        <RInput
          v-model="form.title"
          label="Название *"
          placeholder="Название программы"
          :error="form.errors.title"
          required
          @input="onTitleInput"
        />
        <RInput
          v-model="form.slug"
          label="Slug (URL)"
          placeholder="Автоматически из названия"
          :error="form.errors.slug"
          @input="onSlugManualInput"
        />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Краткое описание</label>
          <textarea
            v-model="form.description"
            rows="4"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
          />
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
        </div>

        <div>
          <RichTextEditor
            v-model="form.content"
            label="Содержание программы"
            :upload-url="route('admin.upload.image')"
          />
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <ImageUploadCrop
          v-model="form.image"
          label="Изображение"
          :upload-url="route('admin.upload.image')"
          :error="form.errors.image"
        />

        <RInput v-model="form.duration" label="Длительность" placeholder="3 месяца" :error="form.errors.duration" />
        <RInput v-model="form.format" label="Формат" placeholder="Онлайн / очно" :error="form.errors.format" />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Целевая аудитория</label>
          <textarea
            v-model="form.target_audience"
            rows="3"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
          />
          <p v-if="form.errors.target_audience" class="mt-1 text-sm text-red-600">{{ form.errors.target_audience }}</p>
        </div>

        <RInput v-model="form.price_info" label="Информация о стоимости" placeholder="По запросу" :error="form.errors.price_info" />

        <RInput
          v-model.number="form.position"
          type="number"
          min="0"
          label="Позиция в списке"
          placeholder="0"
          :error="form.errors.position"
        />

        <RCheckbox v-model="form.is_active" label="Активен (отображается на сайте)" />

        <div class="flex gap-3 border-t border-gray-100 pt-6">
          <RButton variant="primary" type="submit" :loading="form.processing" :disabled="form.processing">
            Сохранить
          </RButton>
          <Link
            :href="route('admin.education-products.index')"
            class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
          >
            Отмена
          </Link>
        </div>
      </form>
    </RCard>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'

const props = defineProps({
  product: { type: Object, default: null },
})

let slugManuallyEdited = false

const form = useForm({
  title: props.product?.title ?? '',
  slug: props.product?.slug ?? '',
  description: props.product?.description ?? '',
  content: props.product?.content ?? '',
  image: props.product?.image ?? '',
  duration: props.product?.duration ?? '',
  format: props.product?.format ?? '',
  target_audience: props.product?.target_audience ?? '',
  price_info: props.product?.price_info ?? '',
  position: props.product?.position ?? 0,
  is_active: props.product?.is_active ?? true,
})

function transliterate(str) {
  const map = {
    а: 'a',
    б: 'b',
    в: 'v',
    г: 'g',
    д: 'd',
    е: 'e',
    ё: 'yo',
    ж: 'zh',
    з: 'z',
    и: 'i',
    й: 'y',
    к: 'k',
    л: 'l',
    м: 'm',
    н: 'n',
    о: 'o',
    п: 'p',
    р: 'r',
    с: 's',
    т: 't',
    у: 'u',
    ф: 'f',
    х: 'kh',
    ц: 'ts',
    ч: 'ch',
    ш: 'sh',
    щ: 'shch',
    ъ: '',
    ы: 'y',
    ь: '',
    э: 'e',
    ю: 'yu',
    я: 'ya',
    ' ': '-',
  }
  return str
    .toLowerCase()
    .split('')
    .map((c) => map[c] ?? c)
    .join('')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

function onTitleInput() {
  if (slugManuallyEdited || props.product) return
  form.slug = transliterate(form.title)
}

function onSlugManualInput() {
  slugManuallyEdited = true
}

function submit() {
  form.transform((data) => ({
    ...data,
    position:
      data.position === '' || data.position == null || Number.isNaN(Number(data.position))
        ? 0
        : Number(data.position),
  }))
  if (props.product) {
    form.put(route('admin.education-products.update', props.product.id))
  } else {
    form.post(route('admin.education-products.store'))
  }
}
</script>
