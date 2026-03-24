<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.forms.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к формам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ formData ? 'Редактировать форму' : 'Новая форма' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard>
        <template #header><h2 class="text-base font-bold text-gray-900">Основные настройки</h2></template>
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2"><RInput v-model="form.title" label="Название *" required :error="form.errors.title" @input="onTitleInput" /></div>
          <div class="sm:col-span-2">
            <RInput v-model="form.slug" label="Slug (URL)" :error="form.errors.slug" @input="onSlugManualInput" />
            <div class="mt-1 min-h-[1.25rem]">
              <span v-if="slugChecking" class="text-xs text-gray-400">Проверка доступности...</span>
              <span v-else-if="slugAvailable === true" class="text-xs text-green-600">&#10003; Slug свободен</span>
              <span v-else-if="slugAvailable === false" class="text-xs text-red-600">
                Slug занят.
                <template v-if="slugSuggestions.length"> Варианты:
                  <button v-for="s in slugSuggestions" :key="s" type="button" class="ml-1 font-medium text-rosatom-600 underline hover:text-rosatom-800" @click="form.slug = s; checkSlug()">{{ s }}</button>
                </template>
              </span>
            </div>
          </div>
          <div class="sm:col-span-2"><RInput v-model="form.description" label="Описание" /></div>
          <div class="sm:col-span-2"><RInput v-model="form.thank_you_message" label="Сообщение после отправки" placeholder="Спасибо за участие!" /></div>
          <RCheckbox v-model="form.is_active" label="Активна" />
          <RCheckbox v-model="form.is_anonymous" label="Анонимное прохождение" />
          <RCheckbox v-model="form.allow_embed" label="Разрешить встраивание (embed)" />
          <RCheckbox v-model="form.create_users" label="Создавать пользователей из ответов" />
        </div>
      </RCard>

      <!-- User mapping -->
      <RCard v-if="form.create_users">
        <template #header><h2 class="text-base font-bold text-gray-900">Маппинг полей → пользователь</h2></template>
        <p class="mb-4 text-xs text-gray-400">Укажите ключи полей формы, которые содержат данные пользователя</p>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Поле ФИО</label>
            <select v-model="form.fio_field_key" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="f in form.fields" :key="f.key" :value="f.key">{{ f.label }} ({{ f.key }})</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Поле Email</label>
            <select v-model="form.email_field_key" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="f in form.fields" :key="f.key" :value="f.key">{{ f.label }} ({{ f.key }})</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Поле телефона</label>
            <select v-model="form.phone_field_key" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="f in form.fields" :key="f.key" :value="f.key">{{ f.label }} ({{ f.key }})</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Поле должности</label>
            <select v-model="form.position_field_key" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
              <option value="">—</option>
              <option v-for="f in form.fields" :key="f.key" :value="f.key">{{ f.label }} ({{ f.key }})</option>
            </select>
          </div>
        </div>
      </RCard>

      <!-- Field constructor -->
      <RCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Поля формы</h2>
            <RButton variant="outline" size="sm" type="button" @click="addField">
              <template #icon><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></template>
              Добавить поле
            </RButton>
          </div>
        </template>

        <div v-if="form.fields.length === 0" class="py-8 text-center text-sm text-gray-400">
          Добавьте поля формы
        </div>

        <div class="space-y-4">
          <div v-for="(field, fIdx) in form.fields" :key="fIdx" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Поле {{ fIdx + 1 }}</span>
              <div class="flex gap-1">
                <button v-if="fIdx > 0" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-200" @click="moveField(fIdx, -1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                </button>
                <button v-if="fIdx < form.fields.length - 1" type="button" class="rounded p-1 text-gray-400 hover:bg-gray-200" @click="moveField(fIdx, 1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <button type="button" class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500" @click="form.fields.splice(fIdx, 1)">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <RInput v-model="field.label" placeholder="Название поля *" required />
              <RInput v-model="field.key" placeholder="Ключ (латиница)" required />
              <div>
                <select v-model="field.type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                  <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </div>
            </div>

            <div class="mt-3 flex items-center gap-4">
              <RCheckbox v-model="field.required" label="Обязательное" />
              <div class="flex-1">
                <RInput v-model="field.placeholder" placeholder="Подсказка (placeholder)" />
              </div>
            </div>

            <div v-if="['select', 'radio', 'checkbox'].includes(field.type)" class="mt-3">
              <label class="mb-1 block text-xs font-medium text-gray-500">Варианты (по одному на строку)</label>
              <textarea
                :value="(field.options || []).join('\n')"
                @input="field.options = $event.target.value.split('\n').filter(v => v.trim())"
                rows="3"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder-gray-400"
                placeholder="Вариант 1&#10;Вариант 2&#10;Вариант 3"
              />
            </div>
          </div>
        </div>
      </RCard>

      <div class="flex gap-3">
        <RButton type="submit" variant="primary" :loading="form.processing">Сохранить</RButton>
        <Link :href="route('lms.admin.forms.index', event.slug)" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</Link>
      </div>
    </form>
  </LmsAdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, form: Object })

const slugChecking = ref(false)
const slugAvailable = ref(null)
const slugSuggestions = ref([])
let slugDebounce = null
let slugManuallyEdited = false

const fieldTypes = [
  { value: 'text', label: 'Текст' },
  { value: 'textarea', label: 'Многострочный текст' },
  { value: 'email', label: 'Email' },
  { value: 'phone', label: 'Телефон' },
  { value: 'number', label: 'Число' },
  { value: 'select', label: 'Выпадающий список' },
  { value: 'radio', label: 'Радио-кнопки' },
  { value: 'checkbox', label: 'Чекбоксы' },
  { value: 'date', label: 'Дата' },
  { value: 'rating', label: 'Рейтинг (1-5)' },
]

const formData = props.form

const form = useForm({
  title: formData?.title ?? '',
  description: formData?.description ?? '',
  slug: formData?.slug ?? '',
  is_active: formData?.is_active ?? true,
  is_anonymous: formData?.is_anonymous ?? true,
  allow_embed: formData?.allow_embed ?? true,
  create_users: formData?.create_users ?? false,
  fio_field_key: formData?.fio_field_key ?? '',
  email_field_key: formData?.email_field_key ?? '',
  phone_field_key: formData?.phone_field_key ?? '',
  position_field_key: formData?.position_field_key ?? '',
  thank_you_message: formData?.thank_you_message ?? '',
  fields: (formData?.fields || []).map(f => ({
    key: f.key, label: f.label, type: f.type,
    required: f.required, placeholder: f.placeholder ?? '',
    options: f.options || [],
  })),
})

function transliterate(str) {
  const map = {
    'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'yo','ж':'zh','з':'z','и':'i',
    'й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t',
    'у':'u','ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'shch','ъ':'','ы':'y',
    'ь':'','э':'e','ю':'yu','я':'ya',' ':'-',
  }
  return str.toLowerCase().split('').map(c => map[c] ?? c).join('').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '')
}

function onTitleInput() {
  if (slugManuallyEdited || formData) return
  form.slug = transliterate(form.title)
  checkSlugDebounced()
}

function onSlugManualInput() {
  slugManuallyEdited = true
  checkSlugDebounced()
}

function checkSlugDebounced() {
  clearTimeout(slugDebounce)
  slugAvailable.value = null
  if (!form.slug || form.slug.length < 2) return
  slugDebounce = setTimeout(() => checkSlug(), 400)
}

async function checkSlug() {
  if (!form.slug) return
  slugChecking.value = true
  slugAvailable.value = null
  slugSuggestions.value = []
  try {
    const { data } = await axios.get(route('lms.admin.forms.check-slug', props.event.slug), {
      params: { title: form.slug, exclude_id: formData?.id },
    })
    if (data.available) {
      slugAvailable.value = true
      slugSuggestions.value = []
    } else {
      slugAvailable.value = false
      slugSuggestions.value = [data.slug, ...(data.suggestions || [])].filter((v, i, a) => a.indexOf(v) === i && v !== form.slug)
    }
  } catch { /* ignore */ }
  finally { slugChecking.value = false }
}

function addField() {
  const idx = form.fields.length + 1
  form.fields.push({ key: `field_${idx}`, label: '', type: 'text', required: false, placeholder: '', options: [] })
}

function moveField(idx, delta) {
  const newIdx = idx + delta
  if (newIdx < 0 || newIdx >= form.fields.length) return
  const temp = form.fields[idx]
  form.fields[idx] = form.fields[newIdx]
  form.fields[newIdx] = temp
}

function submit() {
  const fields = form.fields.filter(f => f.label?.trim() && f.key?.trim()).map((f, i) => ({ ...f, position: i }))
  if (formData) {
    form.transform(d => ({ ...d, fields })).put(route('lms.admin.forms.update', [props.event.slug, formData.id]))
  } else {
    form.transform(d => ({ ...d, fields })).post(route('lms.admin.forms.store', props.event.slug))
  }
}
</script>
