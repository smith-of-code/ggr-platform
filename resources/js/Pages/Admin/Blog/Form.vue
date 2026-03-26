<template>
  <AdminLayout>
    <Head :title="post ? 'Редактировать статью' : 'Новая статья'" />

    <div class="mb-8">
      <Link
        :href="route('admin.blog.index')"
        class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Назад к списку
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ post ? 'Редактировать статью' : 'Новая статья' }}</h1>
    </div>

    <RCard elevation="raised" class="max-w-4xl">
      <form @submit.prevent="submit" class="space-y-6">
        <RInput
          v-model="form.title"
          label="Заголовок *"
          placeholder="Заголовок статьи"
          :error="form.errors.title"
          required
          @input="onTitleInput"
        />
        <RInput
          v-model="form.slug"
          label="Slug (URL)"
          placeholder="Автоматически из заголовка"
          :error="form.errors.slug"
          @input="onSlugManualInput"
        />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Категория *</label>
          <select
            v-model="form.category"
            required
            class="w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          >
            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
          </select>
          <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Краткое описание</label>
          <textarea
            v-model="form.excerpt"
            rows="3"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
            placeholder="Анонс для списка и соцсетей"
          />
          <p v-if="form.errors.excerpt" class="mt-1 text-sm text-red-600">{{ form.errors.excerpt }}</p>
        </div>

        <div>
          <RichTextEditor
            v-model="form.content"
            label="Содержание *"
          />
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <div>
          <RInput v-model="form.image" type="url" label="URL изображения" placeholder="https://..." :error="form.errors.image" />
          <div v-if="form.image" class="mt-3 overflow-hidden rounded-xl border border-gray-200">
            <img :src="form.image" class="h-48 w-full object-cover" alt="" @error="form.image = ''" />
          </div>
        </div>

        <RInput
          v-model="tagsJoined"
          label="Теги"
          placeholder="через запятую: туры, атомград"
          :error="form.errors.tags"
        />

        <RCheckbox v-model="form.is_published" label="Опубликовано" />

        <div class="flex gap-3 border-t border-gray-100 pt-6">
          <RButton variant="primary" type="submit" :loading="form.processing" :disabled="form.processing">
            Сохранить
          </RButton>
          <Link
            :href="route('admin.blog.index')"
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
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'

const props = defineProps({
  post: { type: Object, default: null },
  categories: { type: Object, required: true },
})

let slugManuallyEdited = false

const form = useForm({
  title: props.post?.title ?? '',
  slug: props.post?.slug ?? '',
  category: props.post?.category ?? Object.keys(props.categories)[0] ?? 'news',
  excerpt: props.post?.excerpt ?? '',
  content: props.post?.content ?? '',
  image: props.post?.image ?? '',
  tags: props.post?.tags?.length ? [...props.post.tags] : [],
  is_published: props.post?.is_published ?? false,
})

const tagsJoined = computed({
  get() {
    return (form.tags || []).join(', ')
  },
  set(val) {
    form.tags = String(val ?? '')
      .split(',')
      .map((s) => s.trim())
      .filter(Boolean)
  },
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
  if (slugManuallyEdited || props.post) return
  form.slug = transliterate(form.title)
}

function onSlugManualInput() {
  slugManuallyEdited = true
}

function submit() {
  if (props.post) {
    form.put(route('admin.blog.update', props.post.id))
  } else {
    form.post(route('admin.blog.store'))
  }
}
</script>
