<template>
  <MainLayout>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
      <!-- Header: Title + Meta + Button -->
      <div class="reveal mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1">
          <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">{{ tour.title }}</h1>
          <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-600">
            <span v-if="tour.start_city" class="flex items-center gap-1.5">
              <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
              Старт программы: {{ tour.start_city }}
            </span>
            <span class="hidden text-gray-300 sm:inline">|</span>
            <span v-if="tour.duration" class="flex items-center gap-1.5">
              <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
              {{ tour.duration }}
            </span>
            <span class="hidden text-gray-300 sm:inline">|</span>
            <span v-if="tour.price_from > 0" class="flex items-center gap-1.5 font-semibold text-gray-900">
              от {{ formatPrice(tour.price_from) }} &#8381; за человека
            </span>
          </div>
        </div>
        <RButton variant="primary" size="lg" class="shrink-0" @click="openApplicationModal()">
          Оставить заявку
        </RButton>
      </div>

      <!-- Gallery -->
      <div v-if="allMedia.length > 1" class="reveal mb-8">
        <div class="grid gap-2" :class="allMedia.length >= 3 ? 'grid-cols-4 grid-rows-2' : 'grid-cols-2'">
          <button
            type="button"
            class="group relative overflow-hidden rounded-xl bg-gray-200 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003274]"
            :class="allMedia.length >= 3 ? 'col-span-2 row-span-2' : ''"
            @click="openLightbox(0)"
          >
            <div :class="allMedia.length >= 3 ? 'aspect-[4/3]' : 'aspect-video'" class="overflow-hidden">
              <img :src="allMedia[0]" :alt="tour.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            </div>
            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/10" />
          </button>
          <button
            v-for="(img, mi) in allMedia.slice(1, allMedia.length >= 3 ? 5 : 2)"
            :key="mi"
            type="button"
            class="group relative overflow-hidden rounded-xl bg-gray-200 shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003274]"
            @click="openLightbox(mi + 1)"
          >
            <div class="aspect-video overflow-hidden">
              <img :src="img" :alt="`Фото ${mi + 2}`" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            </div>
            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/10" />
            <div
              v-if="mi === (allMedia.length >= 3 ? 3 : 1) && allMedia.length > (allMedia.length >= 3 ? 5 : 2)"
              class="absolute inset-0 flex items-center justify-center bg-black/50 text-xl font-bold text-white"
            >
              +{{ allMedia.length - (allMedia.length >= 3 ? 5 : 2) }} фото
            </div>
          </button>
        </div>
      </div>
      <div v-else-if="tour.image" class="reveal mb-8 overflow-hidden rounded-xl bg-gray-200 shadow-sm">
        <button type="button" class="group w-full focus:outline-none" @click="openLightbox(0)">
          <div class="aspect-video overflow-hidden">
            <img :src="tour.image" :alt="tour.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
          </div>
        </button>
      </div>

      <!-- Tab Navigation -->
      <nav class="reveal mb-8 overflow-x-auto border-b border-gray-200">
        <div class="flex gap-0">
          <button
            v-for="tab in visibleTabs"
            :key="tab.id"
            type="button"
            class="cursor-pointer whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition"
            :class="activeTab === tab.id ? 'border-[#003274] text-[#003274]' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
            @click="scrollToSection(tab.id)"
          >
            {{ tab.label }}
          </button>
        </div>
      </nav>

      <!-- Content grid -->
      <div class="lg:grid lg:grid-cols-3 lg:gap-10">
        <div class="lg:col-span-2">
          <!-- Description -->
          <section id="section-description" class="reveal">
            <div class="text-lg leading-relaxed text-gray-600" v-html="tour.description" />
          </section>

          <!-- Program -->
          <section id="section-program" v-if="tour.program_days?.length || tour.program_pdf" class="reveal mt-10">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900">Программа тура</h2>
              <a v-if="tour.program_pdf" :href="tour.program_pdf" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#003274] shadow-sm transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Скачать программу тура
              </a>
            </div>
            <div v-if="tour.program_days?.length" class="mt-6 space-y-0">
              <div v-for="(day, di) in tour.program_days" :key="di" class="relative pl-8 pb-8 last:pb-0">
                <div class="absolute left-[11px] top-7 bottom-0 w-px bg-gray-200 last:hidden" />
                <div class="absolute left-0 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-[#003274] text-xs font-bold text-white">{{ di + 1 }}</div>
                <h3 class="text-base font-semibold text-gray-900">{{ day.title }}</h3>
                <p v-if="day.description" class="mt-1.5 text-sm leading-relaxed text-gray-600 whitespace-pre-line">{{ day.description }}</p>
              </div>
            </div>
          </section>

          <!-- Город отправления -->
          <section id="section-departure" v-if="tour.start_city || tour.departure_info" class="reveal mt-10">
            <h2 class="text-xl font-bold text-gray-900">Город отправления</h2>
            <div class="mt-4 space-y-3">
              <div v-if="tour.start_city" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                  <svg class="h-4 w-4 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Точка сбора</p>
                  <p class="font-medium text-gray-900">{{ tour.start_city }}</p>
                </div>
              </div>
              <div v-if="tour.departure_info" class="html-content text-base leading-relaxed text-gray-700" v-html="tour.departure_info" />
            </div>
          </section>

          <!-- Accommodations / Проживание -->
          <section id="section-accommodation" v-if="tour.accommodations?.length || tour.accommodation_info" class="reveal mt-10">
            <h2 class="text-xl font-bold text-gray-900">Проживание</h2>
            <div v-if="tour.accommodation_info" class="html-content mt-4 text-base leading-relaxed text-gray-700" v-html="tour.accommodation_info" />
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
              <div v-for="(acc, ai) in tour.accommodations" :key="ai" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div v-if="acc.images?.length" class="aspect-video overflow-hidden bg-gray-100">
                  <img :src="acc.images[0]" :alt="acc.title" class="h-full w-full object-cover" />
                </div>
                <div class="p-5">
                  <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900">{{ acc.title }}</h3>
                    <RBadge v-if="acc.budget" :variant="budgetVariant(acc.budget)">{{ budgetLabel(acc.budget) }}</RBadge>
                  </div>
                  <p v-if="acc.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ acc.description }}</p>
                  <div v-if="acc.images?.length > 1" class="mt-3 flex gap-1.5">
                    <button v-for="(img, ii) in acc.images.slice(0, 4)" :key="ii" type="button" class="h-12 w-16 overflow-hidden rounded-lg border border-gray-200" @click="openLightbox(allMedia.indexOf(img) >= 0 ? allMedia.indexOf(img) : 0)">
                      <img :src="img" alt="" class="h-full w-full object-cover" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Memo -->
          <section id="section-memo" v-if="tour.memo_text || tour.memo_pdf || tour.closed_city" class="reveal mt-10">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900">Памятка участника</h2>
              <a v-if="tour.memo_pdf" :href="tour.memo_pdf" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-[#003274] shadow-sm transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Скачать памятку
              </a>
            </div>
            <div v-if="tour.closed_city" class="mt-4 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
              <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
              </svg>
              <div>
                <p class="font-semibold text-amber-800">Закрытый город</p>
                <p class="mt-1 text-sm text-amber-700">Для посещения этого тура требуется оформление специального пропуска. Подробности смотрите в разделе «Оформление пропуска».</p>
              </div>
            </div>
            <div v-if="tour.memo_text" class="html-content mt-4 text-base leading-relaxed text-gray-700" v-html="tour.memo_text" />
          </section>

          <!-- Departures -->
          <section id="section-dates" v-if="tour.departures?.length" class="reveal mt-12">
            <h2 class="text-xl font-bold text-gray-900">Даты заездов</h2>
            <div class="mt-6 space-y-4">
              <div v-for="dep in tour.departures" :key="dep.id" class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5 transition hover:shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
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

          <!-- Reviews -->
          <section id="section-reviews" class="reveal mt-12">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-gray-900">Отзывы</h2>
                <div v-if="reviews?.length" class="flex items-center gap-2 text-sm text-gray-500">
                  <span>{{ reviews.length }} {{ reviewsWord(reviews.length) }}</span>
                  <span class="text-gray-300">•</span>
                  <span class="flex items-center gap-1 text-amber-500">
                    <span class="text-base">&#9733;</span>
                    <span class="font-semibold text-gray-900">{{ avgRating }}</span>
                  </span>
                </div>
              </div>
              <button
                v-if="isAuthed && !userReviewExists && !reviewSent"
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                @click="showReviewForm = !showReviewForm"
              >
                Написать отзыв
              </button>
            </div>

            <!-- Review form -->
            <div v-if="showReviewForm && isAuthed && !userReviewExists && !reviewSent" class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
              <h3 class="mb-4 font-semibold text-gray-900">Оставить отзыв</h3>
              <div class="space-y-4">
                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Оценка</label>
                  <div class="flex gap-1">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      class="text-2xl transition-transform hover:scale-110"
                      @click="reviewForm.rating = star"
                    >
                      <span :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-gray-300'">&#9733;</span>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="mb-2 block text-sm font-medium text-gray-700">Текст отзыва</label>
                  <textarea v-model="reviewForm.text" rows="3" class="w-full rounded-xl border-gray-300 px-4 py-3 text-sm transition focus:border-[#003274] focus:ring-[#003274]/20" placeholder="Расскажите о вашем опыте..." />
                </div>
                <RButton variant="primary" :disabled="reviewForm.rating === 0" @click="submitReview">
                  Отправить отзыв
                </RButton>
              </div>
            </div>

            <div v-if="reviewSent" class="mt-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4">
              <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
              <p class="text-sm font-medium text-green-800">Спасибо! Ваш отзыв отправлен на модерацию.</p>
            </div>

            <div v-if="userReviewExists && !reviewSent" class="mt-6 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
              <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
              <p class="text-sm font-medium text-blue-800">Вы уже оставили отзыв на этот тур.</p>
            </div>

            <div v-if="!isAuthed" class="mt-6 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
              <p class="text-sm text-gray-600">
                <Link :href="route('login')" class="font-medium text-[#003274] hover:underline">Войдите</Link>, чтобы оставить отзыв.
              </p>
            </div>

            <!-- Reviews list -->
            <div v-if="reviews?.length" class="mt-6 space-y-4">
              <div v-for="review in reviews" :key="review.id" class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#003274]/10 text-sm font-bold text-[#003274]">
                      {{ review.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">{{ review.user?.name ?? 'Пользователь' }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex gap-0.5 text-amber-400">
                    <span v-for="s in 5" :key="s" :class="s <= review.rating ? '' : 'text-gray-300'">&#9733;</span>
                  </div>
                </div>
                <p v-if="review.text" class="mt-3 text-sm leading-relaxed text-gray-700">{{ review.text }}</p>
              </div>
            </div>
            <p v-else-if="!reviewSent && !userReviewExists" class="mt-6 text-sm text-gray-500">Пока нет отзывов. Будьте первым!</p>
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
                  <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Тур</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ tour.title }}</dd>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                  <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Продолжительность</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ tour.duration }}</dd>
                </div>
              </div>
              <div v-if="tour.group_size" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                  <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Группа</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ tour.group_size }}{{ tour.min_age ? `, от ${tour.min_age} лет` : '' }}</dd>
                </div>
              </div>

              <!-- Pass info in sidebar -->
              <div v-if="tour.closed_city" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                  <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Оформление пропуска</dt>
                  <dd v-if="tour.pass_info" class="html-content mt-0.5 text-sm leading-relaxed text-gray-700" v-html="tour.pass_info" />
                  <dd v-else class="mt-0.5 font-medium text-gray-900">Требуется оформление пропуска</dd>
                </div>
              </div>

              <!-- Conditions -->
              <div v-if="tour.conditions" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                  <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Условия участия</dt>
                  <dd class="html-content mt-0.5 text-sm leading-relaxed text-gray-700" v-html="tour.conditions" />
                </div>
              </div>

              <!-- Cost -->
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50">
                  <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Стоимость</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">
                    <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381; за человека</template>
                    <template v-else><span class="text-green-600">Бесплатно (конкурсный отбор)</span></template>
                  </dd>
                  <dd v-if="tour.cost_info" class="html-content mt-1.5 text-sm leading-relaxed text-gray-600" v-html="tour.cost_info" />
                </div>
              </div>
              <div v-if="tour.project" class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                  <svg class="h-4 w-4 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                </div>
                <div>
                  <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Проект</dt>
                  <dd class="mt-0.5 font-medium text-gray-900">{{ projectLabel(tour.project) }}</dd>
                </div>
              </div>
            </dl>

            <!-- Sidebar departures -->
            <div v-if="tour.departures?.length" class="mt-6 border-t border-gray-100 pt-5">
              <p class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-400">Даты заезда</p>
              <div class="space-y-2">
                <div v-for="dep in tour.departures.slice(0, 3)" :key="dep.id" class="cursor-pointer rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 transition hover:border-[#003274]/20 hover:bg-blue-50/50" @click="scrollToSection('dates')">
                  <p class="text-sm font-medium text-gray-900">{{ formatDateShort(dep.start_date) }}–{{ formatDateShort(dep.end_date) }}</p>
                  <p v-if="dep.price_per_person" class="text-xs text-gray-500">{{ formatPrice(dep.price_per_person) }} &#8381; за человека</p>
                </div>
                <button v-if="tour.departures.length > 3" type="button" class="w-full cursor-pointer rounded-lg border border-gray-200 bg-white py-2 text-center text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="scrollToSection('dates')">
                  смотреть все даты
                </button>
              </div>
            </div>

            <RButton variant="primary" size="lg" block class="mt-6" @click="openApplicationModal()">
              Оставить заявку
            </RButton>
          </RCard>
        </div>
      </div>

      <!-- Videos -->
      <div v-if="videoEmbeds.length" class="reveal mt-10 space-y-4">
        <h2 class="text-xl font-bold text-gray-900">Видео</h2>
        <div v-for="(src, vi) in videoEmbeds" :key="vi" class="overflow-hidden rounded-xl border border-gray-200 bg-black shadow-md">
          <div class="aspect-video w-full">
            <iframe :src="src" class="h-full w-full" :title="`Видео ${vi + 1}`" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen />
          </div>
        </div>
      </div>

      <!-- Reactions -->
      <div class="reveal mt-10">
        <p class="mb-3 text-sm font-medium text-gray-500">Как вам тур?</p>
        <div v-if="isAuthed" class="flex flex-wrap gap-2 sm:gap-3">
          <button
            v-for="item in reactionItems"
            :key="item.key"
            type="button"
            :disabled="reactionSending"
            class="group flex min-w-[4.25rem] flex-col items-center gap-1 rounded-xl border px-3 py-2.5 text-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-[#003274]/35 hover:shadow-md active:scale-[0.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#003274]/40 disabled:cursor-wait disabled:opacity-60 disabled:active:scale-100"
            :class="currentUserReaction === item.key ? 'border-[#003274] bg-[#003274]/[0.08] ring-2 ring-[#003274]/25 shadow-sm' : 'border-gray-200 bg-white'"
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
        <div v-else class="flex flex-wrap items-center gap-2">
          <div v-for="item in reactionItems" :key="item.key" class="flex min-w-[4.25rem] flex-col items-center gap-1 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm">
            <span class="text-xl leading-none" aria-hidden="true">{{ item.emoji }}</span>
            <span class="tabular-nums text-xs font-semibold text-gray-700">{{ reactionsDisplay[item.key] }}</span>
          </div>
          <Link :href="route('login')" class="ml-2 text-sm font-medium text-[#003274] transition hover:underline">Войдите, чтобы оценить</Link>
        </div>
      </div>

      <!-- BChP banner -->
      <div v-if="tour.participation_type === 'bchp'" class="reveal mt-10 rounded-2xl border border-[#003274]/10 bg-[#003274]/5 px-6 py-5 text-center">
        <p class="text-base font-medium text-gray-800">
          Данный тур участвует в программе «Больше, чем путешествие». Вы можете принять участие в конкурсе, если хотите отправиться в тур бесплатно.
        </p>
        <p class="mt-2 text-sm text-gray-600">
          Об условиях участия можно узнать на странице проекта или в личном кабинете
        </p>
      </div>

      <!-- CTA: Варианты участия -->
      <div v-if="tour.participation_type" class="reveal mt-10 rounded-2xl bg-gray-900 px-6 py-10 text-center">
        <h2 class="text-2xl font-bold text-white sm:text-3xl">Готовы отправиться в тур?</h2>
        <p class="mt-2 text-lg text-gray-300">Выбирайте способ участия:</p>
        <div class="mt-6 flex flex-wrap justify-center gap-4">
          <button v-if="tour.participation_type === 'bchp'" type="button" class="cursor-pointer rounded-xl bg-[#003274] px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-[#004090]" @click="openApplicationModal(null, 'bchp')">
            Забронировать
          </button>
          <button v-if="tour.participation_type === 'paid'" type="button" class="cursor-pointer rounded-xl bg-green-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700" @click="openApplicationModal(null, 'paid')">
            Купить тур
          </button>
          <button v-if="tour.participation_type === 'contest'" type="button" class="cursor-pointer rounded-xl border-2 border-white/30 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-white/20" @click="openApplicationModal(null, 'contest')">
            Участвовать в конкурсе
          </button>
        </div>
      </div>

      <!-- Application modal -->
      <RModal v-model="showModal" :title="applicationSent ? '' : 'Оставить заявку'" :subtitle="applicationSent ? '' : 'Заполните форму, и мы свяжемся с вами'" size="md">
        <div v-if="applicationSent" class="flex flex-col items-center py-6 text-center">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
          </div>
          <h3 class="mt-4 text-xl font-bold text-gray-900">Заявка отправлена!</h3>
          <p class="mt-2 max-w-sm text-gray-600">Спасибо за интерес к туру. Мы свяжемся с вами в ближайшее время.</p>
        </div>
        <form v-else @submit.prevent="submitApplication" class="space-y-4">
          <RInput v-model="appForm.name" label="Имя" placeholder="Ваше имя" required />
          <RInput v-model="appForm.email" type="email" label="Email" placeholder="your@email.com" required />
          <RInput v-model="appForm.phone" type="tel" label="Телефон" placeholder="+7 (___) ___-__-__" />
          <div v-if="appForm.participation_variant" class="rounded-lg bg-blue-50 p-3">
            <p class="text-sm font-medium text-blue-800">
              <template v-if="appForm.participation_variant === 'bchp'">Вариант: Сертификат «Больше, чем путешествие»</template>
              <template v-else-if="appForm.participation_variant === 'contest'">Вариант: Участие в конкурсе</template>
              <template v-else>Вариант: За свой счёт</template>
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Сообщение</label>
            <textarea v-model="appForm.message" rows="3" placeholder="Ваше сообщение..." class="mt-1.5 w-full rounded-xl border-gray-300 px-4 py-3 transition focus:border-[#003274] focus:ring-[#003274]/20" />
          </div>
        </form>

        <template #footer>
          <template v-if="applicationSent">
            <RButton variant="primary" @click="closeApplicationModal">Закрыть</RButton>
          </template>
          <template v-else>
            <RButton variant="outline" @click="showModal = false">Отмена</RButton>
            <RButton variant="primary" @click="submitApplication">Отправить</RButton>
          </template>
        </template>
      </RModal>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
      <div
        v-if="lightboxIndex !== null && allMedia[lightboxIndex]"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-label="Просмотр фото"
        @click.self="lightboxIndex = null"
      >
        <button type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2.5 text-white transition hover:bg-white/20 focus:outline-none" @click="lightboxIndex = null">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
        <button v-if="lightboxIndex > 0" type="button" class="absolute left-4 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20 focus:outline-none" @click="lightboxIndex--">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </button>
        <button v-if="lightboxIndex < allMedia.length - 1" type="button" class="absolute right-4 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20 focus:outline-none" @click="lightboxIndex++">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        </button>
        <img :src="allMedia[lightboxIndex]" :alt="`Фото ${lightboxIndex + 1}`" class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl" />
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-4 py-1.5 text-sm font-medium text-white/80">
          {{ lightboxIndex + 1 }} / {{ allMedia.length }}
        </div>
      </div>
    </Teleport>
  </MainLayout>
</template>

<style scoped>
.html-content :deep(p) { margin-bottom: 1rem; }
.html-content :deep(p:last-child) { margin-bottom: 0; }
.html-content :deep(a) { color: #003274; text-decoration: underline; text-underline-offset: 2px; }
.html-content :deep(ul), .html-content :deep(ol) { margin: 0.75rem 0 1rem; padding-left: 1.25rem; }
.html-content :deep(ul) { list-style-type: disc; }
.count-pop-enter-active, .count-pop-leave-active { transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.34, 1.2, 0.64, 1); }
.count-pop-enter-from { opacity: 0; transform: translateY(-5px) scale(0.85); }
.count-pop-leave-to { opacity: 0; transform: translateY(5px) scale(0.85); }
</style>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const lightboxIndex = ref(null)
const activeTab = ref('description')
const showReviewForm = ref(false)

const allTabs = [
  { id: 'description', label: 'Описание' },
  { id: 'program', label: 'Программа тура' },
  { id: 'departure', label: 'Город отправления' },
  { id: 'accommodation', label: 'Проживание' },
  { id: 'dates', label: 'Даты тура' },
  { id: 'memo', label: 'Памятка участника' },
  { id: 'reviews', label: 'Отзывы' },
]

function scrollToSection(id) {
  activeTab.value = id
  const el = document.getElementById(`section-${id}`)
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function reviewsWord(count) {
  const n = Math.abs(count) % 100
  const n1 = n % 10
  if (n > 10 && n < 20) return 'отзывов'
  if (n1 > 1 && n1 < 5) return 'отзыва'
  if (n1 === 1) return 'отзыв'
  return 'отзывов'
}

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
  reviews: { type: Array, default: () => [] },
  userReviewExists: { type: Boolean, default: false },
})

const page = usePage()
const isAuthed = computed(() => !!page.props.auth?.user)

const visibleTabs = computed(() => {
  return allTabs.filter(tab => {
    if (tab.id === 'description') return true
    if (tab.id === 'program') return props.tour.program_days?.length || props.tour.program_pdf
    if (tab.id === 'departure') return props.tour.start_city || props.tour.departure_info
    if (tab.id === 'accommodation') return props.tour.accommodations?.length || props.tour.accommodation_info
    if (tab.id === 'dates') return props.tour.departures?.length
    if (tab.id === 'memo') return props.tour.memo_text || props.tour.memo_pdf || props.tour.closed_city
    if (tab.id === 'reviews') return true
    return false
  })
})

const avgRating = computed(() => {
  if (!props.reviews?.length) return '0'
  const sum = props.reviews.reduce((acc, r) => acc + (r.rating || 0), 0)
  return (sum / props.reviews.length).toFixed(1)
})

const allMedia = computed(() => {
  const imgs = []
  if (props.tour.image) imgs.push(props.tour.image)
  if (Array.isArray(props.tour.gallery)) {
    for (const url of props.tour.gallery) {
      if (url && !imgs.includes(url)) imgs.push(url)
    }
  }
  return imgs
})

function parseEmbed(url) {
  if (!url || typeof url !== 'string') return null
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{6,})/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  const rt = url.match(/rutube\.ru\/(?:video\/|play\/embed\/)([a-zA-Z0-9_-]+)/)
  if (rt) return `https://rutube.ru/play/embed/${rt[1]}`
  return null
}

const videoEmbeds = computed(() => {
  const vids = props.tour.videos
  if (!Array.isArray(vids)) return []
  return vids.map(parseEmbed).filter(Boolean)
})

function openLightbox(index) { lightboxIndex.value = index }

function onLightboxKeydown(e) {
  if (lightboxIndex.value === null) return
  if (e.key === 'Escape') lightboxIndex.value = null
  if (e.key === 'ArrowLeft' && lightboxIndex.value > 0) lightboxIndex.value--
  if (e.key === 'ArrowRight' && lightboxIndex.value < allMedia.value.length - 1) lightboxIndex.value++
}

onMounted(() => window.addEventListener('keydown', onLightboxKeydown))
onUnmounted(() => window.removeEventListener('keydown', onLightboxKeydown))

const currentUserReaction = computed(() => props.userReaction ?? props.tour?.user_reaction ?? null)

const reactionsDisplay = computed(() => {
  const raw = props.tour?.reactions_count
  const out = {}
  for (const key of REACTION_KEYS) { out[key] = raw?.[key] ?? 0 }
  return out
})

const reactionItems = computed(() =>
  REACTION_KEYS.map((key) => ({ key, emoji: REACTION_META[key].emoji, label: REACTION_META[key].label })),
)

const reactionSending = ref(false)
const favoriteSending = ref(false)
const isFavorited = ref(!!props.tour?.is_favorited)

watch(() => props.tour?.is_favorited, (v) => { if (typeof v === 'boolean') isFavorited.value = v })

function sendReaction(emoji) {
  if (reactionSending.value) return
  reactionSending.value = true
  router.post(route('tours.react', props.tour.id), { emoji }, {
    preserveScroll: true,
    only: ['tour', 'userReaction'],
    onFinish: () => { reactionSending.value = false },
  })
}

function toggleFavorite() {
  if (!isAuthed.value) { router.visit(route('login')); return }
  if (favoriteSending.value) return
  favoriteSending.value = true
  router.post(route('favorites.toggle', { type: 'tour', id: props.tour.id }), {}, {
    preserveScroll: true,
    only: ['tour', 'userReaction'],
    onFinish: () => { favoriteSending.value = false },
  })
}

const showModal = ref(false)
const applicationSent = ref(false)
const appForm = reactive({
  name: '',
  email: '',
  phone: '',
  message: '',
  tour_departure_id: null,
  participation_variant: null,
})

function openApplicationModal(departureId = null, variant = null) {
  appForm.tour_departure_id = departureId
  appForm.participation_variant = variant
  applicationSent.value = false
  showModal.value = true
}

function closeApplicationModal() {
  showModal.value = false
  applicationSent.value = false
  appForm.name = appForm.email = appForm.phone = appForm.message = ''
  appForm.participation_variant = null
}

function submitApplication() {
  const payload = {
    type: 'tour',
    tour_id: props.tour.id,
    tour_departure_id: appForm.tour_departure_id,
    name: appForm.name,
    email: appForm.email,
    phone: appForm.phone,
    message: appForm.message,
  }
  if (appForm.participation_variant) {
    payload.message = `[${appForm.participation_variant === 'bchp' ? 'Сертификат БЧП' : appForm.participation_variant === 'contest' ? 'Конкурс' : 'За свой счёт'}] ${appForm.message || ''}`
  }
  router.post(route('applications.store'), payload, {
    preserveScroll: true,
    onSuccess: () => {
      applicationSent.value = true
      appForm.name = appForm.email = appForm.phone = appForm.message = ''
    },
  })
}

const reviewForm = reactive({ rating: 0, text: '' })
const reviewSent = ref(false)

function submitReview() {
  if (reviewForm.rating === 0) return
  router.post(route('tours.reviews.store', props.tour.id), {
    rating: reviewForm.rating,
    text: reviewForm.text,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      reviewSent.value = true
      reviewForm.rating = 0
      reviewForm.text = ''
    },
  })
}

function budgetLabel(key) {
  return { economy: 'Эконом', standard: 'Стандарт', comfort: 'Комфорт', luxury: 'Люкс' }[key] || key
}

function budgetVariant(key) {
  return { economy: 'neutral', standard: 'info', comfort: 'primary', luxury: 'warning' }[key] || 'neutral'
}

function projectLabel(key) {
  return { start_atomgrad: 'Старт в Атомград', atoms_vkusa: 'Атомы вкуса', llr: 'Лучшие люди Росатома' }[key] || key
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function formatDateShort(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
