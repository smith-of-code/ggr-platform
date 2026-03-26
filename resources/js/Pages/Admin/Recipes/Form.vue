<template>
  <AdminLayout>
    <Head :title="recipe ? 'Редактировать рецепт' : 'Новый рецепт'" />

    <div class="mb-8">
      <Link
        :href="route('admin.recipes.index')"
        class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Назад к списку
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ recipe ? 'Редактировать рецепт' : 'Новый рецепт' }}</h1>
    </div>

    <RCard elevation="raised" class="max-w-4xl">
      <form class="space-y-6" @submit.prevent="submit">
        <RInput
          v-model="form.title"
          label="Название *"
          placeholder="Название блюда"
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

        <RInput v-model="form.cooking_time" label="Время приготовления" placeholder="45 мин" :error="form.errors.cooking_time" />

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Сложность *</label>
          <select
            v-model="form.difficulty"
            required
            class="w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
          >
            <option value="easy">Лёгкая</option>
            <option value="medium">Средняя</option>
            <option value="hard">Сложная</option>
          </select>
          <p v-if="form.errors.difficulty" class="mt-1 text-sm text-red-600">{{ form.errors.difficulty }}</p>
        </div>

        <RInput
          v-model.number="form.servings"
          type="number"
          min="1"
          max="999"
          label="Порции"
          placeholder="4"
          :error="form.errors.servings"
        />

        <div>
          <div class="mb-2 flex items-center justify-between">
            <label class="text-sm font-semibold text-gray-700">Ингредиенты</label>
            <button
              type="button"
              class="text-sm font-medium text-[#003274] transition hover:text-[#025ea1]"
              @click="addIngredientRow"
            >
              + Добавить строку
            </button>
          </div>
          <div class="space-y-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4">
            <div v-for="(row, idx) in form.ingredients" :key="idx" class="flex flex-wrap items-end gap-3">
              <div class="min-w-0 flex-1">
                <label class="mb-1 block text-xs text-gray-500">Название</label>
                <input
                  v-model="row.name"
                  type="text"
                  class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
                  placeholder="Продукт"
                />
              </div>
              <div class="w-32 shrink-0">
                <label class="mb-1 block text-xs text-gray-500">Количество</label>
                <input
                  v-model="row.amount"
                  type="text"
                  class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/10"
                  placeholder="200 г"
                />
              </div>
              <button
                type="button"
                class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                title="Удалить строку"
                :disabled="form.ingredients.length <= 1"
                @click="removeIngredientRow(idx)"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          <p v-if="form.errors.ingredients" class="mt-1 text-sm text-red-600">{{ form.errors.ingredients }}</p>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">Краткое описание</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
          />
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
        </div>

        <div>
          <RichTextEditor
            v-model="form.content"
            label="Пошаговый рецепт"
            :upload-url="route('admin.upload.image')"
          />
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <ImageUploadCrop
          v-model="form.image"
          label="Фото блюда"
          :upload-url="route('admin.upload.image')"
          :aspect-ratio="4 / 3"
          :error="form.errors.image"
        />

        <RCheckbox v-model="form.is_published" label="Опубликовано" />

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
            :href="route('admin.recipes.index')"
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
      :description="form.description"
      :content="form.content"
      :image="form.image"
      :meta="[
        form.cooking_time ? { label: form.cooking_time, class: 'bg-orange-50 text-orange-700' } : null,
        { label: { easy: 'Лёгкая', medium: 'Средняя', hard: 'Сложная' }[form.difficulty] || form.difficulty, class: 'bg-purple-50 text-purple-700' },
        form.servings ? { label: `${form.servings} порций`, class: 'bg-blue-50 text-blue-700' } : null,
      ].filter(Boolean)"
      @close="showPreview = false"
    >
      <div v-if="filteredIngredients().length" class="mb-6 rounded-xl border border-gray-200 p-4">
        <p class="mb-3 text-sm font-bold text-gray-900">Ингредиенты</p>
        <ul class="space-y-1.5">
          <li v-for="(ing, i) in filteredIngredients()" :key="i" class="flex items-center justify-between text-sm">
            <span class="text-gray-700">{{ ing.name }}</span>
            <span class="font-medium text-gray-500">{{ ing.amount }}</span>
          </li>
        </ul>
      </div>
    </ContentPreview>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'

const props = defineProps({
  recipe: { type: Object, default: null },
  cities: { type: Array, default: () => [] },
})

const showPreview = ref(false)
let slugManuallyEdited = false

function normalizeIngredients(raw) {
  if (!raw?.length) {
    return [{ name: '', amount: '' }]
  }
  return raw.map((item) =>
    typeof item === 'string'
      ? { name: item, amount: '' }
      : { name: item?.name ?? '', amount: item?.amount ?? '' },
  )
}

const form = useForm({
  title: props.recipe?.title ?? '',
  slug: props.recipe?.slug ?? '',
  city_id: props.recipe?.city_id ?? '',
  cooking_time: props.recipe?.cooking_time ?? '',
  difficulty: props.recipe?.difficulty ?? 'easy',
  servings: props.recipe?.servings ?? '',
  ingredients: normalizeIngredients(props.recipe?.ingredients),
  description: props.recipe?.description ?? '',
  content: props.recipe?.content ?? '',
  image: props.recipe?.image ?? '',
  is_published: props.recipe?.is_published ?? false,
})

function addIngredientRow() {
  form.ingredients.push({ name: '', amount: '' })
}

function removeIngredientRow(idx) {
  if (form.ingredients.length <= 1) return
  form.ingredients.splice(idx, 1)
}

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
  if (slugManuallyEdited || props.recipe) return
  form.slug = transliterate(form.title)
}

function onSlugManualInput() {
  slugManuallyEdited = true
}

function filteredIngredients() {
  return form.ingredients.filter((row) => (row.name || '').trim() !== '' || (row.amount || '').trim() !== '')
}

function submit() {
  form.transform((data) => {
    const ing = filteredIngredients()
    return {
      ...data,
      city_id: data.city_id === '' || data.city_id == null ? null : Number(data.city_id),
      servings:
        data.servings === '' || data.servings == null || Number.isNaN(Number(data.servings))
          ? null
          : Number(data.servings),
      ingredients: ing.length ? ing : null,
    }
  })
  if (props.recipe) {
    form.put(route('admin.recipes.update', props.recipe.id))
  } else {
    form.post(route('admin.recipes.store'))
  }
}
</script>
