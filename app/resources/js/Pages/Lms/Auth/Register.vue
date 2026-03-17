<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Регистрация – ВШГР" />

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
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-4 h-24 w-auto" />
          <p class="font-brand text-lg font-bold text-rosatom-800">ВШГР</p>
        </div>

        <h1 class="text-2xl font-bold text-gray-900">Регистрация</h1>
        <p class="mt-2 text-sm text-gray-500">Создайте аккаунт для доступа к курсам</p>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
          <RInput
            v-model="form.name"
            label="Имя"
            placeholder="Ваше имя"
            required
            :error="form.errors.name"
          />

          <RInput
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="your@email.com"
            required
            :error="form.errors.email"
          />

          <RInput
            v-model="form.password"
            label="Пароль"
            type="password"
            placeholder="Минимум 8 символов"
            required
            :error="form.errors.password"
          />

          <RInput
            v-model="form.password_confirmation"
            label="Подтвердите пароль"
            type="password"
            placeholder="Повторите пароль"
            required
            :error="form.errors.password_confirmation"
          />

          <RButton
            type="submit"
            variant="primary"
            size="lg"
            block
            :loading="form.processing"
            :disabled="form.processing"
          >
            Зарегистрироваться
          </RButton>
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
