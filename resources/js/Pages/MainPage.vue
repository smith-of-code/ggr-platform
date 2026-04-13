<template>
  <MainLayout>
    <div>
      <!-- Hero -->
      <section class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-24 text-white sm:px-6 sm:py-32 lg:px-8 lg:py-40">
        <img
          src="https://images.unsplash.com/photo-1513326738677-b964603b136d?w=1600&h=600&fit=crop"
          alt=""
          class="absolute inset-0 h-full w-full object-cover opacity-15 mix-blend-luminosity"
        />
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.1),transparent_70%)]" />
        <div class="relative mx-auto max-w-7xl text-center">
          <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
            Гостеприимные города<br class="hidden sm:block" /> Росатома
          </h1>
          <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/85">
            Цифровая экосистема для развития туристического, образовательного и предпринимательского потенциала атомных городов
          </p>
          <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link
              :href="route('tours.index')"
              class="group rounded-xl bg-white px-8 py-3.5 font-semibold text-[#003274] shadow-lg shadow-black/10 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
            >
              Выбрать тур
              <span class="ml-1 inline-block transition group-hover:translate-x-1">&rarr;</span>
            </Link>
            <Link
              :href="route('cities.index')"
              class="rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
            >
              Города
            </Link>
            <a
              v-if="page.props.auth?.user && isLmsFullPageUrl(vshgrHref)"
              :href="vshgrHref"
              class="rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
            >
              ВШГР
            </a>
            <Link
              v-else-if="page.props.auth?.user"
              :href="vshgrHref"
              class="rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
            >
              ВШГР
            </Link>
          </div>
        </div>
      </section>

      <!-- Program stages -->
      <section class="bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Этапы программы</h2>
          </div>

          <div class="space-y-8">
            <article
              v-for="(stage, i) in programStages"
              :key="stage.step"
              class="reveal overflow-hidden rounded-2xl bg-white shadow-lg shadow-slate-300/30 lg:grid lg:grid-cols-12"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="h-64 lg:col-span-7 lg:h-full">
                <img
                  :src="stage.image"
                  :alt="stage.title"
                  class="h-full w-full object-cover"
                  loading="lazy"
                />
              </div>
              <div class="flex flex-col justify-between gap-6 p-6 sm:p-8 lg:col-span-5">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#003274]/70">{{ stage.step }}</p>
                  <h3 class="mt-3 text-3xl font-bold leading-tight text-[#1f2b63]">{{ stage.title }}</h3>
                  <p class="mt-4 text-base leading-relaxed text-gray-600">{{ stage.description }}</p>
                </div>
                <div>
                  <Link
                    v-if="stage.href"
                    :href="stage.href"
                    class="inline-flex items-center rounded-md bg-[#27377b] px-8 py-3 text-sm font-semibold text-white transition hover:bg-[#1f2b63]"
                  >
                    {{ stage.buttonLabel }}
                  </Link>
                  <button
                    v-else
                    type="button"
                    disabled
                    class="inline-flex cursor-not-allowed items-center rounded-md bg-gray-200 px-8 py-3 text-sm font-semibold text-gray-500"
                  >
                    {{ stage.buttonLabel }}
                  </button>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>

      <!-- Program cities by year -->
      <section class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Города программы</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              Города-участники программы «Гостеприимные города Росатома» по годам
            </p>
          </div>

          <div class="mb-8 flex justify-center gap-2">
            <button
              v-for="year in programYears"
              :key="year"
              type="button"
              class="rounded-full px-6 py-2.5 text-sm font-semibold transition duration-200"
              :class="activeCitiesYear === year
                ? 'bg-[#003274] text-white shadow-lg'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
              @click="activeCitiesYear = year"
            >
              {{ year }}
            </button>
          </div>

          <div class="grid gap-5 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div
              v-for="(city, i) in programCitiesByYear[activeCitiesYear]"
              :key="city.name"
              class="reveal group overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="relative aspect-[13/15] overflow-hidden">
                <img
                  :src="city.image"
                  :alt="city.name"
                  class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  loading="lazy"
                />
              </div>
              <div class="p-4">
                <p class="text-sm font-bold text-gray-900">{{ city.name }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ city.region }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Program results by year -->
      <section class="bg-[#2a376c] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Результаты программы</h2>
            <p class="mx-auto mt-3 max-w-2xl text-white/70">
              Ключевые достижения по годам реализации программы
            </p>
          </div>

          <div class="mb-10 flex justify-center gap-2">
            <button
              v-for="year in programYears"
              :key="year"
              type="button"
              class="rounded-full px-6 py-2.5 text-sm font-semibold transition duration-200"
              :class="activeResultsYear === year
                ? 'bg-white text-[#2a376c] shadow-lg'
                : 'bg-white/10 text-white/80 hover:bg-white/20'"
              @click="activeResultsYear = year"
            >
              {{ year }}
            </button>
          </div>

          <div class="grid items-start gap-10 lg:grid-cols-2">
            <div class="flex items-center justify-center">
              <img
                src="https://optim.tildacdn.com/tild3735-3663-4333-b331-333938383739/-/format/webp/Mask_group.png.webp"
                alt="Результаты программы"
                class="w-full max-w-md rounded-2xl"
                loading="lazy"
              />
            </div>
            <ul class="space-y-6">
              <li
                v-for="(result, i) in programResultsByYear[activeResultsYear]"
                :key="i"
                class="reveal"
                :class="'reveal-delay-' + ((i % 5) + 1)"
              >
                <p class="text-xl font-bold text-white sm:text-2xl">{{ result.value }}</p>
                <p class="mt-1 text-base text-white/80">{{ result.description }}</p>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- City benefits -->
      <section class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Что получает город</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              Преимущества участия в программе для городов-присутствия Росатома
            </p>
          </div>
          <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="(benefit, i) in cityBenefits"
              :key="i"
              class="reveal group relative aspect-[4/3] overflow-hidden rounded-2xl shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <img
                :src="benefit.image"
                :alt="benefit.title"
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/10" />
              <div class="absolute inset-x-0 bottom-0 flex items-end p-5 sm:p-6">
                <p class="text-sm font-light leading-snug text-white sm:text-base">{{ benefit.title }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Additional initiatives -->
      <section class="overflow-hidden bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Дополнительные инициативы</h2>
          </div>
          <div class="grid gap-5 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div
              v-for="(initiative, i) in additionalInitiatives"
              :key="i"
              class="reveal group overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="relative aspect-[13/15] overflow-hidden">
                <img
                  :src="initiative.image"
                  :alt="initiative.title"
                  class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  loading="lazy"
                />
              </div>
              <div class="p-4">
                <p class="text-sm font-normal leading-snug text-gray-800">{{ initiative.title }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Videos slideshow -->
      <section v-if="videoItems.length" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Видеоролики</h2>
            <p class="mt-2 text-gray-500">Смотрите, как живут и развиваются атомные города</p>
          </div>
          <div class="relative">
            <div
              ref="videoSlider"
              class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4"
              style="scrollbar-width: none"
            >
              <div
                v-for="(video, i) in videoItems"
                :key="i"
                class="w-full flex-shrink-0 snap-center sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]"
              >
                <button
                  type="button"
                  class="group w-full overflow-hidden rounded-2xl bg-white text-left shadow-md transition hover:shadow-xl"
                  @click="openVideoModal(video)"
                >
                  <div class="relative aspect-video bg-gray-200">
                    <img
                      v-if="video.thumbnail"
                      :src="video.thumbnail"
                      :alt="video.title"
                      class="h-full w-full object-cover"
                      loading="lazy"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-200">
                      <svg class="h-12 w-12 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                      </svg>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 transition group-hover:bg-black/30">
                      <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 shadow-lg transition group-hover:scale-110">
                        <svg class="ml-1 h-6 w-6 text-[#003274]" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M8 5v14l11-7z" />
                        </svg>
                      </div>
                    </div>
                  </div>
                  <div class="p-4">
                    <h3 class="font-semibold text-gray-900">{{ video.title }}</h3>
                  </div>
                </button>
              </div>
            </div>
            <button
              v-if="videoItems.length > 3"
              @click="scrollVideos(-1)"
              class="absolute -left-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
            >
              <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              v-if="videoItems.length > 3"
              @click="scrollVideos(1)"
              class="absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block"
            >
              <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </section>

      <!-- Video modal -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition duration-200"
          enter-from-class="opacity-0"
          leave-active-class="transition duration-200"
          leave-to-class="opacity-0"
        >
          <div
            v-if="activeVideo"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @click.self="closeVideoModal"
          >
            <Transition
              enter-active-class="transition duration-200"
              enter-from-class="scale-95 opacity-0"
              leave-active-class="transition duration-200"
              leave-to-class="scale-95 opacity-0"
            >
              <div v-if="activeVideo" class="relative w-full max-w-4xl">
                <button
                  type="button"
                  @click="closeVideoModal"
                  class="absolute -right-2 -top-10 rounded-full p-1.5 text-white/80 transition hover:text-white"
                >
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
                <div class="overflow-hidden rounded-2xl bg-black">
                  <div class="aspect-video">
                    <video
                      v-if="activeVideo.videoFile"
                      :src="activeVideo.videoFile"
                      controls
                      autoplay
                      class="h-full w-full"
                      :poster="activeVideo.thumbnail || undefined"
                    />
                    <iframe
                      v-else-if="activeVideo.embedUrl"
                      :src="activeVideo.embedUrl"
                      class="h-full w-full"
                      frameborder="0"
                      allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                      allowfullscreen
                    />
                  </div>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>
      </Teleport>

      <!-- News -->
      <section v-if="latestPosts?.length" class="bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Новости</h2>
              <p class="mt-2 text-gray-500">Последние новости программы</p>
            </div>
            <Link
              :href="route('blog.index')"
              class="group flex items-center gap-1.5 text-sm font-semibold text-[#003274] transition hover:text-[#025ea1]"
            >
              Все новости
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </Link>
          </div>
          <div class="reveal mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="post in latestPosts"
              :key="post.id"
              :href="route('blog.show', post.slug)"
              class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
            >
              <div class="relative aspect-[16/9] overflow-hidden bg-gray-100">
                <img
                  v-if="post.image"
                  :src="post.image"
                  :alt="post.title"
                  class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  loading="lazy"
                />
                <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
                  <svg class="h-12 w-12 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5" />
                  </svg>
                </div>
              </div>
              <div class="p-5">
                <time v-if="post.published_at" class="text-xs font-medium text-gray-400">{{ formatEventDate(post.published_at) }}</time>
                <h3 class="mt-1.5 text-base font-bold leading-snug text-gray-900 transition group-hover:text-[#003274]">{{ post.title }}</h3>
                <p v-if="post.excerpt" class="mt-2 line-clamp-2 text-sm text-gray-500">{{ post.excerpt }}</p>
                <span class="mt-3 inline-block text-sm font-medium text-[#003274] opacity-0 transition group-hover:opacity-100">Читать &rarr;</span>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Moving -->
      <section class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-16 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-7xl">
          <div class="relative overflow-hidden rounded-2xl bg-white/10 px-8 py-12 text-center text-white shadow-xl backdrop-blur-sm sm:px-16 sm:py-16">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.12),transparent_60%)]" />
            <div class="relative">
              <h2 class="text-2xl font-bold sm:text-3xl">Переезжаем</h2>
              <p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">
                Узнайте о возможностях переезда в атомные города — программа поддержки, условия и перспективы
              </p>
              <div class="mt-8">
                <Link
                  :href="route('moving')"
                  class="inline-flex items-center rounded-xl bg-white px-8 py-3.5 font-semibold text-[#003274] shadow-lg transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
                >
                  Подробнее
                  <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Stats -->
      <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <RCard v-for="(stat, i) in statCards" :key="i" elevation="raised" hoverable class="reveal text-center" :class="'reveal-delay-' + (i + 1)">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50">
              <span v-html="stat.icon" />
            </div>
            <p class="mt-4 text-3xl font-bold text-[#003274]">{{ stat.value }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ stat.label }}</p>
          </RCard>
        </div>
      </section>

      <!-- Featured tours -->
      <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Популярные туры</h2>
              <p class="mt-2 text-gray-500">Откройте для себя уникальные маршруты</p>
            </div>
            <Link
              :href="route('tours.index')"
              class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block"
            >
              Все туры &rarr;
            </Link>
          </div>
          <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(tour, i) in featuredTours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="reveal"
              :class="'reveal-delay-' + (i + 1)"
            >
            <RCard elevation="raised" hoverable class="group h-full">
              <template #cover>
                <div class="aspect-video overflow-hidden">
                  <img
                    v-if="tour.image"
                    :src="tour.image"
                    :alt="tour.title"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                  />
                  <div v-else class="h-full w-full bg-gray-200" />
                </div>
              </template>
              <div>
                <div class="flex items-center gap-2">
                  <RBadge variant="primary" size="sm">{{ tour.duration }}</RBadge>
                </div>
                <h3 class="mt-3 text-lg font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                <div v-if="tour.cities?.length" class="mt-1.5 flex flex-wrap gap-1.5">
                  <RBadge v-for="city in tour.cities" :key="city.id" variant="info" size="md">{{ city.name }}</RBadge>
                </div>
                <p v-if="tour.start_city" class="mt-1.5 text-sm text-gray-500">
                  <span class="font-medium text-gray-600">Логистические точки:</span> {{ tour.start_city }}
                </p>
                <p v-if="!tour.cities?.length && !tour.start_city" class="mt-2 line-clamp-2 text-sm text-gray-500">{{ stripHtml(tour.description) }}</p>
                <div class="mt-4 flex items-center justify-between">
                  <p class="text-lg font-bold text-[#003274]">
                    <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                    <template v-else><span class="text-green-600">Бесплатно</span></template>
                  </p>
                  <span class="text-sm font-medium text-[#003274] opacity-0 transition group-hover:opacity-100">Подробнее &rarr;</span>
                </div>
              </div>
            </RCard>
            </Link>
          </div>
          <div v-if="featuredTours.length === 0" class="py-12 text-center text-gray-500">
            Туры скоро появятся
          </div>
          <div class="mt-10 text-center sm:hidden">
            <Link
              :href="route('tours.index')"
              class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3 font-medium text-white transition hover:bg-[#025ea1]"
            >
              Все туры &rarr;
            </Link>
          </div>
        </div>
      </section>

      <!-- Cities -->
      <section v-if="cities?.length" class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Атомные города</h2>
              <p class="mt-2 text-gray-500">Современные города с уникальной историей</p>
            </div>
            <Link
              :href="route('cities.index')"
              class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block"
            >
              Все города &rarr;
            </Link>
          </div>
          <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(city, i) in cities"
              :key="city.id"
              :href="route('cities.show', city.slug)"
              class="reveal group relative overflow-hidden rounded-xl shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
              :class="'reveal-delay-' + (i + 1)"
            >
              <div class="aspect-[4/3] overflow-hidden">
                <img
                  v-if="city.image"
                  :src="city.image"
                  :alt="city.name"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                />
                <div v-else class="h-full w-full bg-gray-200" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
              <div class="absolute bottom-0 left-0 right-0 p-5">
                <h3 class="text-xl font-bold text-white">{{ city.name }}</h3>
                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-white/75">{{ city.description }}</p>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Interactive Yandex Map -->
      <section
        v-if="allCities?.length"
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_30%_20%,rgba(255,255,255,0.12),transparent_55%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">География проекта</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-white/80 sm:text-base">
              Атомные города на карте России — нажмите на маркер, чтобы узнать о городе и перейти на его страницу
            </p>
          </div>
          <div class="reveal mx-auto mt-10 overflow-hidden rounded-2xl shadow-2xl shadow-black/30" style="height: 520px">
            <YandexCityMap :cities="allCities" />
          </div>
        </div>
      </section>

      <!-- Атомы вкуса -->
      <section v-if="latestRecipes?.length" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Книга <span class="text-[#003274]">атомных</span> рецептов</h2>
              <p class="mt-2 max-w-xl text-gray-500">
                Блюда из городов атомной отрасли — откройте для себя кулинарные традиции регионов
              </p>
            </div>
            <Link
              :href="route('recipes.index')"
              class="group flex items-center gap-1.5 text-sm font-semibold text-[#003274] transition hover:text-[#025ea1]"
            >
              Все рецепты
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </Link>
          </div>

          <div class="reveal mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="recipe in latestRecipes"
              :key="recipe.id"
              :href="route('recipes.show', recipe.slug)"
              class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
            >
              <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                <img
                  v-if="recipe.image"
                  :src="recipe.image"
                  :alt="recipe.title"
                  class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-[#003274]/10 to-gray-100">
                  <svg class="h-12 w-12 text-[#003274]/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                  </svg>
                </div>
                <div v-if="recipe.cooking_time" class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm backdrop-blur-sm">
                  {{ recipe.cooking_time }}
                </div>
              </div>
              <div class="p-5">
                <h3 class="text-base font-bold text-gray-900 transition group-hover:text-[#003274]">{{ recipe.title }}</h3>
                <p v-if="recipe.city" class="mt-1 text-xs font-medium text-[#025ea1]">{{ recipe.city.name }}</p>
                <p v-if="recipe.description" class="mt-2 line-clamp-2 text-sm text-gray-500">{{ recipe.description }}</p>
                <div v-if="recipe.difficulty || recipe.servings" class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                  <span v-if="recipe.difficulty">{{ recipe.difficulty }}</span>
                  <span v-if="recipe.servings">{{ recipe.servings }} порц.</span>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>

      <!-- Timeline -->
      <section class="bg-slate-50 px-4 py-20 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-5xl">
          <div class="reveal mb-14 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Хронология событий</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-500">
              Ключевые новости, события и вехи развития программы
            </p>
          </div>
          <div
            v-if="sortedTimelineEvents.length"
            class="relative"
          >
            <div
              class="absolute bottom-0 left-4 top-0 w-px bg-gradient-to-b from-[#003274]/25 via-[#003274]/15 to-transparent md:left-1/2 md:-translate-x-1/2"
              aria-hidden="true"
            />
            <ul class="space-y-0">
              <li
                v-for="(event, i) in sortedTimelineEvents"
                :key="event.id ?? i"
                class="relative pb-14 md:grid md:grid-cols-2 md:gap-0"
              >
                <div
                  class="absolute left-4 top-2 z-10 flex h-4 w-4 -translate-x-1/2 items-center justify-center rounded-full border-4 border-slate-50 bg-[#003274] shadow md:left-1/2"
                  aria-hidden="true"
                />
                <template v-if="i % 2 === 0">
                  <div
                    class="reveal pl-12 md:pr-10 md:text-right"
                    :class="'reveal-delay-' + ((i % 5) + 1)"
                  >
                    <article
                      class="inline-block max-w-md rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-md shadow-gray-200/60 md:text-right"
                    >
                      <time class="text-sm font-semibold text-[#003274]">{{ formatEventDate(event.event_date) }}</time>
                      <h3 class="mt-2 text-lg font-bold text-gray-900">{{ event.title }}</h3>
                      <p v-if="event.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ event.description }}</p>
                      <div class="mt-3 flex flex-wrap items-center gap-2 md:justify-end">
                        <span
                          class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                          :class="timelineTypeClass(event.type)"
                        >
                          {{ timelineTypeLabel(event.type) }}
                        </span>
                        <a
                          v-if="event.link"
                          :href="event.link"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center rounded-lg border border-[#003274]/20 bg-white px-3 py-1.5 text-xs font-semibold text-[#003274] transition hover:border-[#003274]/40 hover:bg-[#003274]/5"
                        >
                          Подробнее
                        </a>
                      </div>
                    </article>
                  </div>
                  <div class="hidden md:block" />
                </template>
                <template v-else>
                  <div class="hidden md:block" />
                  <div
                    class="reveal pl-12 md:pl-10"
                    :class="'reveal-delay-' + ((i % 5) + 1)"
                  >
                    <article class="max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-md shadow-gray-200/60">
                      <time class="text-sm font-semibold text-[#003274]">{{ formatEventDate(event.event_date) }}</time>
                      <h3 class="mt-2 text-lg font-bold text-gray-900">{{ event.title }}</h3>
                      <p v-if="event.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ event.description }}</p>
                      <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                          class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                          :class="timelineTypeClass(event.type)"
                        >
                          {{ timelineTypeLabel(event.type) }}
                        </span>
                        <a
                          v-if="event.link"
                          :href="event.link"
                          target="_blank"
                          rel="noopener noreferrer"
                          class="inline-flex items-center rounded-lg border border-[#003274]/20 bg-white px-3 py-1.5 text-xs font-semibold text-[#003274] transition hover:border-[#003274]/40 hover:bg-[#003274]/5"
                        >
                          Подробнее
                        </a>
                      </div>
                    </article>
                  </div>
                </template>
              </li>
            </ul>
          </div>
          <p
            v-else
            class="reveal rounded-2xl border border-dashed border-gray-200 bg-white py-12 text-center text-gray-500"
          >
            События появятся в ближайшее время
          </p>
        </div>
      </section>

      <!-- CTA -->
      <section class="reveal mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003274] to-[#025ea1] px-8 py-16 text-center text-white shadow-xl sm:px-16">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.1),transparent_60%)]" />
          <div class="relative">
            <h2 class="text-2xl font-bold sm:text-3xl">Хотите узнать подробнее о программе?</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">
              Оставьте заявку, и мы свяжемся с вами в ближайшее время
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
              <Link
                :href="route('tours.index')"
                class="inline-flex items-center rounded-xl bg-white px-8 py-3.5 font-semibold text-[#003274] shadow-lg transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
              >
                Выбрать тур
              </Link>
              <a
                v-if="page.props.auth?.user && isLmsFullPageUrl(vshgrHref)"
                :href="vshgrHref"
                class="inline-flex items-center rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
              >
                Перейти в ВШГР
              </a>
              <Link
                v-else-if="page.props.auth?.user"
                :href="vshgrHref"
                class="inline-flex items-center rounded-xl border-2 border-white/40 px-8 py-3.5 font-semibold text-white transition duration-300 hover:border-white/70 hover:bg-white/10"
              >
                Перейти в ВШГР
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact -->
      <section
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#024a85] to-[#025ea1] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_80%_0%,rgba(255,255,255,0.12),transparent_50%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">Хочу узнать подробнее</h2>
            <p class="mx-auto mt-3 max-w-2xl text-base text-white/85">
              Заполните форму — мы ответим на вопросы о турах, городах и возможностях программы
            </p>
          </div>
          <div class="reveal grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="flex flex-col justify-center space-y-6 text-white/90">
              <p class="text-lg leading-relaxed">
                Команда проекта поможет подобрать маршрут, расскажет о датах и условиях участия.
              </p>
              <ul class="space-y-3 text-sm text-white/80">
                <li class="flex items-start gap-2">
                  <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300" />
                  Ответ в рабочие дни в течение 1–2 дней
                </li>
                <li class="flex items-start gap-2">
                  <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300" />
                  Консультация без обязательства записи на тур
                </li>
              </ul>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 p-6 shadow-2xl shadow-black/20 backdrop-blur-md sm:p-8">
              <div
                v-if="flashSuccess"
                role="status"
                class="mb-6 rounded-xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-3 text-sm text-emerald-50"
              >
                {{ flashSuccess }}
              </div>
              <form class="space-y-4" @submit.prevent="submitContact">
                <div>
                  <label for="contact-name" class="mb-1.5 block text-xs font-medium text-white/80">Имя</label>
                  <input
                    id="contact-name"
                    v-model="contactForm.name"
                    type="text"
                    required
                    autocomplete="name"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="Как к вам обращаться"
                  />
                  <p v-if="contactForm.errors.name" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.name }}</p>
                </div>
                <div>
                  <label for="contact-email" class="mb-1.5 block text-xs font-medium text-white/80">E-mail</label>
                  <input
                    id="contact-email"
                    v-model="contactForm.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="name@example.com"
                  />
                  <p v-if="contactForm.errors.email" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.email }}</p>
                </div>
                <div>
                  <label for="contact-phone" class="mb-1.5 block text-xs font-medium text-white/80">Телефон</label>
                  <input
                    id="contact-phone"
                    v-model="contactForm.phone"
                    type="tel"
                    autocomplete="tel"
                    class="w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="+7 …"
                  />
                  <p v-if="contactForm.errors.phone" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.phone }}</p>
                </div>
                <div>
                  <label for="contact-message" class="mb-1.5 block text-xs font-medium text-white/80">Сообщение</label>
                  <textarea
                    id="contact-message"
                    v-model="contactForm.message"
                    required
                    rows="4"
                    class="w-full resize-y rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 text-gray-900 shadow-inner outline-none ring-[#003274]/30 transition placeholder:text-gray-400 focus:border-white focus:ring-2"
                    placeholder="Ваш вопрос или пожелание"
                  />
                  <p v-if="contactForm.errors.message" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.message }}</p>
                </div>
                <div>
                  <label class="flex items-start gap-3 cursor-pointer">
                    <input
                      v-model="contactForm.consent"
                      type="checkbox"
                      class="mt-0.5 h-4 w-4 rounded border-white/30 bg-white/20 text-white focus:ring-white/50"
                    />
                    <span class="text-sm text-white/80">
                      Отправляя сообщение, вы даете
                      <a :href="$page.props.consentDocumentUrl" target="_blank" class="text-white underline hover:text-white/90">согласие на обработку персональных данных</a>
                    </span>
                  </label>
                  <p v-if="contactForm.errors.consent" class="mt-1 text-xs text-amber-200">{{ contactForm.errors.consent }}</p>
                </div>
                <button
                  type="submit"
                  :disabled="contactForm.processing || !contactForm.consent"
                  class="w-full rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-[#003274] shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {{ contactForm.processing ? 'Отправка…' : 'Отправить' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>

      <!-- Contacts -->
      <section class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Контакты</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              Свяжитесь с нами любым удобным способом
            </p>
          </div>
          <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
              v-for="(contact, i) in contactItems"
              :key="i"
              class="reveal rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
              :class="'reveal-delay-' + (i + 1)"
            >
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#003274]/10">
                <span v-html="contact.icon" />
              </div>
              <h3 class="mt-4 text-sm font-semibold text-gray-500">{{ contact.label }}</h3>
              <a
                v-if="contact.href"
                :href="contact.href"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-1 block text-base font-bold text-[#003274] transition hover:text-[#025ea1]"
              >
                {{ contact.value }}
              </a>
              <p v-else class="mt-1 text-base font-bold text-gray-900">{{ contact.value }}</p>
            </div>
          </div>

          <div class="reveal mt-10 flex items-center justify-center gap-4">
            <a
              v-for="(social, i) in socialLinks"
              :key="i"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="social.label"
              class="flex h-11 w-11 items-center justify-center rounded-full bg-[#003274]/10 text-[#003274] transition hover:bg-[#003274] hover:text-white"
            >
              <span v-html="social.icon" />
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { isLmsFullPageUrl } from '@/composables/useLmsFullPageNav'
import MainLayout from '@/Layouts/MainLayout.vue'
import YandexCityMap from '@/Components/YandexCityMap.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const props = defineProps({
  featuredTours: Array,
  cities: Array,
  allCities: { type: Array, default: () => [] },
  latestRecipes: { type: Array, default: () => [] },
  latestPosts: { type: Array, default: () => [] },
  stats: Object,
  timelineEvents: {
    type: Array,
    default: () => [],
  },
  userFavorites: {
    type: Object,
    default: null,
  },
})

const page = usePage()

const currentYear = new Date().getFullYear()
const programYears = [currentYear, currentYear - 1, currentYear - 2]
const activeCitiesYear = ref(currentYear)
const activeResultsYear = ref(currentYear)

const programCitiesData = [
  { name: 'Саров', region: 'Нижегородская область', image: 'https://optim.tildacdn.com/tild3737-6434-4934-a233-666136616434/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Глазов', region: 'Удмуртская Республика', image: 'https://optim.tildacdn.com/tild3434-6435-4664-b966-383133643365/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Певек', region: 'Чукотский автономный округ', image: 'https://optim.tildacdn.com/tild6466-6638-4634-a336-613335646161/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Билибино', region: 'Чукотский автономный округ', image: 'https://optim.tildacdn.com/tild3066-3934-4232-b065-383932616531/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Советск', region: 'Калининградская область', image: 'https://optim.tildacdn.com/tild3266-3161-4831-b938-653732366261/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Неман', region: 'Калининградская область', image: 'https://optim.tildacdn.com/tild6632-3634-4530-a138-346334333565/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Нововоронеж', region: 'Воронежская область', image: 'https://optim.tildacdn.com/tild3563-3666-4863-b838-393533643238/-/cover/520x600/center/center/-/format/webp/photo.png.webp' },
  { name: 'Сосновый Бор', region: 'Ленинградская область', image: 'https://optim.tildacdn.com/tild3330-6438-4566-a263-363731383763/-/cover/520x600/center/center/-/format/webp/_.png.webp' },
]

const programCitiesByYear = Object.fromEntries(
  programYears.map(y => [y, programCitiesData]),
)

const programResultsData = [
  { value: '13 млн руб.', description: 'грантовой поддержки от «Росатома» получили четырнадцать лучших предпринимательских проектов «Гостеприимного акселератора „Росатома"»' },
  { value: '74 «Тура возможностей»', description: 'в Волгодонск, Полярные Зори и Железногорск совместно с программами «Больше, чем путешествие» и «Студтуризм»' },
  { value: '>25 млн руб.', description: 'общая сумма привлечённых инвестиций в проекты' },
  { value: '>100 382 654 млн руб.', description: 'доход субъектов туристической деятельности в атомных городах от Туров возможностей' },
  { value: '6 туроператоров', description: 'создано в атомных городах Железногорск, Саров, Трёхгорный, Волгодонск, Глазов, Сосновый Бор' },
]

const programResultsByYear = Object.fromEntries(
  programYears.map(y => [y, programResultsData]),
)

const vshgrHref = computed(() => {
  if (page.props.auth?.user) {
    return page.props.lmsEntryUrl || route('education.index')
  }
  return route('education.index')
})

const flashSuccess = computed(() => page.props.flash?.success ?? null)

const videoSlider = ref(null)
const activeVideo = ref(null)

const videoItems = [
  {
    title: 'Гостеприимные города Росатома — о программе',
    thumbnail: 'https://optim.tildacdn.com/tild3561-3633-4131-b363-376163356263/-/format/webp/1.jpg.webp',
    embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239000&hd=2',
  },
  {
    title: 'Туры возможностей — как это было',
    thumbnail: 'https://optim.tildacdn.com/tild3863-6639-4539-b162-373464633166/-/format/webp/1.jpg.webp',
    embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239001&hd=2',
  },
  {
    title: 'Атомные города — жизнь и перспективы',
    thumbnail: 'https://optim.tildacdn.com/tild6135-3663-4432-b634-646662353234/-/format/webp/1.jpg.webp',
    embedUrl: 'https://vk.com/video_ext.php?oid=-200000000&id=456239002&hd=2',
  },
]

function scrollVideos(direction) {
  if (!videoSlider.value) return
  const cardWidth = videoSlider.value.firstElementChild?.offsetWidth ?? 400
  videoSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

function openVideoModal(video) {
  if (!video.videoFile && !video.embedUrl) return
  activeVideo.value = video
}

function closeVideoModal() {
  activeVideo.value = null
}

const programStages = [
  {
    step: 'Этап 1',
    title: 'Исследования туристического потенциала атомных городов',
    description: 'Работе с конкретным городом предшествует комплексное исследование: опросы, интервью и анализ потенциала развития.',
    image: 'https://optim.tildacdn.com/tild3561-3633-4131-b363-376163356263/-/format/webp/1.jpg.webp',
    buttonLabel: 'Перейти к исследованиям',
    href: route('research.index'),
  },
  {
    step: 'Этап 2',
    title: 'Развитие гостеприимной инфраструктуры',
    description: 'Формируются решения по улучшению городской среды, точек притяжения и сервисов для туристов в атомных городах.',
    image: 'https://optim.tildacdn.com/tild6135-3663-4432-b634-646662353234/-/format/webp/1.jpg.webp',
    buttonLabel: 'Скоро',
    href: null,
  },
  {
    step: 'Этап 3',
    title: 'Повышение компетенций муниципалитетов',
    description: 'Муниципальные команды усиливают навыки управления, проектирования и продвижения туристических инициатив.',
    image: 'https://optim.tildacdn.com/tild3436-3139-4265-b636-396532643735/-/format/webp/1.jpg.webp',
    buttonLabel: 'Скоро',
    href: null,
  },
  {
    step: 'Этап 4',
    title: 'Туры возможностей в атомные города',
    description: 'Итоговый этап программы: запуск маршрутов и форматов посещения, объединяющих ключевые возможности городов.',
    image: 'https://optim.tildacdn.com/tild3863-6639-4539-b162-373464633166/-/format/webp/1.jpg.webp',
    buttonLabel: 'Перейти к турам возможностей',
    href: route('opportunity-tours.index'),
  },
]

const cityBenefits = [
  {
    title: 'Комплексное исследование сферы досуга, отдыха жителей и\u00a0потенциала туристической привлекательности',
    image: 'https://optim.tildacdn.com/tild6664-3264-4361-b931-346231303464/-/cover/720x540/center/center/-/format/webp/1.png.webp',
  },
  {
    title: 'Дополнительное профессиональное образование муниципальной команды, консультации и\u00a0работа с\u00a0лучшими экспертами отрасли',
    image: 'https://optim.tildacdn.com/tild3863-3563-4136-b466-323864396336/-/cover/720x540/center/center/-/format/webp/3.png.webp',
  },
  {
    title: 'Гостеприимный акселератор с\u00a0грантовой поддержкой для предпринимателей, формирование турпродуктов',
    image: 'https://optim.tildacdn.com/tild3563-6634-4434-a636-326235366563/-/cover/720x540/center/center/-/format/webp/5.png.webp',
  },
  {
    title: 'Подбор инвестиционной программы и\u00a0мер поддержки. Привлечение финансирования из\u00a0региональных и\u00a0федеральных программ',
    image: 'https://optim.tildacdn.com/tild6563-3138-4264-a135-303834396533/-/cover/720x540/center/center/-/format/webp/4.png.webp',
  },
  {
    title: 'Формирование гостеприимной среды: создание сообщества, улучшение качества досуга жителей и\u00a0гостей, развитие инфраструктуры',
    image: 'https://optim.tildacdn.com/tild3236-3264-4364-b236-343862356166/-/cover/720x540/center/center/-/format/webp/6.png.webp',
  },
  {
    title: 'Популяризация турпродуктов города на\u00a0региональном и\u00a0федеральном уровнях: таргетированный турпоток\u00a0— потенциальные сотрудники предприятий и\u00a0жители атомных городов',
    image: 'https://optim.tildacdn.com/tild6563-3764-4766-a536-313239376436/-/cover/720x540/center/center/-/format/webp/_3.png.webp',
  },
]

const additionalInitiatives = [
  {
    title: 'Формирование сети домашних горнолыжных баз',
    image: 'https://optim.tildacdn.com/tild3431-3935-4066-b135-653938383231/-/cover/520x600/center/center/-/format/webp/___.jpg.webp',
  },
  {
    title: 'Создание сети туристических клубов в\u00a0атомных городах',
    image: 'https://optim.tildacdn.com/tild3832-6433-4266-a161-653737316164/-/cover/520x600/center/center/-/format/webp/__.jpg.webp',
  },
  {
    title: 'Автомобильный туризм',
    image: 'https://optim.tildacdn.com/tild3832-3365-4235-b838-666561363566/-/cover/520x600/center/center/-/format/webp/_.jpg.webp',
  },
  {
    title: 'Гастротуризм',
    image: 'https://optim.tildacdn.com/tild6232-3331-4662-a434-393066303162/-/cover/520x600/center/center/-/format/webp/photo.jpg.webp',
  },
  {
    title: 'Водный туризм',
    image: 'https://optim.tildacdn.com/tild3233-3564-4030-a332-346562636263/-/cover/520x600/center/center/-/format/webp/_.jpg.webp',
  },
  {
    title: 'Создание сети экологичных «АТОМотелей»',
    image: 'https://optim.tildacdn.com/tild6632-6637-4131-b030-633935333233/-/cover/520x600/center/center/-/format/webp/photo.jpg.webp',
  },
  {
    title: 'Создание сувенирной линейки атомных городов',
    image: 'https://optim.tildacdn.com/tild6166-3735-4531-b561-376461343161/-/cover/520x600/center/center/-/format/webp/__.jpg.webp',
  },
  {
    title: 'Интеграция сферы развития туризма, досуга и\u00a0гостеприимства в\u00a0мастер-планы городов',
    image: 'https://optim.tildacdn.com/tild6264-6338-4836-b264-653765373538/-/cover/520x600/center/center/-/format/webp/__-_.jpg.webp',
  },
]

const sortedTimelineEvents = computed(() => {
  const raw = props.timelineEvents || []
  return [...raw].sort(
    (a, b) => new Date(a.event_date).getTime() - new Date(b.event_date).getTime(),
  )
})

const timelineEventsCount = computed(() => (props.timelineEvents || []).length)

const statCards = computed(() => [
  {
    value: props.stats?.cities ?? 0,
    label: 'Атомных городов',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>',
  },
  {
    value: props.stats?.tours ?? 0,
    label: 'Туров возможностей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>',
  },
  {
    value: timelineEventsCount.value,
    label: 'Событий в хронологии',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>',
  },
  {
    value: '3000+',
    label: 'Гостей',
    icon: '<svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>',
  },
])

const contactForm = useForm({
  name: '',
  email: '',
  phone: '',
  message: '',
  consent: false,
})

function submitContact() {
  contactForm.post(route('contact.submit'), {
    preserveScroll: true,
    onSuccess: () => {
      contactForm.reset()
    },
  })
}

const contactItems = [
  {
    label: 'Телефон',
    value: '+7 (495) 668-28-83',
    href: 'tel:+74956682883',
    icon: '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>',
  },
  {
    label: 'E-mail',
    value: 'info@gostepr.ru',
    href: 'mailto:info@gostepr.ru',
    icon: '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>',
  },
  {
    label: 'Адрес',
    value: 'Москва, ул. Большая Ордынка, 24',
    href: null,
    icon: '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>',
  },
  {
    label: 'Время работы',
    value: 'Пн — Пт, 9:00 — 18:00',
    href: null,
    icon: '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
  },
]

const socialLinks = [
  {
    label: 'VK',
    href: 'https://vk.com/gostepr',
    icon: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.587-1.496c.598-.188 1.368 1.259 2.184 1.814.616.42 1.084.328 1.084.328l2.178-.03s1.14-.071.6-.964c-.045-.073-.32-.659-1.644-1.864-1.386-1.262-1.2-1.058.468-3.243.834-1.093 1.168-1.76 1.064-2.045-.1-.272-.708-.2-.708-.2h-2.476s-.184-.025-.32.056c-.133.08-.219.266-.219.266s-.392 1.044-.916 1.932c-1.104 1.872-1.546 1.972-1.728 1.856-.424-.272-.318-1.092-.318-1.674 0-1.82.276-2.58-.536-2.778-.27-.066-.468-.11-1.156-.116-.882-.01-1.628.002-2.05.209-.282.138-.498.444-.366.462.164.022.534.1.73.366.254.344.244 1.116.244 1.116s.146 2.14-.34 2.404c-.332.182-.788-.19-1.768-1.892-.502-.872-.882-1.836-.882-1.836s-.074-.18-.204-.276c-.158-.118-.378-.156-.378-.156h-2.354s-.354.01-.484.164c-.116.138-.01.422-.01.422s1.838 4.3 3.92 6.468c1.908 1.988 4.072 1.858 4.072 1.858h.98Z"/></svg>',
  },
  {
    label: 'Telegram',
    href: 'https://t.me/gostepr',
    icon: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0h-.056Zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635Z"/></svg>',
  },
]

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatEventDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return String(dateStr)
  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(d)
}

function timelineTypeClass(type) {
  switch (type) {
    case 'news':
      return 'bg-blue-100 text-blue-800'
    case 'milestone':
      return 'bg-amber-100 text-amber-900'
    case 'event':
    default:
      return 'bg-emerald-100 text-emerald-900'
  }
}

function timelineTypeLabel(type) {
  switch (type) {
    case 'news':
      return 'Новость'
    case 'milestone':
      return 'Веха'
    case 'event':
    default:
      return 'Событие'
  }
}
</script>
