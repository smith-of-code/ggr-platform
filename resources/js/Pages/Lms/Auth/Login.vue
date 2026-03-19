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
