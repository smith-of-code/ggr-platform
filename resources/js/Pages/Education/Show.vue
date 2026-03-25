<template>
  <MainLayout>
    <Head :title="`${product.title} — ВШГР`" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <Link
        :href="route('education.index')"
        class="inline-flex items-center gap-2 text-sm font-medium text-[#003274] transition hover:text-[#025ea1]"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Каталог программ
      </Link>

      <!-- Hero -->
      <div class="mt-8 overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 via-[#003274] to-[#024a8f] shadow-xl">
        <div class="lg:grid lg:grid-cols-2 lg:gap-0">
          <div class="relative aspect-[16/10] max-h-[22rem] lg:max-h-none lg:min-h-[20rem]">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.title"
              class="h-full w-full object-cover opacity-95 lg:rounded-l-3xl"
            />
            <div v-else class="flex h-full min-h-[12rem] items-center justify-center bg-white/5 lg:rounded-l-3xl">
              <svg class="h-20 w-20 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
              </svg>
            </div>
          </div>
          <div class="flex flex-col justify-center px-8 py-10 text-white lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-widest text-white/60">Образовательная программа</p>
            <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">{{ product.title }}</h1>
            <div class="mt-6 flex flex-wrap gap-2">
              <span
                v-if="product.duration"
                class="rounded-full bg-white/15 px-4 py-1.5 text-xs font-semibold backdrop-blur-sm"
              >
                {{ product.duration }}
              </span>
              <span
                v-if="product.format"
                class="rounded-full bg-white px-4 py-1.5 text-xs font-semibold text-[#003274]"
              >
                {{ product.format }}
              </span>
              <span
                v-if="product.price_info"
                class="rounded-full border border-white/40 bg-white/10 px-4 py-1.5 text-xs font-semibold"
              >
                {{ product.price_info }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Body -->
      <div class="mx-auto mt-12 max-w-3xl">
        <div
          v-if="product.content"
          class="html-content max-w-none text-base leading-relaxed text-gray-700"
          v-html="product.content"
        />

        <section v-if="product.target_audience" class="mt-12 rounded-2xl border border-[#003274]/15 bg-[#003274]/[0.04] p-8">
          <h2 class="text-lg font-bold text-[#003274]">Для кого программа</h2>
          <div
            class="html-content mt-4 max-w-none text-gray-700"
            v-html="product.target_audience"
          />
        </section>

        <!-- Application -->
        <section id="application-form" class="mt-16 scroll-mt-24 border-t border-gray-100 pt-12">
          <div v-if="$page.props.flash?.success" class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ $page.props.flash.success }}
          </div>
          <h2 class="text-xl font-bold text-gray-900">Заявка на информацию о программе</h2>
          <p class="mt-2 text-sm text-gray-500">
            Укажите контакты — мы ответим на вопросы по программе «{{ product.title }}».
          </p>
          <form class="mt-8 space-y-5" @submit.prevent="submitApplication">
            <div>
              <label class="block text-sm font-medium text-gray-700" for="show-name">Имя <span class="text-red-500">*</span></label>
              <input
                id="show-name"
                v-model="form.name"
                type="text"
                required
                autocomplete="name"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700" for="show-email">Email <span class="text-red-500">*</span></label>
              <input
                id="show-email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              />
              <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700" for="show-phone">Телефон</label>
              <input
                id="show-phone"
                v-model="form.phone"
                type="tel"
                autocomplete="tel"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              />
              <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700" for="show-message">Сообщение</label>
              <textarea
                id="show-message"
                v-model="form.message"
                rows="4"
                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
                :placeholder="`Интерес к программе: ${product.title}`"
              />
              <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
            </div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full rounded-xl bg-[#003274] py-3.5 text-sm font-semibold text-white shadow-md shadow-[#003274]/25 transition hover:bg-[#025ea1] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ form.processing ? 'Отправка…' : 'Отправить заявку' }}
            </button>
          </form>
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Link, Head, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

defineProps({
  product: { type: Object, required: true },
})

const form = useForm({
  type: 'program_info',
  name: '',
  email: '',
  phone: '',
  message: '',
})

function submitApplication() {
  form.post(route('applications.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

</script>

<style scoped>
.html-content :deep(p) {
  margin-bottom: 1rem;
}
.html-content :deep(p:last-child) {
  margin-bottom: 0;
}
.html-content :deep(a) {
  color: #003274;
  text-decoration: underline;
  text-underline-offset: 2px;
}
.html-content :deep(ul),
.html-content :deep(ol) {
  margin: 0.75rem 0 1rem;
  padding-left: 1.25rem;
}
.html-content :deep(ul) {
  list-style-type: disc;
}
.html-content :deep(ol) {
  list-style-type: decimal;
}
.html-content :deep(h2) {
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: rgb(17 24 39);
}
.html-content :deep(h3) {
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: rgb(31 41 55);
}
.html-content :deep(img) {
  margin-top: 1rem;
  margin-bottom: 1rem;
  max-width: 100%;
  border-radius: 0.75rem;
}
</style>
