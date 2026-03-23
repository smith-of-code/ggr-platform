<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Вход – ВШГР" />

    <!-- Left panel: branding -->
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

    <!-- Right panel: form -->
    <div class="flex flex-1 flex-col items-center justify-center bg-white px-6 py-12">
      <div class="w-full max-w-md">
        <!-- Mobile logo -->
        <div class="mb-8 text-center lg:hidden">
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-4 h-24 w-auto rounded-lg" />
          <p class="font-brand text-lg font-bold text-rosatom-800">ВШГР</p>
        </div>

        <h1 class="text-2xl font-bold text-gray-900">Вход в систему</h1>
        <p class="mt-2 text-sm text-gray-500">Введите email и пароль для доступа к платформе</p>

        <div v-if="status" class="mt-4 rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
          <RInput v-model="form.email" type="email" label="Email" placeholder="your@email.com" :error="form.errors.email" required id="email" />

          <RInput v-model="form.password" type="password" label="Пароль" placeholder="••••••••" :error="form.errors.password" required id="password" />

          <RCheckbox v-model="form.remember" label="Запомнить меня" />

          <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing">
            Войти
          </RButton>
        </form>

        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="bg-white px-4 text-gray-400">или</span>
            </div>
          </div>

          <div v-if="$page.props.errors?.social" class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
            {{ $page.props.errors.social }}
          </div>

          <div class="mt-4 flex flex-col gap-3">
            <a
              :href="route('lms.social.login', { event: event?.slug, provider: 'vkontakte' })"
              class="flex h-11 w-full items-center justify-center gap-2.5 rounded-lg bg-[#0077FF] px-5 text-sm font-medium text-white transition hover:bg-[#0071F2] active:bg-[#0069E0]"
            >
              <svg width="24" height="24" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.54 1.66h18.92c1.59 0 2.88 1.29 2.88 2.88v18.92c0 1.59-1.29 2.88-2.88 2.88H4.54c-1.59 0-2.88-1.29-2.88-2.88V4.54c0-1.59 1.29-2.88 2.88-2.88z" fill="#0077FF"/><path d="M14.67 19.47c-5.62 0-8.82-3.86-8.95-10.28h2.81c.09 4.71 2.17 6.71 3.81 7.12V9.19h2.65v4.07c1.62-.17 3.33-2.02 3.91-4.07h2.65c-.44 2.53-2.3 4.38-3.62 5.15 1.32.62 3.4 2.25 4.2 5.13h-2.92c-.63-1.95-2.18-3.46-4.22-3.66v3.66h-.32z" fill="white"/></svg>
              Войти с VK&nbsp;ID
            </a>
            <a
              :href="route('lms.social.login', { event: event?.slug, provider: 'yandex' })"
              class="flex h-11 w-full items-center justify-center gap-2.5 rounded-lg bg-black px-5 text-sm font-medium text-white transition hover:bg-[#222] active:bg-[#333]"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z" fill="#FC3F1D"/><path d="M13.63 18.71h1.67V5.29h-2.62c-2.79 0-4.25 1.4-4.25 3.49 0 1.71.79 2.74 2.44 3.83l-2.67 6.1h1.77l2.86-7.06-.76-.52c-1.35-.92-1.93-1.65-1.93-2.88 0-1.3.92-2.2 2.54-2.2h.95v12.66z" fill="white"/></svg>
              Войти с Яндекс&nbsp;ID
            </a>
          </div>
        </div>

        <p class="mt-8 text-center text-sm text-gray-400">
          Для получения доступа обратитесь к администратору
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  canResetPassword: { type: Boolean, default: false },
  status: { type: String, default: '' },
  event: { type: Object, default: () => ({ slug: '' }) },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  const url = props.event?.slug
    ? route('lms.login', { event: props.event.slug })
    : route('lms.login')
  form.post(url, {
    onFinish: () => form.reset('password'),
  })
}
</script>
