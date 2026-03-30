<template>
  <MainLayout>
    <Head :title="city.name" />

    <div>
      <!-- 1. Hero -->
      <div class="relative h-72 overflow-hidden bg-gray-200 sm:h-80 lg:h-[28rem]">
        <img
          v-if="city.image"
          :src="city.image"
          :alt="city.name"
          class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/35 to-transparent" />
        <div class="absolute bottom-0 left-0 right-0 mx-auto max-w-7xl px-4 pb-8 sm:px-6 sm:pb-10 lg:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-4xl font-bold text-white sm:text-5xl">{{ city.name }}</h1>
              <p v-if="city.region" class="mt-2 text-lg text-white/85 sm:text-xl">{{ city.region }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 disabled:cursor-wait disabled:opacity-60"
                :class="favorited ? 'border-rose-300/50 bg-rose-500/20' : ''"
                :disabled="favoriteSending"
                :title="isAuthed ? (favorited ? 'Убрать из избранного' : 'В избранное') : 'Войдите, чтобы добавить в избранное'"
                @click="toggleFavorite"
              >
                <svg v-if="favorited" class="h-5 w-5 shrink-0 fill-current text-rose-300" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17l-.022.012-.007.003-.002.001h-.002z" />
                </svg>
                <svg v-else class="h-5 w-5 shrink-0 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                {{ favorited ? 'В избранном' : 'В избранное' }}
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                @click="shareCity"
              >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 5.314 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
                Поделиться
              </button>
            </div>
          </div>
          <p
            v-if="shareCopied"
            class="mt-3 text-sm text-white/90"
            role="status"
          >
            Ссылка скопирована
          </p>
        </div>
      </div>

      <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
        <!-- 2. Key stats bar -->
        <div
          class="reveal -mt-6 mb-10 grid gap-4 rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm sm:-mt-8"
          :class="hasPopulationStat ? 'sm:grid-cols-3' : 'sm:grid-cols-2'"
        >
          <div
            v-if="hasPopulationStat"
            class="flex items-center gap-4"
          >
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#003274]/10">
              <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.813-2.882M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Население</p>
              <p class="text-xl font-bold text-gray-900">{{ formatPopulation(city.population) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#003274]/10">
              <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Достопримечательности</p>
              <p class="text-xl font-bold text-gray-900">{{ attractionsCount }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#003274]/10">
              <svg class="h-6 w-6 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V6.741c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 5.698a1.125 1.125 0 0 0-1.006 0L3.622 7.632c-.548.274-1.087-.306-1.006-1.006V17.25c0 .414.336.75.75.75Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Туры</p>
              <p class="text-xl font-bold text-gray-900">{{ toursCount }}</p>
            </div>
          </div>
        </div>

        <!-- 3. About -->
        <section v-if="city.description || (city.facts?.length) || aboutInfographicRows.length" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">О городе</h2>
          <div
            v-if="city.description"
            class="html-content mt-6 max-w-3xl text-lg leading-relaxed text-gray-600"
            v-html="city.description"
          />
          <div v-if="aboutInfographicRows.length" class="mt-8 grid gap-4 sm:grid-cols-3">
            <div
              v-for="(row, i) in aboutInfographicRows"
              :key="row.key + i"
              class="rounded-2xl border border-gray-100 bg-gradient-to-br from-[#003274]/[0.06] to-white p-5 shadow-sm"
            >
              <p class="text-3xl font-bold tabular-nums text-[#003274]">{{ row.value }}</p>
              <p class="mt-1 text-sm font-medium text-gray-600">{{ row.label }}</p>
            </div>
          </div>
          <div v-if="city.facts?.length" class="mt-8 flex flex-wrap gap-2">
            <div
              v-for="(fact, i) in city.facts"
              :key="i"
              class="inline-flex max-w-full items-start gap-2 rounded-xl border border-[#003274]/15 bg-[#003274]/[0.04] px-4 py-2.5 text-sm text-gray-800"
            >
              <span class="mt-0.5 shrink-0 text-[#003274]" aria-hidden="true">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" /></svg>
              </span>
              <span>{{ fact }}</span>
            </div>
          </div>
        </section>

        <!-- 4. Infrastructure -->
        <section v-if="hasInfrastructureContent" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Инфраструктура</h2>
          <p v-if="infrastructureScores" class="mt-2 max-w-2xl text-gray-600">Оценка развития ключевых сфер (условные баллы).</p>
          <p v-else class="mt-2 max-w-2xl text-gray-600">Ключевые объекты и направления развития города.</p>
          <div v-if="infrastructureScores" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="key in infrastructureScoreKeys"
              :key="key"
              class="flex gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-[#003274]/20 hover:shadow-md"
            >
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#003274]/10 text-[#003274]"
                v-html="infrastructureScoreEntries[key].iconSvg"
              ></div>
              <div class="min-w-0 flex-1">
                <p class="font-semibold text-gray-900">{{ infrastructureScoreEntries[key].label }}</p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    class="h-full rounded-full bg-[#003274] transition-all duration-700"
                    :style="{ width: `${Math.min(100, Math.max(0, infrastructureScores[key]))}%` }"
                  />
                </div>
                <p class="mt-1 text-sm font-medium text-[#003274]">{{ infrastructureScores[key] }}%</p>
              </div>
            </div>
          </div>
          <div v-else-if="infrastructureList.length" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="(item, i) in infrastructureList"
              :key="i"
              class="flex gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#003274]/10 text-[#003274]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
              </div>
              <p class="text-sm font-medium leading-snug text-gray-800">{{ item }}</p>
            </div>
          </div>
        </section>

        <!-- 5. Attractions -->
        <section v-if="city.attractions?.length" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Достопримечательности</h2>
          <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article
              v-for="(a, i) in city.attractions"
              :key="i"
              class="reveal group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#003274]/20 hover:shadow-lg"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                <img
                  v-if="a.image"
                  :src="a.image"
                  :alt="a.title || 'Достопримечательность'"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <div v-else class="flex h-full items-center justify-center text-gray-300">
                  <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008H12V8.25Z" />
                  </svg>
                </div>
              </div>
              <div class="p-5">
                <h3 class="font-semibold text-gray-900">{{ a.title }}</h3>
                <p v-if="a.description" class="mt-2 text-sm leading-relaxed text-gray-600">{{ a.description }}</p>
              </div>
            </article>
          </div>
        </section>

        <!-- 6. Social objects -->
        <section v-if="hasSocialObjects" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Социальная сфера</h2>
          <div class="mt-8 grid gap-8 lg:grid-cols-3">
            <div v-if="socialColumnEducation.length">
              <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-[#003274]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm6 0a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" /></svg>
                Образование
              </h3>
              <ul class="mt-4 space-y-3">
                <li
                  v-for="(line, j) in socialColumnEducation"
                  :key="'e' + j"
                  class="flex gap-2 text-sm text-gray-700"
                >
                  <span class="mt-0.5 shrink-0 text-[#003274]"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                  <span>{{ line }}</span>
                </li>
              </ul>
            </div>
            <div v-if="socialColumnMedicine.length">
              <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-[#003274]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.866 9.623 3.75 7.688 3.75c-2.59 0-4.688 2.015-4.688 4.5 0 3.925 2.438 7.111 4.739 9.256a25.175 25.175 0 0 0 4.244 3.17l.022.012.007.003.002.001h.002l.007-.003.022-.012a25.18 25.18 0 0 0 4.244-3.17c2.3-2.145 4.739-5.331 4.739-9.256Z" /></svg>
                Медицина
              </h3>
              <ul class="mt-4 space-y-3">
                <li
                  v-for="(line, j) in socialColumnMedicine"
                  :key="'m' + j"
                  class="flex gap-2 text-sm text-gray-700"
                >
                  <span class="mt-0.5 shrink-0 text-[#003274]"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                  <span>{{ line }}</span>
                </li>
              </ul>
            </div>
            <div v-if="socialColumnCulture.length">
              <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-[#003274]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                Культура
              </h3>
              <ul class="mt-4 space-y-3">
                <li
                  v-for="(line, j) in socialColumnCulture"
                  :key="'c' + j"
                  class="flex gap-2 text-sm text-gray-700"
                >
                  <span class="mt-0.5 shrink-0 text-[#003274]"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg></span>
                  <span>{{ line }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- 7. Gallery -->
        <section v-if="city.gallery?.length" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Галерея</h2>
          <div class="gallery-masonry mt-8 columns-1 gap-4 sm:columns-2 lg:columns-3">
            <button
              v-for="(url, i) in city.gallery"
              :key="i"
              type="button"
              class="gallery-masonry__item mb-4 block w-full break-inside-avoid overflow-hidden rounded-2xl ring-[#003274] transition hover:opacity-95 focus:outline-none focus-visible:ring-2"
              @click="openGalleryModal(i)"
            >
              <img :src="url" :alt="`Фото ${i + 1}`" class="w-full rounded-2xl object-cover shadow-sm" loading="lazy" />
            </button>
          </div>
        </section>

        <!-- 8. Video -->
        <section v-if="videoEmbedSrc" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Видео</h2>
          <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-black shadow-lg">
            <div class="aspect-video w-full">
              <iframe
                :src="videoEmbedSrc"
                class="h-full w-full"
                title="Видео о городе"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              />
            </div>
          </div>
        </section>

        <!-- 9. Researches -->
        <section v-if="city.researches?.length" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Исследования</h2>
          <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(r, i) in city.researches"
              :key="r.id"
              :href="route('research.show', r.slug)"
              class="reveal group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#003274]/25 hover:shadow-lg"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                <img
                  v-if="r.image"
                  :src="r.image"
                  :alt="r.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
              </div>
              <div class="flex flex-1 flex-col p-5">
                <h3 class="font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ r.title }}</h3>
                <p v-if="r.description" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ stripHtml(r.description) }}</p>
                <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-[#003274]">
                  Читать
                  <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </span>
              </div>
            </Link>
          </div>
        </section>

        <!-- 10. Recipes -->
        <section v-if="city.recipes?.length" class="reveal mb-16">
          <h2 class="text-2xl font-bold text-gray-900">Рецепты региона</h2>
          <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(recipe, i) in city.recipes"
              :key="recipe.id"
              :href="route('recipes.show', recipe.slug)"
              class="reveal group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#003274]/25 hover:shadow-lg"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                <img
                  v-if="recipe.image"
                  :src="recipe.image"
                  :alt="recipe.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
              </div>
              <div class="flex flex-1 flex-col p-5">
                <h3 class="font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ recipe.title }}</h3>
                <p v-if="recipe.description" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ stripHtml(recipe.description) }}</p>
                <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-[#003274]">
                  Открыть рецепт
                  <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </span>
              </div>
            </Link>
          </div>
        </section>

        <!-- 11. Vacancies -->
        <section v-if="city.vacancies?.length" class="reveal mb-16">
          <div class="flex items-end justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Вакансии в городе</h2>
            <Link
              :href="route('vacancies.index') + '?city=' + city.id"
              class="group flex items-center gap-1.5 text-sm font-semibold text-[#003274] transition hover:text-[#025ea1]"
            >
              Все вакансии
              <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </Link>
          </div>
          <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(v, i) in city.vacancies"
              :key="v.id"
              :href="route('vacancies.show', v.slug)"
              class="reveal group flex flex-col rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#003274]/25 hover:shadow-lg"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="flex flex-wrap items-center gap-2">
                <RBadge v-if="v.employment_type" variant="primary" size="sm">{{ employmentTypeLabel(v.employment_type) }}</RBadge>
                <span v-if="v.company" class="text-xs text-gray-500">{{ v.company }}</span>
              </div>
              <h3 class="mt-2 font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ v.title }}</h3>
              <p v-if="v.salary" class="mt-2 text-sm font-bold text-[#003274]">{{ v.salary }}</p>
              <p v-if="v.description" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ stripHtml(v.description) }}</p>
              <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-medium text-[#003274]">
                Подробнее
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
              </span>
            </Link>
          </div>
        </section>

        <!-- 12. Tours -->
        <section v-if="city.tours?.length" class="reveal">
          <h2 class="text-2xl font-bold text-gray-900">Туры в городе</h2>
          <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="(tour, i) in city.tours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="reveal group overflow-hidden rounded-xl bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
              :class="'reveal-delay-' + ((i % 5) + 1)"
            >
              <div class="aspect-video overflow-hidden bg-gray-200">
                <img
                  v-if="tour.image"
                  :src="tour.image"
                  :alt="tour.title"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                />
              </div>
              <div class="p-5">
                <h3 class="font-semibold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ tour.duration }}</p>
                <p class="mt-3 text-lg font-bold text-[#003274]">
                  <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                  <template v-else><span class="text-green-600">Бесплатно</span></template>
                </p>
              </div>
            </Link>
          </div>
        </section>
      </div>
    </div>

    <!-- Gallery lightbox -->
    <Teleport to="body">
      <div
        v-if="galleryModalIndex !== null && city.gallery?.[galleryModalIndex]"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-label="Просмотр фото"
        @click.self="closeGalleryModal"
      >
        <button
          type="button"
          class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
          aria-label="Закрыть"
          @click="closeGalleryModal"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
        <img
          :src="city.gallery[galleryModalIndex]"
          alt=""
          class="max-h-[90vh] max-w-full rounded-lg object-contain shadow-2xl"
        />
      </div>
    </Teleport>
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
.gallery-masonry__item img {
  display: block;
}
</style>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, Head, router, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { useScrollReveal } from '@/composables/useScrollReveal'

useScrollReveal()

const INFRA_META = {
  work: {
    label: 'Работа',
    iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.25m0 0h4.5m-4.5 0-3-3m3 3 3-3m-9 3H9m.75-9H15m-.75 9H9m.75-9v9m.75-9h4.5a2.25 2.25 0 0 1 2.25 2.25v4.5a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25v-4.5A2.25 2.25 0 0 1 9.75 5.25h4.5Z" /></svg>',
  },
  housing: {
    label: 'Жильё',
    iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>',
  },
  leisure: {
    label: 'Досуг',
    iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18 10.5h.008v.008H18V10.5Z" /></svg>',
  },
  education: {
    label: 'Образование',
    iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>',
  },
  medicine: {
    label: 'Медицина',
    iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.866 9.623 3.75 7.688 3.75c-2.59 0-4.688 2.015-4.688 4.5 0 3.925 2.438 7.111 4.739 9.256a25.175 25.175 0 0 0 4.244 3.17l.022.012.007.003.002.001h.002l.007-.003.022-.012a25.18 25.18 0 0 0 4.244-3.17c2.3-2.145 4.739-5.331 4.739-9.256Z" /></svg>',
  },
}

const props = defineProps({
  city: { type: Object, required: true },
  isFavorited: { type: Boolean, default: false },
})

const page = usePage()
const isAuthed = computed(() => !!page.props.auth?.user)

const favorited = ref(!!props.isFavorited)
watch(
  () => props.isFavorited,
  (v) => {
    if (typeof v === 'boolean') {
      favorited.value = v
    }
  },
)

const favoriteSending = ref(false)
const shareCopied = ref(false)
let shareCopiedTimer = null

const galleryModalIndex = ref(null)

const attractionsCount = computed(() => props.city.attractions?.length ?? 0)
const toursCount = computed(() => props.city.tours?.length ?? 0)

const hasPopulationStat = computed(() => props.city.population != null && props.city.population !== '')

function formatPopulation(v) {
  const n = Number(v)
  if (Number.isFinite(n)) {
    return new Intl.NumberFormat('ru-RU').format(n)
  }
  return String(v)
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function stripHtml(html) {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return (doc.body.textContent || '').replace(/\s+/g, ' ').trim()
}

function employmentTypeLabel(type) {
  const map = {
    full_time: 'Полная занятость',
    part_time: 'Частичная занятость',
    contract: 'Контракт',
    internship: 'Стажировка',
    remote: 'Удалённо',
    shift: 'Вахта',
  }
  return map[type] || type
}

function isInfrastructureScores(val) {
  if (!val || typeof val !== 'object' || Array.isArray(val)) {
    return false
  }
  const keys = Object.keys(val)
  if (!keys.length) {
    return false
  }
  return keys.every((k) => typeof val[k] === 'number' && !Number.isNaN(val[k]))
}

const infrastructureScores = computed(() => {
  const inf = props.city.infrastructure
  if (isInfrastructureScores(inf)) {
    return inf
  }
  return null
})

const infrastructureScoreEntries = computed(() => {
  const scores = infrastructureScores.value
  if (!scores) {
    return {}
  }
  const out = {}
  for (const key of Object.keys(scores)) {
    if (INFRA_META[key]) {
      out[key] = INFRA_META[key]
    } else {
      out[key] = {
        label: key,
        iconSvg: '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
      }
    }
  }
  return out
})

const infrastructureScoreKeys = computed(() => {
  const scores = infrastructureScores.value
  if (!scores) {
    return []
  }
  const preferred = ['work', 'housing', 'leisure', 'education', 'medicine']
  const keys = Object.keys(scores)
  return [...preferred.filter((k) => keys.includes(k)), ...keys.filter((k) => !preferred.includes(k))]
})

const infrastructureList = computed(() => {
  const inf = props.city.infrastructure
  if (!inf || infrastructureScores.value) {
    return []
  }
  if (Array.isArray(inf)) {
    return inf.map((item) => {
      if (typeof item === 'string') {
        return item
      }
      if (item && typeof item === 'object') {
        return item.title || item.name || item.label || JSON.stringify(item)
      }
      return String(item)
    })
  }
  return []
})

const hasInfrastructureContent = computed(() => {
  if (infrastructureScores.value && infrastructureScoreKeys.value.length) {
    return true
  }
  return infrastructureList.value.length > 0
})

function normalizeSocialList(arr) {
  if (!Array.isArray(arr)) {
    return []
  }
  return arr.map((item) => {
    if (typeof item === 'string') {
      return item
    }
    if (item && typeof item === 'object') {
      return item.title || item.name || item.text || ''
    }
    return String(item)
  }).filter(Boolean)
}

const socialColumnEducation = computed(() => normalizeSocialList(props.city.social_objects?.education))
const socialColumnMedicine = computed(() => normalizeSocialList(props.city.social_objects?.medicine))
const socialColumnCulture = computed(() => normalizeSocialList(props.city.social_objects?.culture))

const hasSocialObjects = computed(() =>
  socialColumnEducation.value.length + socialColumnMedicine.value.length + socialColumnCulture.value.length > 0,
)

const aboutInfographicRows = computed(() => {
  const rows = []
  if (props.city.population != null && props.city.population !== '') {
    rows.push({ key: 'pop', label: 'Население', value: formatPopulation(props.city.population) })
  }
  if (attractionsCount.value > 0) {
    rows.push({ key: 'attr', label: 'Объектов для туристов', value: String(attractionsCount.value) })
  }
  if (toursCount.value > 0) {
    rows.push({ key: 'tours', label: 'Туров по городу', value: String(toursCount.value) })
  }
  return rows
})

const videoEmbedSrc = computed(() => {
  const url = props.city.video_url
  if (!url || typeof url !== 'string') {
    return null
  }
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{6,})/)
  if (yt) {
    return `https://www.youtube.com/embed/${yt[1]}`
  }
  const rt = url.match(/rutube\.ru\/(?:video\/|play\/embed\/)([a-zA-Z0-9_-]+)/)
  if (rt) {
    return `https://rutube.ru/play/embed/${rt[1]}`
  }
  if (url.includes('youtube.com/embed/') || url.includes('rutube.ru/play/embed/')) {
    return url
  }
  return null
})

function toggleFavorite() {
  if (!isAuthed.value) {
    router.visit(route('login'))
    return
  }
  if (favoriteSending.value) {
    return
  }
  favoriteSending.value = true
  router.post(route('favorites.toggle', { type: 'city', id: props.city.id }), {}, {
    preserveScroll: true,
    onFinish: () => {
      favoriteSending.value = false
    },
  })
}

async function shareCity() {
  const url = typeof window !== 'undefined' ? window.location.href : ''
  const title = props.city.name
  shareCopied.value = false
  if (shareCopiedTimer) {
    clearTimeout(shareCopiedTimer)
    shareCopiedTimer = null
  }
  if (navigator.share) {
    try {
      await navigator.share({ title, text: title, url })
      return
    } catch (e) {
      if (e?.name === 'AbortError') {
        return
      }
    }
  }
  try {
    await navigator.clipboard.writeText(url)
    shareCopied.value = true
    shareCopiedTimer = setTimeout(() => {
      shareCopied.value = false
      shareCopiedTimer = null
    }, 2500)
  } catch {
    /* noop */
  }
}

function openGalleryModal(index) {
  galleryModalIndex.value = index
}

function closeGalleryModal() {
  galleryModalIndex.value = null
}

function onKeydown(e) {
  if (e.key === 'Escape') {
    closeGalleryModal()
  }
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
})
onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  if (shareCopiedTimer) {
    clearTimeout(shareCopiedTimer)
  }
})
</script>
