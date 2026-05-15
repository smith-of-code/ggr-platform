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

        <template v-if="canSetCurator">
          <SearchSelect
            v-model="form.curator_id"
            :options="userOptions"
            label="Куратор"
            placeholder="Выберите куратора"
            search-placeholder="Поиск по имени или email..."
          />
        </template>
        <div v-else class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
          <p class="text-xs uppercase tracking-wide text-gray-500">Куратор</p>
          <p class="mt-1 text-sm font-medium text-gray-900">{{ fixedCuratorName || 'Текущий пользователь' }}</p>
        </div>

        <MultiSelect
          v-model="form.user_ids"
          :options="userOptions"
          label="Участники"
          placeholder="Выберите участников"
        />

        <MultiSelect
          v-if="cityOptions.length"
          v-model="form.linked_cities"
          :options="cityOptions"
          value-key="value"
          label-key="label"
          label="Города для рейтинга"
          placeholder="Привяжите города (для бонусов группы в геймификации)"
        />
        <p v-else class="text-xs text-gray-400">В событии пока нет городов в профилях участников — сначала заполните поле «Город» у участников.</p>

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
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import MultiSelect from '@/Components/MultiSelect.vue'

const props = defineProps({
  event: Object,
  group: Object,
  users: Array,
  cityOptions: { type: Array, default: () => [] },
  canSetCurator: { type: Boolean, default: true },
  fixedCuratorName: { type: String, default: '' },
})

const canSetCurator = computed(() => props.canSetCurator)

const userOptions = computed(() =>
  (props.users ?? []).map(u => ({ id: u.id, name: `${u.name} (${u.email})` }))
)

const form = useForm({
  title: props.group?.title ?? '',
  curator_id: props.group?.curator_id ?? null,
  user_ids: props.group?.members?.map(m => m.id) ?? [],
  linked_cities: Array.isArray(props.group?.linked_cities) ? [...props.group.linked_cities] : [],
})

function submit() {
  if (props.group) {
    form.put(route('lms.admin.groups.update', [props.event.slug, props.group.id]))
  } else {
    form.post(route('lms.admin.groups.store', props.event.slug))
  }
}
</script>
