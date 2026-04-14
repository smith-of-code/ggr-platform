<template>
  <MainLayout>
    <div class="flex flex-col">
      <HeroSection
        v-if="isBlockVisible('hero')"
        :style="blockStyle('hero')"
        :title="pd.hero_title || 'Гостеприимные города Росатома'"
        :description="pd.hero_description || 'Цифровая экосистема для развития туристического, образовательного и предпринимательского потенциала атомных городов'"
        :bg-image="pd.hero_bg_image || '/images/unsplash/hero-bg.jpg'"
        :bg-color-from="pd.hero_bg_color_from"
        :bg-color-via="pd.hero_bg_color_via"
        :bg-color-to="pd.hero_bg_color_to"
        :text-color="pd.hero_text_color"
        :bg-color-enabled="Boolean(Number(pd.hero_bg_color_enabled))"
        overlay
        centered
        size="lg"
      >
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
      </HeroSection>

      <!-- Program stages -->
      <section v-if="isBlockVisible('program_stages') && programStages.length" :style="blockStyle('program_stages')" class="bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('program_stages', 'Этапы программы') }}</h2>
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
      <section v-if="isBlockVisible('program_cities') && programCitiesRaw.length" :style="blockStyle('program_cities')" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('program_cities', 'Города программы') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              {{ sectionSubtitle('program_cities', 'Города-участники программы «Гостеприимные города Росатома» по годам') }}
            </p>
          </div>

          <div class="mb-8 flex justify-center gap-2">
            <button
              v-for="year in programYears"
              :key="year"
              type="button"
              class="rounded-full px-6 py-2.5 text-sm font-semibold transition duration-200"
              :class="currentActiveCitiesYear === year
                ? 'bg-[#003274] text-white shadow-lg'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
              @click="activeCitiesYear = year"
            >
              {{ year }}
            </button>
          </div>

          <div class="grid gap-5 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div
              v-for="(city, i) in programCitiesByYear[currentActiveCitiesYear]"
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
      <section v-if="isBlockVisible('program_results') && programResultsRaw.length" :style="blockStyle('program_results')" class="bg-[#2a376c] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">{{ sectionTitle('program_results', 'Результаты программы') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-white/70">
              {{ sectionSubtitle('program_results', 'Ключевые достижения по годам реализации программы') }}
            </p>
          </div>

          <div class="mb-10 flex justify-center gap-2">
            <button
              v-for="year in resultYears"
              :key="year"
              type="button"
              class="rounded-full px-6 py-2.5 text-sm font-semibold transition duration-200"
              :class="currentActiveResultsYear === year
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
                :src="pd.program_results_image || 'https://optim.tildacdn.com/tild3735-3663-4333-b331-333938383739/-/format/webp/Mask_group.png.webp'"
                alt="Результаты программы"
                class="w-full max-w-md rounded-2xl"
                loading="lazy"
              />
            </div>
            <ul class="space-y-6">
              <li
                v-for="(result, i) in programResultsByYear[currentActiveResultsYear]"
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
      <section v-if="isBlockVisible('city_benefits') && cityBenefits.length" :style="blockStyle('city_benefits')" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('city_benefits', 'Что получает город') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              {{ sectionSubtitle('city_benefits', 'Преимущества участия в программе для городов-присутствия Росатома') }}
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
      <section v-if="isBlockVisible('additional_initiatives') && additionalInitiatives.length" :style="blockStyle('additional_initiatives')" class="overflow-hidden bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('additional_initiatives', 'Дополнительные инициативы') }}</h2>
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
      <section v-if="isBlockVisible('videos') && videoItems.length" :style="blockStyle('videos')" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-10 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('videos', 'Видеоролики') }}</h2>
            <p class="mt-2 text-gray-500">{{ sectionSubtitle('videos', 'Смотрите, как живут и развиваются атомные города') }}</p>
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

      <!-- Video Presentation block -->
      <section v-if="isBlockVisible('video_presentation')" :style="blockStyle('video_presentation')" class="overflow-hidden bg-gradient-to-b from-[#f8f9fc] to-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('video_presentation', 'О программе') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">{{ sectionSubtitle('video_presentation', '') }}</p>
          </div>

          <!-- Video + Mission -->
          <div class="reveal mb-16 grid items-center gap-10 lg:grid-cols-2">
            <div
              v-if="vp.video_file || vp.video_embed_url || vp.video_thumbnail"
              class="group relative cursor-pointer overflow-hidden rounded-2xl shadow-xl"
              @click="openPresentationVideo"
            >
              <div class="aspect-video bg-gray-200">
                <img
                  v-if="vp.video_thumbnail"
                  :src="vp.video_thumbnail"
                  :alt="vp.video_title || 'Видеопрезентация'"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                  loading="lazy"
                />
              </div>
              <div class="absolute inset-0 flex items-center justify-center bg-black/20 transition group-hover:bg-black/30">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 shadow-lg transition group-hover:scale-110 sm:h-20 sm:w-20">
                  <svg class="ml-1 h-8 w-8 text-[#003274] sm:h-10 sm:w-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              </div>
              <div v-if="vp.video_title" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 pt-10">
                <p class="text-sm font-semibold text-white sm:text-base">{{ vp.video_title }}</p>
              </div>
            </div>

            <div v-if="vp.mission" class="flex flex-col justify-center">
              <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#003274]/10">
                <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
              </div>
              <h3 class="mb-3 text-xl font-bold text-[#1f2b63]">Миссия</h3>
              <p class="text-base leading-relaxed text-gray-600">{{ vp.mission }}</p>
            </div>
          </div>

          <!-- Goals -->
          <div v-if="vpGoals.length" class="reveal reveal-delay-1 mb-16">
            <h3 class="mb-6 text-center text-xl font-bold text-[#1f2b63]">Цели программы</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(goal, gi) in vpGoals"
                :key="gi"
                class="flex items-start gap-3 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md"
              >
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-600">
                  {{ gi + 1 }}
                </div>
                <p class="text-sm leading-relaxed text-gray-700">{{ goal.text }}</p>
              </div>
            </div>
          </div>

          <!-- Values -->
          <div v-if="vpValues.length" class="reveal reveal-delay-2 mb-16">
            <h3 class="mb-6 text-center text-xl font-bold text-[#1f2b63]">Ключевые ценности</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div
                v-for="(val, vi) in vpValues"
                :key="vi"
                class="group rounded-xl bg-gradient-to-br from-[#003274]/5 to-[#003274]/10 p-5 text-center transition hover:from-[#003274]/10 hover:to-[#003274]/20"
              >
                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-[#003274]/10 transition group-hover:bg-[#003274]/20">
                  <svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                  </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">{{ val.text }}</p>
              </div>
            </div>
          </div>

          <!-- Organizers -->
          <div v-if="vpOrganizers.length" class="reveal reveal-delay-3 mb-16">
            <h3 class="mb-6 text-center text-xl font-bold text-[#1f2b63]">Организаторы</h3>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(org, oi) in vpOrganizers"
                :key="oi"
                class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md"
              >
                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-gray-200">
                  <img
                    v-if="org.image"
                    :src="org.image"
                    :alt="org.name"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                  <div v-else class="flex h-full w-full items-center justify-center text-xl font-bold text-gray-400">
                    {{ (org.name || '?')[0] }}
                  </div>
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ org.name }}</p>
                  <p v-if="org.role" class="text-sm text-gray-500">{{ org.role }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- History -->
          <div v-if="vp.history" class="reveal reveal-delay-2 mb-16">
            <div class="mx-auto max-w-3xl rounded-2xl bg-[#003274]/5 p-8 text-center">
              <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#003274]/10">
                <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
              </div>
              <h3 class="mb-3 text-xl font-bold text-[#1f2b63]">История</h3>
              <p class="text-base leading-relaxed text-gray-600">{{ vp.history }}</p>
            </div>
          </div>

          <!-- Facts -->
          <div v-if="vpFacts.length" class="reveal reveal-delay-3 mb-16">
            <h3 class="mb-6 text-center text-xl font-bold text-[#1f2b63]">Цифры и факты</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div
                v-for="(fact, fi) in vpFacts"
                :key="fi"
                class="rounded-xl bg-white p-6 text-center shadow-md transition hover:-translate-y-1 hover:shadow-lg"
              >
                <p class="text-3xl font-extrabold text-[#003274]">{{ fact.value }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ fact.label }}</p>
              </div>
            </div>
          </div>

          <!-- Audience -->
          <div v-if="vp.audience" class="reveal reveal-delay-4">
            <div class="mx-auto max-w-3xl rounded-2xl border border-[#003274]/10 bg-gradient-to-r from-[#003274]/5 to-transparent p-8">
              <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#003274]/10">
                  <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="mb-2 text-xl font-bold text-[#1f2b63]">Аудитория</h3>
                  <p class="text-base leading-relaxed text-gray-600">{{ vp.audience }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Video Presentation modal -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition duration-200"
          enter-from-class="opacity-0"
          leave-active-class="transition duration-200"
          leave-to-class="opacity-0"
        >
          <div
            v-if="vpShowVideo"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @click.self="closePresentationVideo"
          >
            <Transition
              enter-active-class="transition duration-200"
              enter-from-class="scale-95 opacity-0"
              leave-active-class="transition duration-200"
              leave-to-class="scale-95 opacity-0"
            >
              <div v-if="vpShowVideo" class="relative w-full max-w-4xl">
                <button
                  type="button"
                  @click="closePresentationVideo"
                  class="absolute -right-2 -top-10 rounded-full p-1.5 text-white/80 transition hover:text-white"
                >
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
                <div class="overflow-hidden rounded-2xl bg-black">
                  <div class="aspect-video">
                    <video
                      v-if="vp.video_file"
                      :src="vp.video_file"
                      controls
                      autoplay
                      class="h-full w-full"
                      :poster="vp.video_thumbnail || undefined"
                    />
                    <iframe
                      v-else-if="vp.video_embed_url"
                      :src="vp.video_embed_url"
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
      <section v-if="isBlockVisible('news') && latestPosts?.length" :style="blockStyle('news')" class="bg-[#f3f4fa] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('news', 'Новости') }}</h2>
              <p class="mt-2 text-gray-500">{{ sectionSubtitle('news', 'Последние новости программы') }}</p>
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
      <section v-if="isBlockVisible('moving')" :style="blockStyle('moving')" class="bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-16 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-7xl">
          <div class="relative overflow-hidden rounded-2xl bg-white/10 px-8 py-12 text-center text-white shadow-xl backdrop-blur-sm sm:px-16 sm:py-16">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.12),transparent_60%)]" />
            <div class="relative">
              <h2 class="text-2xl font-bold sm:text-3xl">{{ pd.moving_title || 'Переезжаем' }}</h2>
              <p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">
                {{ pd.moving_description || 'Узнайте о возможностях переезда в атомные города — программа поддержки, условия и перспективы' }}
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
      <section v-if="isBlockVisible('stats')" :style="blockStyle('stats')" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
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
      <section v-if="isBlockVisible('featured_tours')" :style="blockStyle('featured_tours')" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('featured_tours', 'Популярные туры') }}</h2>
              <p class="mt-2 text-gray-500">{{ sectionSubtitle('featured_tours', 'Откройте для себя уникальные маршруты') }}</p>
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
      <section v-if="isBlockVisible('cities') && cities?.length" :style="blockStyle('cities')" class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex items-end justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('cities', 'Атомные города') }}</h2>
              <p class="mt-2 text-gray-500">{{ sectionSubtitle('cities', 'Современные города с уникальной историей') }}</p>
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
        v-if="isBlockVisible('map') && allCities?.length"
        :style="blockStyle('map')"
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_30%_20%,rgba(255,255,255,0.12),transparent_55%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">{{ sectionTitle('map', 'География проекта') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-white/80 sm:text-base">
              {{ sectionSubtitle('map', 'Атомные города на карте России — нажмите на маркер, чтобы узнать о городе и перейти на его страницу') }}
            </p>
          </div>
          <div class="reveal mx-auto mt-10 overflow-hidden rounded-2xl shadow-2xl shadow-black/30" style="height: 520px">
            <YandexCityMap :cities="allCities" />
          </div>
        </div>
      </section>

      <!-- Атомы вкуса -->
      <section v-if="isBlockVisible('recipes') && latestRecipes?.length" :style="blockStyle('recipes')" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('recipes', 'Книга атомных рецептов') }}</h2>
              <p class="mt-2 max-w-xl text-gray-500">
                {{ sectionSubtitle('recipes', 'Блюда из городов атомной отрасли — откройте для себя кулинарные традиции регионов') }}
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
      <section v-if="isBlockVisible('timeline')" :style="blockStyle('timeline')" class="bg-slate-50 px-4 py-20 sm:px-6 lg:px-8">
        <div class="relative mx-auto max-w-5xl">
          <div class="reveal mb-14 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('timeline', 'Хронология событий') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-500">
              {{ sectionSubtitle('timeline', 'Ключевые новости, события и вехи развития программы') }}
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
      <section v-if="isBlockVisible('cta')" :style="blockStyle('cta')" class="reveal mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#003274] to-[#025ea1] px-8 py-16 text-center text-white shadow-xl sm:px-16">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.1),transparent_60%)]" />
          <div class="relative">
            <h2 class="text-2xl font-bold sm:text-3xl">{{ pd.cta_title || 'Хотите узнать подробнее о программе?' }}</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">
              {{ pd.cta_description || 'Оставьте заявку, и мы свяжемся с вами в ближайшее время' }}
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
        v-if="isBlockVisible('contact_form')"
        :style="blockStyle('contact_form')"
        class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#024a85] to-[#025ea1] px-4 py-20 text-white sm:px-6 lg:px-8"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_80%_0%,rgba(255,255,255,0.12),transparent_50%)]" />
        <div class="relative mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">{{ pd.contact_title || 'Хочу узнать подробнее' }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-base text-white/85">
              {{ pd.contact_description || 'Заполните форму — мы ответим на вопросы о турах, городах и возможностях программы' }}
            </p>
          </div>
          <div class="reveal grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="flex flex-col justify-center space-y-6 text-white/90">
              <p class="text-lg leading-relaxed">
                {{ pd.contact_left_text || 'Команда проекта поможет подобрать маршрут, расскажет о датах и условиях участия.' }}
              </p>
              <ul v-if="contactBullets.length" class="space-y-3 text-sm text-white/80">
                <li v-for="(bullet, bi) in contactBullets" :key="bi" class="flex items-start gap-2">
                  <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300" />
                  {{ bullet.text }}
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
      <section v-if="isBlockVisible('contacts')" :style="blockStyle('contacts')" class="bg-white px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
          <div class="reveal mb-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ sectionTitle('contacts', 'Контакты') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">
              {{ sectionSubtitle('contacts', 'Свяжитесь с нами любым удобным способом') }}
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
import HeroSection from '@/Components/shared/HeroSection.vue'
import YandexCityMap from '@/Components/YandexCityMap.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'
import { getIconSvg } from '@/utils/iconLibrary'

useScrollReveal()

const props = defineProps({
  featuredTours: Array,
  cities: Array,
  allCities: { type: Array, default: () => [] },
  latestRecipes: { type: Array, default: () => [] },
  latestPosts: { type: Array, default: () => [] },
  stats: Object,
  timelineEvents: { type: Array, default: () => [] },
  userFavorites: { type: Object, default: null },
  pageData: { type: Object, default: () => ({}) },
})

const page = usePage()
const pd = props.pageData || {}
const st = pd.section_titles || {}

function sectionTitle(id, fallback) {
  return st[id]?.title || fallback
}
function sectionSubtitle(id, fallback) {
  return st[id]?.subtitle || fallback
}

const enabledBlocks = computed(() => {
  const order = pd.block_order || []
  const set = new Set()
  order.forEach(b => { if (b.enabled) set.add(b.id) })
  if (set.size === 0) return null
  return set
})

function isBlockVisible(id) {
  return enabledBlocks.value === null || enabledBlocks.value.has(id)
}

const blockOrderMap = computed(() => {
  const order = pd.block_order || []
  const map = {}
  order.forEach((b, i) => { map[b.id] = i })
  return map
})

function blockStyle(id) {
  const idx = blockOrderMap.value[id]
  return idx !== undefined ? { order: idx } : {}
}

const programStages = computed(() => pd.program_stages || [])
const cityBenefits = computed(() => pd.city_benefits || [])
const additionalInitiatives = computed(() => pd.additional_initiatives || [])
const videoItems = computed(() => pd.videos || [])
const vp = computed(() => pd.video_presentation || {})
const vpGoals = computed(() => vp.value.goals || [])
const vpValues = computed(() => vp.value.values || [])
const vpOrganizers = computed(() => vp.value.organizers || [])
const vpFacts = computed(() => vp.value.facts || [])

const vpShowVideo = ref(false)

function openPresentationVideo() {
  if (vp.value.video_file || vp.value.video_embed_url) {
    vpShowVideo.value = true
  }
}

function closePresentationVideo() {
  vpShowVideo.value = false
}

const contactItems = computed(() => {
  const items = pd.contacts || []
  const icons = {
    'Телефон': '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>',
    'E-mail': '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>',
    'Адрес': '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>',
    'Время работы': '<svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
  }
  return items.map(c => ({ ...c, icon: icons[c.label] || icons['Телефон'] }))
})

const socialIconSvgs = {
  vk: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.587-1.496c.598-.188 1.368 1.259 2.184 1.814.616.42 1.084.328 1.084.328l2.178-.03s1.14-.071.6-.964c-.045-.073-.32-.659-1.644-1.864-1.386-1.262-1.2-1.058.468-3.243.834-1.093 1.168-1.76 1.064-2.045-.1-.272-.708-.2-.708-.2h-2.476s-.184-.025-.32.056c-.133.08-.219.266-.219.266s-.392 1.044-.916 1.932c-1.104 1.872-1.546 1.972-1.728 1.856-.424-.272-.318-1.092-.318-1.674 0-1.82.276-2.58-.536-2.778-.27-.066-.468-.11-1.156-.116-.882-.01-1.628.002-2.05.209-.282.138-.498.444-.366.462.164.022.534.1.73.366.254.344.244 1.116.244 1.116s.146 2.14-.34 2.404c-.332.182-.788-.19-1.768-1.892-.502-.872-.882-1.836-.882-1.836s-.074-.18-.204-.276c-.158-.118-.378-.156-.378-.156h-2.354s-.354.01-.484.164c-.116.138-.01.422-.01.422s1.838 4.3 3.92 6.468c1.908 1.988 4.072 1.858 4.072 1.858h.98Z"/></svg>',
  telegram: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0h-.056Zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635Z"/></svg>',
  max: '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 720 720"><path d="M350.4,9.6C141.8,20.5,4.1,184.1,12.8,390.4c3.8,90.3,40.1,168,48.7,253.7,2.2,22.2-4.2,49.6,21.4,59.3,31.5,11.9,79.8-8.1,106.2-26.4,9-6.1,17.6-13.2,24.2-22,27.3,18.1,53.2,35.6,85.7,43.4,143.1,34.3,299.9-44.2,369.6-170.3C799.6,291.2,622.5-4.6,350.4,9.6h0ZM269.4,504c-11.3,8.8-22.2,20.8-34.7,27.7-18.1,9.7-23.7-.4-30.5-16.4-21.4-50.9-24-137.6-11.5-190.9,16.8-72.5,72.9-136.3,150-143.1,78-6.9,150.4,32.7,183.1,104.2,72.4,159.1-112.9,316.2-256.4,218.6h0Z"/></svg>',
}

const socialLinks = computed(() => {
  const items = pd.socials || []
  return items.map(s => ({ ...s, icon: socialIconSvgs[s.icon] || socialIconSvgs.vk }))
})

const contactBullets = computed(() => pd.contact_bullets || [])

const programCitiesRaw = computed(() => pd.program_cities || [])
const programYears = computed(() => programCitiesRaw.value.map(g => g.year).sort((a, b) => b - a))
const activeCitiesYear = ref(null)
const currentActiveCitiesYear = computed(() => activeCitiesYear.value ?? programYears.value[0] ?? new Date().getFullYear())
const programCitiesByYear = computed(() => {
  const map = {}
  programCitiesRaw.value.forEach(g => { map[g.year] = g.cities })
  return map
})

const programResultsRaw = computed(() => pd.program_results || [])
const resultYears = computed(() => programResultsRaw.value.map(g => g.year).sort((a, b) => b - a))
const activeResultsYear = ref(null)
const currentActiveResultsYear = computed(() => activeResultsYear.value ?? resultYears.value[0] ?? new Date().getFullYear())
const programResultsByYear = computed(() => {
  const map = {}
  programResultsRaw.value.forEach(g => { map[g.year] = g.results })
  return map
})

const vshgrHref = computed(() => {
  if (page.props.auth?.user) {
    return page.props.lmsEntryUrl || route('education.index')
  }
  return route('education.index')
})

const flashSuccess = computed(() => page.props.flash?.success ?? null)

const videoSlider = ref(null)
const activeVideo = ref(null)

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

const sortedTimelineEvents = computed(() => {
  const raw = props.timelineEvents || []
  return [...raw].sort(
    (a, b) => new Date(a.event_date).getTime() - new Date(b.event_date).getTime(),
  )
})

const timelineEventsCount = computed(() => (props.timelineEvents || []).length)

const sourceValues = computed(() => ({
  cities: props.stats?.cities ?? 0,
  tours: props.stats?.tours ?? 0,
  events: timelineEventsCount.value,
}))

const defaultStatsCards = [
  { icon: 'building', label: 'Атомных городов', source: 'cities', value: '' },
  { icon: 'map', label: 'Туров возможностей', source: 'tours', value: '' },
  { icon: 'calendar', label: 'Событий в хронологии', source: 'events', value: '' },
  { icon: 'users', label: 'Гостей', source: 'custom', value: '3000+' },
]

const statCards = computed(() => {
  const cards = pd.stats_cards?.length ? pd.stats_cards : defaultStatsCards
  return cards.map(c => ({
    value: c.source === 'custom' ? (c.value || '') : (sourceValues.value[c.source] ?? 0),
    label: c.label,
    icon: getIconSvg(c.icon, 'h-6 w-6 text-[#003274]'),
  }))
})

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
