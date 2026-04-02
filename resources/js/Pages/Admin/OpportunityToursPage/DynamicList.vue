<template>
  <div class="space-y-4">
    <div
      v-for="(item, idx) in modelValue"
      :key="idx"
      class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
    >
      <!-- Card header -->
      <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-2.5">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#003274]/10 text-xs font-bold text-[#003274]">
          {{ idx + 1 }}
        </span>
        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700">
          {{ itemSummary(item) || `${addLabel.replace(/^Добавить\s*/i, '')} #${idx + 1}` }}
        </span>
        <button
          v-if="modelValue.length > 1"
          type="button"
          @click="remove(idx)"
          class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
          title="Удалить"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Two-column video layout -->
      <div v-if="hasTwoColumnLayout" class="grid gap-0 sm:grid-cols-2">
        <!-- Left: title + thumbnail preview -->
        <div class="space-y-3 border-b p-4 sm:border-b-0 sm:border-r border-gray-100">
          <div v-for="field in leftFields(item)" :key="field.key">
            <template v-if="field.preview === 'video-card'">
              <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
              <!-- Large thumbnail -->
              <button
                type="button"
                class="group relative block w-full overflow-hidden rounded-lg bg-gray-100"
                :class="item[field.key] ? 'cursor-pointer' : 'cursor-default'"
                @click="item[field.key] && $emit('lightbox', item[field.key])"
              >
                <div class="max-h-[231px] overflow-hidden">
                  <img
                    v-if="item[field.key]"
                    :src="item[field.key]"
                    :alt="itemSummary(item)"
                    class="w-full object-cover transition group-hover:scale-105"
                  />
                  <div v-else class="flex h-[231px] items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                    <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                    </svg>
                  </div>
                </div>
                <div v-if="item[field.key]" class="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition group-hover:bg-black/10 group-hover:opacity-100">
                  <svg class="h-6 w-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                  </svg>
                </div>
              </button>
              <!-- Upload controls -->
              <div class="mt-2 flex items-center gap-2">
                <input
                  type="text"
                  :value="item[field.key]"
                  @input="updateField(idx, field.key, $event.target.value)"
                  :placeholder="field.placeholder"
                  class="w-full rounded-lg border-gray-200 bg-white px-2.5 py-1.5 text-xs transition focus:border-[#003274] focus:ring-[#003274]/10"
                />
                <label class="shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]">
                  <input type="file" accept="image/*" class="hidden" @change="handleImageUpload($event, idx, field.key)" />
                  Загрузить
                </label>
                <button type="button" class="shrink-0 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-[#003274] transition hover:border-[#003274]/30" @click="openMediaPicker(idx, field.key)">
                  Библиотека
                </button>
              </div>
            </template>
            <template v-else>
              <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
              <FieldRenderer
                :field="field" :item="item" :idx="idx"
                @update="updateField" @image-upload="handleImageUpload" @file-upload="handleFileUpload" @media-pick="openMediaPicker"
              />
            </template>
          </div>
        </div>

        <!-- Right column -->
        <div class="space-y-3 p-4">
          <div v-for="field in rightFields(item)" :key="field.key">
            <!-- Logo/image card preview -->
            <template v-if="field.preview === 'logo-card'">
              <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
              <button
                type="button"
                class="group relative block w-full overflow-hidden rounded-lg border border-gray-100 bg-white"
                :class="item[field.key] ? 'cursor-pointer' : 'cursor-default'"
                @click="item[field.key] && $emit('lightbox', item[field.key])"
              >
                <div class="flex h-[120px] items-center justify-center p-4">
                  <img
                    v-if="item[field.key]"
                    :src="item[field.key]"
                    :alt="itemSummary(item)"
                    class="max-h-full max-w-full object-contain transition group-hover:scale-105"
                  />
                  <div v-else class="text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                    </svg>
                    <p class="mt-1 text-xs text-gray-400">Нет логотипа</p>
                  </div>
                </div>
              </button>
              <div class="mt-2 flex items-center gap-2">
                <input
                  type="text"
                  :value="item[field.key]"
                  @input="updateField(idx, field.key, $event.target.value)"
                  :placeholder="field.placeholder"
                  class="w-full rounded-lg border-gray-200 bg-white px-2.5 py-1.5 text-xs transition focus:border-[#003274] focus:ring-[#003274]/10"
                />
                <label class="shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]">
                  <input type="file" accept="image/*" class="hidden" @change="handleImageUpload($event, idx, field.key)" />
                  Загрузить
                </label>
                <button type="button" class="shrink-0 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-[#003274] transition hover:border-[#003274]/30" @click="openMediaPicker(idx, field.key)">
                  Библиотека
                </button>
              </div>
            </template>

            <!-- Video file preview -->
            <template v-else-if="field.type === 'file-upload' && item[field.key]">
              <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
              <button
                type="button"
                class="group relative block w-full overflow-hidden rounded-lg bg-black"
                @click="$emit('preview', item)"
              >
                <div class="flex max-h-[231px] items-center justify-center overflow-hidden">
                  <video
                    :src="item[field.key]"
                    :poster="item.thumbnail || undefined"
                    class="h-full w-full object-cover"
                    muted
                    preload="metadata"
                  />
                  <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition group-hover:bg-black/40">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg transition group-hover:scale-110">
                      <svg class="ml-0.5 h-4 w-4 text-[#003274]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z" />
                      </svg>
                    </div>
                  </div>
                </div>
              </button>
              <div class="mt-2 flex items-center gap-2">
                <span class="min-w-0 flex-1 truncate text-xs text-gray-400">{{ item[field.key] }}</span>
                <button type="button" @click="updateField(idx, field.key, '')" class="shrink-0 text-xs text-red-400 hover:text-red-600">Удалить</button>
                <label class="shrink-0 cursor-pointer text-xs font-medium text-[#003274] hover:underline">
                  <input type="file" :accept="field.accept || '*'" class="hidden" @change="handleFileUpload($event, idx, field.key)" />
                  Заменить
                </label>
              </div>
            </template>

            <!-- Default field -->
            <template v-else>
              <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
              <FieldRenderer
                :field="field" :item="item" :idx="idx"
                @update="updateField" @image-upload="handleImageUpload" @file-upload="handleFileUpload" @media-pick="openMediaPicker"
              />
              <button
                v-if="!field.type && field.parseIframe && item[field.key]"
                type="button"
                class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-medium text-[#003274] hover:underline"
                @click="$emit('preview', item)"
              >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>
                Предпросмотр
              </button>
            </template>
          </div>
        </div>
      </div>

      <!-- Default card body (non-video) -->
      <div v-else class="space-y-3 p-4">
        <div
          v-if="inlineFields(item).length"
          class="grid gap-3"
          :class="gridColsFor(inlineFields(item).length)"
        >
          <div v-for="field in inlineFields(item)" :key="field.key">
            <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
            <FieldRenderer
              :field="field" :item="item" :idx="idx"
              @update="updateField" @image-upload="handleImageUpload" @file-upload="handleFileUpload" @media-pick="openMediaPicker"
            />
          </div>
        </div>
        <div v-for="field in fullWidthFields(item)" :key="field.key">
          <label class="mb-1 block text-xs font-medium text-gray-500">{{ field.label }}</label>
          <FieldRenderer
            :field="field" :item="item" :idx="idx"
            @update="updateField" @image-upload="handleImageUpload" @file-upload="handleFileUpload" @media-pick="openMediaPicker"
          />
        </div>
      </div>
    </div>

    <button
      type="button"
      @click="add"
      class="inline-flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      {{ addLabel }}
    </button>

    <MediaPickerModal
      :show="mediaPicker.show"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.image')"
      @close="mediaPicker.show = false"
      @select="onMediaPickerSelect"
    />
  </div>
</template>

<script setup>
import { h, ref, computed, defineComponent } from 'vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const FULL_WIDTH_TYPES = ['image-upload', 'file-upload', 'textarea']

const props = defineProps({
  modelValue: { type: Array, required: true },
  fields: { type: Array, required: true },
  addLabel: { type: String, default: 'Добавить' },
  newItem: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue', 'preview', 'lightbox'])

const mediaPicker = ref({ show: false, idx: -1, key: '' })

function openMediaPicker(idx, key) {
  mediaPicker.value = { show: true, idx, key }
}

function onMediaPickerSelect(url) {
  const { idx, key } = mediaPicker.value
  if (idx >= 0 && key) updateField(idx, key, toRelativeUrl(url))
  mediaPicker.value = { show: false, idx: -1, key: '' }
}

const hasTwoColumnLayout = computed(() =>
  props.fields.some(f => f.column === 'left' || f.column === 'right')
)

function visibleFields(item) {
  return props.fields.filter(f => {
    if (!f.visibleWhen) return true
    return item[f.visibleWhen.field] === f.visibleWhen.value
  })
}

function leftFields(item) {
  return visibleFields(item).filter(f => f.column === 'left')
}

function rightFields(item) {
  return visibleFields(item).filter(f => f.column === 'right')
}

function inlineFields(item) {
  return visibleFields(item).filter(f =>
    !FULL_WIDTH_TYPES.includes(f.type) && f.span !== 'full' && f.preview !== 'video-card'
  )
}

function fullWidthFields(item) {
  return visibleFields(item).filter(f =>
    (FULL_WIDTH_TYPES.includes(f.type) || f.span === 'full')
  )
}

function gridColsFor(count) {
  if (count <= 1) return 'grid-cols-1'
  if (count === 2) return 'sm:grid-cols-2'
  return 'sm:grid-cols-2 lg:grid-cols-3'
}

function itemSummary(item) {
  const titleField = props.fields.find(f => !f.type || f.type === 'text')
  if (titleField && item[titleField.key]) return item[titleField.key]
  const nameField = props.fields.find(f => f.key === 'name' || f.key === 'title' || f.key === 'question')
  if (nameField && item[nameField.key]) return item[nameField.key]
  return ''
}

function updateField(idx, key, value) {
  const updated = [...props.modelValue]
  updated[idx] = { ...updated[idx], [key]: value }
  emit('update:modelValue', updated)
}

function add() {
  emit('update:modelValue', [...props.modelValue, { ...props.newItem }])
}

function remove(idx) {
  emit('update:modelValue', props.modelValue.filter((_, i) => i !== idx))
}

function extractIframeSrc(value) {
  if (value && value.includes('<iframe') && value.includes('src=')) {
    const match = value.match(/src=["']([^"']+)["']/)
    if (match) return match[1]
  }
  return value
}

function toRelativeUrl(url) {
  if (!url) return url
  try {
    return new URL(url).pathname
  } catch {
    return url
  }
}

async function handleImageUpload(event, idx, key) {
  const file = event.target.files?.[0]
  if (!file) return
  const formData = new FormData()
  formData.append('image', file)
  try {
    const { data } = await window.axios.post(route('admin.upload.image'), formData)
    if (data.url) updateField(idx, key, toRelativeUrl(data.url))
  } catch (e) {
    console.error('Image upload error:', e)
  }
  event.target.value = ''
}

async function handleFileUpload(event, idx, key) {
  const file = event.target.files?.[0]
  if (!file) return
  const formData = new FormData()
  formData.append('file', file)
  try {
    const { data } = await window.axios.post(route('admin.upload.file'), formData)
    if (data.url) updateField(idx, key, toRelativeUrl(data.url))
  } catch (e) {
    console.error('File upload error:', e)
  }
  event.target.value = ''
}

const FieldRenderer = defineComponent({
  props: {
    field: { type: Object, required: true },
    item: { type: Object, required: true },
    idx: { type: Number, required: true },
  },
  emits: ['update', 'image-upload', 'file-upload', 'media-pick'],
  setup(props, { emit }) {
    const inputClass = 'w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-[#003274] focus:ring-[#003274]/10'

    return () => {
      const { field, item, idx } = props
      const val = item[field.key]

      if (field.type === 'icon-select') {
        return h('div', { class: 'flex items-center gap-2' }, [
          field.iconMap?.[val]
            ? h('div', {
                class: 'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white',
                innerHTML: field.iconMap[val],
              })
            : null,
          h('select', {
            value: val,
            class: inputClass,
            onInput: e => emit('update', idx, field.key, e.target.value),
          }, field.options.map(opt =>
            h('option', { key: opt.value, value: opt.value }, opt.label)
          )),
        ])
      }

      if (field.type === 'image-upload') {
        return h('div', { class: 'space-y-2' }, [
          h('div', { class: 'flex items-center gap-2' }, [
            h('input', {
              type: 'text', value: val, placeholder: field.placeholder, class: inputClass,
              onInput: e => emit('update', idx, field.key, e.target.value),
            }),
            h('label', {
              class: 'shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]',
            }, [
              h('input', { type: 'file', accept: 'image/*', class: 'hidden', onChange: e => emit('image-upload', e, idx, field.key) }),
              'Загрузить',
            ]),
            h('button', {
              type: 'button',
              class: 'shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-[#003274] transition hover:border-[#003274]/30',
              onClick: () => emit('media-pick', idx, field.key),
            }, 'Библиотека'),
          ]),
          val
            ? h('div', { class: 'flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-2' }, [
                h('img', { src: val, alt: field.label, class: 'h-16 max-w-[160px] rounded-lg object-contain', onError: e => { e.target.style.display = 'none' } }),
                h('div', { class: 'min-w-0 flex-1' }, [h('p', { class: 'truncate text-xs text-gray-400' }, val)]),
                h('button', {
                  type: 'button', class: 'shrink-0 rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500',
                  onClick: () => emit('update', idx, field.key, ''),
                }, [h('svg', { class: 'h-4 w-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2', innerHTML: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />' })]),
              ])
            : null,
        ])
      }

      if (field.type === 'file-upload') {
        return h('div', { class: 'space-y-2' }, [
          h('div', { class: 'flex items-center gap-2' }, [
            h('input', {
              type: 'text', value: val, placeholder: field.placeholder, class: inputClass,
              onInput: e => emit('update', idx, field.key, e.target.value),
            }),
            h('label', {
              class: 'shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition hover:border-[#003274] hover:text-[#003274]',
            }, [
              h('input', { type: 'file', accept: field.accept || '*', class: 'hidden', onChange: e => emit('file-upload', e, idx, field.key) }),
              'Загрузить',
            ]),
          ]),
          val
            ? h('div', { class: 'flex items-center gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2' }, [
                h('svg', { class: 'h-5 w-5 shrink-0 text-green-500', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2', innerHTML: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />' }),
                h('span', { class: 'min-w-0 flex-1 truncate text-xs text-gray-500' }, val),
                h('button', {
                  type: 'button', class: 'shrink-0 rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500',
                  onClick: () => emit('update', idx, field.key, ''),
                }, [h('svg', { class: 'h-4 w-4', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2', innerHTML: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />' })]),
              ])
            : null,
        ])
      }

      if (field.type === 'select') {
        return h('select', {
          value: val, class: inputClass,
          onInput: e => emit('update', idx, field.key, e.target.value),
        }, field.options.map(opt => h('option', { key: opt.value, value: opt.value }, opt.label)))
      }

      if (field.type === 'textarea') {
        return h('textarea', {
          value: val, placeholder: field.placeholder, rows: 3, class: inputClass,
          onInput: e => emit('update', idx, field.key, e.target.value),
        })
      }

      return h('input', {
        type: 'text', value: val, placeholder: field.placeholder, class: inputClass,
        onInput: e => emit('update', idx, field.key, field.parseIframe ? extractIframeSrc(e.target.value) : e.target.value),
      })
    }
  },
})
</script>
