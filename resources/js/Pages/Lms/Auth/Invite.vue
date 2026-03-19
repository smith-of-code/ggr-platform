<template>
  <div class="flex min-h-screen font-sans">
    <Head :title="`Регистрация – ${event?.title || 'ВШГР'}`" />

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

    <!-- Right panel -->
    <div class="flex flex-1 flex-col items-center justify-center bg-white px-6 py-12">
      <div class="w-full max-w-md">
        <!-- Mobile logo -->
        <div class="mb-8 text-center lg:hidden">
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-4 h-24 w-auto" />
          <p class="font-brand text-lg font-bold text-rosatom-800">ВШГР</p>
        </div>

        <!-- Invalid invitation -->
        <template v-if="error">
          <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
              <XCircleIcon class="h-8 w-8 text-red-500" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Ссылка недействительна</h1>
            <p class="mt-2 text-sm text-gray-500">{{ error }}</p>
            <Link
              :href="route('lms.login', { event: event?.slug })"
              class="mt-6 inline-block rounded-xl bg-rosatom-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700"
            >
              Перейти к входу
            </Link>
          </div>
        </template>

        <!-- Valid invitation — registration form -->
        <template v-else>
          <h1 class="text-2xl font-bold text-gray-900">Регистрация по приглашению</h1>
          <p class="mt-2 text-sm text-gray-500">
            {{ event?.title }}
            <span v-if="invitation?.role"> · Роль: {{ invitation.role }}</span>
          </p>
          <p v-if="invitation?.label" class="mt-1 text-xs text-gray-400">{{ invitation.label }}</p>

          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
              <RInput
                v-model="form.last_name"
                label="Фамилия"
                placeholder="Иванов"
                required
                :error="form.errors.last_name"
              />
              <RInput
                v-model="form.first_name"
                label="Имя"
                placeholder="Иван"
                required
                :error="form.errors.first_name"
              />
            </div>

            <RInput
              v-model="form.patronymic"
              label="Отчество"
              placeholder="Иванович"
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
              v-model="form.phone"
              label="Телефон"
              type="tel"
              placeholder="+7 (900) 123-45-67"
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

            <div v-if="form.errors.token" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
              {{ form.errors.token }}
            </div>

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
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { XCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: { type: Object, default: () => ({}) },
  invitation: { type: Object, default: null },
  error: { type: String, default: null },
})

const form = useForm({
  last_name: '',
  first_name: '',
  patronymic: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('lms.invite.register', {
    event: props.event?.slug,
    token: props.invitation?.token,
  }), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
