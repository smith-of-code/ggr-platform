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
            :upload-url="route('admin.upload.image')"
            :media-picker-url="route('admin.media.index')" collection="blog" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
          />
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <ImageUploadCrop
          v-model="form.image"
          label="Изображение статьи"
          :upload-url="route('admin.upload.image')"
          :media-picker-url="route('admin.media.index')" collection="blog" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
          :error="form.errors.image"
        />

        <RInput
          v-model="tagsJoined"
          label="Теги"
          placeholder="через запятую: туры, атомград"
          :error="form.errors.tags"
        />

        <!-- Видео -->
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Видео (YouTube / RuTube)</label>
          <div v-for="(video, vi) in form.videos" :key="vi" class="mb-2 flex items-center gap-2">
            <input
              v-model="form.videos[vi]"
              type="url"
              placeholder="https://www.youtube.com/watch?v=..."
              class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
            />
            <button type="button" @click="removeVideo(vi)" class="shrink-0 rounded-lg p-2 text-red-400 transition hover:bg-red-50 hover:text-red-600">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <button type="button" @click="addVideo" class="mt-1 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-[#003274] transition hover:bg-[#003274]/5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Добавить видео
          </button>
          <p v-if="form.errors.videos" class="mt-1 text-sm text-red-600">{{ form.errors.videos }}</p>
        </div>

        <RCheckbox v-model="form.is_published" label="Опубликовано" />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Дата и время публикации</label>
          <input
            v-model="form.published_at"
            type="datetime-local"
            :disabled="!form.is_published"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003274]/10 disabled:cursor-not-allowed disabled:opacity-50"
          />
          <p class="mt-1 text-xs text-gray-500">Активно для опубликованных записей. Если поле пустое: при создании подставится текущее время, при редактировании сохранится прежняя дата публикации.</p>
          <p v-if="form.errors.published_at" class="mt-1 text-sm text-red-600">{{ form.errors.published_at }}</p>
        </div>

        <div class="flex gap-3 border-t border-gray-100 pt-6">
          <RButton variant="primary" type="submit" :loading="form.processing" :disabled="form.processing">
            Сохранить
          </RButton>
          <button type="button" class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="showPreview = true">
            <span class="flex items-center gap-1.5">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
              Предпросмотр
            </span>
          </button>
          <Link
            :href="route('admin.blog.index')"
            class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
          >
            Отмена
          </Link>
        </div>
      </form>
    </RCard>

    <ContentPreview
      :open="showPreview"
      :title="form.title"
      :description="form.excerpt"
      :content="form.content"
      :image="form.image"
      :meta="[
        { label: categories[form.category] || form.category, class: 'bg-blue-50 text-blue-700' },
        ...(form.tags || []).map(t => ({ label: t })),
      ]"
      @close="showPreview = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'

const props = defineProps({
  post: { type: Object, default: null },
  categories: { type: Object, required: true },
})

const showPreview = ref(false)
const mediaEntityType = 'App\\Models\\Post'
const mediaEntityId = props.post?.id || null

let slugManuallyEdited = false

function toDatetimeLocal(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const form = useForm({
  title: props.post?.title ?? '',
  slug: props.post?.slug ?? '',
  category: props.post?.category ?? Object.keys(props.categories)[0] ?? 'news',
  excerpt: props.post?.excerpt ?? '',
  content: props.post?.content ?? '',
  image: props.post?.image ?? '',
  tags: props.post?.tags?.length ? [...props.post.tags] : [],
  videos: props.post?.videos?.length ? [...props.post.videos] : [],
  is_published: props.post?.is_published ?? false,
  published_at: props.post?.published_at ? toDatetimeLocal(props.post.published_at) : '',
})

watch(
  () => form.is_published,
  (pub) => {
    if (pub && !form.published_at) {
      form.published_at = toDatetimeLocal(new Date().toISOString())
    }
  }
)

function addVideo() {
  form.videos = [...form.videos, '']
}

function removeVideo(index) {
  form.videos = form.videos.filter((_, i) => i !== index)
}

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
