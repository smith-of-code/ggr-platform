<template>
  <MainLayout>
    <Head title="ВШГР — образовательные программы" />

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#024a8f] to-[#003274] text-white">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-white blur-3xl" />
        <div class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-sky-300 blur-3xl" />
      </div>
      <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
        <p class="text-sm font-semibold uppercase tracking-widest text-white/70">ВШГР</p>
        <h1 class="mt-3 max-w-4xl text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
          Высшая школа гостеприимного развития
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/90">
          Мы готовим профессионалов сферы гостеприимства и сервиса для атомных городов и индустриального туризма —
          через практику, экспертизу и программы, ориентированные на реальные задачи отрасли.
        </p>
      </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
      <!-- Catalog -->
      <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Программы обучения</h2>
          <p class="mt-1 text-gray-500">Выберите направление и узнайте подробности</p>
        </div>
      </div>

      <div v-if="products?.length" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="p in products"
          :key="p.id"
          class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:border-[#003274]/20 hover:shadow-lg"
        >
          <div class="relative aspect-[16/10] overflow-hidden bg-gray-100">
            <img
              v-if="p.image"
              :src="p.image"
              :alt="p.title"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-sky-100">
              <svg class="h-14 w-14 text-[#003274]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.01 50.01 0 0 1 12 0c1.607 0 3.158.112 4.656.326" />
              </svg>
            </div>
            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
              <span
                v-if="p.duration"
                class="rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-[#003274] shadow-sm backdrop-blur-sm"
              >
                {{ p.duration }}
              </span>
              <span
                v-if="p.format"
                class="rounded-full bg-[#003274]/90 px-3 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-sm"
              >
                {{ p.format }}
              </span>
            </div>
          </div>
          <div class="flex flex-1 flex-col p-6">
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#003274]">{{ p.title }}</h3>
            <p v-if="p.description" class="mt-3 flex-1 text-sm leading-relaxed text-gray-600 line-clamp-3">
              {{ stripHtml(p.description) }}
            </p>
            <Link
              :href="route('education.show', p.slug)"
              class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-[#003274] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#025ea1] active:scale-[0.99]"
            >
              Подробнее
            </Link>
          </div>
        </article>
      </div>
      <div v-else class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/80 py-16 text-center text-gray-500">
        Программы скоро появятся в каталоге.
      </div>

      <!-- Announcements -->
      <section v-if="announcementItems.length" class="mt-20 border-t border-gray-100 pt-16">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Анонсы и новости</h2>
        <p class="mt-1 text-gray-500">Последние материалы</p>
        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="post in announcementItems"
            :key="post.id"
            class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
          >
            <div class="aspect-[16/9] overflow-hidden bg-gray-100">
              <img
                v-if="post.image"
                :src="post.image"
                :alt="post.title"
                class="h-full w-full object-cover"
              />
              <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-50">
                <span class="text-sm font-medium text-gray-400">Нет изображения</span>
              </div>
            </div>
            <div class="p-5">
              <time v-if="post.published_at" class="text-xs font-medium uppercase tracking-wide text-[#003274]/80">
                {{ formatPostDate(post.published_at) }}
              </time>
              <h3 class="mt-2 text-lg font-bold text-gray-900">{{ post.title }}</h3>
              <p v-if="post.excerpt" class="mt-2 text-sm text-gray-600 line-clamp-3">{{ post.excerpt }}</p>
            </div>
          </article>
        </div>
      </section>

      <!-- CTA -->
      <section class="mt-20">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-[#003274] to-[#025ea1] px-8 py-12 text-center text-white shadow-xl sm:px-12">
          <h2 class="text-2xl font-bold sm:text-3xl">Интересует сотрудничество или вопрос по программам?</h2>
          <p class="mx-auto mt-3 max-w-xl text-white/90">Оставьте заявку — расскажем подробнее о формате и сроках.</p>
          <a
            href="#application-form"
            class="mt-8 inline-flex items-center justify-center rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-[#003274] shadow-lg transition hover:bg-gray-50"
          >
            Хочу узнать подробнее
          </a>
        </div>
      </section>

      <!-- Regulation -->
      <section class="mt-20">
        <div class="rounded-3xl bg-[#e9eef4] px-8 py-12 text-center sm:px-12">
          <a
            :href="regulationUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2.5 rounded-xl bg-[#003274] px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#003274]/20 transition hover:bg-[#025ea1] active:scale-[0.98]"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Положение
          </a>
          <p class="mx-auto mt-4 max-w-lg text-sm text-gray-500">
            Положение о грантовом конкурсе «Высшая школа гостеприимства Росатома»
          </p>
        </div>
      </section>

      <!-- Application form -->
      <section id="application-form" class="mx-auto mt-16 max-w-2xl scroll-mt-24">
        <div v-if="$page.props.flash?.success" class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
          {{ $page.props.flash.success }}
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm sm:p-10">
          <h2 class="text-xl font-bold text-gray-900">Заявка на консультацию</h2>
          <p class="mt-2 text-sm text-gray-500">Заполните форму — мы свяжемся с вами в ближайшее время.</p>
          <form class="mt-8 space-y-5" @submit.prevent="submitApplication">
            <div>
              <label class="block text-sm font-medium text-gray-700" for="idx-name">Имя <span class="text-red-500">*</span></label>
              <input
                id="idx-name"
                v-model="form.name"
                type="text"
                required
                autocomplete="name"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700" for="idx-email">Email <span class="text-red-500">*</span></label>
              <input
                id="idx-email"
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
              <label class="block text-sm font-medium text-gray-700" for="idx-phone">Телефон</label>
              <input
                id="idx-phone"
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
              <label class="block text-sm font-medium text-gray-700" for="idx-message">Сообщение</label>
              <textarea
                id="idx-message"
                v-model="form.message"
                rows="4"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
                placeholder="Ваш вопрос или пожелание"
              />
              <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
            </div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-[#003274] py-3.5 text-sm font-semibold text-white shadow-md shadow-[#003274]/25 transition hover:bg-[#025ea1] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ form.processing ? 'Отправка…' : 'Отправить' }}
            </button>
          </form>
        </div>
      </section>

      <!-- Social links -->
      <section v-if="socials.length" class="mt-20">
        <div class="text-center">
          <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Мы в социальных сетях</h2>
          <p class="mt-2 text-gray-500">Следите за нашими новостями</p>
          <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a
              v-for="s in socials"
              :key="s.name"
              :href="s.url"
              target="_blank"
              rel="noopener noreferrer"
              class="flex items-center gap-3 rounded-xl border border-gray-200 px-6 py-4 transition hover:border-[#003274]/30 hover:shadow-md"
            >
              <span class="text-2xl" v-html="socialIconSvg(s.icon)" />
              <span class="font-medium text-gray-700">{{ s.name }}</span>
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { socialIcon } from '@/utils/opportunityToursIcons'

const props = defineProps({
  products: { type: Array, default: () => [] },
  latestAnnouncements: { type: Array, default: () => [] },
  announcements: { type: Array, default: () => [] },
})

const announcementItems = computed(() =>
  props.announcements?.length ? props.announcements : props.latestAnnouncements,
)

const regulationUrl = 'https://disk.yandex.ru/i/QFSwdBIFTR55EA'

const socials = [
  { name: 'ВКонтакте', url: 'https://vk.com/rosatom_travel', icon: 'vk' },
  { name: 'Telegram', url: 'https://t.me/rosatom_travel', icon: 'telegram' },
]

function socialIconSvg(key) {
  return socialIcon(key, 'h-6 w-6')
}

const emailError = ref('')
const phoneError = ref('')

const form = useForm({
  type: 'program_info',
  name: '',
  email: '',
  phone: '',
  message: '',
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

function stripHtml(html) {
  if (!html || typeof html !== 'string') return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return (doc.body.textContent || '').replace(/\s+/g, ' ').trim()
}

function formatPostDate(value) {
  try {
    return new Date(value).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
  } catch {
    return ''
  }
}
</script>
