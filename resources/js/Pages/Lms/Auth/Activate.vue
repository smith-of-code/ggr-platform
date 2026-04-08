<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Высшая школа гостеприимства Росатома - 2026" />

    <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col lg:justify-between">
      <div class="absolute inset-0 bg-gradient-to-br from-rosatom-900 via-rosatom-800 to-rosatom-700" />
      <div class="absolute -left-20 -top-20 h-80 w-80 rounded-full bg-white/[0.04]" />
      <div class="absolute -bottom-32 -right-32 h-[500px] w-[500px] rounded-full bg-white/[0.03]" />
      <div class="absolute right-10 top-1/3 h-40 w-40 rounded-full bg-rosatom-500/10 blur-2xl" />
      <div class="absolute bottom-1/4 left-16 h-24 w-24 rounded-full bg-rosatom-400/10 blur-xl" />
      <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')" />
      <div class="relative z-10 flex flex-1 flex-col justify-between p-12">
        <div>
          <img src="/images/logo-horizontal.svg" alt="ГГР" class="h-20 w-auto brightness-0 invert" />
        </div>
        <div>
          <h2 class="font-brand text-4xl font-bold leading-tight text-white lg:text-5xl">
            Высшая школа<br />гостеприимства Росатома - 2026
          </h2>
<!--          <p class="mt-6 max-w-lg text-lg text-rosatom-300/80">-->
<!--            Образовательная платформа для подготовки специалистов в области гостеприимства гостеприимных городов Росатома-->
<!--          </p>-->
        </div>
        <p class="text-sm text-white/30">&copy; {{ new Date().getFullYear() }} Росатом</p>
      </div>
    </div>

    <div class="relative flex flex-1 flex-col items-center justify-center px-6 py-12">
      <div class="absolute inset-0 bg-gradient-to-br from-white via-white to-rosatom-50/60" />
      <div class="relative z-10 w-full max-w-md">
        <div class="mb-8 flex justify-center lg:hidden">
          <img src="/images/logo-horizontal.svg" alt="ГГР" class="h-20 w-auto" />
        </div>

        <template v-if="error">
          <div class="rounded-xl border border-red-200 bg-red-50 p-6 text-center">
            <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-400" />
            <h2 class="mt-4 text-lg font-semibold text-red-800">Ссылка недействительна</h2>
            <p class="mt-2 text-sm text-red-600">{{ error }}</p>
            <a :href="route('login')"
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

            <div>
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  v-model="form.consent"
                  type="checkbox"
                  class="mt-0.5 h-4 w-4 rounded border-gray-300 text-rosatom-600 focus:ring-rosatom-500"
                />
                <span class="text-sm text-gray-600">
                  Регистрируясь на платформе, вы даете
                  <a :href="$page.props.consentDocumentUrl" target="_blank" class="text-rosatom-600 underline hover:text-rosatom-700">согласие на обработку персональных данных</a>
                </span>
              </label>
              <p v-if="form.errors.consent" class="mt-1 text-sm text-red-600">{{ form.errors.consent }}</p>
            </div>

            <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing || !form.consent">
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
  consent: false,
})

const submit = () => {
  form.post(route('lms.activate.submit', { event: props.event.slug, token: props.token }), {
    onFinish: () => form.reset(),
  })
}
</script>
