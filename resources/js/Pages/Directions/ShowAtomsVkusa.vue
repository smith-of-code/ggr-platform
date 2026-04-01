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

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

      <!-- Этапы конкурса -->
      <section v-if="content.competition_stages?.length" id="section-stages" class="py-16">
        <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Этапы конкурса</h2>
        <div class="relative mx-auto mt-12 max-w-3xl">
          <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-[#003274]/20 sm:left-1/2 sm:-translate-x-px" />
          <div v-for="(stage, i) in content.competition_stages" :key="i" class="relative mb-10 pl-16 sm:mb-12 sm:pl-0" :class="i % 2 === 0 ? 'sm:pr-[calc(50%+2rem)]' : 'sm:pl-[calc(50%+2rem)]'">
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
        <div class="mt-10 flex flex-wrap items-center justify-center gap-8">
          <a v-for="(p, i) in content.partners" :key="i" :href="p.url || '#'" target="_blank" rel="noopener" class="group flex flex-col items-center gap-2">
            <div class="flex h-20 w-32 items-center justify-center rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition group-hover:shadow-md">
              <img v-if="p.logo" :src="p.logo" :alt="p.name" class="max-h-full max-w-full object-contain" />
              <span v-else class="text-sm font-medium text-gray-500">{{ p.name }}</span>
            </div>
            <span class="text-xs text-gray-500">{{ p.name }}</span>
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
import { ref, reactive, onMounted, nextTick } from 'vue'
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
const mapContainer = ref(null)

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

function scrollTours(direction) {
  if (!toursSlider.value) return
  const cardWidth = toursSlider.value.firstElementChild?.offsetWidth ?? 300
  toursSlider.value.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' })
}

// Yandex Maps
onMounted(async () => {
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
