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

          <!-- Реакции -->
          <div class="reveal mt-6">
            <p class="mb-3 text-sm font-medium text-gray-500">Как вам тур?</p>
            <div class="flex flex-wrap gap-2 sm:gap-3">
              <button
                v-for="item in reactionItems"
                :key="item.key"
                type="button"
                :disabled="reactionSending"
                class="group flex min-w-[4.25rem] flex-col items-center gap-1 rounded-xl border px-3 py-2.5 text-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-[#003274]/35 hover:shadow-md active:scale-[0.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003274]/40 disabled:cursor-wait disabled:opacity-60 disabled:active:scale-100"
                :class="currentUserReaction === item.key
                  ? 'border-[#003274] bg-[#003274]/[0.08] ring-2 ring-[#003274]/25 shadow-sm'
                  : 'border-gray-200 bg-white'"
                :title="item.label"
                @click="sendReaction(item.key)"
              >
                <span class="text-xl leading-none transition-transform duration-200 group-hover:scale-110" aria-hidden="true">{{ item.emoji }}</span>
                <span class="tabular-nums text-xs font-semibold text-gray-700">
                  <Transition name="count-pop" mode="out-in">
                    <span :key="reactionsDisplay[item.key]">{{ reactionsDisplay[item.key] }}</span>
                  </Transition>
                </span>
              </button>
            </div>
          </div>

          <div class="reveal mt-8 text-lg leading-relaxed text-gray-600" v-html="tour.description" />

          <section v-if="tour.target_audience" class="reveal mt-10">
            <h2 class="text-xl font-bold text-gray-900">Для кого этот тур</h2>
            <div class="html-content mt-4 text-base leading-relaxed text-gray-700" v-html="tour.target_audience" />
          </section>

          <section v-if="tour.organizer_info" class="reveal mt-10">
            <h2 class="text-xl font-bold text-gray-900">Организатор</h2>
            <div class="html-content mt-4 text-base leading-relaxed text-gray-700" v-html="tour.organizer_info" />
          </section>

          <section v-if="tour.cities?.length" class="reveal mt-12">
            <h2 class="text-xl font-bold text-gray-900">Города</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
              <Link
                v-for="city in tour.cities"
                :key="city.id"
                :href="route('cities.show', city.slug)"
                class="group flex gap-4 overflow-hidden rounded-xl border border-gray-200 bg-white p-4 transition-all duration-200 hover:border-[#003274]/30 hover:shadow-md"
              >
                <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                  <img
                    v-if="city.image"
                    :src="city.image"
                    :alt="city.name"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                  />
                </div>
                <div class="flex min-w-0 flex-1 flex-col justify-center">
                  <span class="font-semibold text-gray-900 transition-colors group-hover:text-[#003274]">{{ city.name }}</span>
                  <span v-if="city.region" class="mt-0.5 text-sm text-gray-500">{{ city.region }}</span>
                  <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-[#003274]">
                    Подробнее
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                  </span>
                </div>
              </Link>
            </div>
          </section>

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
            <div class="flex items-start justify-between gap-3">
              <h2 class="text-lg font-bold text-gray-900">Детали тура</h2>
              <button
                type="button"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/50 disabled:cursor-wait disabled:opacity-60"
                :class="isFavorited ? 'border-rose-200 bg-rose-50 text-rose-600' : ''"
                :disabled="favoriteSending"
                :title="isAuthed ? (isFavorited ? 'Убрать из избранного' : 'В избранное') : 'Войдите, чтобы добавить в избранное'"
                aria-label="Избранное"
                @click="toggleFavorite"
              >
                <svg v-if="isFavorited" class="h-5 w-5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17l-.022.012-.007.003-.002.001h-.002z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
              </button>
            </div>
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
.count-pop-enter-active,
.count-pop-leave-active {
  transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.34, 1.2, 0.64, 1);
}
.count-pop-enter-from {
  opacity: 0;
  transform: translateY(-5px) scale(0.85);
}
.count-pop-leave-to {
  opacity: 0;
  transform: translateY(5px) scale(0.85);
}
</style>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const REACTION_KEYS = ['love', 'wow', 'fire', 'cool', 'star']
const REACTION_META = {
  love: { emoji: '❤️', label: 'Нравится' },
  wow: { emoji: '😮', label: 'Вау' },
  fire: { emoji: '🔥', label: 'Огонь' },
  cool: { emoji: '😎', label: 'Круто' },
  star: { emoji: '⭐', label: 'Звезда' },
}

const props = defineProps({
  tour: { type: Object, required: true },
  userReaction: { type: String, default: null },
})

const page = usePage()

const isAuthed = computed(() => !!page.props.auth?.user)

const currentUserReaction = computed(() => props.userReaction ?? props.tour?.user_reaction ?? null)

const reactionsDisplay = computed(() => {
  const raw = props.tour?.reactions_count
  const out = {}
  for (const key of REACTION_KEYS) {
    out[key] = raw?.[key] ?? 0
  }
  return out
})

const reactionItems = computed(() =>
  REACTION_KEYS.map((key) => ({
    key,
    emoji: REACTION_META[key].emoji,
    label: REACTION_META[key].label,
  })),
)

const reactionSending = ref(false)
const favoriteSending = ref(false)

const isFavorited = ref(!!props.tour?.is_favorited)

watch(
  () => props.tour?.is_favorited,
  (v) => {
    if (typeof v === 'boolean') {
      isFavorited.value = v
    }
  },
)

function sendReaction(emoji) {
  if (reactionSending.value) {
    return
  }
  reactionSending.value = true
  router.post(route('tours.react', props.tour.id), { emoji }, {
    preserveScroll: true,
    only: ['tour', 'userReaction'],
    onFinish: () => {
      reactionSending.value = false
    },
  })
}

function toggleFavorite() {
  if (!isAuthed.value) {
    router.visit(route('login'))
    return
  }
  if (favoriteSending.value) {
    return
  }
  favoriteSending.value = true
  router.post(route('favorites.toggle', { type: 'tour', id: props.tour.id }), {}, {
    preserveScroll: true,
    only: ['tour', 'userReaction'],
    onFinish: () => {
      favoriteSending.value = false
    },
  })
}

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
