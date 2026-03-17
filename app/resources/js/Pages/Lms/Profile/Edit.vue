<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Профиль – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Редактирование профиля</h1>

      <!-- Display section with ProfileCard -->
      <ProfileCard
        :full-name="user?.name"
        :avatar="avatarDisplayUrl"
        :position="profile?.position"
        :workplace="profile?.city"
        :phone="profile?.phone"
        :email="user?.email"
        :points="profile?.points"
        :rank="profile?.rank"
        :status="profile?.status"
      />

      <RCard>
        <template #default>
          <form @submit.prevent="submit" class="space-y-6">
            <RInput
              :model-value="user?.name"
              label="Имя"
              type="text"
              disabled
            />
            <RInput
              :model-value="user?.email"
              label="Email"
              type="email"
              disabled
            />
            <RInput
              v-model="form.position"
              label="Должность"
              placeholder="Ваша должность"
              :error="form.errors.position"
            />
            <RInput
              v-model="form.city"
              label="Город"
              placeholder="Город"
              :error="form.errors.city"
            />
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-500">Аватар</label>
              <div class="flex items-center gap-4">
                <RAvatar
                  v-if="avatarPreview || profile?.avatar"
                  :src="avatarPreview || (profile?.avatar ? `/storage/${profile.avatar}` : null)"
                  :name="user?.name"
                  size="lg"
                />
                <RAvatar
                  v-else
                  :name="user?.name"
                  size="lg"
                />
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
              <RButton
                type="submit"
                :disabled="form.processing"
                :loading="form.processing"
              >
                Сохранить
              </RButton>
              <Link
                :href="route('lms.dashboard', { event: event?.slug })"
                as="div"
                class="inline-block"
              >
                <RButton variant="outline">
                  Отмена
                </RButton>
              </Link>
            </div>
          </form>
        </template>
      </RCard>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

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

const avatarDisplayUrl = computed(() => {
  if (avatarPreview.value) return avatarPreview.value
  if (props.profile?.avatar) return `/storage/${props.profile.avatar}`
  return null
})

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
