<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.groups.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к группам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ group ? 'Редактировать группу' : 'Новая группа' }}</h1>
    </div>

    <RCard>
      <form @submit.prevent="submit" class="max-w-2xl space-y-6 p-8">
        <RInput
          v-model="form.title"
          label="Название *"
          required
          :error="form.errors.title"
        />
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Куратор</label>
          <select v-model="form.curator_id" class="w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20">
            <option :value="null">— Не назначен —</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
          </select>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Участники</label>
          <div class="max-h-60 space-y-2 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-3">
            <div v-for="u in users" :key="u.id" class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-gray-100" :class="form.user_ids.includes(u.id) ? 'bg-rosatom-50' : ''">
              <RCheckbox
                :model-value="form.user_ids.includes(u.id)"
                @update:model-value="(v) => { if (v) form.user_ids.push(u.id); else form.user_ids = form.user_ids.filter(id => id !== u.id) }"
                :label="`${u.name} (${u.email})`"
              />
            </div>
          </div>
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
          <RButton type="submit" :disabled="form.processing" :loading="form.processing" variant="primary">
            Сохранить
          </RButton>
          <Link :href="route('lms.admin.groups.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
        </div>
      </form>
    </RCard>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, group: Object, users: Array })

const form = useForm({
  title: props.group?.title ?? '',
  curator_id: props.group?.curator_id ?? null,
  user_ids: props.group?.members?.map(m => m.id) ?? [],
})

function submit() {
  if (props.group) {
    form.put(route('lms.admin.groups.update', [props.event.slug, props.group.id]))
  } else {
    form.post(route('lms.admin.groups.store', props.event.slug))
  }
}
</script>
