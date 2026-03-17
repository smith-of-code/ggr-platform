<template>
  <div class="flex min-h-screen font-sans">
    <Head title="Вход – ВШГР" />

    <!-- Left panel: branding -->
    <div class="hidden w-1/2 flex-col justify-between bg-rosatom-800 p-12 lg:flex">
      <div>
        <img src="/images/logo-horizontal.png" alt="ГГР" class="w-full max-w-md brightness-0 invert" />
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
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="your@email.com"
              required
              class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              :class="{ 'border-red-400': form.errors.email }"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              required
              class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              :class="{ 'border-red-400': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-rosatom-600 focus:ring-rosatom-500"
            />
            <span class="text-sm text-gray-600">Запомнить меня</span>
          </label>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Вход...' : 'Войти' }}
          </button>
        </form>

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
