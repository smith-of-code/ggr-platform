<template>
  <div class="min-h-dvh bg-gradient-to-b from-slate-100 to-slate-50 px-4 py-8 font-sans text-slate-900">
    <Head title="Личный кабинет туров" />
    <div class="mx-auto max-w-6xl">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Личный кабинет</h1>
          <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-slate-600">Профиль участника, заявки на туры и этапы конкурса.</p>
        </div>
        <form @submit.prevent="logout" class="shrink-0">
          <RButton type="submit" variant="outline" size="sm">Выйти</RButton>
        </form>
      </div>

      <div v-if="$page.props.flash?.success" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
        {{ $page.props.flash.error }}
      </div>

      <!-- Верхний ряд: карточка участника + статус заявок -->
      <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div
          class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 lg:col-span-2"
        >
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Участник</p>
          <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-stretch">
            <div
              class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 text-xs text-slate-500 shadow-inner sm:h-32 sm:w-32"
            >
              <img v-if="avatarDisplayUrl" :src="avatarDisplayUrl" alt="" class="h-full w-full object-cover" />
              <span v-else>Нет фото</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="font-mono text-xs text-slate-500">ID {{ profile.user_id }}</p>
              <p class="mt-1 text-xl font-bold leading-tight text-slate-900 sm:text-2xl">{{ profile.display_name }}</p>
              <p v-if="ageLabel" class="mt-2 text-sm text-slate-600">{{ ageLabel }}</p>
              <button
                type="button"
                class="mt-4 inline-flex cursor-pointer text-sm font-semibold text-rosatom-700 underline decoration-rosatom-300 decoration-2 underline-offset-4 transition hover:text-rosatom-900"
                :aria-expanded="fullProfileVisible"
                aria-controls="tour-cabinet-profile"
                @click="toggleFullProfile"
              >
                {{ fullProfileVisible ? 'Скрыть полный профиль' : 'Показать полный профиль' }}
              </button>
            </div>
          </div>
        </div>

        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5">
          <div class="flex items-center gap-2.5 border-b border-slate-100 bg-slate-50/90 px-5 py-4">
            <ClipboardDocumentListIcon class="h-5 w-5 shrink-0 text-rosatom-600" aria-hidden="true" />
            <h2 class="text-base font-bold text-slate-900">Статус заявок</h2>
          </div>
          <div class="flex-1 p-4 sm:p-5">
            <template v-if="tourApplications.length">
              <ul class="space-y-3">
                <li
                  v-for="app in tourApplications"
                  :key="app.id"
                  class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3.5 transition hover:border-slate-200 hover:bg-slate-50"
                >
                  <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0 flex-1">
                      <p class="font-semibold leading-snug text-slate-900">{{ app.tour_title }}</p>
                      <p v-if="app.date_range" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-600">
                        <CalendarDaysIcon class="h-3.5 w-3.5 shrink-0 text-slate-400" aria-hidden="true" />
                        {{ app.date_range }}
                      </p>
                    </div>
                    <span
                      class="inline-flex w-fit shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                      :class="tourApplicationStatusPillClass(app.status_key)"
                    >
                      {{ app.status_label }}
                    </span>
                  </div>
                </li>
              </ul>
            </template>
            <p v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-4 py-6 text-center text-sm leading-relaxed text-slate-600">
              Заявок на туры по этому аккаунту пока нет.
            </p>
          </div>
        </div>
      </div>

      <!-- Полный профиль -->
      <section id="tour-cabinet-profile" class="mt-10 scroll-mt-8">
        <div v-show="fullProfileVisible">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Полный профиль</h2>
          <form
            class="mt-3 space-y-5 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 sm:p-8"
            @submit.prevent="submitProfile"
          >
          <div>
            <p class="mb-3 text-xs font-medium text-slate-600">Фото профиля</p>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
              <div
                class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 text-xs text-slate-500 shadow-inner sm:h-32 sm:w-32"
              >
                <img v-if="avatarDisplayUrl" :src="avatarDisplayUrl" alt="" class="h-full w-full object-cover" />
                <span v-else>Нет фото</span>
              </div>
              <div class="min-w-0 flex-1 space-y-2">
                <input
                  :key="avatarInputKey"
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif"
                  class="block w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-rosatom-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-rosatom-800 hover:file:bg-rosatom-100"
                  @change="onAvatarFile"
                />
                <p v-if="profileForm.errors.avatar" class="text-xs text-red-600">{{ profileForm.errors.avatar }}</p>
                <p class="text-xs text-slate-500">JPEG, PNG, WebP или GIF, до 2 МБ.</p>
              </div>
            </div>
          </div>

          <RInput v-model="profileForm.last_name" label="Фамилия" :error="profileForm.errors.last_name" autocomplete="family-name" />
          <RInput v-model="profileForm.first_name" label="Имя" :error="profileForm.errors.first_name" autocomplete="given-name" />
          <RInput v-model="profileForm.patronymic" label="Отчество" :error="profileForm.errors.patronymic" autocomplete="additional-name" />

          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Пол</label>
            <select
              v-model="profileForm.gender"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            >
              <option value="">Не указано</option>
              <option value="male">Мужской</option>
              <option value="female">Женский</option>
            </select>
            <p v-if="profileForm.errors.gender" class="mt-1 text-xs text-red-600">{{ profileForm.errors.gender }}</p>
          </div>

          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Дата рождения</label>
            <input
              v-model="profileForm.birth_date"
              type="date"
              class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-rosatom-500 focus:ring-1 focus:ring-rosatom-500/20"
            />
            <p v-if="profileForm.errors.birth_date" class="mt-1 text-xs text-red-600">{{ profileForm.errors.birth_date }}</p>
          </div>

          <RInput v-model="profileForm.phone" type="tel" label="Телефон" :error="profileForm.errors.phone" autocomplete="tel" />
          <RInput v-model="profileForm.email" type="email" label="Email" :error="profileForm.errors.email" required autocomplete="email" />

          <div class="flex flex-wrap gap-3 pt-2">
            <RButton type="submit" variant="primary" :loading="profileForm.processing" :disabled="profileForm.processing">Сохранить профиль</RButton>
          </div>
        </form>
        </div>
      </section>

      <section id="tour-cabinet-contest" class="mt-10 scroll-mt-8 space-y-10">
        <div v-if="showContestLocationOffers">
          <h2 class="text-base font-bold lowercase leading-snug text-gray-900">
            сейчас доступно участие в конкурсе следующих туров:
          </h2>
          <ul v-if="contestLocationOffers.length" class="mt-4 space-y-3">
            <li
              v-for="row in contestLocationOffers"
              :key="row.id"
              class="flex flex-col gap-3 rounded-2xl bg-gray-200/90 px-4 py-4 shadow-sm sm:flex-row sm:items-stretch sm:gap-5 sm:px-6 sm:py-5"
            >
              <div class="min-w-0 sm:w-[28%]">
                <p class="font-bold text-gray-900">{{ row.city_name }}</p>
                <p v-if="row.date_range" class="mt-1 text-sm text-gray-900">{{ row.date_range }}</p>
              </div>
              <div v-if="row.footnote" class="min-w-0 flex-1 self-center text-xs leading-snug text-gray-500 sm:px-2">
                {{ row.footnote }}
              </div>
              <div v-else class="hidden flex-1 sm:block" />
              <div class="flex shrink-0 justify-end sm:ml-auto sm:w-[11rem]">
                <a
                  v-if="row.button_kind === 'participate' && row.city_form_url"
                  :href="row.city_form_url"
                  class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl bg-[#8f5f52] px-4 py-2.5 text-center text-sm font-semibold lowercase text-white shadow-sm transition hover:bg-[#7a5146] sm:w-auto sm:min-w-[10.5rem]"
                >
                  принять участие
                </a>
                <a
                  v-else-if="row.button_kind === 'participate'"
                  href="#tour-cabinet-contest"
                  class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl bg-[#8f5f52] px-4 py-2.5 text-center text-sm font-semibold lowercase text-white shadow-sm transition hover:bg-[#7a5146] sm:w-auto sm:min-w-[10.5rem]"
                >
                  принять участие
                </a>
                <span
                  v-else
                  class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl bg-[#8f5f52] px-4 py-2.5 text-center text-sm font-semibold lowercase text-white/95 shadow-sm sm:w-auto sm:min-w-[10.5rem]"
                >
                  заявка на проверке
                </span>
              </div>
            </li>
          </ul>
          <p v-else class="mt-4 rounded-2xl bg-gray-200/90 px-4 py-6 text-sm text-gray-600 shadow-sm">
            Направления конкурса пока не настроены в админке или список городов пуст.
          </p>
        </div>

        <div
          id="tour-cabinet-contest-detail"
          class="scroll-mt-8 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 sm:p-7"
        >
          <div class="border-b border-slate-100 pb-4">
            <h2 class="text-base font-bold text-slate-900">Конкурс</h2>
            <p class="mt-1 text-sm text-slate-600">Сейчас в прогрессе: этап {{ contestProgress.current_stage }}.</p>
          </div>

          <div class="mt-5 space-y-6" role="tablist" aria-label="Этапы конкурса">
            <div v-for="(st, stageIdx) in contestStageSummary" :key="st.roman">
              <h3 class="text-base font-bold text-slate-900">{{ st.title }}</h3>
              <button
                type="button"
                role="tab"
                class="mt-2 flex w-full cursor-pointer items-center gap-4 rounded-2xl border border-slate-100 bg-slate-100/90 px-4 py-4 text-left shadow-sm outline-none transition ring-offset-2 ring-offset-white hover:bg-slate-100 sm:gap-5 sm:px-6 sm:py-5"
                :class="
                  activeContestTab === stageIdx + 1
                    ? 'ring-2 ring-rosatom-500'
                    : 'ring-2 ring-transparent focus-visible:ring-rosatom-400'
                "
                :aria-selected="activeContestTab === stageIdx + 1"
                @click="activeContestTab = stageIdx + 1"
              >
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-rosatom-50 ring-1 ring-rosatom-100/80"
                  aria-hidden="true"
                >
                  <UserCircleIcon v-if="stageIdx === 0" class="h-8 w-8 text-rosatom-700" />
                  <ChatBubbleLeftRightIcon v-else-if="stageIdx === 1" class="h-8 w-8 text-rosatom-700" />
                  <AcademicCapIcon v-else class="h-8 w-8 text-rosatom-700" />
                </div>
                <p class="min-w-0 flex-1 text-base font-semibold text-slate-900">{{ st.label }}</p>
                <p class="shrink-0 text-sm font-medium lowercase text-slate-600">{{ st.status_label }}</p>
              </button>

              <div
                v-show="activeContestTab === stageIdx + 1"
                class="mt-3 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-4 sm:px-5"
              >
                <ContestStage1Panel v-if="stageIdx === 0" v-bind="contestStage1" />
                <ContestStage2Panel
                  v-else-if="stageIdx === 1"
                  :questions="contestStage2Questions"
                  :locked="contestProgress.current_stage !== 2"
                  :contest-stage="contestProgress.current_stage"
                />
                <ContestStage3Panel
                  v-else
                  :progress="contestStage3Progress"
                  :locked="contestStage3Locked"
                  :lock-notice="contestStage3LockNotice"
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import {
  AcademicCapIcon,
  CalendarDaysIcon,
  ChatBubbleLeftRightIcon,
  ClipboardDocumentListIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline'
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import ContestStage1Panel from './Contest/ContestStage1Panel.vue'
import ContestStage2Panel from './Contest/ContestStage2Panel.vue'
import ContestStage3Panel from './Contest/ContestStage3Panel.vue'

const props = defineProps({
  contestProgress: {
    type: Object,
    default: () => ({ current_stage: 1 }),
  },
  contestStage1: {
    type: Object,
    required: true,
  },
  contestStage2Questions: {
    type: Array,
    default: () => [],
  },
  contestStage3Progress: {
    type: Object,
    required: true,
  },
  profile: {
    type: Object,
    required: true,
  },
  tourApplications: {
    type: Array,
    default: () => [],
  },
  contestLocationOffers: {
    type: Array,
    default: () => [],
  },
  contestStageSummary: {
    type: Array,
    default: () => [],
  },
})

function tourApplicationStatusPillClass(statusKey) {
  switch (statusKey) {
    case 'approved':
      return 'bg-emerald-50 text-emerald-900 ring-emerald-600/15'
    case 'rejected':
      return 'bg-red-50 text-red-900 ring-red-600/15'
    default:
      return 'bg-amber-50 text-amber-950 ring-amber-600/20'
  }
}

/** Список «доступно участие в конкурсе…» скрыт по требованию UI; включите true, чтобы снова показать блок. */
const showContestLocationOffers = false

const fullProfileVisible = ref(false)

function toggleFullProfile() {
  fullProfileVisible.value = !fullProfileVisible.value
}

function openFullProfileFromHash() {
  if (typeof window === 'undefined' || window.location.hash !== '#tour-cabinet-profile') {
    return
  }
  fullProfileVisible.value = true
  nextTick(() => {
    document.getElementById('tour-cabinet-profile')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
}

function onProfileHashChange() {
  openFullProfileFromHash()
}

onMounted(() => {
  openFullProfileFromHash()
  window.addEventListener('hashchange', onProfileHashChange)
})

function ruYearsWord(n) {
  const x = Math.abs(Number(n)) % 100
  const y = x % 10
  if (x > 10 && x < 20) return 'лет'
  if (y === 1) return 'год'
  if (y >= 2 && y <= 4) return 'года'
  return 'лет'
}

function computeAgeFromBirthDate(iso) {
  if (!iso || typeof iso !== 'string') return null
  const d = new Date(`${iso}T12:00:00`)
  if (Number.isNaN(d.getTime())) return null
  const today = new Date()
  let age = today.getFullYear() - d.getFullYear()
  const m = today.getMonth() - d.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < d.getDate())) {
    age -= 1
  }
  return age >= 0 ? age : null
}

const ageLabel = computed(() => {
  const age = computeAgeFromBirthDate(props.profile.birth_date)
  if (age === null) return null
  return `${age} ${ruYearsWord(age)}`
})

const avatarInputKey = ref(0)
const avatarObjectUrl = ref(null)

const avatarDisplayUrl = computed(() => {
  if (avatarObjectUrl.value) {
    return avatarObjectUrl.value
  }
  return props.profile.avatar_url || null
})

function revokeAvatarPreview() {
  if (avatarObjectUrl.value) {
    URL.revokeObjectURL(avatarObjectUrl.value)
    avatarObjectUrl.value = null
  }
}

function onAvatarFile(e) {
  const file = e.target.files?.[0] ?? null
  revokeAvatarPreview()
  profileForm.avatar = file
  if (file instanceof File) {
    avatarObjectUrl.value = URL.createObjectURL(file)
  }
}

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('hashchange', onProfileHashChange)
  }
  revokeAvatarPreview()
})

function defaultContestTab() {
  const s = Number(props.contestProgress?.current_stage)
  const n = Number.isFinite(s) ? s : 1
  return Math.min(Math.max(n, 1), 3)
}

const activeContestTab = ref(defaultContestTab())

const contestStage3LockNotice = computed(() => {
  const st = Number(props.contestProgress?.current_stage)
  if (!Number.isFinite(st) || st < 3) {
    return 'early'
  }
  const t = props.contestStage3Progress?.stage3_text
  if (typeof t === 'string' && t.trim() !== '') {
    return 'saved'
  }

  return null
})

const contestStage3Locked = computed(() => contestStage3LockNotice.value !== null)

watch(
  () => props.contestProgress?.current_stage,
  () => {
    activeContestTab.value = defaultContestTab()
  },
)

const profileForm = useForm({
  last_name: props.profile.last_name ?? '',
  first_name: props.profile.first_name ?? '',
  patronymic: props.profile.patronymic ?? '',
  gender: props.profile.gender ?? '',
  birth_date: props.profile.birth_date ?? '',
  phone: props.profile.phone ?? '',
  email: props.profile.email ?? '',
  avatar: null,
})

watch(
  () => props.profile,
  (p) => {
    profileForm.last_name = p.last_name ?? ''
    profileForm.first_name = p.first_name ?? ''
    profileForm.patronymic = p.patronymic ?? ''
    profileForm.gender = p.gender ?? ''
    profileForm.birth_date = p.birth_date ?? ''
    profileForm.phone = p.phone ?? ''
    profileForm.email = p.email ?? ''
    profileForm.avatar = null
    revokeAvatarPreview()
    avatarInputKey.value += 1
  },
  { deep: true },
)

function submitProfile() {
  profileForm
    .transform((data) => ({
      ...data,
      _method: 'patch',
    }))
    .post(route('tour-cabinet.profile.update'), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        profileForm.avatar = null
        revokeAvatarPreview()
        avatarInputKey.value += 1
      },
      onError: () => {
        fullProfileVisible.value = true
      },
    })
}

function logout() {
  router.post(route('tour-cabinet.logout'))
}
</script>
