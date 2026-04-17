<template>
  <div class="flex min-h-dvh flex-col bg-gray-50 px-4 py-10 font-sans">
    <Head title="Вход — ЛК туров" />
    <div class="mx-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
      <h1 class="text-xl font-bold text-gray-900">Личный кабинет туров</h1>
      <p class="mt-1 text-sm text-gray-500">Вход для участников конкурса (отдельная регистрация от сайта)</p>
      <p class="mt-3 rounded-lg border border-rosatom-100 bg-rosatom-50/80 px-3 py-2 text-sm leading-snug text-slate-700">
        Аккаунт портала или ВШГР?
        <Link :href="portalLoginUrl" class="font-semibold text-rosatom-700 underline decoration-rosatom-300 underline-offset-2 hover:text-rosatom-900">
          Войти через общую форму
        </Link>
        — там вход по email или телефону и паролю; для обучения ВШГР переключите режим «Я студент».
      </p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <RInput v-model="form.email" type="email" label="Email" required :error="form.errors.email" autocomplete="username" />
        <RInput v-model="form.password" type="password" label="Пароль" required :error="form.errors.password" autocomplete="current-password" />
        <RCheckbox v-model="form.remember" label="Запомнить меня" />
        <RButton type="submit" variant="primary" block :loading="form.processing" :disabled="form.processing">
          Войти
        </RButton>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Нет аккаунта?
        <Link :href="route('tour-cabinet.register')" class="font-medium text-rosatom-600 hover:text-rosatom-800">Регистрация</Link>
      </p>
      <p class="mt-4 text-center text-sm">
        <Link :href="route('home')" class="text-gray-500 underline hover:text-gray-700">На главную сайта</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const portalLoginUrl = computed(() =>
  route('login', { redirect: route('tour-cabinet.dashboard') }),
)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('tour-cabinet.login.store'))
}
</script>
