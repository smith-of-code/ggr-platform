<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.settings.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к настройкам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">Настройки почты (SMTP)</h1>
      <p class="mt-1 text-sm text-gray-500">Настройте параметры отправки электронной почты</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
      <div class="lg:col-span-2">
        <RCard elevation="raised">
          <form @submit.prevent="submitSettings" class="space-y-6">
            <div>
              <label class="mb-2 block text-sm font-semibold text-gray-700">Транспорт</label>
              <select
                v-model="form.mailer"
                class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10"
              >
                <option value="smtp">SMTP</option>
                <option value="sendmail">Sendmail</option>
                <option value="log">Log (для отладки)</option>
              </select>
            </div>

            <template v-if="form.mailer === 'smtp'">
              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="form.host" label="SMTP хост *" placeholder="smtp.example.com" :error="form.errors.host" />
                <RInput v-model="form.port" label="Порт *" placeholder="587" type="number" :error="form.errors.port" />
              </div>

              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="form.username" label="Имя пользователя" placeholder="user@example.com" :error="form.errors.username" />
                <RInput v-model="form.password" label="Пароль" type="password" placeholder="••••••••" :error="form.errors.password" />
              </div>

              <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Шифрование</label>
                <select
                  v-model="form.encryption"
                  class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10"
                >
                  <option :value="null">Нет</option>
                  <option value="tls">TLS</option>
                  <option value="ssl">SSL</option>
                </select>
              </div>
            </template>

            <div class="border-t border-gray-100 pt-6">
              <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Отправитель</p>
              <div class="grid gap-4 sm:grid-cols-2">
                <RInput v-model="form.from_address" label="Email отправителя *" placeholder="noreply@example.com" :error="form.errors.from_address" />
                <RInput v-model="form.from_name" label="Имя отправителя *" placeholder="My App" :error="form.errors.from_name" />
              </div>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-6">
              <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
                Сохранить настройки
              </RButton>
            </div>
          </form>
        </RCard>
      </div>

      <div>
        <RCard elevation="raised">
          <h3 class="mb-1 text-sm font-bold text-gray-900">Тест отправки</h3>
          <p class="mb-4 text-xs text-gray-500">Отправьте тестовое письмо для проверки настроек</p>

          <form @submit.prevent="submitTest" class="space-y-4">
            <RInput v-model="testForm.email" label="Email получателя" placeholder="test@example.com" :error="testForm.errors.email" />
            <RButton variant="primary" :loading="testForm.processing" :disabled="testForm.processing" class="w-full">
              Отправить тест
            </RButton>
          </form>
        </RCard>

        <RCard elevation="raised" class="mt-4">
          <h3 class="mb-3 text-sm font-bold text-gray-900">Текущая конфигурация</h3>
          <dl class="space-y-2 text-xs">
            <div class="flex justify-between">
              <dt class="text-gray-500">Транспорт</dt>
              <dd class="font-medium text-gray-900">{{ form.mailer }}</dd>
            </div>
            <div v-if="form.mailer === 'smtp'" class="flex justify-between">
              <dt class="text-gray-500">Сервер</dt>
              <dd class="font-medium text-gray-900">{{ form.host }}:{{ form.port }}</dd>
            </div>
            <div v-if="form.mailer === 'smtp'" class="flex justify-between">
              <dt class="text-gray-500">Шифрование</dt>
              <dd class="font-medium text-gray-900">{{ form.encryption || 'Нет' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Отправитель</dt>
              <dd class="truncate pl-4 font-medium text-gray-900">{{ form.from_address }}</dd>
            </div>
          </dl>
        </RCard>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ settings: Object })

const form = useForm({
  mailer: props.settings?.mailer ?? 'smtp',
  host: props.settings?.host ?? '',
  port: props.settings?.port ?? 587,
  username: props.settings?.username ?? '',
  password: props.settings?.password ?? '',
  encryption: props.settings?.encryption ?? null,
  from_address: props.settings?.from_address ?? '',
  from_name: props.settings?.from_name ?? '',
})

const testForm = useForm({
  email: '',
})

function submitSettings() {
  form.put(route('admin.settings.mail.update'), {
    preserveScroll: true,
  })
}

function submitTest() {
  testForm.post(route('admin.settings.mail.test'), {
    preserveScroll: true,
  })
}
</script>
