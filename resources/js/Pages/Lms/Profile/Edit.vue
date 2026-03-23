<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Профиль – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Редактирование профиля</h1>

      <!-- Display section with ProfileCard -->
      <ProfileCard
        :full-name="fullName"
        :avatar="avatarDisplayUrl"
        :position="profile?.position"
        :workplace="profile?.city"
        :phone="form.phone || profile?.phone"
        :email="user?.email"
        :points="profile?.points"
        :rank="profile?.rank"
        :status="profile?.status"
      />

      <RCard>
        <template #default>
          <form @submit.prevent="submit" class="space-y-6">
            <RInput
              :model-value="fullName"
              label="ФИО"
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
              v-model="form.phone"
              label="Телефон"
              type="tel"
              placeholder="+7 (___) ___-__-__"
              :error="form.errors.phone"
            />
            <RInput
              v-model="form.position"
              label="Должность"
              placeholder="Ваша должность"
              :error="form.errors.position"
            />
            <RInput
              v-model="form.city"
              label="Место работы"
              placeholder="Организация / город"
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
      <RCard>
        <template #default>
          <h2 class="mb-4 text-lg font-semibold text-gray-900">Привязанные аккаунты</h2>
          <p class="mb-4 text-sm text-gray-500">
            Привяжите аккаунт ВКонтакте или Яндекс, чтобы входить в систему через них.
          </p>
          <p v-if="$page.props.errors?.social" class="mb-4 text-sm text-red-600">
            {{ $page.props.errors.social }}
          </p>
          <div class="space-y-3">
            <div
              v-for="p in providers"
              :key="p.key"
              class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3"
            >
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" :class="p.bgClass">
                  <span v-html="p.icon" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ p.label }}</p>
                  <p class="text-xs text-gray-500">
                    {{ socialAccounts[p.key] ? 'Привязан' : 'Не привязан' }}
                  </p>
                </div>
              </div>
              <div class="flex gap-2">
                <template v-if="socialAccounts[p.key]">
                  <RButton
                    variant="outline"
                    size="sm"
                    @click="confirmRelink(p)"
                  >
                    Сменить
                  </RButton>
                  <RButton
                    variant="outline"
                    size="sm"
                    @click="confirmUnlink(p)"
                    :disabled="unlinkForm.processing"
                  >
                    Отвязать
                  </RButton>
                </template>
                <template v-else>
                  <a
                    :href="route('lms.social.link', { event: event?.slug, provider: p.key })"
                    class="inline-block"
                  >
                    <RButton variant="outline" size="sm">
                      Привязать
                    </RButton>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </template>
      </RCard>

      <RModal
        v-model="showConfirmModal"
        :title="confirmModalTitle"
        :subtitle="confirmModalText"
        size="sm"
      >
        <template #footer>
          <div class="flex justify-end gap-3">
            <RButton variant="outline" size="sm" @click="showConfirmModal = false">
              Отмена
            </RButton>
            <RButton
              :variant="confirmAction === 'unlink' ? 'danger' : 'primary'"
              size="sm"
              @click="executeConfirmedAction"
              :loading="unlinkForm.processing"
            >
              {{ confirmAction === 'unlink' ? 'Отвязать' : 'Сменить аккаунт' }}
            </RButton>
          </div>
        </template>
      </RModal>
    </div>
  </LmsLayout>
</template>

<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, required: true },
  profile: { type: Object, default: () => ({}) },
  socialAccounts: { type: Object, default: () => ({}) },
})

const user = computed(() => props.user || usePage().props.auth?.user || {})

const fullName = computed(() => {
  const u = user.value
  const parts = [u.last_name, u.first_name, u.patronymic].filter(Boolean)
  return parts.length > 0 ? parts.join(' ') : u.name || ''
})

const socialAccounts = computed(() => props.socialAccounts || {})

const providers = [
  {
    key: 'vkontakte',
    label: 'VK ID',
    bgClass: 'bg-[#0077FF]',
    icon: '<svg width="20" height="20" viewBox="0 0 28 28" fill="none"><path d="M4.54 1.66h18.92c1.59 0 2.88 1.29 2.88 2.88v18.92c0 1.59-1.29 2.88-2.88 2.88H4.54c-1.59 0-2.88-1.29-2.88-2.88V4.54c0-1.59 1.29-2.88 2.88-2.88z" fill="#0077FF"/><path d="M14.67 19.47c-5.62 0-8.82-3.86-8.95-10.28h2.81c.09 4.71 2.17 6.71 3.81 7.12V9.19h2.65v4.07c1.62-.17 3.33-2.02 3.91-4.07h2.65c-.44 2.53-2.3 4.38-3.62 5.15 1.32.62 3.4 2.25 4.2 5.13h-2.92c-.63-1.95-2.18-3.46-4.22-3.66v3.66h-.32z" fill="white"/></svg>',
  },
  {
    key: 'yandex',
    label: 'Яндекс ID',
    bgClass: 'bg-black',
    icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z" fill="#FC3F1D"/><path d="M13.63 18.71h1.67V5.29h-2.62c-2.79 0-4.25 1.4-4.25 3.49 0 1.71.79 2.74 2.44 3.83l-2.67 6.1h1.77l2.86-7.06-.76-.52c-1.35-.92-1.93-1.65-1.93-2.88 0-1.3.92-2.2 2.54-2.2h.95v12.66z" fill="white"/></svg>',
  },
]

const unlinkForm = useForm({})

const showConfirmModal = ref(false)
const confirmAction = ref('')
const confirmProvider = ref(null)
const confirmModalTitle = ref('')
const confirmModalText = ref('')

function confirmRelink(provider) {
  confirmAction.value = 'relink'
  confirmProvider.value = provider
  confirmModalTitle.value = `Сменить аккаунт ${provider.label}?`
  confirmModalText.value = `Текущая привязка к ${provider.label} будет заменена на новый аккаунт. Вы не сможете войти через старый аккаунт.`
  showConfirmModal.value = true
}

function confirmUnlink(provider) {
  confirmAction.value = 'unlink'
  confirmProvider.value = provider
  confirmModalTitle.value = `Отвязать ${provider.label}?`
  confirmModalText.value = `Вы не сможете входить в систему через ${provider.label}. Привязать аккаунт можно будет снова в любой момент.`
  showConfirmModal.value = true
}

function executeConfirmedAction() {
  if (confirmAction.value === 'unlink') {
    unlinkForm.delete(route('lms.social.unlink', { event: props.event?.slug, provider: confirmProvider.value.key }), {
      preserveScroll: true,
      onSuccess: () => { showConfirmModal.value = false },
    })
  } else {
    showConfirmModal.value = false
    window.location.href = route('lms.social.link', { event: props.event?.slug, provider: confirmProvider.value.key })
  }
}

const form = useForm({
  phone: props.profile?.phone ?? '',
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
