<template>
  <div class="flex min-h-screen font-sans">
    <Head :title="`Регистрация – ${event?.title || 'ВШГР'}`" />

    <!-- Left panel: branding -->
    <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col lg:justify-between">
      <div class="absolute inset-0 bg-gradient-to-br from-rosatom-900 via-rosatom-800 to-rosatom-700" />
      <div class="absolute -left-20 -top-20 h-80 w-80 rounded-full bg-white/[0.04]" />
      <div class="absolute -bottom-32 -right-32 h-[500px] w-[500px] rounded-full bg-white/[0.03]" />
      <div class="absolute right-10 top-1/3 h-40 w-40 rounded-full bg-rosatom-500/10 blur-2xl" />
      <div class="absolute bottom-1/4 left-16 h-24 w-24 rounded-full bg-rosatom-400/10 blur-xl" />
      <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')" />
      <div class="relative z-10 flex flex-1 flex-col justify-between p-12">
        <div>
          <img src="/images/logo-horizontal.png" alt="ГГР" class="h-20 w-auto" />
        </div>
        <div>
          <h2 class="font-brand text-4xl font-bold leading-tight text-white lg:text-5xl">
            Высшая школа<br />гостеприимного развития
          </h2>
          <p class="mt-6 max-w-lg text-lg text-rosatom-300/80">
            Образовательная платформа для подготовки специалистов в области гостеприимства гостеприимных городов Росатома
          </p>
        </div>
        <p class="text-sm text-white/30">&copy; {{ new Date().getFullYear() }} Росатом</p>
      </div>
    </div>

    <!-- Right panel -->
    <div class="relative flex flex-1 flex-col items-center justify-center px-6 py-12">
      <div class="absolute inset-0 bg-gradient-to-br from-white via-white to-rosatom-50/60" />
      <div class="relative z-10 w-full max-w-md">
        <!-- Mobile logo -->
        <div class="mb-8 text-center lg:hidden">
          <img src="/images/logo-compact.png" alt="ГГР" class="mx-auto mb-4 h-20 w-auto" />
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
