<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Геймификация</h1>
        <p class="mt-1 text-sm text-gray-500">Правила начисления баллов</p>
      </div>
      <button @click="showManualDialog = true" type="button" class="flex items-center gap-2 rounded-xl bg-rosatom-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rosatom-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Начислить баллы
      </button>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50">
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Название</th>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Действие</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Макс. раз</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Авто</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Статус</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="r in rules.data" :key="r.id" class="transition hover:bg-gray-50">
            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ r.title }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ r.action ?? '—' }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ r.points }}</td>
            <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ r.max_times ?? '∞' }}</td>
            <td class="px-5 py-3.5 text-center">
              <span :class="r.is_auto ? 'text-rosatom-600' : 'text-gray-400'" class="text-sm">{{ r.is_auto ? 'Да' : 'Нет' }}</span>
            </td>
            <td class="px-5 py-3.5 text-center">
              <span :class="r.is_active ? 'bg-rosatom-50 text-rosatom-700' : 'bg-gray-100 text-gray-500'" class="rounded-full px-3 py-1 text-xs font-semibold">
                {{ r.is_active ? 'Активно' : 'Выкл' }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('lms.admin.gamification.edit', [event.id, r.id])" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <button @click="confirmDestroy(r)" class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79" /></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="rules.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Правил пока нет</div>
    </div>

    <Link :href="route('lms.admin.gamification.create', event.id)" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
      Создать правило
    </Link>

    <!-- Manual Points Dialog -->
    <div v-show="showManualDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showManualDialog = false">
      <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-xl" @click.stop>
        <h3 class="mb-4 text-lg font-bold text-gray-900">Начислить баллы</h3>
        <form @submit.prevent="submitManual" class="space-y-4">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Участники</label>
            <div class="max-h-40 space-y-2 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-3">
              <label v-for="u in users" :key="u.id" class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-gray-100">
                <input v-model="manualForm.user_ids" type="checkbox" :value="u.id" class="h-4 w-4 rounded border-gray-300 text-rosatom-600" />
                <span class="text-sm text-gray-900">{{ u.name }} ({{ u.email }})</span>
              </label>
            </div>
            <p v-if="users?.length === 0" class="text-sm text-gray-500">Нет участников в событии</p>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Баллы</label>
            <input v-model.number="manualForm.points" type="number" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" />
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Причина</label>
            <input v-model="manualForm.reason" type="text" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20" placeholder="За что начислены баллы" />
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showManualDialog = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Отмена</button>
            <button type="submit" :disabled="manualForm.user_ids.length === 0" class="rounded-xl bg-rosatom-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rosatom-700 disabled:opacity-50">
              Начислить
            </button>
          </div>
        </form>
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({ event: Object, rules: Object, users: Array })

const showManualDialog = ref(false)
const manualForm = reactive({ user_ids: [], points: 0, reason: '' })

function submitManual() {
  router.post(route('lms.admin.gamification.manual-points', props.event.id), manualForm, {
    onSuccess: () => {
      showManualDialog.value = false
      manualForm.user_ids = []
      manualForm.points = 0
      manualForm.reason = ''
    },
  })
}

function confirmDestroy(rule) {
  if (confirm(`Удалить правило "${rule.title}"?`)) {
    router.delete(route('lms.admin.gamification.destroy', [props.event.id, rule.id]))
  }
}
</script>
