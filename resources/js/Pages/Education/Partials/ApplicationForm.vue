<template>
  <div>
    <div v-if="$page.props.flash?.success" class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
      {{ $page.props.flash.success }}
    </div>
    <h2 class="text-xl font-bold text-gray-900">Заявка на информацию о программе</h2>
    <p class="mt-2 text-sm text-gray-500">
      Укажите контакты — мы ответим на вопросы по программе «{{ product.title }}».
    </p>
    <form class="mt-8 space-y-5" @submit.prevent="submitApplication">
      <div>
        <label class="block text-sm font-medium text-gray-700" for="app-name">Имя <span class="text-red-500">*</span></label>
        <input
          id="app-name"
          v-model="form.name"
          type="text"
          required
          autocomplete="name"
          class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
        />
        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700" for="app-email">Email <span class="text-red-500">*</span></label>
        <input
          id="app-email"
          v-model="form.email"
          type="email"
          required
          autocomplete="email"
          class="mt-1.5 w-full rounded-xl border px-4 py-3 text-sm transition focus:outline-none focus:ring-2"
          :class="emailError
            ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
            : 'border-gray-200 focus:border-[#003274] focus:ring-[#003274]/20'"
          @blur="validateEmail"
        />
        <p v-if="emailError || form.errors.email" class="mt-1 text-xs text-red-600">{{ emailError || form.errors.email }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700" for="app-phone">Телефон</label>
        <input
          id="app-phone"
          :value="form.phone"
          type="tel"
          autocomplete="tel"
          placeholder="+7 (___) ___-__-__"
          class="mt-1.5 w-full rounded-xl border px-4 py-3 text-sm transition focus:outline-none focus:ring-2"
          :class="phoneError
            ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
            : 'border-gray-200 focus:border-[#003274] focus:ring-[#003274]/20'"
          @input="onPhoneInput"
          @blur="validatePhone"
        />
        <p v-if="phoneError || form.errors.phone" class="mt-1 text-xs text-red-600">{{ phoneError || form.errors.phone }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700" for="app-message">Сообщение</label>
        <textarea
          id="app-message"
          v-model="form.message"
          rows="4"
          class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
          :placeholder="`Интерес к программе: ${product.title}`"
        />
        <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
      </div>
      <div>
        <label class="flex items-start gap-3 cursor-pointer">
          <input
            v-model="form.consent"
            type="checkbox"
            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]"
          />
          <span class="text-sm text-gray-600">
            Отправляя заявку, вы даете
            <a :href="$page.props.consentDocumentUrl" target="_blank" class="text-[#003274] underline hover:text-[#025ea1]">согласие на обработку персональных данных</a>
          </span>
        </label>
        <p v-if="form.errors.consent" class="mt-1 text-xs text-red-600">{{ form.errors.consent }}</p>
      </div>
      <button
        type="submit"
        :disabled="form.processing || !form.consent"
        class="w-full rounded-xl bg-[#003274] py-3.5 text-sm font-semibold text-white shadow-md shadow-[#003274]/25 transition hover:bg-[#025ea1] disabled:cursor-not-allowed disabled:opacity-60"
      >
        {{ form.processing ? 'Отправка…' : 'Отправить заявку' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
  product: { type: Object, required: true },
})

const emailError = ref('')
const phoneError = ref('')

const form = useForm({
  type: 'program_info',
  name: '',
  email: '',
  phone: '',
  message: '',
  consent: false,
})

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/

function validateEmail() {
  emailError.value = ''
  if (!form.email) return
  if (!EMAIL_RE.test(form.email)) {
    emailError.value = 'Введите корректный email-адрес'
  }
}

function onPhoneInput(e) {
  phoneError.value = ''
  let digits = e.target.value.replace(/\D/g, '')
  if (digits.startsWith('8')) digits = '7' + digits.slice(1)
  if (digits && !digits.startsWith('7')) digits = '7' + digits
  digits = digits.slice(0, 11)

  let formatted = ''
  if (digits.length > 0) formatted = '+7'
  if (digits.length > 1) formatted += ' (' + digits.slice(1, 4)
  if (digits.length >= 4) formatted += ') '
  if (digits.length > 4) formatted += digits.slice(4, 7)
  if (digits.length >= 7) formatted += '-'
  if (digits.length > 7) formatted += digits.slice(7, 9)
  if (digits.length >= 9) formatted += '-'
  if (digits.length > 9) formatted += digits.slice(9, 11)

  form.phone = formatted
  e.target.value = formatted
}

function validatePhone() {
  phoneError.value = ''
  if (!form.phone) return
  const digits = form.phone.replace(/\D/g, '')
  if (digits.length > 0 && digits.length < 11) {
    phoneError.value = 'Введите полный номер телефона'
  }
}

function submitApplication() {
  validateEmail()
  validatePhone()
  if (emailError.value || phoneError.value) return

  form.post(route('applications.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emailError.value = ''
      phoneError.value = ''
    },
  })
}
</script>
