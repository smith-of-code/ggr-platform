<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Страница ВШГР</h1>
      <p class="mt-1 text-sm text-gray-500">Редактирование лендинга /vshgr (каталог программ)</p>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <RCard elevation="raised">
        <SectionHeader title="Hero" />
        <div class="mt-4 grid gap-4">
          <RInput v-model="form.hero_eyebrow" label="Надзаголовок *" :error="form.errors.hero_eyebrow" />
          <RInput v-model="form.hero_title" label="Заголовок *" :error="form.errors.hero_title" />
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Описание *</label>
            <textarea
              v-model="form.hero_description"
              rows="4"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              :class="{ 'border-red-500': form.errors.hero_description }"
            />
            <p v-if="form.errors.hero_description" class="mt-1 text-xs text-red-600">{{ form.errors.hero_description }}</p>
          </div>
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Каталог программ" />
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <RInput v-model="form.catalog_title" label="Заголовок *" :error="form.errors.catalog_title" />
          <RInput v-model="form.catalog_subtitle" label="Подзаголовок *" :error="form.errors.catalog_subtitle" />
        </div>
        <div class="mt-4">
          <RInput v-model="form.catalog_empty_text" label="Текст при пустом каталоге *" :error="form.errors.catalog_empty_text" />
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Блок анонсов" />
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <RInput v-model="form.announcements_title" label="Заголовок *" :error="form.errors.announcements_title" />
          <RInput v-model="form.announcements_subtitle" label="Подзаголовок *" :error="form.errors.announcements_subtitle" />
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Призыв к действию (CTA)" />
        <div class="mt-4 grid gap-4">
          <RInput v-model="form.cta_title" label="Заголовок *" :error="form.errors.cta_title" />
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Текст *</label>
            <textarea
              v-model="form.cta_body"
              rows="2"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              :class="{ 'border-red-500': form.errors.cta_body }"
            />
            <p v-if="form.errors.cta_body" class="mt-1 text-xs text-red-600">{{ form.errors.cta_body }}</p>
          </div>
          <RInput v-model="form.cta_button_label" label="Текст кнопки *" :error="form.errors.cta_button_label" />
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Положение" />
        <div class="mt-4 grid gap-4">
          <RInput v-model="form.regulation_url" label="URL файла *" :error="form.errors.regulation_url" />
          <RInput v-model="form.regulation_button_label" label="Текст кнопки *" :error="form.errors.regulation_button_label" />
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Подпись под кнопкой *</label>
            <textarea
              v-model="form.regulation_caption"
              rows="2"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#003274] focus:outline-none focus:ring-2 focus:ring-[#003274]/20"
              :class="{ 'border-red-500': form.errors.regulation_caption }"
            />
            <p v-if="form.errors.regulation_caption" class="mt-1 text-xs text-red-600">{{ form.errors.regulation_caption }}</p>
          </div>
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Форма заявки" />
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <RInput v-model="form.form_title" label="Заголовок *" :error="form.errors.form_title" />
          <RInput v-model="form.form_subtitle" label="Подзаголовок *" :error="form.errors.form_subtitle" />
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Социальные сети — заголовки секции" />
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <RInput v-model="form.socials_title" label="Заголовок *" :error="form.errors.socials_title" />
          <RInput v-model="form.socials_subtitle" label="Подзаголовок *" :error="form.errors.socials_subtitle" />
        </div>
      </RCard>

      <RCard elevation="raised">
        <SectionHeader title="Социальные сети" />
        <p class="mb-3 text-xs text-gray-500">Пустой список скрывает блок на сайте</p>
        <DynamicList
          v-model="form.socials"
          :fields="[
            { key: 'name', label: 'Название', placeholder: 'ВКонтакте' },
            { key: 'url', label: 'Ссылка', placeholder: 'https://vk.com/...' },
            { key: 'icon', label: 'Иконка', type: 'icon-select', options: socialIconOptions, iconMap: socialIconMap },
          ]"
          add-label="Добавить соцсеть"
          :new-item="{ name: '', url: '', icon: 'vk' }"
        />
        <p v-if="form.errors.socials" class="mt-2 text-xs text-red-600">{{ form.errors.socials }}</p>
      </RCard>

      <div class="flex items-center gap-4">
        <RButton variant="primary" type="submit" :loading="form.processing" :disabled="form.processing">
          Сохранить все изменения
        </RButton>
        <Transition
          enter-active-class="transition duration-200"
          enter-from-class="opacity-0"
          leave-active-class="transition duration-200"
          leave-to-class="opacity-0"
        >
          <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Сохранено</p>
        </Transition>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SectionHeader from '@/Pages/Admin/OpportunityToursPage/SectionHeader.vue'
import DynamicList from '@/Pages/Admin/OpportunityToursPage/DynamicList.vue'
import { socialIcon, socialIconKeys } from '@/utils/opportunityToursIcons'

const props = defineProps({
  pageData: { type: Object, default: () => ({}) },
})

const socialIconOptions = [
  { value: 'vk', label: 'ВКонтакте' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'rutube', label: 'Рутьюб' },
  { value: 'ok', label: 'Одноклассники' },
  { value: 'dzen', label: 'Дзен' },
]

const socialIconMap = Object.fromEntries(
  socialIconKeys.map(k => [k, socialIcon(k, 'h-5 w-5')])
)

const d = props.pageData

const form = useForm({
  hero_eyebrow: d.hero_eyebrow ?? '',
  hero_title: d.hero_title ?? '',
  hero_description: d.hero_description ?? '',
  catalog_title: d.catalog_title ?? '',
  catalog_subtitle: d.catalog_subtitle ?? '',
  catalog_empty_text: d.catalog_empty_text ?? '',
  announcements_title: d.announcements_title ?? '',
  announcements_subtitle: d.announcements_subtitle ?? '',
  cta_title: d.cta_title ?? '',
  cta_body: d.cta_body ?? '',
  cta_button_label: d.cta_button_label ?? '',
  regulation_url: d.regulation_url ?? '',
  regulation_button_label: d.regulation_button_label ?? '',
  regulation_caption: d.regulation_caption ?? '',
  form_title: d.form_title ?? '',
  form_subtitle: d.form_subtitle ?? '',
  socials_title: d.socials_title ?? '',
  socials_subtitle: d.socials_subtitle ?? '',
  socials: Array.isArray(d.socials) ? d.socials.map(s => ({
    name: s.name ?? '',
    url: s.url ?? '',
    icon: s.icon ?? 'vk',
  })) : [],
})

function submit() {
  form.put(route('admin.vshgr-page.update'), {
    preserveScroll: true,
  })
}
</script>
