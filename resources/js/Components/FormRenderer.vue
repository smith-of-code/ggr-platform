<template>
  <div>
    <!-- Success state -->
    <div v-if="submitted" class="p-8 text-center">
      <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-gray-900">Ответ отправлен!</h2>
      <p class="mt-2 text-gray-500">{{ form.thank_you_message || 'Спасибо за участие в опросе.' }}</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="submitForm">
      <div class="border-b border-gray-100 px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ form.title }}</h2>
        <p v-if="form.description" class="mt-2 text-sm text-gray-500">{{ form.description }}</p>
      </div>

      <div class="space-y-6 px-8 py-6">
        <div v-for="field in fields" :key="field.id" class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            {{ field.label }}
            <span v-if="field.required" class="text-red-500">*</span>
          </label>

          <input
            v-if="['text', 'email', 'phone', 'date'].includes(field.type)"
            v-model="answers[field.key]"
            :type="field.type === 'phone' ? 'tel' : field.type"
            :placeholder="field.placeholder"
            :required="field.required"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
          />

          <input
            v-else-if="field.type === 'number'"
            v-model="answers[field.key]"
            type="number"
            :placeholder="field.placeholder"
            :required="field.required"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
          />

          <textarea
            v-else-if="field.type === 'textarea'"
            v-model="answers[field.key]"
            :placeholder="field.placeholder"
            :required="field.required"
            rows="4"
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
          />

          <select
            v-else-if="field.type === 'select'"
            v-model="answers[field.key]"
            :required="field.required"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900"
          >
            <option value="">{{ field.placeholder || 'Выберите...' }}</option>
            <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
          </select>

          <div v-else-if="field.type === 'radio'" class="space-y-2">
            <label v-for="opt in field.options" :key="opt" class="flex cursor-pointer items-center gap-2">
              <input type="radio" v-model="answers[field.key]" :value="opt" :name="field.key" :required="field.required" class="border-gray-300 text-blue-600" />
              <span class="text-sm text-gray-700">{{ opt }}</span>
            </label>
          </div>

          <div v-else-if="field.type === 'checkbox'" class="space-y-2">
            <label v-for="opt in field.options" :key="opt" class="flex cursor-pointer items-center gap-2">
              <input type="checkbox" :value="opt" @change="toggleCheckbox(field.key, opt)" :checked="(answers[field.key] || []).includes(opt)" class="rounded border-gray-300 text-blue-600" />
              <span class="text-sm text-gray-700">{{ opt }}</span>
            </label>
          </div>

          <div v-else-if="field.type === 'rating'" class="flex gap-1">
            <button
              v-for="n in 5"
              :key="n"
              type="button"
              @click="answers[field.key] = String(n)"
              class="rounded-lg p-1.5 transition"
              :class="Number(answers[field.key]) >= n ? 'text-amber-400' : 'text-gray-300 hover:text-amber-300'"
            >
              <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg>
            </button>
          </div>

          <p v-if="errors[`answers.${field.key}`]" class="text-xs text-red-600">{{ errors[`answers.${field.key}`] }}</p>
        </div>
      </div>

      <div class="border-t border-gray-100 px-8 py-5 space-y-4">
        <div v-if="form.require_consent">
          <label class="flex items-start gap-3 cursor-pointer">
            <input
              v-model="consentChecked"
              type="checkbox"
              class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="text-sm text-gray-600">
              Отправляя форму, вы даете
              <a :href="consentUrl" target="_blank" class="text-blue-600 underline hover:text-blue-800">согласие на обработку персональных данных</a>
            </span>
          </label>
          <p v-if="errors.consent" class="mt-1 text-xs text-red-600">{{ errors.consent }}</p>
        </div>
        <button
          type="submit"
          :disabled="processing || (form.require_consent && !consentChecked)"
          class="rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-700 disabled:opacity-50"
        >
          {{ processing ? 'Отправка...' : 'Отправить' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  form: { type: Object, required: true },
  fields: { type: Array, required: true },
})

const emit = defineEmits(['submitted'])

const page = usePage()
const consentUrl = computed(() => props.form.consent_document_url || page.props.consentDocumentUrl || '#')

const answers = reactive({})
const errors = ref({})
const processing = ref(false)
const submitted = ref(false)
const consentChecked = ref(false)

props.fields.forEach(f => {
  answers[f.key] = f.type === 'checkbox' ? [] : ''
})

function toggleCheckbox(key, opt) {
  if (!Array.isArray(answers[key])) answers[key] = []
  const idx = answers[key].indexOf(opt)
  if (idx > -1) answers[key].splice(idx, 1)
  else answers[key].push(opt)
}

async function submitForm() {
  processing.value = true
  errors.value = {}

  const payload = { answers }
  if (props.form.require_consent) {
    payload.consent = consentChecked.value
  }

  try {
    await axios.post(`/forms/${props.form.slug}/submit`, payload)
    submitted.value = true
    emit('submitted')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    }
  } finally {
    processing.value = false
  }
}
</script>
