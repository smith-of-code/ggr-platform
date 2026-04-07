<template>
  <LmsAdminLayout :event="event">
    <div class="mx-auto max-w-3xl">
      <div class="mb-8">
        <RButton variant="ghost" size="sm" @click="router.visit(route('lms.admin.users.index', event.slug))" class="mb-4">
          <template #icon><ArrowLeftIcon class="h-4 w-4" /></template>
          Назад к участникам
        </RButton>
        <h1 class="text-2xl font-bold text-gray-900">Добавить участника</h1>
        <p class="mt-1 text-sm text-gray-500">Создайте нового пользователя и назначьте ему роль и программы</p>
      </div>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Personal info -->
        <RCard elevation="raised">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Личные данные</h2>
          <div class="grid gap-5 sm:grid-cols-3">
            <RInput v-model="form.last_name" label="Фамилия" placeholder="Иванов" :error="form.errors.last_name" required />
            <RInput v-model="form.first_name" label="Имя" placeholder="Иван" :error="form.errors.first_name" required />
            <RInput v-model="form.patronymic" label="Отчество" placeholder="Иванович" />
          </div>
        </RCard>

        <!-- Contact info -->
        <RCard elevation="raised">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Контактные данные</h2>
          <div class="grid gap-5 sm:grid-cols-2">
            <RInput v-model="form.email" type="email" label="Email" placeholder="ivanov@example.com" :error="form.errors.email" required />
            <RInput v-model="form.phone" type="tel" label="Телефон" placeholder="+7 (900) 123-45-67" />
          </div>
        </RCard>

        <!-- Role & position -->
        <RCard elevation="raised">
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
            <RInput v-model="form.position" label="Должность" placeholder="Менеджер проектов" />
          </div>
        </RCard>

        <!-- Course assignment -->
        <RCard elevation="raised">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Назначение программ</h2>
          <MultiSelect
            v-model="form.course_ids"
            :options="courses"
            value-key="id"
            label-key="title"
            label="Программы"
            placeholder="Выберите программы для назначения"
          />
        </RCard>

        <!-- Password -->
        <RCard elevation="raised">
          <h2 class="mb-5 text-lg font-bold text-gray-900">Пароль</h2>
          <div class="max-w-md">
            <RInput v-model="form.password" label="Пароль (оставьте пустым для автогенерации)" placeholder="Сгенерируется автоматически" hint="Минимум 6 символов. Пароль будет показан после создания." />
          </div>
        </RCard>

        <div class="flex items-center gap-3 pb-8">
          <RButton type="submit" variant="primary" :loading="form.processing" :disabled="form.processing">
            {{ form.processing ? 'Сохранение...' : 'Создать участника' }}
          </RButton>
          <RButton variant="outline" @click="router.visit(route('lms.admin.users.index', event.slug))">
            Отмена
          </RButton>
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
