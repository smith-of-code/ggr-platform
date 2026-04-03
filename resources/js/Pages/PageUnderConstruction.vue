<template>
  <MainLayout>
    <div class="flex min-h-[70vh] items-center justify-center px-4">
      <div class="mx-auto max-w-lg text-center">
        <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-[#003274]/5">
          <svg class="h-12 w-12 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.192-.14 1.743" />
          </svg>
        </div>

        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
          Страница в разработке
        </h1>

        <p class="mt-4 text-lg leading-relaxed text-gray-500">
          Мы работаем над этим разделом. Совсем скоро здесь появится что-то интересное. Следите за обновлениями!
        </p>

        <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
          <Link
            :href="route('home')"
            class="inline-flex items-center gap-2 rounded-xl bg-[#003274] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#025ea1] focus:outline-none focus:ring-2 focus:ring-[#003274] focus:ring-offset-2"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            На главную
          </Link>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
</script>
