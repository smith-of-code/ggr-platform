<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.materials.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к материалам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ material ? 'Редактировать раздел' : 'Новый раздел' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.title"
          label="Название *"
          required
          :error="form.errors.title"
        />
        <RichTextEditor v-model="form.content" label="Контент" :upload-url="route('lms.admin.upload.image', event.slug)" />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Группы</label>
          <div class="space-y-2">
            <div v-for="g in groups" :key="g.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-gray-50" :class="form.group_ids.includes(g.id) ? 'bg-rosatom-50' : ''">
              <RCheckbox
                :model-value="form.group_ids.includes(g.id)"
                @update:model-value="(v) => { if (v) form.group_ids.push(g.id); else form.group_ids = form.group_ids.filter(id => id !== g.id) }"
                :label="g.title"
              />
            </div>
          </div>
        </div>
        <div class="flex gap-4">
          <RCheckbox v-model="form.in_menu" label="В меню" />
          <div class="w-24">
            <label class="mb-1 block text-xs text-gray-500">Позиция</label>
            <RInput v-model.number="form.position" type="number" size="sm" />
          </div>
        </div>
        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.materials.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'

const props = defineProps({ event: Object, material: Object, groups: Array })

const form = useForm({
  title: props.material?.title ?? '',
  content: props.material?.content ?? '',
  in_menu: props.material?.in_menu ?? false,
  position: props.material?.position ?? 0,
  group_ids: props.material?.groups?.map(g => g.id) ?? [],
})

function submit() {
  if (props.material) {
    form.put(route('lms.admin.materials.update', [props.event.slug, props.material.id]))
  } else {
    form.post(route('lms.admin.materials.store', props.event.slug))
  }
}
</script>
