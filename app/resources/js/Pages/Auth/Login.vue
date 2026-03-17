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
            <RInput v-model="form.email" type="email" label="Email" placeholder="admin@rosatom-travel.ru" :error="form.errors.email" required id="email" />

            <RInput v-model="form.password" type="password" label="Пароль" placeholder="Введите пароль" :error="form.errors.password" required id="password" />

            <div class="flex items-center justify-between">
              <RCheckbox v-model="form.remember" label="Запомнить меня" />
              <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm font-medium text-[#003274] hover:text-[#025ea1]"
              >
                Забыли пароль?
              </Link>
            </div>

            <RButton variant="primary" size="lg" block :loading="form.processing" :disabled="form.processing">
              Войти
            </RButton>
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
