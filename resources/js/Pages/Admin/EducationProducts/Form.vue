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
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-gray-900">{{ product ? 'Редактировать продукт' : 'Новый продукт' }}</h1>
        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="typeBadgeClass">{{ typeBadgeLabel }}</span>
      </div>
    </div>

    <form class="max-w-4xl space-y-8" @submit.prevent="submit">
      <!-- Base fields -->
      <RCard elevation="raised">
        <div class="space-y-6">
          <input type="hidden" name="type" :value="currentType" />

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
              rows="3"
              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-2 focus:ring-[#003274]/10"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <ImageUploadCrop
            v-model="form.image"
            label="Изображение карточки"
            :upload-url="route('admin.upload.image')"
            :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
            :error="form.errors.image"
          />

          <template v-if="currentType === 'education'">
            <RInput v-model="form.duration" label="Длительность" placeholder="3 месяца" :error="form.errors.duration" />
            <RInput v-model="form.format" label="Формат" placeholder="Онлайн / очно" :error="form.errors.format" />
            <RInput v-model="form.price_info" label="Информация о стоимости" placeholder="По запросу" :error="form.errors.price_info" />
          </template>

          <div class="grid grid-cols-2 gap-4">
            <RInput
              v-model.number="form.position"
              type="number"
              min="0"
              label="Позиция в списке"
              placeholder="0"
              :error="form.errors.position"
            />
            <div class="flex items-end pb-1">
              <RCheckbox v-model="form.is_active" label="Активен (отображается на сайте)" />
            </div>
          </div>
        </div>
      </RCard>

      <!-- Sections -->
      <RCard v-for="slug in availableSections" :key="slug" elevation="raised">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
          <div class="flex items-center gap-3">
            <RCheckbox
              :model-value="form.sections[slug]?.enabled ?? false"
              @update:model-value="toggleSection(slug, $event)"
            />
            <h3 class="text-base font-bold text-gray-900">{{ sectionLabels[slug] || slug }}</h3>
          </div>
          <button
            v-if="form.sections[slug]?.enabled"
            type="button"
            class="text-xs text-gray-400 transition hover:text-gray-600"
            @click="collapsedSections[slug] = !collapsedSections[slug]"
          >
            {{ collapsedSections[slug] ? 'Развернуть' : 'Свернуть' }}
          </button>
        </div>

        <div v-if="form.sections[slug]?.enabled && !collapsedSections[slug]" class="mt-4">
          <!-- Experts section -->
          <template v-if="slug === 'experts'">
            <div class="space-y-4">
              <div
                v-for="(expert, idx) in (form.sections.experts?.items || [])"
                :key="idx"
                class="rounded-xl border border-gray-100 bg-gray-50/50 p-4"
              >
                <div class="mb-3 flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-700">Эксперт {{ idx + 1 }}</span>
                  <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeExpert(idx)">Удалить</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <RInput v-model="expert.name" label="Имя" placeholder="Иван Иванов" />
                  <RInput v-model="expert.position" label="Должность" placeholder="Профессор" />
                </div>
                <div class="mt-3">
                  <ImageUploadCrop
                    v-model="expert.photo"
                    label="Фото"
                    :upload-url="route('admin.upload.image')"
                    :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
                  />
                </div>
                <div class="mt-3">
                  <label class="mb-1 block text-sm font-semibold text-gray-700">Краткая биография</label>
                  <textarea
                    v-model="expert.bio"
                    rows="2"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10"
                  />
                </div>
              </div>
              <button
                type="button"
                class="flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]"
                @click="addExpert"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Добавить эксперта
              </button>
            </div>
          </template>

          <!-- Regulation section (PDF upload) -->
          <template v-else-if="slug === 'regulation'">
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Файл положения (PDF)</label>
              <ImageUploadCrop
                v-model="form.regulation_file"
                label="PDF-документ"
                :upload-url="route('admin.upload.image')"
                :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
                :error="form.errors.regulation_file"
              />
              <p v-if="form.regulation_file" class="mt-2 text-xs text-gray-500">
                Текущий файл: <a :href="form.regulation_file" target="_blank" class="text-[#003274] underline">{{ form.regulation_file.split('/').pop() }}</a>
              </p>
            </div>
            <div class="mt-4">
              <RichTextEditor
                :model-value="form.sections[slug]?.content || ''"
                label="Описание положения"
                :upload-url="route('admin.upload.image')"
                :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
                @update:model-value="updateSectionContent(slug, $event)"
              />
            </div>
          </template>

          <!-- Application form section (just a toggle, rendered automatically) -->
          <template v-else-if="slug === 'application_form'">
            <p class="text-sm text-gray-500">Форма заявки будет отображена на публичной странице автоматически.</p>
            <div class="mt-3">
              <RichTextEditor
                :model-value="form.sections[slug]?.content || ''"
                label="Текст над формой (необязательно)"
                :upload-url="route('admin.upload.image')"
                :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
                @update:model-value="updateSectionContent(slug, $event)"
              />
            </div>
          </template>

          <!-- RichText section (default) -->
          <template v-else>
            <RichTextEditor
              :model-value="form.sections[slug]?.content || ''"
              :label="sectionLabels[slug] || slug"
              :upload-url="route('admin.upload.image')"
              :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
              @update:model-value="updateSectionContent(slug, $event)"
            />
          </template>
        </div>
      </RCard>

      <!-- Countries (international type) -->
      <RCard v-if="currentType === 'international'" elevation="raised">
        <h3 class="mb-4 border-b border-gray-100 pb-4 text-base font-bold text-gray-900">Страны</h3>
        <div class="space-y-4">
          <div
            v-for="(country, idx) in form.countries"
            :key="idx"
            class="rounded-xl border border-gray-100 bg-gray-50/50 p-4"
          >
            <div class="mb-3 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-700">{{ country.name || `Страна ${idx + 1}` }}</span>
              <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="removeCountry(idx)">Удалить</button>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <RInput v-model="country.name" label="Название *" placeholder="Абхазия" />
              <RInput v-model="country.slug" label="Slug" placeholder="abkhazia" />
            </div>
            <div class="mt-3">
              <label class="mb-1 block text-sm font-semibold text-gray-700">Краткое описание</label>
              <textarea
                v-model="country.description"
                rows="2"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm transition focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/10"
              />
            </div>
            <div class="mt-3">
              <RichTextEditor
                v-model="country.content"
                label="Содержание"
                :upload-url="route('admin.upload.image')"
                :media-picker-url="route('admin.media.index')" collection="education_products" :entity-type="mediaEntityType" :entity-id="mediaEntityId"
              />
            </div>
          </div>
          <button
            type="button"
            class="flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]"
            @click="addCountry"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Добавить страну
          </button>
        </div>
      </RCard>

      <!-- Actions -->
      <div class="flex gap-3">
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
          :href="route('admin.education-products.index')"
          class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
        >
          Отмена
        </Link>
      </div>
    </form>

    <ContentPreview
      :open="showPreview"
      :title="form.title"
      :description="form.description"
      :content="form.content"
      :image="form.image"
      :meta="previewMeta"
      @close="showPreview = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'

const props = defineProps({
  product: { type: Object, default: null },
  productType: { type: String, default: 'education' },
  sectionDefinitions: { type: Object, default: () => ({}) },
  sectionLabels: { type: Object, default: () => ({}) },
})

const currentType = computed(() => props.product?.type || props.productType)

const availableSections = computed(() =>
  props.sectionDefinitions[currentType.value] || []
)

const TYPE_META = {
  education: { label: 'Образование', cls: 'bg-blue-50 text-blue-700' },
  partner: { label: 'Партнёры', cls: 'bg-amber-50 text-amber-700' },
  international: { label: 'Международный', cls: 'bg-emerald-50 text-emerald-700' },
}

const typeBadgeLabel = computed(() => TYPE_META[currentType.value]?.label ?? currentType.value)
const typeBadgeClass = computed(() => TYPE_META[currentType.value]?.cls ?? 'bg-gray-100 text-gray-700')

const showPreview = ref(false)
const mediaEntityType = 'App\\Models\\EducationProduct'
const mediaEntityId = props.product?.id || null
let slugManuallyEdited = false

const collapsedSections = reactive({})

function buildInitialSections() {
  const existing = props.product?.sections || {}
  const result = {}
  const allowed = props.sectionDefinitions[currentType.value] || []
  for (const slug of allowed) {
    result[slug] = existing[slug] || { enabled: false, content: '' }
    if (slug === 'experts' && !result[slug].items) {
      result[slug].items = result[slug].items || []
    }
  }
  return result
}

const form = useForm({
  title: props.product?.title ?? '',
  slug: props.product?.slug ?? '',
  type: currentType.value,
  description: props.product?.description ?? '',
  content: props.product?.content ?? '',
  image: props.product?.image ?? '',
  duration: props.product?.duration ?? '',
  format: props.product?.format ?? '',
  target_audience: props.product?.target_audience ?? '',
  price_info: props.product?.price_info ?? '',
  position: props.product?.position ?? 0,
  is_active: props.product?.is_active ?? true,
  sections: buildInitialSections(),
  regulation_file: props.product?.regulation_file ?? '',
  countries: props.product?.countries ?? [],
})

const previewMeta = computed(() => [
  form.duration ? { label: form.duration, class: 'bg-blue-50 text-blue-700' } : null,
  form.format ? { label: form.format, class: 'bg-purple-50 text-purple-700' } : null,
  form.price_info ? { label: form.price_info, class: 'bg-green-50 text-green-700' } : null,
].filter(Boolean))

function toggleSection(slug, enabled) {
  if (!form.sections[slug]) {
    form.sections[slug] = { enabled: false, content: '' }
  }
  form.sections[slug].enabled = enabled
}

function updateSectionContent(slug, content) {
  if (!form.sections[slug]) {
    form.sections[slug] = { enabled: true, content: '' }
  }
  form.sections[slug].content = content
}

function addExpert() {
  if (!form.sections.experts) {
    form.sections.experts = { enabled: true, items: [] }
  }
  if (!form.sections.experts.items) {
    form.sections.experts.items = []
  }
  form.sections.experts.items.push({ name: '', position: '', photo: '', bio: '' })
}

function removeExpert(idx) {
  form.sections.experts.items.splice(idx, 1)
}

function addCountry() {
  form.countries.push({ name: '', slug: '', description: '', content: '' })
}

function removeCountry(idx) {
  form.countries.splice(idx, 1)
}

function transliterate(str) {
  const map = {
    а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'yo', ж: 'zh',
    з: 'z', и: 'i', й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o',
    п: 'p', р: 'r', с: 's', т: 't', у: 'u', ф: 'f', х: 'kh', ц: 'ts',
    ч: 'ch', ш: 'sh', щ: 'shch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu',
    я: 'ya', ' ': '-',
  }
  return str.toLowerCase().split('').map((c) => map[c] ?? c).join('')
    .replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '')
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
    position: data.position === '' || data.position == null || Number.isNaN(Number(data.position)) ? 0 : Number(data.position),
  }))
  if (props.product) {
    form.put(route('admin.education-products.update', props.product.id))
  } else {
    form.post(route('admin.education-products.store'))
  }
}
</script>
