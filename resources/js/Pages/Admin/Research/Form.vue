<template>
  <AdminLayout>
    <Head :title="research ? 'Редактировать исследование' : 'Новое исследование'" />

    <div class="mb-8">
      <Link
        :href="route('admin.research.index')"
        class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Назад к списку
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">
        {{ research ? 'Редактировать исследование' : 'Новое исследование' }}
      </h1>
    </div>

    <RCard elevation="raised" class="max-w-4xl">
      <form class="space-y-6" @submit.prevent="submit">
        <RInput
          v-model="form.title"
          label="Название *"
          placeholder="Название исследования"
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
          <label class="mb-2 block text-sm font-semibold text-gray-700">Город</label>
          <select
            v-model="form.city_id"
            class="w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          >
            <option value="">Не выбран</option>
            <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="form.errors.city_id" class="mt-1 text-sm text-red-600">{{ form.errors.city_id }}</p>
        </div>

        <RInput v-model="form.year" type="number" label="Год" placeholder="2024" :error="form.errors.year" />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Методология</label>
          <textarea
            v-model="form.methodology"
            rows="4"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
            placeholder="Описание методики"
          />
          <p v-if="form.errors.methodology" class="mt-1 text-sm text-red-600">{{ form.errors.methodology }}</p>
        </div>

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
          <label class="mb-2 block text-sm font-semibold text-gray-700">Содержание</label>
          <textarea
            v-model="form.content"
            rows="16"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
            placeholder="Полный текст"
          />
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Итоги / выводы</label>
          <textarea
            v-model="form.results_summary"
            rows="4"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
          />
          <p v-if="form.errors.results_summary" class="mt-1 text-sm text-red-600">{{ form.errors.results_summary }}</p>
        </div>

        <RInput v-model="form.image" type="url" label="URL изображения" placeholder="https://..." :error="form.errors.image" />
        <div v-if="form.image" class="overflow-hidden rounded-xl border border-gray-200">
          <img :src="form.image" class="h-48 w-full object-cover" alt="" @error="form.image = ''" />
        </div>

        <RInput
          v-model="form.pdf_file"
          type="url"
          label="URL PDF-файла"
          placeholder="https://..."
          :error="form.errors.pdf_file"
        />

        <RCheckbox v-model="form.is_published" label="Опубликовано" />

        <div class="flex gap-3 border-t border-gray-100 pt-6">
          <RButton variant="primary" type="submit" :loading="form.processing" :disabled="form.processing">
            Сохранить
          </RButton>
          <Link
            :href="route('admin.research.index')"
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

const props = defineProps({
  research: { type: Object, default: null },
  cities: { type: Array, default: () => [] },
})

let slugManuallyEdited = false

const form = useForm({
  title: props.research?.title ?? '',
  slug: props.research?.slug ?? '',
  city_id: props.research?.city_id ?? '',
  year: props.research?.year ?? '',
  methodology: props.research?.methodology ?? '',
  description: props.research?.description ?? '',
  content: props.research?.content ?? '',
  results_summary: props.research?.results_summary ?? '',
  image: props.research?.image ?? '',
  pdf_file: props.research?.pdf_file ?? '',
  is_published: props.research?.is_published ?? false,
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
  if (slugManuallyEdited || props.research) return
  form.slug = transliterate(form.title)
}

function onSlugManualInput() {
  slugManuallyEdited = true
}

function submit() {
  form.transform((data) => ({
    ...data,
    city_id: data.city_id === '' || data.city_id == null ? null : Number(data.city_id),
    year:
      data.year === '' || data.year == null || Number.isNaN(Number(data.year))
        ? null
        : Number(data.year),
  }))
  if (props.research) {
    form.put(route('admin.research.update', props.research.id))
  } else {
    form.post(route('admin.research.store'))
  }
}
</script>