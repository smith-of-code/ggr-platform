# Прогресс: Пункт «Настройки» в левом меню AdminLayout

## Завершённые задачи

### Task 1. Спецификации ✓

- Files:
  - `spec/features/admin-settings-sidebar/spec.md` — целевое состояние:
    единый пункт меню `admin.settings → admin.settings.index`, иконка-шестерёнка,
    `activeItemId` по префиксу `/admin/settings`.
  - `spec/features/portal-admin/spec.md` — в разделе «Навигация AdminLayout»
    добавлена строка «Настройки (единый пункт меню → хаб admin.settings.index…)».
  - `spec/features/settings/spec.md` — раздел «Точки входа из левого меню»
    обновлён под единый пункт меню.
- Verify: `git diff spec/` показывает только согласованные изменения.

### Task 2. `AdminLayout.vue` ✓

- Files: `resources/js/Layouts/AdminLayout.vue`.
- Изменения относительно предыдущей итерации:
  - 4 иконки подразделов настроек удалены, оставлена одна
    `ICON_SETTINGS` (cog/gear, heroicon outline).
  - В `ROUTE_MAP` 4 ключа подразделов заменены одним:
    `'admin.settings': 'admin.settings.index'`.
  - В `sidebarItems` секция-заголовок `sec.settings` удалена; пункт
    `admin.settings` («Настройки») добавлен последним элементом меню
    (после «Вакансии» из секции HR), как элемент верхнего уровня без
    отдельной группы.
  - В `activeItemId` 4 префиксные проверки заменены одной:
    `if (url.startsWith('/admin/settings')) return 'admin.settings'` —
    пункт подсвечивается и на самом хабе, и на любом из 4 подразделов.
- Verify: `ReadLints` по `AdminLayout.vue` — чисто.

### Task 3. Verify (Docker) ✓

- Frontend build (Docker, one-off `fpm`): `npm run build` —
  `✓ built in 7.27s` без ошибок; `AdminLayout` пересобран.

### Task 4. Финализация `progress.md` ✓

- Files: `spec/features/admin-settings-sidebar/progress.md` (этот файл).

## Частично выполненные

_Пусто_

## Не начатые

_Пусто_ — фича реализована полностью.

## Open issues

_Пусто_

## Verify summary

- Vue-lint (`ReadLints`): чисто на `resources/js/Layouts/AdminLayout.vue`.
- Frontend build (Docker, one-off `fpm`): зелёный.
- Имена маршрутов: `admin.settings.index` присутствует в `routes/web.php`
  (строка 295) и доступен под middleware `['auth', 'portal.admin']`.
- Затронутые файлы (4):
  - `resources/js/Layouts/AdminLayout.vue` — единый пункт «Настройки».
  - `spec/features/admin-settings-sidebar/spec.md` — обновлён под единый пункт.
  - `spec/features/portal-admin/spec.md` — формулировка «единый пункт меню».
  - `spec/features/settings/spec.md` — раздел «Точки входа из левого меню».
