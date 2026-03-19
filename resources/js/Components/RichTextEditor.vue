<template>
  <div class="rich-editor">
    <label v-if="label" class="mb-2 block text-sm font-medium text-gray-700">{{ label }}</label>
    <div v-if="editor" class="rounded-xl border border-gray-300 bg-white transition focus-within:border-rosatom-500 focus-within:ring-2 focus-within:ring-rosatom-500/20">
      <div class="flex flex-wrap items-center gap-0.5 border-b border-gray-200 px-2 py-1.5">
        <button
          v-for="btn in toolbarButtons"
          :key="btn.action"
          type="button"
          class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
          :class="{ 'bg-rosatom-50 !text-rosatom-600': btn.isActive?.() }"
          :title="btn.title"
          @click="btn.handler"
        >
          <span v-html="btn.icon" class="flex h-4 w-4 items-center justify-center text-xs font-bold leading-none" />
        </button>

        <div class="mx-1 h-5 w-px bg-gray-200" />

        <button
          type="button"
          class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
          :class="{ 'bg-rosatom-50 !text-rosatom-600': editor.isActive('link') }"
          title="Ссылка"
          @click="setLink"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
        </button>
        <button
          v-if="editor.isActive('link')"
          type="button"
          class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
          title="Убрать ссылку"
          @click="editor.chain().focus().unsetLink().run()"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <div class="mx-1 h-5 w-px bg-gray-200" />

        <button
          type="button"
          class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
          title="Вставить изображение"
          @click="openImagePicker"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15-5-5L5 21" /></svg>
        </button>

        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="handleFileUpload"
        />
      </div>

      <div v-if="uploading" class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2 text-xs text-gray-500">
        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
        Загрузка изображения...
      </div>

      <EditorContent :editor="editor" class="prose prose-sm max-w-none px-4 py-3 [&_.ProseMirror:focus]:outline-none [&_.ProseMirror]:min-h-[120px] [&_.ProseMirror_img]:max-w-full [&_.ProseMirror_img]:rounded-lg" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount, computed } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import Image from '@tiptap/extension-image'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: null },
  uploadUrl: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const uploading = ref(false)

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      heading: { levels: [2, 3] },
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: { class: 'text-rosatom-600 underline' },
    }),
    Underline,
    Image.configure({
      inline: false,
      allowBase64: false,
      HTMLAttributes: { class: 'rounded-lg max-w-full' },
    }),
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

const toolbarButtons = computed(() => [
  {
    action: 'bold', title: 'Жирный',
    icon: '<b>B</b>',
    isActive: () => editor.value?.isActive('bold'),
    handler: () => editor.value?.chain().focus().toggleBold().run(),
  },
  {
    action: 'italic', title: 'Курсив',
    icon: '<i>I</i>',
    isActive: () => editor.value?.isActive('italic'),
    handler: () => editor.value?.chain().focus().toggleItalic().run(),
  },
  {
    action: 'underline', title: 'Подчёркнутый',
    icon: '<u>U</u>',
    isActive: () => editor.value?.isActive('underline'),
    handler: () => editor.value?.chain().focus().toggleUnderline().run(),
  },
  {
    action: 'strike', title: 'Зачёркнутый',
    icon: '<s>S</s>',
    isActive: () => editor.value?.isActive('strike'),
    handler: () => editor.value?.chain().focus().toggleStrike().run(),
  },
  {
    action: 'h2', title: 'Заголовок 2',
    icon: '<span class="text-[11px]">H2</span>',
    isActive: () => editor.value?.isActive('heading', { level: 2 }),
    handler: () => editor.value?.chain().focus().toggleHeading({ level: 2 }).run(),
  },
  {
    action: 'h3', title: 'Заголовок 3',
    icon: '<span class="text-[11px]">H3</span>',
    isActive: () => editor.value?.isActive('heading', { level: 3 }),
    handler: () => editor.value?.chain().focus().toggleHeading({ level: 3 }).run(),
  },
  {
    action: 'bulletList', title: 'Маркированный список',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor" class="w-full h-full"><circle cx="3" cy="5" r="1.5"/><rect x="7" y="4" width="11" height="2" rx="1"/><circle cx="3" cy="10" r="1.5"/><rect x="7" y="9" width="11" height="2" rx="1"/><circle cx="3" cy="15" r="1.5"/><rect x="7" y="14" width="11" height="2" rx="1"/></svg>',
    isActive: () => editor.value?.isActive('bulletList'),
    handler: () => editor.value?.chain().focus().toggleBulletList().run(),
  },
  {
    action: 'orderedList', title: 'Нумерованный список',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor" class="w-full h-full"><text x="1" y="7" font-size="6" stroke="none">1</text><rect x="7" y="4" width="11" height="2" rx="1"/><text x="1" y="12" font-size="6" stroke="none">2</text><rect x="7" y="9" width="11" height="2" rx="1"/><text x="1" y="17" font-size="6" stroke="none">3</text><rect x="7" y="14" width="11" height="2" rx="1"/></svg>',
    isActive: () => editor.value?.isActive('orderedList'),
    handler: () => editor.value?.chain().focus().toggleOrderedList().run(),
  },
  {
    action: 'blockquote', title: 'Цитата',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor" class="w-full h-full"><path d="M3 5c0-1 1-2 2-2h2c1 0 2 1 2 2v3c0 3-2 5-4 6l-1-1c1-1 2-2 2-3H5c-1 0-2-1-2-2V5zm8 0c0-1 1-2 2-2h2c1 0 2 1 2 2v3c0 3-2 5-4 6l-1-1c1-1 2-2 2-3h-1c-1 0-2-1-2-2V5z"/></svg>',
    isActive: () => editor.value?.isActive('blockquote'),
    handler: () => editor.value?.chain().focus().toggleBlockquote().run(),
  },
])

function setLink() {
  const previousUrl = editor.value?.getAttributes('link').href
  const url = window.prompt('URL ссылки', previousUrl || 'https://')
  if (url === null) return
  if (url === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }
  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

function openImagePicker() {
  if (props.uploadUrl) {
    fileInput.value?.click()
  } else {
    const url = window.prompt('URL изображения', 'https://')
    if (url) {
      editor.value?.chain().focus().setImage({ src: url }).run()
    }
  }
}

async function handleFileUpload(e) {
  const file = e.target.files?.[0]
  if (!file) return

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('image', file)
    const { data } = await axios.post(props.uploadUrl, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    if (data.url) {
      editor.value?.chain().focus().setImage({ src: data.url }).run()
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Ошибка загрузки'
    alert(msg)
  } finally {
    uploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

watch(() => props.modelValue, (val) => {
  if (editor.value && editor.value.getHTML() !== val) {
    editor.value.commands.setContent(val, false)
  }
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})
</script>
