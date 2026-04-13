<template>
  <MainLayout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-24 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center">
        <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-[#003274]/10">
          <svg class="h-12 w-12 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Страница в разработке</h1>
        <p class="mx-auto mt-4 max-w-lg text-lg leading-relaxed text-gray-500">
          Мы работаем над разделом «Переезжаем». Совсем скоро здесь появится полезная информация о возможностях переезда в атомные города.
        </p>
        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
          <Link
            :href="route('home.mainpage')"
            class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3.5 font-semibold text-white shadow-lg transition duration-300 hover:-translate-y-0.5 hover:bg-[#025ea1] hover:shadow-xl"
          >
            На главную
          </Link>
          <Link
            :href="route('tours.index')"
            class="inline-flex items-center rounded-xl border-2 border-[#003274]/20 px-8 py-3.5 font-semibold text-[#003274] transition duration-300 hover:border-[#003274]/40 hover:bg-[#003274]/5"
          >
            Посмотреть туры
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
