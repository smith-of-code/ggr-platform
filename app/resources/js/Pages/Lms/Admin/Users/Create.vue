<template>
  <LmsAdminLayout :event="event">
    <div class="mx-auto max-w-3xl">
      <div class="mb-8">
        <button
          @click="router.visit(route('lms.admin.users.index', event.slug))"
          class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-rosatom-600"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          Назад к участникам
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Добавить участника</h1>
        <p class="mt-1 text-sm text-gray-500">Создайте нового пользователя и назначьте ему роль и курсы</p>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Personal info -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Личные данные</h2>
          <div class="grid gap-5 sm:grid-cols-3">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Фамилия <span class="text-red-400">*</span></label>
              <input v-model="form.last_name" type="text" required placeholder="Иванов"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                :class="{ 'border-red-400': form.errors.last_name }"
              />
              <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Имя <span class="text-red-400">*</span></label>
              <input v-model="form.first_name" type="text" required placeholder="Иван"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                :class="{ 'border-red-400': form.errors.first_name }"
              />
              <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Отчество</label>
              <input v-model="form.patronymic" type="text" placeholder="Иванович"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              />
            </div>
          </div>
        </div>

        <!-- Contact info -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Контактные данные</h2>
          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Email <span class="text-red-400">*</span></label>
              <input v-model="form.email" type="email" required placeholder="ivanov@example.com"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
                :class="{ 'border-red-400': form.errors.email }"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Телефон</label>
              <input v-model="form.phone" type="tel" placeholder="+7 (900) 123-45-67"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              />
            </div>
          </div>
        </div>

        <!-- Role & position -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Роль и должность</h2>
          <div class="grid gap-5 sm:grid-cols-2">
            <SearchSelect
              v-model="form.role_id"
              :options="roles"
              value-key="id"
              label-key="name"
              label="Роль"
              placeholder="Выберите роль"
              :error="form.errors.role_id"
            />
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Должность</label>
              <input v-model="form.position" type="text" placeholder="Менеджер проектов"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
              />
            </div>
          </div>
        </div>

        <!-- Course assignment -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Назначение курсов</h2>
          <MultiSelect
            v-model="form.course_ids"
            :options="courses"
            value-key="id"
            label-key="title"
            label="Курсы"
            placeholder="Выберите курсы для назначения"
          />
        </div>

        <!-- Password -->
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Пароль</h2>
          <div class="max-w-md">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Пароль (оставьте пустым для автогенерации)</label>
            <input v-model="form.password" type="text" placeholder="Сгенерируется автоматически"
              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:outline-none focus:ring-2 focus:ring-rosatom-500/20"
            />
            <p class="mt-1.5 text-xs text-gray-400">Минимум 6 символов. Пароль будет показан после создания.</p>
          </div>
        </div>

        <div class="flex items-center gap-3 pb-8">
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-xl bg-rosatom-600 px-8 py-3 text-sm font-semibold text-white transition hover:bg-rosatom-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Сохранение...' : 'Создать участника' }}
          </button>
          <button
            type="button"
            @click="router.visit(route('lms.admin.users.index', event.slug))"
            class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
          >
            Отмена
          </button>
        </div>
      </form>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  event: Object,
  roles: Array,
  courses: Array,
})

const form = useForm({
  last_name: '',
  first_name: '',
  patronymic: '',
  email: '',
  phone: '',
  position: '',
  role_id: null,
  course_ids: [],
  password: '',
})

function submit() {
  form.post(route('lms.admin.users.store', props.event.slug))
}
</script>
