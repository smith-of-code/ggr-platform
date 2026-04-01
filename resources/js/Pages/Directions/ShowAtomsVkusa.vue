<template>
  <MainLayout>
    <Head :title="content.hero_title || direction.title" />

    <!-- Hero -->
    <section
      class="relative overflow-hidden bg-gradient-to-br from-[#003274] via-[#025ea1] to-[#0277bd] px-4 py-24 text-white sm:px-6 lg:px-8"
      :style="content.hero_image ? { backgroundImage: `url(${content.hero_image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
    >
      <div v-if="content.hero_image" class="absolute inset-0 bg-[#003274]/70" />
      <div class="relative mx-auto max-w-7xl text-center">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">{{ content.hero_title || direction.title }}</h1>
        <p v-if="content.hero_description || direction.description" class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-white/85">
          {{ content.hero_description || direction.description }}
        </p>
        <button type="button" class="mt-8 inline-flex cursor-pointer items-center gap-2 rounded-xl bg-white px-8 py-4 font-semibold text-[#003274] shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl" @click="scrollTo('application')">
          Подать заявку
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>
        </button>
      </div>
    </section>

    <!-- Поднаправления -->
    <section v-if="direction.sub_directions?.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 v-if="direction.sub_directions_title" class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ direction.sub_directions_title }}</h2>
        <p v-if="direction.sub_directions_description" class="mt-2 max-w-2xl text-gray-500">{{ direction.sub_directions_description }}</p>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div v-for="(sd, i) in direction.sub_directions" :key="i" class="rounded-2xl border border-gray-200 p-8 transition hover:border-[#003274]/30 hover:shadow-lg">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#003274] text-lg font-bold text-white">{{ i + 1 }}</div>
            <h3 class="mt-4 text-xl font-bold text-gray-900">{{ sd.name }}</h3>
            <p class="mt-3 leading-relaxed text-gray-600">{{ sd.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Для кого -->
    <section v-if="direction.target_audiences?.length" class="bg-gradient-to-b from-sky-50 to-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Для кого</h2>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
          <div v-for="(ta, i) in direction.target_audiences" :key="i" class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm" :class="audienceStyles[i % audienceStyles.length].border">
            <div class="flex h-14 w-14 items-center justify-center rounded-xl" :class="audienceStyles[i % audienceStyles.length].iconBg">
              <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" :d="audienceStyles[i % audienceStyles.length].iconPath" />
              </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-gray-900">{{ ta.title }}</h3>
            <p class="mt-3 leading-relaxed text-gray-600">{{ ta.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

      <!-- Этапы конкурса -->
      <section v-if="content.competition_stages?.length" id="section-stages" class="py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Этапы конкурса</h2>
        <div ref="stagesContainer" class="relative mx-auto mt-12 max-w-3xl">
          <div class="absolute left-6 top-0 w-0.5 bg-[#003274]/20 sm:left-1/2 sm:-translate-x-px" :style="{ height: stagesLineHeight }" />
          <div v-for="(stage, i) in content.competition_stages" :key="i" class="relative pl-16 sm:pl-0" :class="[i % 2 === 0 ? 'sm:pr-[calc(50%+2rem)]' : 'sm:pl-[calc(50%+2rem)]', i < content.competition_stages.length - 1 ? 'mb-10 sm:mb-12' : '']">
            <div class="absolute left-0 top-0 flex h-12 w-12 items-center justify-center rounded-full bg-[#003274] text-lg font-bold text-white shadow-lg sm:left-1/2 sm:-translate-x-1/2">
              {{ i + 1 }}
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
              <h3 class="text-lg font-bold text-gray-900">{{ stage.title }}</h3>
              <p v-if="stage.description" class="mt-2 leading-relaxed text-gray-600">{{ stage.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Условия участия -->
      <section v-if="content.participation_conditions?.length" id="section-conditions" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Условия участия</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(cond, i) in content.participation_conditions" :key="i" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-lg font-bold text-emerald-700">
              {{ i + 1 }}
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900">{{ cond.title }}</h3>
            <p v-if="cond.description" class="mt-2 leading-relaxed text-gray-600">{{ cond.description }}</p>
          </div>
        </div>
      </section>

      <!-- Критерии отбора -->
      <section v-if="content.selection_criteria?.length" id="section-criteria" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Критерии отбора</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
          <div v-for="(crit, i) in content.selection_criteria" :key="i" class="flex gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-lg font-bold text-violet-700">
              {{ i + 1 }}
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">{{ crit.title }}</h3>
              <p v-if="crit.description" class="mt-2 leading-relaxed text-gray-600">{{ crit.description }}</p>
            </div>
          </div>
        </div>
      </section>

    </div>

    <!-- Твой билет в атомный город -->
    <section v-if="direction.free_participation_steps?.length || direction.paid_participation_steps?.length" class="bg-white px-4 py-16 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-7xl">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Твой билет в атомный город</h2>
        <div class="mt-12 grid gap-8 lg:grid-cols-2">
          <div v-if="direction.free_participation_steps?.length" class="flex flex-col">
            <div class="flex-1 rounded-2xl border-2 border-[#003274]/20 bg-gradient-to-b from-[#003274]/5 to-transparent p-8">
              <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Победить в конкурсе</h3>
              </div>
              <div class="space-y-4">
                <div v-for="(step, i) in direction.free_participation_steps" :key="i" class="flex gap-4">
                  <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#003274] text-xs font-bold text-white">{{ i + 1 }}</div>
                  <div>
                    <p class="font-semibold text-gray-900">{{ step.title }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ step.description }}</p>
                  </div>
                </div>
              </div>
              <div class="mt-8 text-center">
                <Link :href="route('login')" class="inline-flex items-center rounded-xl bg-[#003274] px-8 py-3.5 font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#025ea1] hover:shadow-xl">
                  Личный кабинет
                  <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </Link>
              </div>
            </div>
            <div v-if="details" class="relative mt-0">
              <div class="absolute left-8 top-0 h-8 w-px border-l-2 border-dashed border-[#003274]/30" />
              <div class="mt-8 space-y-4 rounded-2xl border-2 border-dashed border-[#003274]/20 bg-[#003274]/[0.02] p-8">
                <div class="mb-4 flex items-center gap-2">
                  <svg class="h-5 w-5 text-[#003274]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                  <h4 class="text-base font-bold text-[#003274]">Конкурсное испытание</h4>
                </div>
                <div v-if="details.questions?.length" class="rounded-xl bg-white p-6 shadow-sm">
                  <h5 class="font-bold text-gray-900">Развёрнутые ответы</h5>
                  <p class="mt-1 text-sm text-gray-500">Участнику в личном кабинете необходимо дать подробные ответы на вопросы:</p>
                  <ul class="mt-4 space-y-3">
                    <li v-for="(q, i) in details.questions" :key="i" class="flex gap-3">
                      <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#003274]/10 text-xs font-bold text-[#003274]">{{ i + 1 }}</span>
                      <p class="text-sm leading-relaxed text-gray-700">{{ q }}</p>
                    </li>
                  </ul>
                </div>
                <div v-if="details.challenge_description" class="rounded-xl bg-white p-6 shadow-sm">
                  <h5 class="font-bold text-gray-900">{{ details.challenge_title || 'Проверочное задание' }}</h5>
                  <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ details.challenge_description }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-if="direction.paid_participation_steps?.length" class="self-start rounded-2xl border-2 border-amber-200 bg-gradient-to-b from-amber-50 to-transparent p-8">
            <div class="mb-6 flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">За свой счёт</h3>
            </div>
            <div class="space-y-4">
              <div v-for="(step, i) in direction.paid_participation_steps" :key="i" class="flex gap-4">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white">{{ i + 1 }}</div>
                <div>
                  <p class="font-semibold text-gray-900">{{ step.title }}</p>
                  <p class="mt-1 text-sm text-gray-600">{{ step.description }}</p>
                </div>
              </div>
            </div>
            <div class="mt-8 text-center">
              <button type="button" class="inline-flex cursor-pointer items-center rounded-xl bg-amber-500 px-8 py-3.5 font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-xl" @click="scrollToTours">
                Оставить заявку
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

      <!-- Итоги года -->
      <section v-if="content.results_year" id="section-results" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Как прошёл конкурс в {{ content.results_year }} году</h2>
        <div v-if="content.results_content" class="mx-auto mt-6 max-w-3xl text-center text-lg leading-relaxed text-gray-600">{{ content.results_content }}</div>

        <!-- Галерея -->
        <div v-if="content.results_gallery?.length" class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(img, i) in content.results_gallery" :key="i" class="group overflow-hidden rounded-xl">
            <img :src="img.url" :alt="img.caption || ''" class="aspect-video w-full object-cover transition duration-500 group-hover:scale-105" />
            <p v-if="img.caption" class="mt-1 text-center text-xs text-gray-500">{{ img.caption }}</p>
          </div>
        </div>

        <!-- Видео -->
        <div v-if="content.results_videos?.length" class="mt-10 grid gap-6 sm:grid-cols-2">
          <div v-for="(vid, i) in content.results_videos" :key="i" class="overflow-hidden rounded-xl border border-gray-200 bg-black shadow-md">
            <div class="aspect-video w-full">
              <iframe :src="parseEmbed(vid.url)" class="h-full w-full" :title="vid.title || `Видео ${i+1}`" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen />
            </div>
            <p v-if="vid.title" class="bg-white px-4 py-2 text-sm font-medium text-gray-900">{{ vid.title }}</p>
          </div>
        </div>

        <!-- Кейсы -->
        <div v-if="content.results_cases?.length" class="mt-12">
          <h3 class="mb-6 text-center text-xl font-bold text-gray-900">Кейсы победителей</h3>
          <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="(cs, i) in content.results_cases" :key="i" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
              <div v-if="cs.image" class="aspect-video overflow-hidden bg-gray-100">
                <img :src="cs.image" :alt="cs.name" class="h-full w-full object-cover" />
              </div>
              <div class="p-5">
                <h4 class="font-bold text-gray-900">{{ cs.name }}</h4>
                <p v-if="cs.city" class="text-sm text-gray-500">{{ cs.city }}</p>
                <p v-if="cs.text" class="mt-2 text-sm leading-relaxed text-gray-600">{{ cs.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Почему это важно -->
      <section v-if="content.why_important_content || content.why_important_stats?.length" id="section-why" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Почему это важно</h2>
        <p v-if="content.why_important_content" class="mx-auto mt-6 max-w-3xl text-center text-lg leading-relaxed text-gray-600">{{ content.why_important_content }}</p>
        <div v-if="content.why_important_stats?.length" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="(stat, i) in content.why_important_stats" :key="i" class="rounded-2xl bg-gradient-to-br from-[#003274] to-[#025ea1] p-6 text-center text-white shadow-lg">
            <p class="text-3xl font-bold sm:text-4xl">{{ stat.value }}</p>
            <p class="mt-2 text-sm text-white/80">{{ stat.label }}</p>
          </div>
        </div>
      </section>

      <!-- Карта городов (Yandex Maps) -->
      <section v-if="content.map_cities?.length" id="section-map" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Города участников</h2>
        <p class="mt-2 text-center text-gray-500">Наведите на маркер, чтобы увидеть рецепт победителя</p>
        <div ref="mapContainer" class="mt-8 h-[500px] w-full overflow-hidden rounded-2xl border border-gray-200 shadow-sm" />
      </section>

      <!-- Форма заявки -->
      <section id="section-application" class="border-t border-gray-100 py-16">
        <div class="mx-auto max-w-2xl">
          <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">{{ content.application_form_title || 'Подать заявку' }}</h2>
          <p v-if="content.application_form_text" class="mt-3 text-center text-gray-500">{{ content.application_form_text }}</p>

          <div v-if="applicationSent" class="mt-8 flex flex-col items-center rounded-2xl border border-green-200 bg-green-50 p-8 text-center">
            <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <h3 class="mt-4 text-xl font-bold text-gray-900">Заявка отправлена!</h3>
            <p class="mt-2 text-gray-600">Спасибо! Мы свяжемся с вами в ближайшее время.</p>
          </div>

          <form v-else class="mt-8 space-y-5" @submit.prevent="submitApplication">
            <RInput v-model="appForm.name" label="Имя *" required />
            <RInput v-model="appForm.email" type="email" label="Email *" required />
            <RInput v-model="appForm.phone" type="tel" label="Телефон" />
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Сообщение</label>
              <textarea v-model="appForm.message" rows="3" class="w-full rounded-xl border-gray-300 px-4 py-3 transition focus:border-[#003274] focus:ring-[#003274]/20" placeholder="Расскажите о себе..." />
            </div>
            <RButton type="submit" variant="primary" size="lg" block>Отправить заявку</RButton>
          </form>
        </div>
      </section>

      <!-- Партнёры -->
      <section v-if="content.partners?.length" id="section-partners" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Партнёры и спонсоры</h2>
        <div class="mt-10 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5">
          <a v-for="(p, i) in content.partners" :key="i" :href="p.url || '#'" target="_blank" rel="noopener" class="group flex flex-col items-center gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-[#003274]/20 hover:shadow-md">
            <div class="flex h-16 w-full items-center justify-center">
              <img v-if="p.logo" :src="p.logo" :alt="p.name" class="max-h-full max-w-full object-contain" />
              <span v-else class="text-center text-sm font-semibold text-gray-700">{{ p.name }}</span>
            </div>
          </a>
        </div>
      </section>

      <!-- Отзывы -->
      <section v-if="content.reviews?.length" id="section-reviews" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Отзывы участников и экспертов</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(rev, i) in content.reviews" :key="i" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
              <div v-if="rev.avatar" class="h-12 w-12 shrink-0 overflow-hidden rounded-full">
                <img :src="rev.avatar" :alt="rev.name" class="h-full w-full object-cover" />
              </div>
              <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#003274]/10 text-lg font-bold text-[#003274]">
                {{ rev.name?.charAt(0)?.toUpperCase() }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ rev.name }}</p>
                <p v-if="rev.role" class="text-sm text-gray-500">{{ rev.role }}</p>
              </div>
            </div>
            <div v-if="rev.rating" class="mt-3 flex gap-0.5 text-amber-400">
              <span v-for="s in 5" :key="s" :class="s <= rev.rating ? '' : 'text-gray-300'">&#9733;</span>
            </div>
            <p class="mt-3 leading-relaxed text-gray-600">{{ rev.text }}</p>
          </div>
        </div>
      </section>

      <!-- Как конкурс помогает туризму -->
      <section v-if="content.tourism_help_content || content.tourism_help_items?.length" id="section-tourism" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Как конкурс помогает туризму</h2>
        <p v-if="content.tourism_help_content" class="mx-auto mt-6 max-w-3xl text-center text-lg leading-relaxed text-gray-600">{{ content.tourism_help_content }}</p>
        <div v-if="content.tourism_help_items?.length" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(item, i) in content.tourism_help_items" :key="i" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div v-if="item.image" class="aspect-video overflow-hidden bg-gray-100">
              <img :src="item.image" :alt="item.title" class="h-full w-full object-cover" />
            </div>
            <div class="p-5">
              <h3 class="text-lg font-bold text-gray-900">{{ item.title }}</h3>
              <p v-if="item.description" class="mt-2 leading-relaxed text-gray-600">{{ item.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Новости и анонсы -->
      <section v-if="news?.length" id="section-news" class="border-t border-gray-100 py-16">
        <div class="flex items-end justify-between">
          <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Новости и анонсы</h2>
          <Link :href="route('blog.index')" class="text-sm font-medium text-[#003274] transition hover:text-[#025ea1]">Все новости &rarr;</Link>
        </div>
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <Link v-for="post in news" :key="post.id" :href="route('blog.show', post.slug)" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
            <div v-if="post.image" class="aspect-video overflow-hidden bg-gray-100">
              <img :src="post.image" :alt="post.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            </div>
            <div class="p-5">
              <p class="text-xs text-gray-400">{{ formatDate(post.published_at) }}</p>
              <h3 class="mt-1 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ post.title }}</h3>
              <p v-if="post.excerpt" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ post.excerpt }}</p>
            </div>
          </Link>
        </div>
      </section>

      <!-- Книга атомных рецептов -->
      <section id="section-recipes" class="border-t border-gray-100 py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Книга атомных рецептов</h2>
        <p class="mt-2 text-center text-gray-500">Кулинарное наследие атомных городов России</p>

        <div class="mt-8 flex flex-wrap items-end gap-4">
          <div class="w-full sm:w-64">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Город</label>
            <select v-model="recipeCity" class="w-full rounded-xl border-gray-300 text-sm focus:border-[#003274] focus:ring-[#003274]">
              <option value="">Все города</option>
              <option v-for="c in recipeCities" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <RButton variant="primary" @click="filterRecipes">Показать</RButton>
          <button v-if="recipeCity" type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="recipeCity = ''; filterRecipes()">Сбросить</button>
        </div>

        <div v-if="recipes?.data?.length" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <Link v-for="recipe in recipes.data" :key="recipe.id" :href="route('recipes.show', recipe.slug)" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
            <div v-if="recipe.image" class="aspect-video overflow-hidden bg-gray-100">
              <img :src="recipe.image" :alt="recipe.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            </div>
            <div class="p-5">
              <h3 class="text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ recipe.title }}</h3>
              <p v-if="recipe.city" class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                {{ recipe.city.name }}
              </p>
              <p v-if="recipe.description" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ recipe.description }}</p>
              <div class="mt-3 flex flex-wrap gap-2">
                <span v-if="recipe.cooking_time" class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                  {{ recipe.cooking_time }}
                </span>
              </div>
            </div>
          </Link>
        </div>
        <p v-else class="mt-8 text-center text-gray-500">Рецепты не найдены</p>

        <div v-if="recipes?.links?.length > 3" class="mt-8 flex justify-center gap-1">
          <Link
            v-for="link in recipes.links"
            :key="link.label"
            :href="link.url || '#'"
            class="rounded-lg border px-3 py-1.5 text-sm transition"
            :class="link.active ? 'border-[#003274] bg-[#003274] text-white' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
            v-html="link.label"
          />
        </div>
      </section>

      <!-- Туры направления -->
      <section v-if="featuredTours.length" ref="toursSection" class="border-t border-gray-100 py-16">
        <div class="flex items-end justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Туры направления</h2>
            <p class="mt-2 text-gray-500">Выберите тур и отправляйтесь в путешествие</p>
          </div>
          <Link :href="route('tours.index')" class="hidden text-sm font-medium text-[#003274] transition hover:text-[#025ea1] sm:block">Все туры &rarr;</Link>
        </div>
        <div class="relative mt-10">
          <div ref="toursSlider" class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-4" style="scrollbar-width: none">
            <Link
              v-for="tour in featuredTours"
              :key="tour.id"
              :href="route('tours.show', tour.slug)"
              class="w-full flex-shrink-0 snap-center sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)]"
            >
              <RCard elevation="raised" hoverable class="group h-full">
                <template #cover>
                  <div class="aspect-video overflow-hidden bg-gray-100">
                    <img v-if="tour.image" :src="tour.image" :alt="tour.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                  </div>
                </template>
                <div>
                  <span class="flex items-center gap-1 text-xs text-gray-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    {{ tour.duration }}
                  </span>
                  <h3 class="mt-3 text-lg font-bold text-gray-900 transition group-hover:text-[#003274]">{{ tour.title }}</h3>
                  <p class="mt-1.5 flex items-center gap-1 text-sm text-gray-500">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    {{ tour.start_city }}
                  </p>
                  <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
                    <p class="text-lg font-bold text-[#003274]">
                      <template v-if="tour.price_from > 0">от {{ formatPrice(tour.price_from) }} &#8381;</template>
                      <template v-else><span class="font-semibold text-green-600">Бесплатно</span></template>
                    </p>
                  </div>
                </div>
              </RCard>
            </Link>
          </div>
          <button @click="scrollTours(-1)" class="absolute -left-4 top-1/2 z-10 hidden -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block">
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
          </button>
          <button @click="scrollTours(1)" class="absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 cursor-pointer rounded-full bg-white p-3 shadow-lg transition hover:bg-gray-50 lg:block">
            <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
          </button>
        </div>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

const props = defineProps({
  direction: { type: Object, required: true },
  content: { type: Object, required: true },
  featuredTours: { type: Array, default: () => [] },
  recipes: { type: Object, default: () => ({}) },
  recipeCities: { type: Array, default: () => [] },
  news: { type: Array, default: () => [] },
  recipeFilters: { type: Object, default: () => ({}) },
})

const recipeCity = ref(props.recipeFilters?.recipe_city ?? '')
const applicationSent = ref(false)

const appForm = reactive({
  name: '',
  email: '',
  phone: '',
  message: '',
})

const toursSlider = ref(null)
const toursSection = ref(null)
const mapContainer = ref(null)
const stagesContainer = ref(null)
const stagesLineHeight = ref('100%')

const details = computed(() => props.direction.free_participation_details)

const audienceStyles = [
  {
    iconBg: 'bg-sky-500',
    border: 'border-t-4 border-sky-500',
    iconPath: 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
  },
  {
    iconBg: 'bg-emerald-500',
    border: 'border-t-4 border-emerald-500',
    iconPath: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
  },
  {
    iconBg: 'bg-violet-500',
    border: 'border-t-4 border-violet-500',
    iconPath: 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418',
  },
]

function scrollTo(id) {
  document.getElementById(`section-${id}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function filterRecipes() {
  const params = {}
  if (recipeCity.value) params.recipe_city = recipeCity.value
  router.get(route('directions.show', props.direction.slug), params, {
    preserveState: true,
    preserveScroll: true,
    only: ['recipes', 'recipeFilters'],
  })
}

function submitApplication() {
  router.post(route('applications.store'), {
    type: 'atoms_vkusa',
    name: appForm.name,
    email: appForm.email,
    phone: appForm.phone,
    message: appForm.message,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      applicationSent.value = true
      appForm.name = appForm.email = appForm.phone = appForm.message = ''
    },
  })
}

function formatPrice(value) {
  if (!value) return '—'
  return new Intl.NumberFormat('ru-RU').format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function parseEmbed(url) {
  if (!url || typeof url !== 'string') return url
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{6,})/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  const rt = url.match(/rutube\.ru\/(?:video\/|play\/embed\/)([a-zA-Z0-9_-]+)/)
  if (rt) return `https://rutube.ru/play/embed/${rt[1]}`
  return url
}

function scrollToTours() {
  toursSection.value?.scrollIntoView({ behavior: 'smooth' })
}

function scrollTours(direction) {
  if (!toursSlider.value) return
  const cardWidth = toursSlider.value.firstElementChild?.offsetWidth ?? 300
  toursSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

function calcStagesLineHeight() {
  if (!stagesContainer.value) return
  const children = stagesContainer.value.querySelectorAll(':scope > div:not(:first-child)')
  if (!children.length) return
  const last = children[children.length - 1]
  const circleCenter = last.offsetTop + 24
  stagesLineHeight.value = circleCenter + 'px'
}

onMounted(async () => {
  nextTick(calcStagesLineHeight)
  if (!props.content.map_cities?.length || !mapContainer.value) return

  const YANDEX_API_KEY = window.__YANDEX_MAPS_KEY || ''
  const src = `https://api-maps.yandex.ru/2.1/?apikey=${YANDEX_API_KEY}&lang=ru_RU`

  if (!window.ymaps) {
    await new Promise((resolve, reject) => {
      const s = document.createElement('script')
      s.src = src
      s.onload = resolve
      s.onerror = reject
      document.head.appendChild(s)
    })
  }

  window.ymaps.ready(() => {
    const map = new window.ymaps.Map(mapContainer.value, {
      center: [60, 80],
      zoom: 3,
      controls: ['zoomControl'],
    })

    props.content.map_cities.forEach(city => {
      const placemark = new window.ymaps.Placemark(
        [city.lat, city.lng],
        {
          hintContent: city.name,
          balloonContentHeader: city.name,
          balloonContentBody: city.recipe_title
            ? `<div style="max-width:250px">` +
              (city.recipe_image ? `<img src="${city.recipe_image}" style="width:100%;border-radius:8px;margin-bottom:8px" />` : '') +
              `<p style="font-weight:600">${city.recipe_title}</p></div>`
            : '',
        },
        {
          preset: 'islands#redFoodIcon',
        },
      )
      map.geoObjects.add(placemark)
    })

    map.setBounds(map.geoObjects.getBounds(), { checkZoomRange: true, zoomMargin: 50 })
  })
})
</script>
