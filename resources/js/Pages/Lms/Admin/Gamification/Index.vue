<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Геймификация</h1>
        <p class="mt-1 text-sm text-gray-500">Правила начисления, рейтинг и расшифровка баллов</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <a :href="route('lms.admin.gamification.export', event.slug)">
          <RButton variant="outline">
            <template #icon>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" /></svg>
            </template>
            Выгрузить Excel
          </RButton>
        </a>
        <RButton variant="primary" @click="showManualDialog = true">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          </template>
          Начислить баллы
        </RButton>
      </div>
    </div>

    <RCard flush class="mb-6">
      <template #header>
        <h2 class="text-base font-bold text-gray-900">Правила начисления</h2>
      </template>
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
                <Link v-if="canManageRules" :href="route('lms.admin.gamification.edit', [event.slug, r.id])" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                </Link>
                <RButton v-if="canManageRules" variant="danger" size="sm" iconOnly @click="confirmDestroy(r)">
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

    <Link v-if="canManageRules" :href="route('lms.admin.gamification.create', event.slug)" class="mb-6 inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
      Создать правило
    </Link>

    <RCard flush class="mb-6">
      <template #header>
        <h2 class="text-base font-bold text-gray-900">Рейтинг участников</h2>
        <p class="mt-0.5 text-xs text-gray-500">Без учёта роли admin в событии, топ 100 по сумме баллов</p>
      </template>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">#</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="row in leaderboard" :key="row.id">
              <tr class="transition hover:bg-gray-50">
                <td class="px-4 py-2.5 text-sm font-semibold text-gray-500">{{ row.rank }}</td>
                <td class="max-w-[200px] px-4 py-2.5">
                  <p class="truncate text-sm font-medium text-gray-900">{{ row.name }}</p>
                  <p class="truncate text-xs text-gray-400">{{ row.email }}</p>
                </td>
                <td class="px-4 py-2.5 text-right text-sm font-semibold text-rosatom-700">{{ row.total_points }}</td>
                <td class="px-4 py-2.5 text-right">
                  <RButton variant="outline" size="sm" @click="toggleExpandUser(row.id)">
                    {{ expandedUserId === row.id ? 'Скрыть' : 'Расшифровка' }}
                  </RButton>
                </td>
              </tr>
              <tr v-if="expandedUserId === row.id" class="bg-gray-50/95">
                <td colspan="4" class="px-4 py-3">
                  <div v-if="pointsForUser(row.id).length" class="max-h-72 overflow-y-auto rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full text-sm">
                      <thead class="sticky top-0 border-b border-gray-200 bg-gray-50">
                        <tr>
                          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Начисление</th>
                          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Дата</th>
                          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
                          <th v-if="canManagePointAdjustments" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in pointsForUser(row.id)" :key="p.id" class="hover:bg-gray-50/80">
                          <td class="px-3 py-2 align-top text-gray-900">
                            <span class="font-medium">{{ reasonLabel(p) }}</span>
                            <span v-if="p.rule_title && p.reason && p.rule_title !== p.reason" class="mt-0.5 block text-xs text-gray-500">Правило: {{ p.rule_title }}</span>
                          </td>
                          <td class="whitespace-nowrap px-3 py-2 align-top text-gray-500">{{ formatPointDate(p.created_at) }}</td>
                          <td class="px-3 py-2 text-right align-top">
                            <RBadge :variant="(p.points ?? 0) >= 0 ? 'success' : 'error'" class="font-bold">
                              {{ (p.points ?? 0) >= 0 ? '+' : '' }}{{ p.points ?? 0 }}
                            </RBadge>
                          </td>
                          <td v-if="canManagePointAdjustments" class="px-3 py-2 text-right align-top">
                            <RButton variant="danger" size="sm" iconOnly @click="confirmDestroyPoint(p)">
                              <template #icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                              </template>
                            </RButton>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p v-else class="py-4 text-center text-sm text-gray-500">Нет записей по начислениям</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
        <div v-if="!leaderboard.length" class="px-5 py-10 text-center text-sm text-gray-500">Пока нет начислений</div>
      </div>
    </RCard>

    <RCard flush class="mb-6">
      <template #header>
        <h2 class="text-base font-bold text-gray-900">Подробная история начислений</h2>
        <p class="mt-0.5 text-xs text-gray-500">Все начисления по событию с фильтрами и пагинацией</p>
      </template>

      <div class="border-b border-gray-100 px-4 py-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
          <input
            v-model="historyForm.search"
            type="text"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            placeholder="Поиск: участник, email, причина, правило"
            @keyup.enter="applyHistoryFilters"
          />
          <select
            v-model="historyForm.type"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          >
            <option value="">Все типы</option>
            <option value="manual">Ручное</option>
            <option value="auto">Автоматическое</option>
          </select>
          <select
            v-model="historyForm.group"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          >
            <option value="">Все группы</option>
            <option v-for="g in historyGroupOptions" :key="g" :value="g">{{ g }}</option>
          </select>
          <input
            v-model="historyForm.date_from"
            type="date"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          />
          <input
            v-model="historyForm.date_to"
            type="date"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
          />
          <div class="flex items-center gap-2">
            <RButton variant="primary" size="sm" @click="applyHistoryFilters">Применить</RButton>
            <RButton variant="outline" size="sm" @click="resetHistoryFilters">Сбросить</RButton>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Дата</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Тип</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Основание</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
              <th v-if="canManagePointAdjustments" class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in historyRows" :key="item.id" class="transition hover:bg-gray-50">
              <td class="whitespace-nowrap px-4 py-2.5 text-sm text-gray-500">{{ formatPointDate(item.created_at) }}</td>
              <td class="px-4 py-2.5">
                <template v-if="item.for_city_ranking_only">
                  <p class="text-sm font-medium text-gray-900">Город: {{ item.city_name || '—' }}</p>
                  <p class="text-xs text-gray-400">{{ item.group?.title ? `Группа: ${item.group.title}` : '—' }}</p>
                </template>
                <template v-else>
                  <p class="text-sm font-medium text-gray-900">{{ item.user?.name || 'Пользователь удалён' }}</p>
                  <p class="text-xs text-gray-400">{{ item.user?.email || '—' }}</p>
                </template>
              </td>
              <td class="px-4 py-2.5 text-sm text-gray-500">
                {{ item.for_city_ranking_only ? 'Город (группа)' : (item.lms_gamification_rule_id ? 'Автоматическое' : 'Ручное') }}
              </td>
              <td class="px-4 py-2.5">
                <p class="text-sm text-gray-900">{{ item.reason || '—' }}</p>
                <p v-if="item.rule?.title" class="text-xs text-gray-500">Правило: {{ item.rule.title }}</p>
              </td>
              <td class="px-4 py-2.5 text-right">
                <RBadge :variant="(item.points ?? 0) >= 0 ? 'success' : 'error'" class="font-bold">
                  {{ (item.points ?? 0) >= 0 ? '+' : '' }}{{ item.points ?? 0 }}
                </RBadge>
              </td>
              <td v-if="canManagePointAdjustments" class="px-4 py-2.5 text-right">
                <RButton variant="danger" size="sm" iconOnly @click="confirmDestroyPoint(item)">
                  <template #icon>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                  </template>
                </RButton>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!historyRows.length" class="px-5 py-10 text-center text-sm text-gray-500">История начислений пока пуста</div>
      </div>

      <div v-if="historyLinks.length > 3" class="border-t border-gray-100 px-4 py-3">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="(link, index) in historyLinks"
            :key="`${index}-${link.label}`"
            type="button"
            class="rounded-md px-3 py-1.5 text-sm transition"
            :class="[
              link.active ? 'bg-rosatom-600 text-white' : 'text-gray-600 hover:bg-gray-100',
              link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-40',
            ]"
            :disabled="!link.url"
            @click="goToHistoryPage(link.url)"
            v-html="link.label"
          />
        </div>
      </div>
    </RCard>

    <!-- Manual points dialog -->
    <RModal v-model="showManualDialog" title="Начислить баллы" size="lg" @update:model-value="onDialogClose">
      <form @submit.prevent="submitManual" class="space-y-5">
        <div class="flex rounded-xl bg-gray-100 p-1">
          <button
            type="button"
            class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="manualMode === 'users' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            @click="manualMode = 'users'"
          >
            Участникам
          </button>
          <button
            type="button"
            class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="manualMode === 'group_cities' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            @click="manualMode = 'group_cities'"
          >
            Городам (через группу)
          </button>
        </div>

        <template v-if="manualMode === 'group_cities'">
          <SearchSelect
            v-model="groupCityForm.lms_group_id"
            :options="groupOptionsForBonus"
            value-key="id"
            label-key="label"
            label="Группа"
            placeholder="Выберите группу с привязанными городами"
            :searchable="true"
          />
          <div class="grid grid-cols-2 gap-4">
            <RInput v-model.number="groupCityForm.points" label="Баллы (всего)" type="number" required />
          </div>
          <RInput v-model="groupCityForm.reason" label="Причина" required placeholder="Например: победа в командном конкурсе" />
        </template>

        <template v-else>
        <!-- Search & filter -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">Участники</label>
          <div class="mb-3 flex gap-2">
            <div class="relative flex-1">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
                placeholder="Поиск по ФИО, email или городу..."
              />
            </div>
            <select
              v-model="roleFilter"
              class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            >
              <option value="">Все роли</option>
              <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
            </select>
            <select
              v-model="cityFilter"
              class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            >
              <option value="">Все города</option>
              <option v-for="c in availableCities" :key="c" :value="c">{{ c }}</option>
            </select>
            <select
              v-model="groupFilter"
              class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            >
              <option value="">Все группы</option>
              <option v-for="g in availableGroups" :key="g" :value="g">{{ g }}</option>
            </select>
          </div>

          <!-- Select all / count -->
          <div class="mb-2 flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2">
              <RCheckbox
                :model-value="allFilteredSelected"
                @update:model-value="toggleAllFiltered"
              />
              <span class="text-xs text-gray-500">
                Выбрать всех{{ filteredUsers.length !== users.length ? ` (${filteredUsers.length} из ${users.length})` : ` (${users.length})` }}
              </span>
            </label>
            <span v-if="manualForm.user_ids.length > 0" class="text-xs font-medium text-rosatom-600">
              Выбрано: {{ manualForm.user_ids.length }}
            </span>
          </div>

          <!-- User list -->
          <div class="max-h-56 space-y-0.5 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-2">
            <div
              v-for="u in filteredUsers"
              :key="u.id"
              :class="[
                'flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition',
                manualForm.user_ids.includes(u.id) ? 'bg-rosatom-50 ring-1 ring-rosatom-200' : 'hover:bg-gray-100',
              ]"
              @click="toggleUser(u.id)"
            >
              <RCheckbox
                :model-value="manualForm.user_ids.includes(u.id)"
                @update:model-value="toggleUser(u.id)"
                @click.stop
              />
              <RAvatar :name="u.name" size="sm" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-900">{{ u.name }}</p>
                <p class="truncate text-xs text-gray-400">{{ u.email }}</p>
              </div>
              <RBadge variant="neutral" size="sm">{{ u.role }}</RBadge>
            </div>
            <div v-if="filteredUsers.length === 0" class="px-3 py-6 text-center text-sm text-gray-400">
              {{ searchQuery || roleFilter || cityFilter ? 'Никого не найдено' : 'Нет участников в событии' }}
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <RInput v-model.number="manualForm.points" label="Баллы" type="number" required />
          <div />
        </div>
        <RInput v-model="manualForm.reason" label="Причина" required placeholder="За что начислены баллы" />
        </template>
      </form>
      <template #footer>
        <RButton variant="outline" @click="showManualDialog = false">Отмена</RButton>
        <RButton
          v-if="manualMode === 'users'"
          variant="primary"
          :disabled="manualForm.user_ids.length === 0 || !manualForm.points || !manualForm.reason?.trim()"
          @click="submitManual"
        >
          Начислить ({{ manualForm.user_ids.length }})
        </RButton>
        <RButton
          v-else
          variant="primary"
          :disabled="!groupCityForm.lms_group_id || !groupCityForm.points || !groupCityForm.reason?.trim()"
          @click="submitManual"
        >
          Начислить группе
        </RButton>
      </template>
    </RModal>
  </LmsAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'
import SearchSelect from '@/Components/SearchSelect.vue'

const props = defineProps({
  event: Object,
  rules: Object,
  users: Array,
  leaderboard: { type: Array, default: () => [] },
  pointsByUser: { type: Object, default: () => ({}) },
  pointsHistory: { type: Object, default: () => ({ data: [], links: [] }) },
  historyFilters: { type: Object, default: () => ({ search: '', type: '', group: '', date_from: '', date_to: '' }) },
  historyGroupOptions: { type: Array, default: () => [] },
  gamificationGroups: { type: Array, default: () => [] },
  canManageRules: { type: Boolean, default: true },
  canManagePointAdjustments: { type: Boolean, default: false },
})

const leaderboard = computed(() => props.leaderboard || [])

const showManualDialog = ref(false)
const manualMode = ref('users')
const expandedUserId = ref(null)
const searchQuery = ref('')
const roleFilter = ref('')
const cityFilter = ref('')
const groupFilter = ref('')
const manualForm = reactive({ user_ids: [], points: 0, reason: '' })
const groupCityForm = reactive({ lms_group_id: null, points: 0, reason: '' })
const historyForm = reactive({
  search: props.historyFilters?.search || '',
  type: props.historyFilters?.type || '',
  group: props.historyFilters?.group || '',
  date_from: props.historyFilters?.date_from || '',
  date_to: props.historyFilters?.date_to || '',
})

function pointsForUser(userId) {
  const m = props.pointsByUser || {}
  return m[userId] ?? m[String(userId)] ?? []
}

const historyRows = computed(() => Array.isArray(props.pointsHistory?.data) ? props.pointsHistory.data : [])
const historyLinks = computed(() => Array.isArray(props.pointsHistory?.links) ? props.pointsHistory.links : [])

function toggleExpandUser(userId) {
  expandedUserId.value = expandedUserId.value === userId ? null : userId
}

function reasonLabel(p) {
  return p.reason || p.rule_title || 'Начислены баллы'
}

function formatPointDate(dateStr) {
  if (!dateStr) return '–'
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const availableRoles = computed(() => {
  const roles = new Set((props.users || []).map(u => u.role).filter(Boolean))
  return [...roles].sort()
})

const availableCities = computed(() => {
  const cities = new Set((props.users || []).map(u => u.city).filter(Boolean))
  return [...cities].sort((a, b) => a.localeCompare(b, 'ru'))
})

const availableGroups = computed(() => {
  const groups = new Set((props.users || []).flatMap(u => Array.isArray(u.groups) ? u.groups : []).filter(Boolean))
  return [...groups].sort((a, b) => a.localeCompare(b, 'ru'))
})

const groupOptionsForBonus = computed(() =>
  (props.gamificationGroups || [])
    .filter(g => Array.isArray(g.linked_cities) && g.linked_cities.length > 0)
    .map(g => ({
      id: g.id,
      label: `${g.title} (${g.linked_cities.length} ${g.linked_cities.length === 1 ? 'город' : 'города'})`,
    }))
)

const filteredUsers = computed(() => {
  let list = props.users || []
  const q = searchQuery.value.toLowerCase().trim()

  if (q) {
    list = list.filter(u =>
      u.name?.toLowerCase().includes(q) ||
      u.email?.toLowerCase().includes(q) ||
      u.city?.toLowerCase().includes(q)
    )
  }

  if (roleFilter.value) {
    list = list.filter(u => u.role === roleFilter.value)
  }

  if (cityFilter.value) {
    list = list.filter(u => u.city === cityFilter.value)
  }

  if (groupFilter.value) {
    list = list.filter(u => Array.isArray(u.groups) && u.groups.includes(groupFilter.value))
  }

  return list
})

const allFilteredSelected = computed(() => {
  if (filteredUsers.value.length === 0) return false
  return filteredUsers.value.every(u => manualForm.user_ids.includes(u.id))
})

function toggleAllFiltered(v) {
  const ids = filteredUsers.value.map(u => u.id)
  if (v) {
    const set = new Set([...manualForm.user_ids, ...ids])
    manualForm.user_ids = [...set]
  } else {
    manualForm.user_ids = manualForm.user_ids.filter(id => !ids.includes(id))
  }
}

function toggleUser(id) {
  const idx = manualForm.user_ids.indexOf(id)
  if (idx >= 0) {
    manualForm.user_ids.splice(idx, 1)
  } else {
    manualForm.user_ids.push(id)
  }
}

function onDialogClose(v) {
  if (!v) resetForm()
}

function resetForm() {
  manualForm.user_ids = []
  manualForm.points = 0
  manualForm.reason = ''
  groupCityForm.lms_group_id = null
  groupCityForm.points = 0
  groupCityForm.reason = ''
  manualMode.value = 'users'
  searchQuery.value = ''
  roleFilter.value = ''
  cityFilter.value = ''
  groupFilter.value = ''
}

function applyHistoryFilters() {
  const params = {}
  if (historyForm.search?.trim()) params.history_search = historyForm.search.trim()
  if (historyForm.type) params.history_type = historyForm.type
  if (historyForm.group) params.history_group = historyForm.group
  if (historyForm.date_from) params.history_date_from = historyForm.date_from
  if (historyForm.date_to) params.history_date_to = historyForm.date_to

  router.get(route('lms.admin.gamification.index', props.event.slug), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function resetHistoryFilters() {
  historyForm.search = ''
  historyForm.type = ''
  historyForm.group = ''
  historyForm.date_from = ''
  historyForm.date_to = ''
  applyHistoryFilters()
}

function goToHistoryPage(url) {
  if (!url) return
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
  })
}

function submitManual() {
  if (manualMode.value === 'group_cities') {
    router.post(route('lms.admin.gamification.manual-group-city-points', props.event.slug), groupCityForm, {
      onSuccess: () => {
        showManualDialog.value = false
        resetForm()
      },
    })
    return
  }
  router.post(route('lms.admin.gamification.manual-points', props.event.slug), manualForm, {
    onSuccess: () => {
      showManualDialog.value = false
      resetForm()
    },
  })
}

function confirmDestroy(rule) {
  if (confirm(`Удалить правило "${rule.title}"?`)) {
    router.delete(route('lms.admin.gamification.destroy', [props.event.slug, rule.id]))
  }
}

function confirmDestroyPoint(point) {
  if (!point?.id) return
  if (confirm('Удалить это начисление баллов?')) {
    router.delete(route('lms.admin.gamification.points.destroy', [props.event.slug, point.id]))
  }
}
</script>
