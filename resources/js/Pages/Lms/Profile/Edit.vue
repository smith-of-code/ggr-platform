<template>
  <LmsLayout :event="event" :user="user" :profile="profile">
    <Head :title="`Профиль – ${event?.title || event?.name}`" />
    <div class="space-y-6">
      <h1 class="font-brand text-2xl font-bold text-gray-900">Личный кабинет</h1>

      <!-- Profile completed banner -->
      <div
        v-if="showCompletedBanner"
        class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4"
      >
        <div class="flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <div>
            <p class="font-semibold text-emerald-800">Профиль заполнен!</p>
            <p class="mt-1 text-sm text-emerald-700">Переходите к выбору курса.</p>
          </div>
        </div>
      </div>

      <!-- Profile incomplete banner -->
      <div
        v-if="!isProfileComplete && missingFields.length > 0 && !showCompletedBanner"
        class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4"
      >
        <div class="flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <div>
            <p class="font-semibold text-amber-800">Заполните профиль</p>
            <p class="mt-1 text-sm text-amber-700">
              Для записи на курс необходимо заполнить все обязательные поля. Не хватает:
            </p>
            <ul class="mt-2 list-inside list-disc space-y-0.5 text-sm text-amber-700">
              <li v-for="field in missingFields" :key="field">{{ field }}</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Avatar card -->
      <ProfileCard
        :full-name="fullName"
        :avatar="avatarDisplayUrl"
        :position="form.position || profile?.position"
        :workplace="selectedCityName || profile?.city"
        :phone="form.phone || profile?.phone"
        :email="form.email || user?.email"
        :points="profile?.points"
        :rank="profile?.rank"
        :status="profile?.status"
      />

      <!-- Personal data -->
      <RCard>
        <template #default>
          <h2 class="mb-5 text-lg font-semibold text-gray-900">Личные данные</h2>
          <form @submit.prevent="submit" class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-3">
              <RInput
                v-model="form.last_name"
                label="Фамилия *"
                placeholder="Иванов"
                :error="form.errors.last_name"
              />
              <RInput
                v-model="form.first_name"
                label="Имя *"
                placeholder="Иван"
                :error="form.errors.first_name"
              />
              <RInput
                v-model="form.patronymic"
                label="Отчество"
                placeholder="Иванович"
                :error="form.errors.patronymic"
              />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <RInput
                v-model="form.email"
                label="Email *"
                type="email"
                placeholder="email@example.com"
                :error="form.errors.email"
              />
              <RInput
                v-model="form.phone"
                label="Телефон *"
                type="tel"
                placeholder="+7 (___) ___-__-__"
                :error="form.errors.phone"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-500">Удобный канал коммуникации</label>
              <div class="flex items-center gap-6">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input
                    type="radio"
                    v-model="form.preferred_channel"
                    value="telegram"
                    class="h-4 w-4 border-gray-300 text-rosatom-500 focus:ring-rosatom-500"
                  />
                  Telegram
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                  <input
                    type="radio"
                    v-model="form.preferred_channel"
                    value="max"
                    class="h-4 w-4 border-gray-300 text-rosatom-500 focus:ring-rosatom-500"
                  />
                  MAX
                </label>
              </div>
              <p v-if="form.errors.preferred_channel" class="mt-1 text-sm text-red-600">{{ form.errors.preferred_channel }}</p>
            </div>

            <SearchSelect
              v-model="form.city"
              :options="cityOptions"
              value-key="value"
              label-key="label"
              label="Город *"
              placeholder="Выберите город"
              search-placeholder="Поиск города..."
              :error="form.errors.city"
            />

            <div class="grid gap-5 sm:grid-cols-2">
              <RInput
                v-model="form.organization"
                label="Организация *"
                placeholder="Название организации"
                :error="form.errors.organization"
              />
              <RInput
                v-model="form.position"
                label="Должность *"
                placeholder="Ваша должность"
                :error="form.errors.position"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-500">Описание проекта или идеи *</label>
              <textarea
                v-model="form.project_description"
                rows="5"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 transition placeholder:text-gray-400 focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
                placeholder="Напишите название и стадию развития проекта на текущий момент. Например: Создание сувенирной продукции в городе Глазов. Проект развиваем 6 месяцев, отшили партию футболок и толстовок с креативными надписями. В месяц получаем 15 заказов, есть 1 сотрудник. Если у вас еще нет проекта, опишите подробно вашу идею."
              />
              <p v-if="form.errors.project_description" class="mt-1 text-sm text-red-600">{{ form.errors.project_description }}</p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-500">Аватар</label>
              <div class="flex items-center gap-4">
                <RAvatar
                  v-if="avatarPreview || profile?.avatar"
                  :src="avatarPreview || fileUrl(profile?.avatar)"
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
                <RButton variant="outline" type="button">
                  Отмена
                </RButton>
              </Link>
            </div>
          </form>
        </template>
      </RCard>

      <!-- Documents -->
      <RCard>
        <template #default>
          <h2 class="mb-5 text-lg font-semibold text-gray-900">Документы</h2>
          <div class="space-y-4">
            <div
              v-for="dt in docConfig"
              :key="dt.type"
              class="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ dt.label }}</p>
                <p v-if="uploadedDoc(dt.type)" class="mt-1 text-xs text-green-600">
                  Загружен: {{ uploadedDoc(dt.type).original_name }}
                </p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <a
                  v-if="dt.hasTemplate"
                  :href="route('lms.profile.templates.download', { event: event?.slug, type: dt.type })"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-rosatom-50 px-3 py-1.5 text-sm font-medium text-rosatom-700 transition hover:bg-rosatom-100"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                  </svg>
                  Скачать шаблон
                </a>
                <label class="cursor-pointer">
                  <input
                    type="file"
                    class="hidden"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    @change="(e) => uploadDoc(dt.type, e)"
                  />
                  <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    {{ uploadedDoc(dt.type) ? 'Заменить' : 'Загрузить' }}
                  </span>
                </label>
                <button
                  v-if="uploadedDoc(dt.type)"
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100"
                  :disabled="docDeleteProcessing"
                  @click="deleteDoc(uploadedDoc(dt.type).id)"
                >
                  Удалить
                </button>
              </div>
            </div>
          </div>
        </template>
      </RCard>

      <!-- Social accounts -->
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
                    type="button"
                    @click="confirmRelink(p)"
                  >
                    Сменить
                  </RButton>
                  <RButton
                    variant="outline"
                    size="sm"
                    type="button"
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
                    <RButton variant="outline" size="sm" type="button">
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
            <RButton variant="outline" size="sm" type="button" @click="showConfirmModal = false">
              Отмена
            </RButton>
            <RButton
              :variant="confirmAction === 'unlink' ? 'danger' : 'primary'"
              size="sm"
              type="button"
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
import { ref, computed, watch } from 'vue'
import LmsLayout from '@/Layouts/LmsLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import { fileUrl } from '@/lib/fileUrl'

const props = defineProps({
  event: { type: Object, required: true },
  user: { type: Object, required: true },
  profile: { type: Object, default: () => ({}) },
  socialAccounts: { type: Object, default: () => ({}) },
  isProfileComplete: { type: Boolean, default: false },
  missingFields: { type: Array, default: () => [] },
  documentTypes: { type: Array, default: () => [] },
  documentTypesWithTemplate: { type: Array, default: () => [] },
})

const page = usePage()
const showCompletedBanner = ref(false)
const wasIncomplete = ref(!props.isProfileComplete)

watch(() => page.props?.flash?.profile_completed, (val) => {
  if (val) showCompletedBanner.value = true
}, { immediate: true })

watch(() => props.isProfileComplete, (val) => {
  if (val && wasIncomplete.value) {
    showCompletedBanner.value = true
    wasIncomplete.value = false
  }
})

const user = computed(() => props.user || usePage().props.auth?.user || {})

const fullName = computed(() => {
  const u = user.value
  const parts = [u.last_name, u.first_name, u.patronymic].filter(Boolean)
  return parts.length > 0 ? parts.join(' ') : u.name || ''
})

const CITY_NAMES = [
  'Ангарск', 'Байкальск', 'Балаково', 'Билибино', 'Волгодонск',
  'Глазов', 'Десногорск', 'Димитровград', 'Железногорск',
  'Заречный (Пензенская область)', 'Заречный (Свердловская область)',
  'Зеленогорск', 'Краснокаменск', 'Курчатов', 'Лесной', 'Неман',
  'Нововоронеж', 'Новоуральск', 'Обнинск', 'Озёрск', 'Певек',
  'Полярные Зори', 'Саров', 'Северск', 'Снежинск', 'Советск',
  'Сосновый Бор', 'Трёхгорный', 'Удомля', 'Усолье-Сибирское', 'Электросталь',
]

const cityOptions = CITY_NAMES.map(name => ({ value: name, label: name }))

const selectedCityName = computed(() => form.city || '')

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
  last_name: props.user?.last_name ?? '',
  first_name: props.user?.first_name ?? '',
  patronymic: props.user?.patronymic ?? '',
  email: props.user?.email ?? '',
  phone: props.profile?.phone ?? '',
  city: props.profile?.city ?? '',
  organization: props.profile?.organization ?? '',
  position: props.profile?.position ?? '',
  project_description: props.profile?.project_description ?? '',
  preferred_channel: props.profile?.preferred_channel ?? '',
  avatar: null,
})

const avatarPreview = ref(null)

const avatarDisplayUrl = computed(() => {
  if (avatarPreview.value) return avatarPreview.value
  return fileUrl(props.profile?.avatar)
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
    preserveScroll: true,
  })
}

// Documents
const docConfig = [
  { type: 'enrollment_application', label: 'Заявление на зачисление *', hasTemplate: true },
  { type: 'snils', label: 'Скан СНИЛС *', hasTemplate: false },
  { type: 'diploma', label: 'Скан диплома о высшем или среднем образовании *', hasTemplate: false },
  { type: 'personal_data_consent', label: 'Согласие на обработку персональных данных *', hasTemplate: true },
  { type: 'name_change_certificate', label: 'Свидетельство о перемене фамилии (при наличии)', hasTemplate: false },
]

const docDeleteProcessing = ref(false)

function uploadedDoc(type) {
  return props.profile?.documents?.find(d => d.type === type) || null
}

function uploadDoc(type, e) {
  const file = e.target?.files?.[0]
  if (!file) return

  const docForm = useForm({ type, file })
  docForm.post(route('lms.profile.documents.upload', { event: props.event?.slug }), {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => { e.target.value = '' },
  })
}

function deleteDoc(docId) {
  if (!confirm('Удалить документ?')) return
  docDeleteProcessing.value = true
  router.delete(route('lms.profile.documents.delete', { event: props.event?.slug, document: docId }), {
    preserveScroll: true,
    onFinish: () => { docDeleteProcessing.value = false },
  })
}
</script>
