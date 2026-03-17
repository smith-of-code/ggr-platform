<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Геймификация</h1>
        <p class="mt-1 text-sm text-gray-500">Правила начисления баллов</p>
      </div>
      <RButton variant="primary" @click="showManualDialog = true">
        <template #icon>
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        </template>
        Начислить баллы
      </RButton>
    </div>

    <RCard flush class="mb-6">
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
              <RBadge :variant="r.is_active ? 'success' : 'neutral'">
                {{ r.is_active ? 'Активно' : 'Выкл' }}
              </RBadge>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('lms.admin.gamification.edit', [event.slug, r.id])" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <RButton variant="danger" size="sm" iconOnly @click="confirmDestroy(r)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </template>
                </RButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="rules.data.length === 0" class="px-5 py-16 text-center text-sm text-gray-500">Правил пока нет</div>
    </RCard>

    <Link :href="route('lms.admin.gamification.create', event.slug)" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
      Создать правило
    </Link>

    <RModal v-model="showManualDialog" title="Начислить баллы" size="md" @update:model-value="(v) => { if (!v) { manualForm.user_ids = []; manualForm.points = 0; manualForm.reason = '' } }">
      <form @submit.prevent="submitManual" class="space-y-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Участники</label>
          <div class="max-h-40 space-y-2 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-3">
            <div v-for="u in users" :key="u.id" class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition hover:bg-gray-100">
              <RCheckbox
                :model-value="manualForm.user_ids.includes(u.id)"
                @update:model-value="(v) => { if (v) manualForm.user_ids.push(u.id); else manualForm.user_ids = manualForm.user_ids.filter(id => id !== u.id) }"
                :label="`${u.name} (${u.email})`"
              />
            </div>
          </div>
          <p v-if="users?.length === 0" class="text-sm text-gray-500">Нет участников в событии</p>
        </div>
        <RInput v-model.number="manualForm.points" label="Баллы" type="number" required />
        <RInput v-model="manualForm.reason" label="Причина" required placeholder="За что начислены баллы" />
      </form>
      <template #footer>
        <RButton variant="outline" @click="showManualDialog.value = false">Отмена</RButton>
        <RButton variant="primary" :disabled="manualForm.user_ids.length === 0 || !manualForm.points || !manualForm.reason?.trim()" @click="submitManual">
          Начислить
        </RButton>
      </template>
    </RModal>
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
  router.post(route('lms.admin.gamification.manual-points', props.event.slug), manualForm, {
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
    router.delete(route('lms.admin.gamification.destroy', [props.event.slug, rule.id]))
  }
}
</script>
