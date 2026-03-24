<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8">
      <Link :href="route('lms.admin.forms.index', event.slug)" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Назад к формам
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ form.title }}</h1>
          <p class="mt-1 text-sm text-gray-400">{{ submissions?.total ?? 0 }} ответов</p>
        </div>
        <div class="flex gap-2">
          <RButton v-if="form.create_users && selectedIds.length > 0" variant="primary" @click="createUsers">
            Создать пользователей ({{ selectedIds.length }})
          </RButton>
          <Link :href="route('lms.admin.forms.edit', [event.slug, form.id])">
            <RButton variant="outline">Редактировать</RButton>
          </Link>
        </div>
      </div>
    </div>

    <!-- Embed code -->
    <RCard class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-900">Ссылка для прохождения</p>
          <p class="mt-0.5 text-xs text-gray-400">{{ embedUrl }}</p>
        </div>
        <RButton variant="outline" size="sm" @click="copyToClipboard(embedUrl)">Копировать ссылку</RButton>
      </div>

      <div class="mt-4">
        <div class="mb-2 flex gap-2">
          <button
            v-for="m in embedModes"
            :key="m.key"
            :class="['rounded-md px-3 py-1.5 text-xs font-medium transition', embedMode === m.key ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
            @click="embedMode = m.key"
          >
            {{ m.label }}
          </button>
        </div>
        <p class="mb-1 text-xs text-gray-400">{{ embedMode === 'script' ? 'Форма отрисуется прямо на странице (рекомендуется)' : 'Форма откроется во фрейме' }}</p>
        <div class="relative">
          <pre class="overflow-x-auto rounded-lg bg-gray-900 p-3 text-xs text-green-400">{{ embedMode === 'script' ? embedScript : embedIframe }}</pre>
          <button
            type="button"
            class="absolute right-2 top-2 rounded bg-gray-700 px-2 py-1 text-xs text-gray-300 hover:bg-gray-600"
            @click="copyToClipboard(embedMode === 'script' ? embedScript : embedIframe)"
          >
            Копировать
          </button>
        </div>
      </div>
    </RCard>

    <!-- Tabs -->
    <div class="mb-6 flex rounded-xl bg-gray-100 p-1">
      <button
        v-for="t in tabs"
        :key="t"
        :class="['rounded-lg px-4 py-2 text-sm font-medium transition', activeTab === t ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
        @click="activeTab = t"
      >
        {{ t }}
      </button>
    </div>

    <!-- Responses table -->
    <div v-show="activeTab === 'Ответы'">
      <RCard flush>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50">
                <th v-if="form.create_users" class="w-10 px-3 py-3">
                  <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-gray-300" />
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">#</th>
                <th v-for="f in form.fields" :key="f.id" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                  {{ f.label }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Дата</th>
                <th v-if="form.create_users" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Пользователь</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="sub in submissions?.data" :key="sub.id" class="hover:bg-gray-50">
                <td v-if="form.create_users" class="px-3 py-3">
                  <input v-if="!sub.user_created" type="checkbox" v-model="selectedIds" :value="sub.id" class="rounded border-gray-300" />
                  <svg v-else class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ sub.id }}</td>
                <td v-for="f in form.fields" :key="f.id" class="px-4 py-3 text-sm text-gray-700">
                  {{ getResponseValue(sub, f.id) }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ formatDate(sub.created_at) }}</td>
                <td v-if="form.create_users" class="px-4 py-3 text-xs">
                  <RBadge v-if="sub.user_created" variant="success" size="sm">Создан</RBadge>
                  <span v-else class="text-gray-400">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="!submissions?.data?.length" class="px-5 py-12 text-center text-sm text-gray-400">Ответов пока нет</div>
      </RCard>
    </div>

    <!-- Statistics -->
    <div v-show="activeTab === 'Статистика'">
      <div class="space-y-6">
        <RCard v-for="f in form.fields" :key="f.id">
          <h3 class="mb-3 font-bold text-gray-900">{{ f.label }}</h3>
          <div v-if="fieldStats[f.id]?.length">
            <div v-for="stat in fieldStats[f.id]" :key="stat.value" class="mb-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-700">{{ stat.value || '(пусто)' }}</span>
                <span class="font-medium text-gray-900">{{ stat.cnt }}</span>
              </div>
              <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-rosatom-500" :style="{ width: statPct(f.id, stat.cnt) + '%' }" />
              </div>
            </div>
          </div>
          <div v-else class="text-xs text-gray-400">
            {{ ['select', 'radio', 'checkbox', 'rating'].includes(f.type) ? 'Нет данных' : 'Текстовое поле — статистика по вариантам недоступна' }}
          </div>
        </RCard>
      </div>
    </div>
  </LmsAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({
  event: Object,
  form: Object,
  submissions: Object,
  fieldStats: Object,
  embedUrl: String,
  embedScript: String,
  embedIframe: String,
})

const tabs = ['Ответы', 'Статистика']
const activeTab = ref('Ответы')
const embedModes = [
  { key: 'script', label: 'Script (виджет)' },
  { key: 'iframe', label: 'iFrame' },
]
const embedMode = ref('script')
const selectedIds = ref([])

const allSelected = computed(() => {
  const eligible = (props.submissions?.data || []).filter(s => !s.user_created)
  return eligible.length > 0 && eligible.every(s => selectedIds.value.includes(s.id))
})

function toggleAll(e) {
  if (e.target.checked) {
    selectedIds.value = (props.submissions?.data || []).filter(s => !s.user_created).map(s => s.id)
  } else {
    selectedIds.value = []
  }
}

function getResponseValue(sub, fieldId) {
  const resp = (sub.responses || []).find(r => r.lms_form_field_id === fieldId)
  return resp?.value ?? '—'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function statPct(fieldId, cnt) {
  const stats = props.fieldStats?.[fieldId] || []
  const max = Math.max(...stats.map(s => s.cnt), 1)
  return Math.round(cnt / max * 100)
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).catch(() => {})
}

function createUsers() {
  router.post(route('lms.admin.forms.create-users', [props.event.slug, props.form.id]), {
    submission_ids: selectedIds.value,
  }, {
    onSuccess: () => { selectedIds.value = [] },
  })
}
</script>
