<template>
  <AdminLayout>
    <div class="mb-8">
      <Link :href="route('admin.tours.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к турам
      </Link>
      <h1 class="text-2xl font-bold text-gray-900">{{ tour ? 'Редактировать тур' : 'Новый тур' }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left column -->
        <div class="space-y-6 lg:col-span-2">
          <RCard elevation="raised">
            <div class="space-y-6">
              <h2 class="text-base font-bold text-gray-900">Основная информация</h2>
              <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                  <RInput v-model="form.title" label="Название *" :error="form.errors.title" required />
                </div>
                <RInput v-model="form.slug" label="Slug" />
                <RInput v-model="form.start_city" label="Город старта" />
                <div class="sm:col-span-2">
                  <RichTextEditor v-model="form.description" label="Описание тура" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
                </div>
                <RInput v-model="form.duration" label="Продолжительность" placeholder="2 дня, 1 ночь" />
                <RInput v-model="form.group_size" label="Размер группы" placeholder="до 30 человек" />
                <RInput v-model.number="form.min_age" label="Мин. возраст" type="number" />
                <div>
                  <label class="mb-2 block text-sm font-semibold text-gray-700">Цена от (₽)</label>
                  <input v-model.number="form.price_from" type="number" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
                </div>
                <div>
                  <label class="mb-2 block text-sm font-semibold text-gray-700">Проект</label>
                  <select v-model="form.project" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                    <option value="">—</option>
                    <option value="start_atomgrad">Старт в Атомград</option>
                    <option value="atoms_vkusa">Атомы вкуса</option>
                    <option value="llr">Лучшие люди Росатома</option>
                  </select>
                </div>
                <div>
                  <label class="mb-2 block text-sm font-semibold text-gray-700">Варианты участия</label>
                  <select v-model="form.participation_type" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                    <option value="">—</option>
                    <option value="bchp">Есть сертификат «Больше, чем путешествие»</option>
                    <option value="contest">Принять участие в конкурсе</option>
                    <option value="paid">Поехать за свой счёт</option>
                  </select>
                </div>
                <div>
                  <label class="mb-2 block text-sm font-semibold text-gray-700">Сезон</label>
                  <select v-model="form.season" class="w-full cursor-pointer appearance-none rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                    <option value="">—</option>
                    <option value="winter">Зима</option>
                    <option value="spring">Весна</option>
                    <option value="summer">Лето</option>
                    <option value="autumn">Осень</option>
                  </select>
                </div>
                <div>
                  <label class="mb-2 block text-sm font-semibold text-gray-700">Позиция</label>
                  <input v-model.number="form.position" type="number" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
                </div>
              </div>
              <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                <RCheckbox v-for="opt in checkboxOptions" :key="opt.key" v-model="form[opt.key]" :label="opt.label" />
              </div>
            </div>
          </RCard>

          <!-- Program days -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">Программа по дням</h2>
                <div v-if="form.program_pdf" class="flex items-center gap-2">
                  <a :href="form.program_pdf" target="_blank" class="text-sm text-[#003274] hover:underline">PDF загружен</a>
                  <button type="button" class="text-red-400 hover:text-red-600" @click="form.program_pdf = ''">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              </div>

              <div v-for="(day, di) in form.program_days" :key="di" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="mb-2 flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-500">День {{ di + 1 }}</span>
                  <button type="button" class="text-red-400 hover:text-red-600" @click="form.program_days.splice(di, 1)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div class="space-y-3">
                  <RInput v-model="day.title" label="Заголовок" placeholder="Прибытие и знакомство" />
                  <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                    <textarea v-model="day.description" rows="3" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Описание дня..." />
                  </div>
                </div>
              </div>

              <div class="flex gap-2">
                <button type="button" class="flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-3 text-sm font-medium text-gray-500 transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03] hover:text-[#003274]" @click="form.program_days.push({ title: '', description: '' })">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                  Добавить день
                </button>
                <button type="button" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="$refs.programPdfInput.click()">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                  {{ pdfUploading === 'program' ? 'Загрузка…' : 'Загрузить PDF' }}
                </button>
                <input ref="programPdfInput" type="file" accept=".pdf" class="hidden" @change="e => uploadPdf(e, 'program_pdf')" />
              </div>
            </div>
          </RCard>

          <!-- Accommodations -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Проживание</h2>

              <div v-for="(acc, ai) in form.accommodations" :key="ai" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-500">Вариант {{ ai + 1 }}</span>
                  <button type="button" class="text-red-400 hover:text-red-600" @click="form.accommodations.splice(ai, 1)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div class="space-y-3">
                  <div class="grid items-end gap-3 sm:grid-cols-2">
                    <RInput v-model="acc.title" label="Название" placeholder="Гостиница «Атомик»" />
                    <div>
                      <label class="mb-1 block text-sm font-semibold text-gray-700">Категория бюджета</label>
                      <select v-model="acc.budget" class="w-full cursor-pointer appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10">
                        <option value="">—</option>
                        <option value="economy">Эконом</option>
                        <option value="standard">Стандарт</option>
                        <option value="comfort">Комфорт</option>
                        <option value="luxury">Люкс</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Описание</label>
                    <textarea v-model="acc.description" rows="2" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" placeholder="Описание проживания..." />
                  </div>
                  <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Фото</label>
                    <div class="flex flex-wrap gap-2">
                      <div v-for="(img, ii) in acc.images" :key="ii" class="group relative h-20 w-28 overflow-hidden rounded-lg border border-gray-200">
                        <img :src="img" alt="" class="h-full w-full object-cover" />
                        <button type="button" class="absolute right-1 top-1 rounded-full bg-red-500/90 p-0.5 text-white opacity-0 transition group-hover:opacity-100" @click="acc.images.splice(ii, 1)">
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                      </div>
                      <button type="button" class="flex h-20 w-28 cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-200 text-gray-400 transition hover:border-[#003274]/40 hover:text-[#003274]" @click="$refs[`accImgInput${ai}`][0].click()" title="Загрузить">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                      <button type="button" class="flex h-20 w-28 cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-200 text-gray-400 transition hover:border-[#003274]/40 hover:text-[#003274]" title="Из библиотеки" @click="accPickerIndex = ai; showAccPicker = true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" /></svg>
                      </button>
                      <input :ref="`accImgInput${ai}`" type="file" accept="image/*" multiple class="hidden" @change="e => uploadAccImages(e, ai)" />
                    </div>
                  </div>
                </div>
              </div>

              <button type="button" class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-3 text-sm font-medium text-gray-500 transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03] hover:text-[#003274]" @click="form.accommodations.push({ title: '', budget: '', description: '', images: [] })">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Добавить вариант проживания
              </button>
            </div>
          </RCard>

          <!-- Memo -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">Памятка участника</h2>
                <div v-if="form.memo_pdf" class="flex items-center gap-2">
                  <a :href="form.memo_pdf" target="_blank" class="text-sm text-[#003274] hover:underline">PDF загружен</a>
                  <button type="button" class="text-red-400 hover:text-red-600" @click="form.memo_pdf = ''">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              </div>
              <RichTextEditor v-model="form.memo_text" label="Текст памятки" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
              <button type="button" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="$refs.memoPdfInput.click()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                {{ pdfUploading === 'memo' ? 'Загрузка…' : 'Загрузить PDF памятки' }}
              </button>
              <input ref="memoPdfInput" type="file" accept=".pdf" class="hidden" @change="e => uploadPdf(e, 'memo_pdf')" />
            </div>
          </RCard>

          <!-- Pass info -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Оформление пропуска</h2>
              <p class="text-sm text-gray-500">Информация об оформлении пропуска в город или на объекты (отображается если «Закрытый город» включен)</p>
              <RichTextEditor v-model="form.pass_info" label="Информация о пропуске" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>

          <!-- Conditions -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Условия участия</h2>
              <RichTextEditor v-model="form.conditions" label="Условия участия в туре" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>

          <!-- Cost info -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Стоимость и оплата</h2>
              <RichTextEditor v-model="form.cost_info" label="Информация о стоимости" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>

          <!-- Target audience -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Для кого этот тур</h2>
              <RichTextEditor v-model="form.target_audience" label="Целевая аудитория" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>

          <!-- Organizer info -->
          <RCard elevation="raised">
            <div class="space-y-4">
              <h2 class="text-base font-bold text-gray-900">Организатор</h2>
              <RichTextEditor v-model="form.organizer_info" label="Информация об организаторе" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
            </div>
          </RCard>
        </div>

        <!-- Right column -->
        <div class="space-y-6">
          <RCard elevation="raised">
            <ImageUploadCrop v-model="form.image" label="Изображение тура" :upload-url="route('admin.upload.image')" :media-picker-url="route('admin.media.index')" collection="tours" :entity-type="mediaEntityType" :entity-id="mediaEntityId" />
          </RCard>

          <!-- Gallery -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Галерея фото</h2>
            <div class="space-y-3">
              <div class="grid grid-cols-2 gap-2">
                <div v-for="(url, gi) in form.gallery" :key="gi" class="group relative aspect-video overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                  <img :src="url" alt="" class="h-full w-full object-cover" />
                  <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition group-hover:opacity-100">
                    <button type="button" class="rounded-lg bg-white/90 p-1.5 text-gray-700 transition hover:bg-white" title="Переместить влево" :disabled="gi === 0" @click="moveGalleryItem(gi, -1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    </button>
                    <button type="button" class="rounded-lg bg-white/90 p-1.5 text-gray-700 transition hover:bg-white" title="Переместить вправо" :disabled="gi === form.gallery.length - 1" @click="moveGalleryItem(gi, 1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                    <button type="button" class="rounded-lg bg-red-500/90 p-1.5 text-white transition hover:bg-red-600" title="Удалить" @click="form.gallery.splice(gi, 1)">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <div class="flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03]" @click="$refs.galleryInput.click()">
                  <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 3h18a1.5 1.5 0 0 1 1.5 1.5v15a1.5 1.5 0 0 1-1.5 1.5H3a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 3 3Z" />
                  </svg>
                  <span v-if="!galleryUploading" class="text-sm font-medium text-gray-500">Загрузить фото</span>
                  <span v-else class="text-sm font-medium text-[#003274]">Загрузка…</span>
                </div>
                <button type="button" class="flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-6 text-center transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03]" @click="showGalleryPicker = true">
                  <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                  </svg>
                  <span class="text-sm font-medium text-gray-500">Из библиотеки</span>
                </button>
              </div>
              <input ref="galleryInput" type="file" accept="image/*" multiple class="hidden" @change="uploadGalleryFiles" />
              <p v-if="galleryError" class="text-xs text-red-500">{{ galleryError }}</p>
            </div>
          </RCard>

          <!-- Videos -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Видео</h2>
            <div class="space-y-3">
              <div v-for="(vid, vi) in form.videos" :key="vi" class="space-y-2">
                <div class="flex gap-2">
                  <input v-model="form.videos[vi]" type="text" placeholder="Ссылка на YouTube, RuTube или VK Video" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition focus:border-[#003274] focus:bg-white focus:ring-[#003274]/10" />
                  <button type="button" class="shrink-0 rounded-xl border border-red-200 bg-white p-2.5 text-red-500 transition hover:bg-red-50" title="Удалить видео" @click="form.videos.splice(vi, 1)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  </button>
                </div>
                <div v-if="parseVideoEmbed(form.videos[vi])" class="overflow-hidden rounded-lg border border-gray-200">
                  <div class="aspect-video">
                    <iframe :src="parseVideoEmbed(form.videos[vi])" class="h-full w-full" allow="autoplay; encrypted-media" allowfullscreen />
                  </div>
                </div>
              </div>
            </div>
            <button type="button" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-3 text-sm font-medium text-gray-500 transition hover:border-[#003274]/40 hover:bg-[#003274]/[0.03] hover:text-[#003274]" @click="form.videos.push('')">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              Добавить видео
            </button>
            <p class="mt-2 text-xs text-gray-400">Поддерживается YouTube, RuTube и VK Video. Для фотографий используйте «Галерею фото» выше.</p>
          </RCard>

          <!-- Cities -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Города</h2>
            <div class="space-y-2">
              <label v-for="c in cities" :key="c.id" class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-gray-50" :class="form.city_ids.includes(c.id) ? 'bg-[#003274]/5' : ''">
                <input v-model="form.city_ids" type="checkbox" :value="c.id" class="h-4 w-4 rounded border-gray-300 text-[#003274] focus:ring-[#003274]/20" />
                <span class="text-sm font-medium text-gray-700">{{ c.name }}</span>
              </label>
            </div>
          </RCard>

          <!-- Departures -->
          <RCard elevation="raised">
            <h2 class="mb-4 text-base font-bold text-gray-900">Даты заездов</h2>
            <div class="space-y-3">
              <div v-for="(dep, i) in form.departures" :key="i" class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                <div class="space-y-2">
                  <input v-model="dep.start_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <input v-model="dep.end_date" type="date" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                  <div class="flex gap-2">
                    <input v-model.number="dep.price_per_person" type="number" placeholder="Цена ₽" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-sm" />
                    <RButton v-if="form.departures.length > 1" variant="danger" size="sm" icon-only @click="form.departures.splice(i, 1)">
                      <template #icon>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                      </template>
                    </RButton>
                  </div>
                </div>
              </div>
            </div>
            <RButton variant="outline" block class="mt-3" @click="form.departures.push({ start_date: '', end_date: '', price_per_person: '' })">
              <template #icon>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
              </template>
              Добавить дату
            </RButton>
          </RCard>
        </div>
      </div>

      <!-- Actions -->
      <div class="sticky bottom-0 z-10 -mx-4 border-t border-gray-200 bg-white/95 px-12 py-4 backdrop-blur sm:-mx-6">
        <div class="flex items-center gap-3">
          <RButton variant="primary" :loading="form.processing" :disabled="form.processing">
            Сохранить
          </RButton>
          <button type="button" class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="showPreview = true">
            <span class="flex items-center gap-1.5">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
              Предпросмотр
            </span>
          </button>
          <Link :href="route('admin.tours.index')" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Отмена</Link>
          <span v-if="form.recentlySuccessful" class="ml-auto text-sm font-medium text-green-600">Сохранено</span>
          <span v-else-if="form.isDirty" class="ml-auto text-sm text-amber-600">Есть несохранённые изменения</span>
        </div>
      </div>
    </form>

    <ContentPreview
      :open="showPreview"
      :title="form.title"
      :content="form.description"
      :image="form.image"
      :meta="tourMeta"
      @close="showPreview = false"
    />

    <MediaPickerModal
      :show="showGalleryPicker"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.image')"
      collection="tours"
      :entity-type="mediaEntityType"
      :entity-id="mediaEntityId"
      multiple
      @close="showGalleryPicker = false"
      @select="onGalleryMediaSelect"
    />

    <MediaPickerModal
      :show="showAccPicker"
      :api-url="route('admin.media.index')"
      :upload-url="route('admin.upload.image')"
      collection="tours"
      :entity-type="mediaEntityType"
      :entity-id="mediaEntityId"
      multiple
      @close="showAccPicker = false"
      @select="onAccMediaSelect"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import ImageUploadCrop from '@/Components/ImageUploadCrop.vue'
import ContentPreview from '@/Components/ContentPreview.vue'
import MediaPickerModal from '@/Components/MediaPickerModal.vue'

const props = defineProps({ tour: Object, cities: Array })
const showPreview = ref(false)
const mediaEntityType = 'App\\Models\\Tour'
const mediaEntityId = props.tour?.id || null
const pdfUploading = ref(null)
const showGalleryPicker = ref(false)
const showAccPicker = ref(false)
let accPickerIndex = null

const checkboxOptions = [
  { key: 'is_active', label: 'Активен' },
  { key: 'is_featured', label: 'На главной' },
  { key: 'for_children', label: 'С детьми' },
  { key: 'for_foreigners', label: 'Иностранцам' },
  { key: 'closed_city', label: 'Закрытый город' },
]

const tourMeta = computed(() => {
  const m = []
  if (form.start_city) m.push({ label: form.start_city, class: 'bg-blue-50 text-blue-700' })
  if (form.duration) m.push({ label: form.duration, class: 'bg-purple-50 text-purple-700' })
  if (form.price_from) m.push({ label: `от ${Number(form.price_from).toLocaleString('ru-RU')} ₽`, class: 'bg-green-50 text-green-700' })
  if (form.season) m.push({ label: { winter: 'Зима', spring: 'Весна', summer: 'Лето', autumn: 'Осень' }[form.season] || form.season })
  return m
})

const galleryUploading = ref(false)
const galleryError = ref('')

function parseVideoEmbed(url) {
  if (!url || typeof url !== 'string') return null
  const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{6,})/)
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`
  const rt = url.match(/rutube\.ru\/(?:video\/|play\/embed\/)([a-zA-Z0-9_-]+)/)
  if (rt) return `https://rutube.ru/play/embed/${rt[1]}`
  if (url.includes('vk.com/video_ext.php')) return url
  const vk = url.match(/(?:vk\.com|vkvideo\.ru)\/(?:video|clip)(-?\d+_\d+)/)
  if (vk) return `https://vk.com/video_ext.php?oid=${vk[1].split('_')[0]}&id=${vk[1].split('_')[1]}&hd=2`
  return null
}

async function uploadGalleryFiles(e) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  galleryUploading.value = true
  galleryError.value = ''
  const failed = []
  for (const file of files) {
    try {
      const fd = new FormData()
      fd.append('image', file)
      const { data } = await axios.post(route('admin.upload.image'), fd)
      if (data.url) form.gallery.push(data.url)
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.image?.[0]
      failed.push(file.name + (msg ? `: ${msg}` : ''))
    }
  }
  if (failed.length) {
    galleryError.value = `Не удалось загрузить: ${failed.join('; ')}`
  }
  galleryUploading.value = false
  e.target.value = ''
}

function moveGalleryItem(index, direction) {
  const newIndex = index + direction
  if (newIndex < 0 || newIndex >= form.gallery.length) return
  const arr = [...form.gallery]
  const tmp = arr[index]
  arr[index] = arr[newIndex]
  arr[newIndex] = tmp
  form.gallery = arr
}

async function uploadPdf(e, field) {
  const file = e.target.files?.[0]
  if (!file) return
  pdfUploading.value = field === 'program_pdf' ? 'program' : 'memo'
  try {
    const fd = new FormData()
    fd.append('file', file)
    const { data } = await axios.post(route('admin.upload.file'), fd)
    if (data.url) form[field] = data.url
  } catch { /* skip */ }
  pdfUploading.value = null
  e.target.value = ''
}

async function uploadAccImages(e, accIndex) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  const newImages = [...(form.accommodations[accIndex].images || [])]
  for (const file of files) {
    try {
      const fd = new FormData()
      fd.append('image', file)
      const { data } = await axios.post(route('admin.upload.image'), fd)
      if (data.url) newImages.push(data.url)
    } catch { /* skip */ }
  }
  const updated = [...form.accommodations]
  updated[accIndex] = { ...updated[accIndex], images: newImages }
  form.accommodations = updated
  e.target.value = ''
}

function onGalleryMediaSelect(urls) {
  const list = Array.isArray(urls) ? urls : [urls]
  form.gallery = [...form.gallery, ...list]
  showGalleryPicker.value = false
}

function onAccMediaSelect(urls) {
  const list = Array.isArray(urls) ? urls : [urls]
  if (accPickerIndex !== null) {
    const updated = [...form.accommodations]
    const acc = { ...updated[accPickerIndex] }
    acc.images = [...(acc.images || []), ...list]
    updated[accPickerIndex] = acc
    form.accommodations = updated
  }
  showAccPicker.value = false
}

const form = useForm({
  title: props.tour?.title ?? '',
  slug: props.tour?.slug ?? '',
  description: props.tour?.description ?? '',
  image: props.tour?.image ?? '',
  gallery: props.tour?.gallery ?? [],
  videos: props.tour?.videos?.length ? [...props.tour.videos] : [],
  start_city: props.tour?.start_city ?? '',
  duration: props.tour?.duration ?? '',
  group_size: props.tour?.group_size ?? '',
  min_age: props.tour?.min_age ?? null,
  project: props.tour?.project ?? '',
  participation_type: props.tour?.participation_type ?? '',
  season: props.tour?.season ?? '',
  price_from: props.tour?.price_from ?? null,
  position: props.tour?.position ?? 0,
  city_ids: props.tour?.cities?.map(c => c.id) ?? [],
  is_active: props.tour?.is_active ?? true,
  is_featured: props.tour?.is_featured ?? false,
  for_children: props.tour?.for_children ?? false,
  for_foreigners: props.tour?.for_foreigners ?? false,
  closed_city: props.tour?.closed_city ?? false,
  program_days: props.tour?.program_days ?? [],
  program_pdf: props.tour?.program_pdf ?? '',
  accommodations: props.tour?.accommodations ?? [],
  memo_text: props.tour?.memo_text ?? '',
  memo_pdf: props.tour?.memo_pdf ?? '',
  pass_info: props.tour?.pass_info ?? '',
  conditions: props.tour?.conditions ?? '',
  cost_info: props.tour?.cost_info ?? '',
  target_audience: props.tour?.target_audience ?? '',
  organizer_info: props.tour?.organizer_info ?? '',
  departures: props.tour?.departures?.length
    ? props.tour.departures.map(d => ({
        start_date: d.start_date?.slice(0, 10) ?? '',
        end_date: d.end_date?.slice(0, 10) ?? '',
        price_per_person: d.price_per_person ?? '',
      }))
    : [{ start_date: '', end_date: '', price_per_person: '' }],
})

function submit() {
  props.tour
    ? form.put(route('admin.tours.update', props.tour.id))
    : form.post(route('admin.tours.store'))
}
</script>
