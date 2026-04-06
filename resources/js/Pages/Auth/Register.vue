<template>
  <MainLayout>
    <Head title="Регистрация" />
    <div class="flex min-h-[calc(100vh-200px)] items-center justify-center px-4 py-16">
      <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl lg:grid lg:grid-cols-2">
        <!-- Left side - image -->
        <div class="relative hidden lg:block">
          <img
            src="https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800&h=900&fit=crop"
            alt="Атомные города"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-[#003274]/90 via-[#003274]/50 to-[#003274]/30" />
          <div class="relative flex h-full flex-col justify-end p-10">
            <h2 class="text-3xl font-bold text-white">Присоединяйтесь!</h2>
            <p class="mt-3 text-base leading-relaxed text-white/80">
              Создайте аккаунт и откройте для себя уникальные туры по атомным городам России
            </p>
          </div>
        </div>

        <!-- Right side - form -->
        <div class="px-8 py-12 sm:px-12 lg:px-14 lg:py-16">
          <div class="mb-8">
            <Link :href="route('home')" class="inline-flex items-center gap-2 text-[#003274]">
              <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" />
              </svg>
              <span class="text-xl font-bold">Росатом Travel</span>
            </Link>
          </div>

          <h1 class="text-2xl font-bold text-gray-900">Регистрация</h1>
          <p class="mt-2 text-gray-500">Заполните данные для создания аккаунта</p>

          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <RInput v-model="form.name" label="Имя" placeholder="Ваше имя" :error="form.errors.name" required id="name" />

            <RInput v-model="form.email" type="email" label="Email" placeholder="your@email.com" :error="form.errors.email" required id="email" />

            <RInput v-model="form.password" type="password" label="Пароль" placeholder="Минимум 8 символов" :error="form.errors.password" required id="password" />

            <RInput v-model="form.password_confirmation" type="password" label="Подтвердите пароль" placeholder="Повторите пароль" :error="form.errors.password_confirmation" required id="password_confirmation" />

            <div>
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  v-model="form.consent"
                  type="checkbox"
                  class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
                />
                <span class="text-sm text-gray-600">
                  Даю согласие на обработку
                  <a href="/privacy-policy" target="_blank" class="text-[#003274] underline hover:text-[#025ea1]">персональных данных</a>
                </span>
              </label>
              <p v-if="form.errors.consent" class="mt-1 text-sm text-red-600">{{ form.errors.consent }}</p>
            </div>

            <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing || !form.consent">
              Зарегистрироваться
            </RButton>
          </form>

          <p class="mt-8 text-center text-sm text-gray-500">
            Уже есть аккаунт?
            <Link :href="route('login')" class="font-medium text-[#003274] hover:text-[#025ea1]">Войти</Link>
          </p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  consent: false,
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
