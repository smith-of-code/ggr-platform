# Прогресс: directions

## Completed tasks

### Task 1: Модель Direction, миграция и seed ✓
- Files: `app/Models/Direction.php`, `database/migrations/2026_03_27_160000_create_directions_table.php`, `database/seeders/PortalSeeder.php`

### Task 2: Публичный контроллер и маршрут ✓
- Files: `app/Http/Controllers/DirectionController.php`, `routes/web.php`

### Task 3: Админский CRUD — контроллер и маршруты ✓
- Files: `app/Http/Controllers/Admin/DirectionController.php`, `routes/web.php`

### Task 4: Админ-страница: список направлений ✓
- Files: `resources/js/Pages/Admin/Directions/Index.vue`, `resources/js/Layouts/AdminLayout.vue`

### Task 5: Админ-страница: форма создания/редактирования ✓
- Files: `resources/js/Pages/Admin/Directions/Form.vue`

### Task 6: Публичная страница — hero + направления + аудитории ✓
- Files: `resources/js/Pages/Directions/Show.vue`

### Task 7: Публичная страница — участие + конкурс + слайдшоу туров ✓
- Files: `resources/js/Pages/Directions/Show.vue`

### Task 8: Интеграция — ссылки в OpportunityTours ✓
- Files: `app/Http/Controllers/OpportunityToursController.php`, `resources/js/Pages/OpportunityTours/Index.vue`

### Task 9: Финализация — линтер, spec, прогресс ✓
- Lint: clean
- Files: `spec/features/directions/progress.md`, `spec/features/directions/spec.md`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)

---

## Правки заказчика (2026-03-31)

### REV-001: Визуальное отличие «Для кого» + убрать звёздочку ✓
- Карточки «Для кого»: иконки (academic cap, user-group, globe) вместо цифр, цветные верхние бордеры (sky/emerald/violet), фон gradient sky-50 → white
- Звёздочка `target_audience_note` убрана
- Files: `resources/js/Pages/Directions/Show.vue`

### REV-002: Конкурсные детали — часть блока «Победить в конкурсе» ✓
- Отдельная секция «Конкурсные детали» удалена
- Развёрнутые ответы + Проверочное задание перенесены под карточку «Победить в конкурсе» с dashed connector
- Расположены вертикально (Проверочное задание под Развёрнутыми ответами)
- Заголовок «Конкурсное испытание» для группировки
- Files: `resources/js/Pages/Directions/Show.vue`

### REV-003: Исправить кнопки ✓
- «Оставить заявку»: заменена с Inertia Link (конфликтный route POST + method GET + @click.prevent) на обычный `<button>` с `@click="scrollToTours"`
- «Личный кабинет»: без изменений, корректно ведёт на route('login')
- Files: `resources/js/Pages/Directions/Show.vue`
