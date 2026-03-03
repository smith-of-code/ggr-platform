<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Регистрация – ВШГР" />

    <!-- Left panel: branding -->
    <div class="hidden w-1/2 flex-col justify-between bg-rosatom-800 p-12 lg:flex">
      <div>
        <img src="/images/logo-horizontal.png" alt="ГГР" class="h-14 brightness-0 invert" />
      </div>
      <div>
        <h2 class="font-brand text-3xl font-bold leading-tight text-white">
          Высшая школа<br />гостеприимного развития
        </h2>
        <p class="mt-4 max-w-md text-rosatom-300">
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
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-3 h-16" />
          <p class="font-brand text-lg font-bold text-rosatom-800">ВШГР</p>
        </div>

        <h1 class="text-2xl font-bold text-gray-900">Регистрация</h1>
        <p class="mt-2 text-sm text-gray-500">Создайте аккаунт для доступа к курсам</p>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
            <input
              id="name"
              type="text"
              v-model="form.name"
              required
              autofocus
              autocomplete="name"
              placeholder="Ваше имя"
              class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              required
              autocomplete="username"
              placeholder="your@email.com"
              class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
            <input
              id="password"
              type="password"
              v-model="form.password"
              required
              autocomplete="new-password"
              placeholder="Минимум 8 символов"
              class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Подтвердите пароль</label>
            <input
              id="password_confirmation"
              type="password"
              v-model="form.password_confirmation"
              required
              autocomplete="new-password"
              placeholder="Повторите пароль"
              class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p v-if="form.errors.password_confirmation" class="mt-1.5 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-rosatom-600 py-3 font-semibold text-white transition hover:bg-rosatom-700 focus:outline-none focus:ring-2 focus:ring-rosatom-500 focus:ring-offset-2 disabled:opacity-50"
          >
            <span v-if="form.processing" class="inline-flex items-center gap-2">
              <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Регистрация...
            </span>
            <span v-else>Зарегистрироваться</span>
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-500">
          Уже есть аккаунт?
          <Link :href="route('lms.login', { event: event?.slug })" class="font-semibold text-rosatom-600 hover:text-rosatom-700">
            Войти
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  event: { type: Object, default: () => ({ slug: '' }) },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  const url = props.event?.slug
    ? route('lms.register', { event: props.event.slug })
    : route('lms.register')
  form.post(url, {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
