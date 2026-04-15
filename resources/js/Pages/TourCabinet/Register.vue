<template>
  <div class="flex min-h-dvh flex-col bg-gray-50 px-4 py-10 font-sans">
    <Head title="Регистрация — ЛК туров" />
    <div class="mx-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
      <h1 class="text-xl font-bold text-gray-900">Регистрация участника</h1>
      <p class="mt-1 text-sm text-gray-500">Аккаунт только для личного кабинета туров / конкурса</p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <RInput v-model="form.name" label="Имя" required :error="form.errors.name" autocomplete="name" />
        <RInput v-model="form.email" type="email" label="Email" required :error="form.errors.email" autocomplete="email" />
        <RInput v-model="form.password" type="password" label="Пароль" required :error="form.errors.password" autocomplete="new-password" />
        <RInput v-model="form.password_confirmation" type="password" label="Пароль ещё раз" required :error="form.errors.password_confirmation" autocomplete="new-password" />

        <div>
          <label class="flex cursor-pointer items-start gap-3">
            <input
              v-model="form.consent"
              type="checkbox"
              class="mt-1 h-4 w-4 rounded border-gray-300 text-rosatom-600 focus:ring-rosatom-500"
              :true-value="true"
              :false-value="false"
            />
            <span class="text-sm text-gray-600">
              Даю
              <a :href="$page.props.consentDocumentUrl" target="_blank" rel="noopener noreferrer" class="text-rosatom-600 underline hover:text-rosatom-800">согласие на обработку персональных данных</a>
            </span>
          </label>
          <p v-if="form.errors.consent" class="mt-1 text-sm text-red-600">{{ form.errors.consent }}</p>
        </div>

        <RButton type="submit" variant="primary" block :loading="form.processing" :disabled="form.processing || !form.consent">
          Зарегистрироваться
        </RButton>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Уже зарегистрированы?
        <Link :href="route('tour-cabinet.login')" class="font-medium text-rosatom-600 hover:text-rosatom-800">Войти</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  consent: false,
})

function submit() {
  form.post(route('tour-cabinet.register.store'))
}
</script>
