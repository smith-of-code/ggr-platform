<template>
  <LmsAdminLayout :event="event">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Аналитика и отчёты</h1>
        <p class="mt-1 text-sm text-gray-500">Статистика обучения по событию «{{ event.title }}»</p>
      </div>
      <div class="flex gap-2">
        <a :href="route('lms.admin.reports.download', { event: event.slug, section: 'all' })" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
          Скачать CSV
        </a>
        <RButton variant="primary" @click="showEmailDialog = true">
          <template #icon>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
          </template>
          Отправить на почту
        </RButton>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-4">
      <div class="min-w-[160px]">
        <label class="mb-1 block text-xs font-medium text-gray-500">Роль</label>
        <select v-model="filterRole" @change="applyFilters" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="">Все роли</option>
          <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
        </select>
      </div>
      <div class="min-w-[200px]">
        <label class="mb-1 block text-xs font-medium text-gray-500">Программа</label>
        <select v-model="filterCourseId" @change="applyFilters" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="">Все программы</option>
          <option v-for="c in availableCourses" :key="c.id" :value="c.id">{{ c.title }}</option>
        </select>
      </div>
      <div class="min-w-[160px]">
        <label class="mb-1 block text-xs font-medium text-gray-500">Город</label>
        <select v-model="filterCityId" @change="applyFilters" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="">Все города</option>
          <option v-for="c in availableCities" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="min-w-[160px]">
        <label class="mb-1 block text-xs font-medium text-gray-500">Факультет</label>
        <select v-model="filterFaculty" @change="applyFilters" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="">Все факультеты</option>
          <option v-for="f in availableFaculties" :key="f" :value="f">{{ f }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">Период (динамика)</label>
        <div class="flex gap-2">
          <input v-model="filterDateFrom" type="date" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" @change="applyFilters" />
          <input v-model="filterDateTo" type="date" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" @change="applyFilters" />
        </div>
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">Гранулярность</label>
        <select v-model="filterGranularity" @change="applyFilters" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="day">День</option>
          <option value="week">Неделя</option>
        </select>
      </div>
      <button v-if="hasActiveFilters" @click="clearFilters" class="rounded-lg px-3 py-2 text-sm font-medium text-rosatom-600 transition hover:bg-rosatom-50">
        Сбросить
      </button>
    </div>

    <!-- Summary cards -->
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      <RCard v-for="card in summaryCards" :key="card.label" class="relative overflow-hidden">
        <div :class="['absolute -right-3 -top-3 h-16 w-16 rounded-full opacity-10', card.bg]" />
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ card.label }}</p>
        <p :class="['mt-1 text-3xl font-black', card.color]">{{ card.value }}</p>
        <p v-if="card.sub" class="mt-0.5 text-xs text-gray-400">{{ card.sub }}</p>
      </RCard>
    </div>

    <!-- Tabs -->
    <div class="mb-6 flex flex-wrap rounded-xl bg-gray-100 p-1">
      <button
        v-for="t in tabs"
        :key="t.id"
        :class="[
          'rounded-lg px-4 py-2 text-sm font-medium transition',
          activeTab === t.id ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700',
        ]"
        @click="activeTab = t.id"
      >
        {{ t.label }}
        <span v-if="t.count !== undefined" class="ml-1 text-xs text-gray-400">({{ t.count }})</span>
      </button>
    </div>

    <!-- Courses tab -->
    <div v-show="activeTab === 'courses'">
      <RCard flush>
        <div class="border-b border-gray-100 bg-blue-50/70 px-5 py-3 text-xs leading-relaxed text-blue-800">
          В строке программы «Записано» — все участники с записью на программу. В строках занятий это та же аудитория программы; «Открыли этап» показывает, сколько участников уже создали прогресс по конкретному занятию, поэтому возможна ситуация: на программу записано 144 человека, но занятие пока никто не открывал.
        </div>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Программа</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Записано</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Не начали</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">В процессе</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Завершено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% завершения</th>
              <th class="px-5 py-3 w-48 text-xs font-semibold uppercase tracking-wider text-gray-500">Прогресс</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template v-for="c in courseStats" :key="c.id">
              <tr class="cursor-pointer transition hover:bg-gray-50" @click="toggleCourseExpand(c.id)">
                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">
                  <div class="flex items-center gap-2">
                    <svg :class="['h-4 w-4 text-gray-400 transition', expandedCourses.includes(c.id) ? 'rotate-90' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    {{ c.title }}
                  </div>
                </td>
                <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ c.enrolled }}</td>
                <td class="px-5 py-3.5 text-center text-sm text-gray-400">{{ c.not_started }}</td>
                <td class="px-5 py-3.5 text-center text-sm text-amber-600 font-medium">{{ c.in_progress }}</td>
                <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ c.completed }}</td>
                <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-900">{{ coursePct(c) }}%</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                      <div class="flex h-full">
                        <div class="h-full bg-green-500 transition-all" :style="{ width: coursePct(c) + '%' }" />
                        <div class="h-full bg-amber-400 transition-all" :style="{ width: courseInProgressPct(c) + '%' }" />
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="expandedCourses.includes(c.id)" v-for="s in courseStages(c.id)" :key="'s-' + s.id">
                <td class="py-2.5 pl-14 pr-5 text-xs text-gray-500">
                  <div>{{ s.stage_title }}</div>
                  <div class="mt-0.5 text-[11px] text-gray-400">Открыли этап: {{ s.opened }}</div>
                </td>
                <td class="py-2.5 text-center text-xs text-gray-400">{{ s.enrolled }}</td>
                <td class="py-2.5 text-center text-xs text-gray-400">{{ s.not_started }}</td>
                <td class="py-2.5 text-center text-xs text-amber-500">{{ s.in_progress }}</td>
                <td class="py-2.5 text-center text-xs text-green-500">{{ s.completed }}</td>
                <td class="py-2.5 text-center text-xs text-gray-500">{{ stagePct(s) }}%</td>
                <td class="py-2.5 px-5">
                  <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-green-400" :style="{ width: stagePct(s) + '%' }" />
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
        <div v-if="courseStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Программ пока нет</div>
      </RCard>
    </div>

    <!-- Tests tab -->
    <div v-show="activeTab === 'tests'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Тест</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Попыток</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Участников</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Сдало</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Не сдало</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% сдачи</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Мин / Ср / Макс</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="t in testStats" :key="t.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ t.title }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ t.total_attempts }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ t.attempted }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ t.passed }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-red-500 font-medium">{{ t.failed }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold" :class="testPassPct(t) >= 70 ? 'bg-green-100 text-green-700' : testPassPct(t) >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                  {{ testPassPct(t) }}%
                </span>
              </td>
              <td class="px-5 py-3.5 text-center text-xs text-gray-500">
                <span class="text-red-500">{{ Math.round(t.min_score) }}%</span>
                <span class="mx-1 text-gray-300">/</span>
                <span class="font-bold text-gray-900">{{ Math.round(t.avg_score) }}%</span>
                <span class="mx-1 text-gray-300">/</span>
                <span class="text-green-500">{{ Math.round(t.max_score) }}%</span>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="testStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Тестов пока нет</div>
      </RCard>
    </div>

    <!-- Assignments tab -->
    <div v-show="activeTab === 'assignments'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Задание</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Сдано работ</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">На проверке</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Принято</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Отклонено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% принятия</th>
              <th class="px-5 py-3 w-32 text-xs font-semibold uppercase tracking-wider text-gray-500">Распределение</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="a in assignmentStats" :key="a.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ a.title }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ a.submitted }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-amber-600 font-medium">{{ a.pending }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-green-600 font-medium">{{ a.approved }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-red-600 font-medium">{{ a.rejected }}</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-900">
                {{ a.submitted > 0 ? Math.round(a.approved / a.submitted * 100) : 0 }}%
              </td>
              <td class="px-5 py-3.5">
                <div v-if="a.submitted > 0" class="flex h-2 overflow-hidden rounded-full">
                  <div class="bg-green-500" :style="{ width: (a.approved / a.submitted * 100) + '%' }" />
                  <div class="bg-amber-400" :style="{ width: (a.pending / a.submitted * 100) + '%' }" />
                  <div class="bg-red-400" :style="{ width: (a.rejected / a.submitted * 100) + '%' }" />
                </div>
                <div v-else class="h-2 rounded-full bg-gray-100" />
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="assignmentStats.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Заданий пока нет</div>
      </RCard>
    </div>

    <!-- Deadline compliance tab -->
    <div v-show="activeTab === 'deadline'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Задание</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Программа</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Город</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Факультет</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Всего</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">В срок</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">С задержкой</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Просрочено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. задержка (дн.)</th>
              <th class="px-5 py-3 w-44 text-xs font-semibold uppercase tracking-wider text-gray-500">Распределение</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(r, idx) in deadlineCompliance" :key="idx" :class="['transition hover:bg-gray-50', r.is_orphan ? 'bg-amber-50/40' : '']">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">
                <div class="flex items-center gap-2">
                  <span>{{ r.assignment_title }}</span>
                  <RBadge v-if="r.is_orphan" variant="warning" size="sm" title="Задание не привязано к этапам ни одной программы">Вне плана</RBadge>
                </div>
              </td>
              <td class="px-5 py-3.5 text-sm text-gray-600">{{ r.course_title || '—' }}</td>
              <td class="px-5 py-3.5 text-sm text-gray-600">{{ r.city_name || '—' }}</td>
              <td class="px-5 py-3.5 text-sm text-gray-600">{{ r.faculty || '—' }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-700">{{ r.total_users }}</td>
              <td class="px-5 py-3.5 text-center">
                <RBadge variant="success" size="sm">{{ r.on_time }}</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center">
                <RBadge variant="warning" size="sm">{{ r.late }}</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center">
                <RBadge variant="danger" size="sm">{{ r.overdue }}</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-700">{{ r.avg_delay_days ?? '—' }}</td>
              <td class="px-5 py-3.5">
                <div v-if="r.total_users > 0" class="flex h-2 overflow-hidden rounded-full">
                  <div class="bg-green-500" :style="{ width: (r.on_time / r.total_users * 100) + '%' }" />
                  <div class="bg-amber-400" :style="{ width: (r.late / r.total_users * 100) + '%' }" />
                  <div class="bg-red-400" :style="{ width: (r.overdue / r.total_users * 100) + '%' }" />
                </div>
                <div v-else class="h-2 rounded-full bg-gray-100" />
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="(deadlineCompliance || []).length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Нет данных за выбранные фильтры</div>
      </RCard>
    </div>

    <!-- Personal progress tab -->
    <div v-show="activeTab === 'personal'">
      <RCard flush>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Город</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Задания</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Тесты</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Этапы</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500" title="Дополнительные orphan-задания вне плана программы — не учитываются в общем %">Доп.</th>
                <th class="px-4 py-3 w-56 text-xs font-semibold uppercase tracking-wider text-gray-500">Общий %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="r in personalProgress" :key="r.user_id" class="transition hover:bg-gray-50">
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-900">{{ r.user_name }}</p>
                  <p class="text-xs text-gray-400">{{ r.user_email }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ r.city_name || '—' }}</td>
                <td class="px-4 py-3 text-center text-xs text-gray-700">
                  {{ r.assignments_done }} / {{ r.assignments_total }}
                  <span class="ml-1 font-bold" :class="progressColor(r.assignments_pct)">({{ r.assignments_pct }}%)</span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-700">
                  {{ r.tests_done }} / {{ r.tests_total }}
                  <span class="ml-1 font-bold" :class="progressColor(r.tests_pct)">({{ r.tests_pct }}%)</span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-700">
                  {{ r.stages_done }} / {{ r.stages_total }}
                  <span class="ml-1 font-bold" :class="progressColor(r.stages_pct)">({{ r.stages_pct }}%)</span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-500" :title="(r.assignments_orphan_total || 0) > 0 ? 'Orphan-задания вне плана программ' : 'Orphan-задания отсутствуют в событии'">
                  <span v-if="(r.assignments_orphan_total || 0) > 0" :class="(r.assignments_orphan_done || 0) > 0 ? 'font-medium text-amber-600' : 'text-gray-400'">
                    {{ r.assignments_orphan_done || 0 }} / {{ r.assignments_orphan_total }}
                  </span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                      <div class="h-full rounded-full transition-all" :class="progressBarColor(r.overall_pct)" :style="{ width: r.overall_pct + '%' }" />
                    </div>
                    <span class="w-12 text-right text-sm font-bold" :class="progressColor(r.overall_pct)">{{ r.overall_pct }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="(personalProgress || []).length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Нет данных за выбранные фильтры</div>
      </RCard>
    </div>

    <!-- Cities comparison tab -->
    <div v-show="activeTab === 'cities'">
      <div v-if="!filterCourseId" class="rounded-xl border border-dashed border-gray-300 bg-gray-50/50 px-5 py-12 text-center text-sm text-gray-500">
        Выберите программу в фильтрах, чтобы увидеть сравнение городов.
      </div>
      <RCard v-else flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Город</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Участников</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% в срок</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">% просрочено</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. общий %</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. балл тестов</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(r, idx) in (cityComparison || [])" :key="idx" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ r.city_name || '— Не указан —' }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-700">{{ r.participants_count }}</td>
              <td class="px-5 py-3.5 text-center">
                <RBadge variant="success" size="sm">{{ r.on_time_pct }}%</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center">
                <RBadge variant="danger" size="sm">{{ r.overdue_pct }}%</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center text-sm font-bold" :class="progressColor(r.avg_overall_pct)">{{ r.avg_overall_pct }}%</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold" :class="progressColor(r.avg_test_score)">{{ r.avg_test_score }}%</td>
            </tr>
          </tbody>
        </table>
        <div v-if="(cityComparison || []).length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Нет городов для выбранной программы</div>
      </RCard>
    </div>

    <!-- Activity Timeline tab -->
    <div v-show="activeTab === 'activity'">
      <RCard>
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-base font-bold text-gray-900">Динамика за период</h3>
          <span class="text-xs text-gray-400">Гранулярность: {{ activityTimeline?.granularity === 'week' ? 'неделя' : 'день' }}</span>
        </div>
        <div v-if="timelineDates.length" class="space-y-6">
          <div v-for="series in timelineSeries" :key="series.key">
            <div class="mb-2 flex items-center gap-2">
              <div :class="['h-3 w-3 rounded-full', series.color]" />
              <span class="text-sm font-medium text-gray-700">{{ series.label }}</span>
              <span class="text-xs text-gray-400">Всего: {{ series.total }}</span>
            </div>
            <div class="flex items-end gap-px" style="height: 80px">
              <div
                v-for="(date, idx) in timelineDates"
                :key="idx"
                class="group relative flex-1"
                :title="date + ': ' + (series.data[date] || 0)"
              >
                <div
                  :class="['w-full rounded-t transition', series.barColor]"
                  :style="{ height: barHeight(series.data[date] || 0, series.max) }"
                />
                <div class="pointer-events-none absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-900 px-1.5 py-0.5 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                  {{ formatShortDate(date) }}: {{ series.data[date] || 0 }}
                </div>
              </div>
            </div>
            <div class="mt-1 flex justify-between text-[10px] text-gray-400">
              <span>{{ formatShortDate(timelineDates[0]) }}</span>
              <span>{{ formatShortDate(timelineDates[timelineDates.length - 1]) }}</span>
            </div>
          </div>
        </div>
        <div v-else class="py-12 text-center text-sm text-gray-400">Нет данных за выбранный период</div>
      </RCard>
    </div>

    <!-- Groups tab -->
    <div v-show="activeTab === 'groups'">
      <div v-if="groupStats.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <RCard v-for="g in groupStats" :key="g.id" class="relative overflow-hidden">
          <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-rosatom-500/5" />
          <h3 class="text-base font-bold text-gray-900">{{ g.title }}</h3>
          <div class="mt-3 grid grid-cols-2 gap-3">
            <div>
              <p class="text-xs text-gray-400">Участников</p>
              <p class="text-xl font-bold text-gray-900">{{ g.members_count }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Баллы</p>
              <p class="text-xl font-bold text-amber-600">{{ g.total_points }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Программ завершено</p>
              <p class="text-xl font-bold text-green-600">{{ g.total_completions }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Ср. программ/чел</p>
              <p class="text-xl font-bold text-blue-600">{{ g.avg_completions }}</p>
            </div>
          </div>
        </RCard>
      </div>
      <div v-else class="rounded-xl border border-dashed border-gray-300 py-12 text-center text-sm text-gray-400">Групп пока нет</div>
    </div>

    <!-- Gamification tab -->
    <div v-show="activeTab === 'gamification'">
      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Правило / Действие</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Начислений</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Участников</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Всего баллов</th>
              <th class="px-5 py-3 w-48 text-xs font-semibold uppercase tracking-wider text-gray-500">Доля</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="g in gamificationBreakdown" :key="g.action" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5">
                <p class="text-sm font-medium text-gray-900">{{ g.rule_title }}</p>
                <p class="text-xs text-gray-400">{{ g.action }}</p>
              </td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ g.awards_count }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ g.unique_users }}</td>
              <td class="px-5 py-3.5 text-center text-sm font-bold text-amber-600">{{ g.total_points }}</td>
              <td class="px-5 py-3.5">
                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                  <div class="h-full rounded-full bg-amber-500" :style="{ width: gamificationPct(g) + '%' }" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="gamificationBreakdown.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Баллов пока нет</div>
      </RCard>
    </div>

    <!-- Users tab -->
    <div v-show="activeTab === 'users'">
      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="userSearch"
            type="text"
            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-rosatom-500 focus:ring-2 focus:ring-rosatom-500/20"
            placeholder="Поиск по ФИО или email..."
          />
        </div>
        <select v-model="userSort" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
          <option value="name">По имени</option>
          <option value="courses_completed">По программам</option>
          <option value="tests_passed">По тестам</option>
          <option value="total_points">По баллам</option>
          <option value="avg_test_score">По ср. баллу</option>
          <option value="last_activity">По активности</option>
        </select>
      </div>

      <RCard flush>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Программ</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Заверш.</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Тестов</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Ср. балл</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Заданий</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Баллы</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Активность</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="u in sortedUsers" :key="u.id" class="transition hover:bg-gray-50">
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-900">{{ u.name }}</p>
                  <p class="text-xs text-gray-400">{{ u.email }}</p>
                </td>
                <td class="px-4 py-3">
                  <RBadge variant="neutral" size="sm">{{ u.role || '—' }}</RBadge>
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ u.courses_enrolled }}</td>
                <td class="px-4 py-3 text-center text-sm font-medium" :class="u.courses_completed > 0 ? 'text-green-600' : 'text-gray-400'">
                  {{ u.courses_completed }}
                </td>
                <td class="px-4 py-3 text-center text-sm" :class="u.tests_passed > 0 ? 'text-green-600 font-medium' : 'text-gray-400'">
                  {{ u.tests_passed }}
                </td>
                <td class="px-4 py-3 text-center text-sm font-medium" :class="u.avg_test_score >= 70 ? 'text-green-600' : u.avg_test_score > 0 ? 'text-amber-600' : 'text-gray-400'">
                  {{ u.avg_test_score > 0 ? u.avg_test_score + '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center text-sm" :class="u.assignments_approved > 0 ? 'text-green-600 font-medium' : 'text-gray-400'">
                  {{ u.assignments_approved }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-sm font-bold text-amber-700">{{ u.total_points }}</span>
                </td>
                <td class="px-4 py-3 text-center text-xs" :class="activityClass(u.last_activity)">
                  {{ u.last_activity ? formatDate(u.last_activity) : 'Нет' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="sortedUsers.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">
          {{ userSearch ? 'Никого не найдено' : 'Участников пока нет' }}
        </div>
      </RCard>
    </div>

    <!-- Inactive Users tab -->
    <div v-show="activeTab === 'inactive'">
      <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
        <div class="flex items-start gap-3">
          <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
          <div>
            <p class="text-sm font-medium text-amber-800">Участники без активности более 14 дней</p>
            <p class="mt-0.5 text-xs text-amber-600">Записаны на программы, но не проходят обучение. Рекомендуется направить напоминание.</p>
          </div>
        </div>
      </div>

      <RCard flush>
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Участник</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Роль</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Программ</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Последняя активность</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="u in inactiveUsers" :key="u.id" class="transition hover:bg-gray-50">
              <td class="px-5 py-3.5">
                <p class="text-sm font-medium text-gray-900">{{ u.name }}</p>
                <p class="text-xs text-gray-400">{{ u.email }}</p>
              </td>
              <td class="px-5 py-3.5">
                <RBadge variant="neutral" size="sm">{{ u.role || '—' }}</RBadge>
              </td>
              <td class="px-5 py-3.5 text-center text-sm text-gray-600">{{ u.courses_enrolled }}</td>
              <td class="px-5 py-3.5 text-center text-sm text-red-500">
                {{ u.last_activity ? formatDate(u.last_activity) : 'Никогда' }}
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="inactiveUsers.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">Все участники активны</div>
      </RCard>
    </div>

    <!-- Email dialog -->
    <RModal v-model="showEmailDialog" title="Отправить отчёт на почту" size="md">
      <form @submit.prevent="sendReport" class="space-y-5">
        <RInput v-model="emailForm.email" type="email" label="Email *" required placeholder="admin@example.com" :error="emailForm.errors?.email" />

        <div>
          <label class="mb-3 block text-sm font-medium text-gray-700">Включить разделы</label>
          <div class="space-y-2">
            <RCheckbox v-model="emailSections.users" label="Участники (детальная таблица)" />
            <RCheckbox v-model="emailSections.courses" label="Программы (статистика)" />
            <RCheckbox v-model="emailSections.tests" label="Тесты (статистика)" />
            <RCheckbox v-model="emailSections.stages" label="Этапы программ (детализация)" />
            <RCheckbox v-model="emailSections.deadline" label="Соблюдение сроков (по заданиям)" />
            <RCheckbox v-model="emailSections.personal" label="Прогресс участников (персональный план)" />
            <RCheckbox v-model="emailSections.cities" label="Сравнение городов (требует выбора программы)" />
            <RCheckbox v-model="emailSections.dynamics" label="Динамика (период + гранулярность из фильтров)" />
          </div>
        </div>

        <div v-if="emailNeedsCourse" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700">
          Раздел «Сравнение городов» требует выбранной программы — выберите её в фильтрах вверху страницы.
        </div>

        <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-500">
          Отчёт будет отправлен в формате CSV (открывается в Excel). Кодировка UTF-8 с BOM.
          Для разделов «Сравнение городов» и «Динамика» используются текущие фильтры
          (программа, период, гранулярность).
        </div>
      </form>
      <template #footer>
        <RButton variant="outline" @click="showEmailDialog = false">Отмена</RButton>
        <RButton variant="primary" :disabled="!emailForm.email || selectedSections.length === 0 || emailNeedsCourse" @click="sendReport">
          Отправить
        </RButton>
      </template>
    </RModal>
  </LmsAdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref, reactive, computed } from 'vue'
import LmsAdminLayout from '@/Layouts/LmsAdminLayout.vue'

const props = defineProps({
  event: Object,
  summary: Object,
  courseStats: Array,
  testStats: Array,
  assignmentStats: Array,
  deadlineCompliance: { type: Array, default: () => [] },
  personalProgress: { type: Array, default: () => [] },
  cityComparison: { type: Array, default: null },
  userDetails: Array,
  stageProgress: Array,
  activityTimeline: Object,
  groupStats: Array,
  gamificationBreakdown: Array,
  inactiveUsers: Array,
  filters: Object,
  availableRoles: Array,
  availableCourses: Array,
  availableCities: { type: Array, default: () => [] },
  availableFaculties: { type: Array, default: () => [] },
})

const tabs = computed(() => [
  { id: 'courses', label: 'Программы', count: props.courseStats?.length || 0 },
  { id: 'tests', label: 'Тесты', count: props.testStats?.length || 0 },
  { id: 'assignments', label: 'Задания', count: props.assignmentStats?.length || 0 },
  { id: 'deadline', label: 'Сроки', count: props.deadlineCompliance?.length || 0 },
  { id: 'personal', label: 'Прогресс участников', count: props.personalProgress?.length || 0 },
  { id: 'cities', label: 'Города', count: props.cityComparison?.length || 0 },
  { id: 'activity', label: 'Динамика' },
  { id: 'groups', label: 'Группы', count: props.groupStats?.length || 0 },
  { id: 'gamification', label: 'Геймификация', count: props.gamificationBreakdown?.length || 0 },
  { id: 'users', label: 'Участники', count: props.userDetails?.length || 0 },
  { id: 'inactive', label: 'Неактивные', count: props.inactiveUsers?.length || 0 },
])

const activeTab = ref('courses')
const userSearch = ref('')
const userSort = ref('name')
const showEmailDialog = ref(false)
const emailForm = reactive({ email: '', errors: {} })
const emailSections = reactive({
  users: true, courses: true, tests: true, stages: false,
  deadline: false, personal: false, cities: false, dynamics: false,
})
const expandedCourses = ref([])

const filterRole = ref(props.filters?.role || '')
const filterCourseId = ref(props.filters?.course_id || '')
const filterCityId = ref(props.filters?.city_id || '')
const filterFaculty = ref(props.filters?.faculty || '')
const filterDateFrom = ref(props.filters?.date_from || '')
const filterDateTo = ref(props.filters?.date_to || '')
const filterGranularity = ref(props.filters?.granularity || 'day')

const hasActiveFilters = computed(() =>
  filterRole.value || filterCourseId.value || filterCityId.value || filterFaculty.value
  || filterDateFrom.value || filterDateTo.value || filterGranularity.value === 'week'
)

function applyFilters() {
  const params = {}
  if (filterRole.value) params.role = filterRole.value
  if (filterCourseId.value) params.course_id = filterCourseId.value
  if (filterCityId.value) params.city_id = filterCityId.value
  if (filterFaculty.value) params.faculty = filterFaculty.value
  if (filterDateFrom.value) params.date_from = filterDateFrom.value
  if (filterDateTo.value) params.date_to = filterDateTo.value
  if (filterGranularity.value && filterGranularity.value !== 'day') params.granularity = filterGranularity.value
  router.get(route('lms.admin.reports.index', props.event.slug), params, { preserveState: true })
}

function clearFilters() {
  filterRole.value = ''
  filterCourseId.value = ''
  filterCityId.value = ''
  filterFaculty.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  filterGranularity.value = 'day'
  router.get(route('lms.admin.reports.index', props.event.slug), {}, { preserveState: true })
}

const selectedSections = computed(() => {
  const s = []
  if (emailSections.users) s.push('users')
  if (emailSections.courses) s.push('courses')
  if (emailSections.tests) s.push('tests')
  if (emailSections.stages) s.push('stages')
  if (emailSections.deadline) s.push('deadline')
  if (emailSections.personal) s.push('personal')
  if (emailSections.cities) s.push('cities')
  if (emailSections.dynamics) s.push('dynamics')
  return s
})

const emailNeedsCourse = computed(() => emailSections.cities && !filterCourseId.value)

const summaryCards = computed(() => [
  { label: 'Участников', value: props.summary?.total_users ?? 0, color: 'text-rosatom-600', bg: 'bg-rosatom-500' },
  { label: 'Активных (7 дн)', value: props.summary?.active_last_7_days ?? 0, color: 'text-blue-600', bg: 'bg-blue-500', sub: `неактивных: ${props.summary?.inactive_users ?? 0}` },
  { label: 'Ср. завершение программ', value: (props.summary?.avg_course_completion ?? 0) + '%', color: 'text-green-600', bg: 'bg-green-500' },
  { label: 'Ср. сдача тестов', value: (props.summary?.avg_test_pass_rate ?? 0) + '%', color: 'text-purple-600', bg: 'bg-purple-500' },
  { label: 'Баллов начислено', value: props.summary?.total_gamification_points ?? 0, color: 'text-amber-600', bg: 'bg-amber-500' },
])

const filteredUsers = computed(() => {
  const q = userSearch.value.toLowerCase().trim()
  if (!q) return props.userDetails || []
  return (props.userDetails || []).filter(u =>
    u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)
  )
})

const sortedUsers = computed(() => {
  const list = [...filteredUsers.value]
  const key = userSort.value
  if (key === 'name') return list.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
  if (key === 'last_activity') return list.sort((a, b) => {
    if (!a.last_activity) return 1
    if (!b.last_activity) return -1
    return new Date(b.last_activity) - new Date(a.last_activity)
  })
  return list.sort((a, b) => (b[key] || 0) - (a[key] || 0))
})

function toggleCourseExpand(courseId) {
  const idx = expandedCourses.value.indexOf(courseId)
  if (idx >= 0) expandedCourses.value.splice(idx, 1)
  else expandedCourses.value.push(courseId)
}

function courseStages(courseId) {
  return (props.stageProgress || []).filter(s => s.course_id === courseId)
}

function coursePct(c) {
  return c.enrolled > 0 ? Math.round(c.completed / c.enrolled * 100) : 0
}

function courseInProgressPct(c) {
  return c.enrolled > 0 ? Math.round(c.in_progress / c.enrolled * 100) : 0
}

function stagePct(s) {
  return s.enrolled > 0 ? Math.round(s.completed / s.enrolled * 100) : 0
}

function testPassPct(t) {
  return t.attempted > 0 ? Math.round(t.passed / t.attempted * 100) : 0
}

const totalGamificationPoints = computed(() =>
  (props.gamificationBreakdown || []).reduce((s, g) => s + Number(g.total_points), 0)
)

function gamificationPct(g) {
  return totalGamificationPoints.value > 0 ? Math.round(g.total_points / totalGamificationPoints.value * 100) : 0
}

const timelineDates = computed(() => {
  const from = props.activityTimeline?.from
  const to = props.activityTimeline?.to
  if (!from || !to) return []
  const isWeek = props.activityTimeline?.granularity === 'week'
  const dates = []
  let d = new Date(from)
  if (isWeek) {
    // Align to Monday (Postgres DATE_TRUNC('week') returns Monday).
    const dow = (d.getDay() + 6) % 7
    d.setDate(d.getDate() - dow)
  }
  const end = new Date(to)
  while (d <= end) {
    dates.push(d.toISOString().split('T')[0])
    d.setDate(d.getDate() + (isWeek ? 7 : 1))
  }
  return dates
})

const timelineSeries = computed(() => {
  const tl = props.activityTimeline || {}
  return [
    { key: 'enrollments', label: 'Записи на программы', color: 'bg-blue-500', barColor: 'bg-blue-400', data: tl.enrollments || {}, max: maxVal(tl.enrollments), total: sumVal(tl.enrollments) },
    { key: 'completions', label: 'Завершения программ', color: 'bg-green-500', barColor: 'bg-green-400', data: tl.completions || {}, max: maxVal(tl.completions), total: sumVal(tl.completions) },
    { key: 'test_attempts', label: 'Попытки тестов', color: 'bg-purple-500', barColor: 'bg-purple-400', data: tl.test_attempts || {}, max: maxVal(tl.test_attempts), total: sumVal(tl.test_attempts) },
    { key: 'assignments_approved', label: 'Принятые задания', color: 'bg-emerald-500', barColor: 'bg-emerald-400', data: tl.assignments_approved || {}, max: maxVal(tl.assignments_approved), total: sumVal(tl.assignments_approved) },
    { key: 'tests_passed', label: 'Сданные тесты', color: 'bg-teal-500', barColor: 'bg-teal-400', data: tl.tests_passed || {}, max: maxVal(tl.tests_passed), total: sumVal(tl.tests_passed) },
    { key: 'stages_completed', label: 'Завершённые этапы', color: 'bg-indigo-500', barColor: 'bg-indigo-400', data: tl.stages_completed || {}, max: maxVal(tl.stages_completed), total: sumVal(tl.stages_completed) },
  ]
})

function maxVal(obj) {
  if (!obj) return 1
  return Math.max(1, ...Object.values(obj).map(Number))
}

function sumVal(obj) {
  if (!obj) return 0
  return Object.values(obj).reduce((s, v) => s + Number(v), 0)
}

function barHeight(val, max) {
  return Math.max(2, (val / max) * 100) + '%'
}

function formatShortDate(d) {
  if (!d) return ''
  const dt = new Date(d)
  return dt.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

function progressColor(pct) {
  const v = Number(pct) || 0
  if (v >= 80) return 'text-green-600'
  if (v >= 50) return 'text-amber-600'
  if (v > 0) return 'text-red-600'
  return 'text-gray-400'
}

function progressBarColor(pct) {
  const v = Number(pct) || 0
  if (v >= 80) return 'bg-green-500'
  if (v >= 50) return 'bg-amber-500'
  if (v > 0) return 'bg-red-500'
  return 'bg-gray-300'
}

function activityClass(lastActivity) {
  if (!lastActivity) return 'text-red-500'
  const diff = (Date.now() - new Date(lastActivity).getTime()) / (1000 * 60 * 60 * 24)
  if (diff < 3) return 'text-green-600 font-medium'
  if (diff < 7) return 'text-gray-600'
  if (diff < 14) return 'text-amber-600'
  return 'text-red-500'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

function sendReport() {
  const payload = {
    email: emailForm.email,
    sections: selectedSections.value,
  }
  if (filterCourseId.value) payload.course_id = filterCourseId.value
  if (emailSections.dynamics) {
    if (filterDateFrom.value) payload.date_from = filterDateFrom.value
    if (filterDateTo.value) payload.date_to = filterDateTo.value
    payload.granularity = filterGranularity.value || 'day'
  }
  router.post(route('lms.admin.reports.send', props.event.slug), payload, {
    onSuccess: () => {
      showEmailDialog.value = false
      emailForm.email = ''
    },
    onError: (errors) => {
      emailForm.errors = errors
    },
  })
}
</script>
