<template>
  <MainLayout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <div class="lg:grid lg:grid-cols-3 lg:gap-10">
        <div class="lg:col-span-2">
          <!-- Hero image -->
          <div class="reveal overflow-hidden rounded-xl bg-gray-200 shadow-sm">
            <div class="aspect-video overflow-hidden">
              <img
                v-if="tour.image"
                :src="tour.image"
                :alt="tour.title"
                class="h-full w-full object-cover"
              />
            </div>
          </div>

          <h1 class="reveal mt-8 text-3xl font-bold text-gray-900 sm:text-4xl">{{ tour.title }}</h1>

          <div class="reveal mt-4 flex flex-wrap gap-2">
            <RBadge variant="primary">{{ tour.start_city }}</RBadge>
            <RBadge variant="neutral">{{ tour.duration }}</RBadge>
            <RBadge v-if="tour.project" variant="info">{{ projectLabel(tour.project) }}</RBadge>
            <RBadge v-if="tour.closed_city" variant="warning">Закрытый город</RBadge>
          </div>

          <div class="reveal mt-8 text-lg leading-relaxed text-gray-600" v-html="tour.description" />

          <!-- Departures -->
          <section v-if="tour.departures?.length" class="reveal mt-12">
            <h2 class="text-xl font-bold text-gray-900">Даты заездов</h2>
            <div class="mt-6 space-y-4">
              <div
                v-for="dep in tour.departures"
                :key="dep.id"
                class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5 transition hover:shadow-sm sm:flex-row sm:items-center sm:justify-between"
              >
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                  </div>
                  <span class="font-medium text-gray-900">{{ formatDate(dep.start_date) }} — {{ formatDate(dep.end_date) }}</span>
                </div>
                <div class="flex items-center gap-4">
                  <span class="text-lg font-bold text-[#003274]">{{ formatPrice(dep.price_per_person) }} &#8381;</span>
                  <RButton variant="primary" @click="openApplicationModal(dep.id)">
                    Оставить заявку
                  </RButton>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Sidebar -->
        <div class="mt-10 lg:mt-0">
          <RCard elevation="raised" class="reveal sticky top-20">
            <h2 class="text-lg font-bold text-gray-900">Детали тура</h2>
            <dl class="mt-5 space-y-5">
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                  <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Продолжительность</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ tour.duration }}</dd>
                </div>
              </div>
              <div v-if="tour.group_size" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                  <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                  </svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Группа</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ tour.group_size }}{{ tour.min_age ? `, от ${tour.min_age} лет` : '' }}</dd>
                </div>
              </div>
              <div v-if="tour.closed_city" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                  <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                  </svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Закрытость</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">Требуется оформление пропуска</dd>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50">
                  <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                  </svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Стоимость</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">
                    <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381; за человека</template>
                    <template v-else><span class="text-green-600">Бесплатно (конкурсный отбор)</span></template>
                  </dd>
                </div>
              </div>
            </dl>
            <RButton variant="primary" size="lg" block class="mt-6" @click="openApplicationModal()">
              Оставить заявку
            </RButton>
          </RCard>
        </div>
      </div>

      <!-- Application modal -->
      <RModal v-model="showModal" title="Оставить заявку" subtitle="Заполните форму, и мы свяжемся с вами" size="md">
        <form @submit.prevent="submitApplication" class="space-y-4">
          <RInput v-model="form.name" label="Имя" placeholder="Ваше имя" required />
          <RInput v-model="form.email" type="email" label="Email" placeholder="your@email.com" required />
          <RInput v-model="form.phone" type="tel" label="Телефон" placeholder="+7 (___) ___-__-__" />
          <div>
            <label class="block text-sm font-medium text-gray-700">Сообщение</label>
            <textarea v-model="form.message" rows="3" placeholder="Ваше сообщение..." class="mt-1.5 w-full rounded-xl border-gray-300 px-4 py-3 transition focus:border-[#003274] focus:ring-[#003274]/20" />
          </div>
        </form>

        <template #footer>
          <RButton variant="outline" @click="showModal = false">
            Отмена
          </RButton>
          <RButton variant="primary" @click="submitApplication">
            Отправить
          </RButton>
        </template>
      </RModal>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  tour: Object,
})

const showModal = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  message: '',
  tour_departure_id: null,
})

function openApplicationModal(departureId = null) {
  form.tour_departure_id = departureId
  showModal.value = true
}

function submitApplication() {
  router.post(route('applications.store'), {
    type: 'tour',
    tour_id: props.tour.id,
    tour_departure_id: form.tour_departure_id,
    name: form.name,
    email: form.email,
    phone: form.phone,
    message: form.message,
  }, {
    onSuccess: () => {
      showModal.value = false
      form.name = form.email = form.phone = form.message = ''
    },
  })
}

function projectLabel(key) {
  const labels = { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }
  return labels[key] || key
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
