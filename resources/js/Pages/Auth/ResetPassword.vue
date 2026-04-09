<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Новый пароль" />

    <div class="hidden w-1/2 flex-col justify-between bg-rosatom-800 p-12 lg:flex">
      <div>
        <img src="/images/logo-horizontal.svg" alt="ГГР" class="h-20 w-auto brightness-0 invert" />
      </div>
      <div>
        <h2 class="font-brand text-4xl font-bold leading-tight text-white lg:text-5xl">
          Высшая школа<br />гостеприимства Росатома - 2026
        </h2>
      </div>
      <p class="text-sm text-rosatom-500">&copy; {{ new Date().getFullYear() }} Росатом</p>
    </div>

    <div class="flex flex-1 flex-col items-center justify-center bg-white px-6 py-12">
      <div class="w-full max-w-md">
        <div class="mb-8 flex justify-center lg:hidden">
          <img src="/images/logo-horizontal.svg" alt="ГГР" class="h-20 w-auto" />
        </div>

        <h1 class="text-2xl font-bold text-gray-900">Новый пароль</h1>
        <p class="mt-2 text-sm text-gray-500">Придумайте надёжный пароль для вашего аккаунта.</p>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
          <RInput
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="your@email.com"
            :error="form.errors.email"
            required
            id="email"
            autocomplete="username"
          />

          <RInput
            v-model="form.password"
            type="password"
            label="Новый пароль"
            placeholder="Минимум 8 символов"
            :error="form.errors.password"
            required
            id="password"
            autocomplete="new-password"
          />

          <RInput
            v-model="form.password_confirmation"
            type="password"
            label="Подтвердите пароль"
            placeholder="Повторите пароль"
            :error="form.errors.password_confirmation"
            required
            id="password_confirmation"
            autocomplete="new-password"
          />

          <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing">
            Сохранить пароль
          </RButton>
        </form>

        <p class="mt-8 text-center text-sm text-gray-500">
          <Link :href="route('login')" class="font-medium text-rosatom-600 hover:text-rosatom-700 hover:underline">
            ← Вернуться ко входу
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  email: { type: String, required: true },
  token: { type: String, required: true },
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
