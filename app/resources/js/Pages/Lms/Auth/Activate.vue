<template>
  <div class="flex min-h-screen font-sans">
    <Head :title="`Активация — ${event.title}`" />

    <div class="hidden w-1/2 flex-col justify-between bg-rosatom-800 p-12 lg:flex">
      <div>
        <img src="/images/logo-horizontal.png" alt="ГГР" class="h-20 w-auto" />
      </div>
      <div>
        <h2 class="font-brand text-4xl font-bold leading-tight text-white lg:text-5xl">
          Высшая школа<br />гостеприимного развития
        </h2>
        <p class="mt-6 max-w-lg text-lg text-rosatom-300">
          Образовательная платформа для подготовки специалистов в области гостеприимства гостеприимных городов Росатома
        </p>
      </div>
      <p class="text-sm text-rosatom-500">&copy; {{ new Date().getFullYear() }} Росатом</p>
    </div>

    <div class="flex flex-1 flex-col items-center justify-center bg-white px-6 py-12">
      <div class="w-full max-w-md">
        <div class="mb-8 text-center lg:hidden">
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-4 h-24 w-auto rounded-lg" />
          <p class="font-brand text-lg font-bold text-rosatom-800">ВШГР</p>
        </div>

        <template v-if="error">
          <div class="rounded-xl border border-red-200 bg-red-50 p-6 text-center">
            <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-400" />
            <h2 class="mt-4 text-lg font-semibold text-red-800">Ссылка недействительна</h2>
            <p class="mt-2 text-sm text-red-600">{{ error }}</p>
            <a :href="route('lms.login', event.slug)"
               class="mt-4 inline-block text-sm font-medium text-rosatom-600 hover:underline">
              Перейти на страницу входа
            </a>
          </div>
        </template>

        <template v-else-if="profile">
          <h1 class="text-2xl font-bold text-gray-900">Добро пожаловать!</h1>
          <p class="mt-2 text-sm text-gray-500">
            Проверьте свои данные и установите пароль для входа на платформу
          </p>

          <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-5 space-y-3">
            <div v-if="fullName">
              <span class="text-xs font-medium text-gray-400">ФИО</span>
              <p class="text-sm font-medium text-gray-800">{{ fullName }}</p>
            </div>
            <div v-if="profile.email">
              <span class="text-xs font-medium text-gray-400">Email</span>
              <p class="text-sm text-gray-800">{{ profile.email }}</p>
            </div>
            <div v-if="profile.phone">
              <span class="text-xs font-medium text-gray-400">Телефон</span>
              <p class="text-sm text-gray-800">{{ profile.phone }}</p>
            </div>
            <div v-if="profile.position">
              <span class="text-xs font-medium text-gray-400">Должность</span>
              <p class="text-sm text-gray-800">{{ profile.position }}</p>
            </div>
            <div v-if="profile.city">
              <span class="text-xs font-medium text-gray-400">Город</span>
              <p class="text-sm text-gray-800">{{ profile.city }}</p>
            </div>
            <div v-if="profile.role">
              <span class="text-xs font-medium text-gray-400">Роль</span>
              <p class="text-sm text-gray-800">{{ profile.role }}</p>
            </div>
          </div>

          <form @submit.prevent="submit" class="mt-6 space-y-5">
            <RInput v-model="form.password" type="password" label="Пароль"
                    placeholder="Минимум 8 символов" :error="form.errors.password" required />

            <RInput v-model="form.password_confirmation" type="password" label="Подтверждение пароля"
                    placeholder="Повторите пароль" required />

            <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing">
              Установить пароль и войти
            </RButton>
          </form>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, required: true },
  profile: { type: Object, default: null },
  token: { type: String, default: '' },
  error: { type: String, default: null },
})

const fullName = computed(() => {
  if (!props.profile) return ''
  return [props.profile.last_name, props.profile.first_name, props.profile.patronymic]
    .filter(Boolean)
    .join(' ')
})

const form = useForm({
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('lms.activate.submit', { event: props.event.slug, token: props.token }), {
    onFinish: () => form.reset(),
  })
}
</script>
