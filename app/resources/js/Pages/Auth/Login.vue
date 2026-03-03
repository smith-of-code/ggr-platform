<template>
  <MainLayout>
    <Head title="Вход" />
    <div class="flex min-h-[calc(100vh-200px)] items-center justify-center px-4 py-16">
      <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl lg:grid lg:grid-cols-2">
        <!-- Left side - image -->
        <div class="relative hidden lg:block">
          <img
            src="https://images.unsplash.com/photo-1513326738677-b964603b136d?w=800&h=900&fit=crop"
            alt="Атомные города"
            class="absolute inset-0 h-full w-full object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-[#003274]/90 via-[#003274]/50 to-[#003274]/30" />
          <div class="relative flex h-full flex-col justify-end p-10">
            <h2 class="text-3xl font-bold text-white">Гостеприимные города Росатома</h2>
            <p class="mt-3 text-base leading-relaxed text-white/80">
              Цифровая экосистема для развития туристического потенциала атомных городов России
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

          <h1 class="text-2xl font-bold text-gray-900">Вход в систему</h1>
          <p class="mt-2 text-gray-500">Введите данные для входа в панель управления</p>

          <div v-if="status" class="mt-4 rounded-lg bg-green-50 p-3 text-sm font-medium text-green-700">
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input
                id="email"
                type="email"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
                placeholder="admin@rosatom-travel.ru"
                class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3.5 text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/20"
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
                autocomplete="current-password"
                placeholder="Введите пароль"
                class="mt-2 block w-full rounded-xl border border-gray-300 px-4 py-3.5 text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-[#003274] focus:ring-2 focus:ring-[#003274]/20"
              />
              <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2">
                <input
                  type="checkbox"
                  v-model="form.remember"
                  class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
                />
                <span class="text-sm text-gray-600">Запомнить меня</span>
              </label>
              <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
              >
                Забыли пароль?
              </Link>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-[#003274] px-6 py-3.5 text-base font-semibold text-white shadow-lg transition duration-200 hover:bg-[#025ea1] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#003274] focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50"
            >
              <span v-if="form.processing" class="inline-flex items-center gap-2">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Вход...
              </span>
              <span v-else>Войти</span>
            </button>
          </form>

          <p class="mt-8 text-center text-sm text-gray-500">
            Нет аккаунта?
            <Link :href="route('register')" class="font-medium text-[#003274] hover:text-[#025ea1]">Зарегистрироваться</Link>
          </p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

defineProps({
  canResetPassword: { type: Boolean },
  status: { type: String },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>
