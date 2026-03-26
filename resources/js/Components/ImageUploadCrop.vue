<template>
  <div>
    <label v-if="label" class="mb-2 block text-sm font-semibold text-gray-700">{{ label }}</label>

    <!-- Preview / Drop zone -->
    <div
      class="relative overflow-hidden rounded-xl border-2 border-dashed transition"
      :class="isDragging ? 'border-[#003274] bg-[#003274]/5' : 'border-gray-200 bg-gray-50'"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <div v-if="previewUrl" class="group relative">
        <img :src="previewUrl" :class="previewClass" alt="" />
        <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition group-hover:opacity-100">
          <button
            type="button"
            class="rounded-lg bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow transition hover:bg-gray-100"
            @click="openPicker"
          >
            Заменить
          </button>
          <button
            type="button"
            class="rounded-lg bg-red-500 px-3 py-2 text-xs font-medium text-white shadow transition hover:bg-red-600"
            @click="removeImage"
          >
            Удалить
          </button>
        </div>
      </div>
      <div v-else class="flex flex-col items-center justify-center px-6 py-10" @click="openPicker">
        <svg class="mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
        </svg>
        <p class="mb-1 text-sm font-medium text-gray-600">Нажмите или перетащите изображение</p>
        <p class="text-xs text-gray-400">PNG, JPG, WEBP до 5 МБ</p>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="uploadError" class="mt-1 text-sm text-red-600">{{ uploadError }}</p>

    <!-- Uploading -->
    <div v-if="uploading" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
      <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      Загрузка...
    </div>

    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelected" />

    <!-- Crop Modal -->
    <teleport to="body">
      <div v-if="showCropper" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="cancelCrop">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Обрезать изображение</h3>
            <button type="button" class="rounded-lg p-1 text-gray-400 hover:text-gray-600" @click="cancelCrop">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6">
            <div class="mx-auto max-h-[60vh] overflow-hidden">
              <img ref="cropImage" :src="cropSrc" class="max-w-full" />
            </div>
          </div>
          <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
            <button
              type="button"
              class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50"
              @click="cancelCrop"
            >
              Отмена
            </button>
            <button
              type="button"
              class="rounded-xl bg-[#003274] px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-[#025ea1]"
              @click="applyCrop"
            >
              Применить
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: null },
  error: { type: String, default: null },
  uploadUrl: { type: String, default: '/admin/upload/image' },
  aspectRatio: { type: Number, default: 16 / 9 },
  previewClass: { type: String, default: 'h-48 w-full object-cover' },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const uploadError = ref('')
const previewUrl = ref(props.modelValue || '')

const showCropper = ref(false)
const cropSrc = ref('')
const cropImage = ref(null)
let cropper = null
let selectedFile = null

watch(() => props.modelValue, (val) => {
  previewUrl.value = val || ''
})

function openPicker() {
  fileInput.value?.click()
}

function onDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file && file.type.startsWith('image/')) {
    handleFile(file)
  }
}

function onFileSelected(e) {
  const file = e.target.files?.[0]
  if (file) handleFile(file)
  if (fileInput.value) fileInput.value.value = ''
}

function handleFile(file) {
  selectedFile = file
  uploadError.value = ''
  const reader = new FileReader()
  reader.onload = (e) => {
    cropSrc.value = e.target.result
    showCropper.value = true
    nextTick(initCropper)
  }
  reader.readAsDataURL(file)
}

function initCropper() {
  nextTick(() => {
    if (cropper) {
      cropper.destroy()
      cropper = null
    }
    if (cropImage.value) {
      cropper = new Cropper(cropImage.value, {
        aspectRatio: props.aspectRatio,
        viewMode: 1,
        autoCropArea: 0.9,
        responsive: true,
        background: true,
      })
    }
  })
}

function cancelCrop() {
  showCropper.value = false
  cropSrc.value = ''
  if (cropper) {
    cropper.destroy()
    cropper = null
  }
  selectedFile = null
}

async function applyCrop() {
  if (!cropper) return

  const canvas = cropper.getCroppedCanvas({
    maxWidth: 1920,
    maxHeight: 1080,
    imageSmoothingQuality: 'high',
  })

  showCropper.value = false
  cropper.destroy()
  cropper = null

  canvas.toBlob(async (blob) => {
    if (!blob) return
    uploading.value = true
    try {
      const ext = selectedFile?.name?.split('.').pop() || 'jpg'
      const fileName = selectedFile?.name || `image.${ext}`
      const file = new File([blob], fileName, { type: blob.type })
      const formData = new FormData()
      formData.append('image', file)

      const { data } = await axios.post(props.uploadUrl, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      if (data.url) {
        previewUrl.value = data.url
        emit('update:modelValue', data.url)
      } else {
        uploadError.value = 'Сервер не вернул URL изображения'
      }
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.image?.[0] || 'Ошибка загрузки'
      uploadError.value = msg
    } finally {
      uploading.value = false
      selectedFile = null
    }
  }, selectedFile?.type || 'image/jpeg', 0.9)
}

function removeImage() {
  previewUrl.value = ''
  emit('update:modelValue', '')
}

onBeforeUnmount(() => {
  if (cropper) {
    cropper.destroy()
    cropper = null
  }
})
</script>
