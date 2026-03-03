<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Профиль – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Редактирование профиля</h1>

      <form @submit.prevent="submit" class="max-w-xl space-y-6 rounded-xl border border-gray-200 bg-white shadow-sm p-6">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-500">Имя</label>
          <input
            :value="user?.name"
            type="text"
            disabled
            class="w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-500">Email</label>
          <input
            :value="user?.email"
            type="email"
            disabled
            class="w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-500"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-500">Должность</label>
          <input
            v-model="form.position"
            type="text"
            placeholder="Ваша должность"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          />
          <p v-if="form.errors.position" class="mt-1 text-sm text-red-600">{{ form.errors.position }}</p>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-500">Город</label>
          <input
            v-model="form.city"
            type="text"
            placeholder="Город"
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          />
          <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-500">Аватар</label>
          <div class="flex items-center gap-4">
            <img
              v-if="avatarPreview || profile?.avatar"
              :src="avatarPreview || (profile?.avatar ? `/storage/${profile.avatar}` : null)"
              alt="Avatar"
              class="h-20 w-20 rounded-full object-cover border border-gray-300"
            />
            <div v-else class="flex h-20 w-20 items-center justify-center rounded-full border border-dashed border-gray-300 bg-gray-50">
              <UserCircleIcon class="h-10 w-10 text-gray-400" />
            </div>
            <div class="flex-1">
              <input
                type="file"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-200 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-300"
                @change="onAvatarChange"
              />
              <p class="mt-1 text-xs text-gray-400">PNG, JPG до 2 МБ</p>
            </div>
          </div>
          <p v-if="form.errors.avatar" class="mt-1 text-sm text-red-600">{{ form.errors.avatar }}</p>
        </div>
        <div class="flex gap-3 pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            Сохранить
          </button>
          <Link
            :href="route('lms.dashboard', { event: event?.slug })"
            class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
          >
            Отмена
          </Link>
        </div>
      </form>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import { UserCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, required: true },
  profile: { type: Object, default: () => ({}) },
})

const user = computed(() => props.user || usePage().props.auth?.user || {})

const form = useForm({
  position: props.profile?.position ?? '',
  city: props.profile?.city ?? '',
  avatar: null,
})

const avatarPreview = ref(null)

function onAvatarChange(e) {
  const file = e.target?.files?.[0]
  if (file) {
    form.avatar = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

function submit() {
  form.transform((data) => ({
    ...data,
    _method: 'patch',
  })).post(route('lms.profile.update', { event: props.event?.slug }), {
    forceFormData: true,
  })
}
</script>
