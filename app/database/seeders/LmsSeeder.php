<?php

namespace Database\Seeders;

use App\Models\Lms\LmsAssignment;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseStage;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGamificationRule;
use App\Models\Lms\LmsGroup;
use App\Models\Lms\LmsKbItem;
use App\Models\Lms\LmsKbSection;
use App\Models\Lms\LmsMaterialSection;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsTest;
use App\Models\Lms\LmsTestAnswer;
use App\Models\Lms\LmsTestQuestion;
use App\Models\Lms\LmsTrajectory;
use App\Models\Lms\LmsTrajectoryStep;
use App\Models\Lms\LmsVideo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsSeeder extends Seeder
{
    public function run(): void
    {
        $event = LmsEvent::create([
            'title' => 'ВШГР 2026',
            'slug' => 'vshgr-2026',
            'description' => 'Высшая школа гостеприимного развития — образовательная платформа для участников программы гостеприимных городов Росатома.',
            'auth_method' => 'email',
            'is_active' => true,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@rosatom-travel.ru'],
            ['name' => 'Администратор ВШГР', 'password' => Hash::make('password')]
        );

        $curator = User::firstOrCreate(
            ['email' => 'curator@rosatom-travel.ru'],
            ['name' => 'Куратор Иванов', 'password' => Hash::make('password')]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@rosatom-travel.ru'],
            ['name' => 'Участник Петров', 'password' => Hash::make('password')]
        );

        LmsProfile::create(['user_id' => $admin->id, 'lms_event_id' => $event->id, 'role' => 'admin', 'position' => 'Администратор', 'city' => 'Москва']);
        LmsProfile::create(['user_id' => $curator->id, 'lms_event_id' => $event->id, 'role' => 'curator', 'position' => 'Куратор группы', 'city' => 'Саров']);
        LmsProfile::create(['user_id' => $student->id, 'lms_event_id' => $event->id, 'role' => 'participant', 'position' => 'Специалист', 'city' => 'Железногорск']);

        $group = LmsGroup::create([
            'lms_event_id' => $event->id,
            'title' => 'Группа 1 — Пилотные города',
            'curator_id' => $curator->id,
        ]);
        $group->members()->sync([$student->id, $curator->id]);

        // ── Courses ──
        $course1 = LmsCourse::create([
            'lms_event_id' => $event->id,
            'title' => 'Основы гостеприимства территорий',
            'slug' => 'osnovy-gostepriimstva',
            'description' => 'Базовый курс об основных принципах создания гостеприимной территории.',
            'sequential' => true,
            'is_active' => true,
            'position' => 1,
        ]);

        LmsCourseStage::create(['lms_course_id' => $course1->id, 'title' => 'Введение в гостеприимство', 'type' => 'content', 'content' => '<h2>Добро пожаловать!</h2><p>В этом модуле вы узнаете об основных принципах территориального гостеприимства и роли атомных городов в развитии туризма.</p>', 'position' => 1]);
        LmsCourseStage::create(['lms_course_id' => $course1->id, 'title' => 'История атомных городов', 'type' => 'content', 'content' => '<h2>История</h2><p>Атомные города России — уникальные территории с богатой историей и потенциалом для развития внутреннего туризма.</p>', 'position' => 2]);
        LmsCourseStage::create(['lms_course_id' => $course1->id, 'title' => 'Видеолекция: Туризм в ЗАТО', 'type' => 'video', 'position' => 3]);

        $course2 = LmsCourse::create([
            'lms_event_id' => $event->id,
            'title' => 'Проектирование туристических маршрутов',
            'slug' => 'proektirovanie-marshrutov',
            'description' => 'Практический курс по разработке туристических маршрутов в атомных городах.',
            'sequential' => true,
            'is_active' => true,
            'position' => 2,
        ]);

        LmsCourseStage::create(['lms_course_id' => $course2->id, 'title' => 'Анализ целевой аудитории', 'type' => 'content', 'content' => '<h2>Целевая аудитория</h2><p>Определение и сегментация целевой аудитории — первый шаг в проектировании успешного маршрута.</p>', 'position' => 1]);
        LmsCourseStage::create(['lms_course_id' => $course2->id, 'title' => 'Практическое задание: Маршрут', 'type' => 'assignment', 'position' => 2]);

        // ── Test ──
        $test = LmsTest::create([
            'lms_event_id' => $event->id,
            'title' => 'Тест: Основы гостеприимства',
            'description' => 'Проверка знаний по базовому курсу.',
            'time_limit_minutes' => 30,
            'shuffle_questions' => true,
            'shuffle_answers' => true,
            'show_correct_answers' => true,
            'passing_score' => 70,
            'max_attempts' => 3,
            'in_menu' => true,
            'is_active' => true,
        ]);

        $q1 = LmsTestQuestion::create(['lms_test_id' => $test->id, 'question' => 'Какой основной принцип территориального гостеприимства?', 'type' => 'single', 'points' => 2, 'position' => 1]);
        LmsTestAnswer::create(['lms_test_question_id' => $q1->id, 'answer' => 'Создание комфортной среды для жителей и гостей', 'is_correct' => true, 'position' => 1]);
        LmsTestAnswer::create(['lms_test_question_id' => $q1->id, 'answer' => 'Максимизация доходов от туризма', 'is_correct' => false, 'position' => 2]);
        LmsTestAnswer::create(['lms_test_question_id' => $q1->id, 'answer' => 'Ограничение доступа на территорию', 'is_correct' => false, 'position' => 3]);

        $q2 = LmsTestQuestion::create(['lms_test_id' => $test->id, 'question' => 'Какие элементы важны для гостеприимной территории?', 'type' => 'multiple', 'points' => 3, 'position' => 2]);
        LmsTestAnswer::create(['lms_test_question_id' => $q2->id, 'answer' => 'Доступная навигация', 'is_correct' => true, 'position' => 1]);
        LmsTestAnswer::create(['lms_test_question_id' => $q2->id, 'answer' => 'Информационная поддержка', 'is_correct' => true, 'position' => 2]);
        LmsTestAnswer::create(['lms_test_question_id' => $q2->id, 'answer' => 'Высокие заборы', 'is_correct' => false, 'position' => 3]);
        LmsTestAnswer::create(['lms_test_question_id' => $q2->id, 'answer' => 'Развитая инфраструктура', 'is_correct' => true, 'position' => 4]);

        $q3 = LmsTestQuestion::create(['lms_test_id' => $test->id, 'question' => 'Опишите ваше видение идеального гостеприимного города.', 'type' => 'text', 'points' => 5, 'position' => 3]);

        // ── Assignment ──
        $assignment = LmsAssignment::create([
            'lms_event_id' => $event->id,
            'title' => 'Разработка концепции туристического маршрута',
            'description' => 'Разработайте концепцию туристического маршрута для вашего города. Документ должен включать: описание маршрута, целевую аудиторию, ключевые точки интереса, необходимую инфраструктуру.',
            'completion_mode' => 'on_review',
            'deadline' => now()->addMonths(1),
            'is_active' => true,
        ]);

        // ── Trajectory ──
        $trajectory = LmsTrajectory::create([
            'lms_event_id' => $event->id,
            'title' => 'Полный курс подготовки координатора',
            'description' => 'Последовательное прохождение всех обучающих модулей для получения статуса координатора программы.',
            'is_active' => true,
        ]);

        LmsTrajectoryStep::create(['lms_trajectory_id' => $trajectory->id, 'lms_course_id' => $course1->id, 'position' => 1]);
        LmsTrajectoryStep::create(['lms_trajectory_id' => $trajectory->id, 'lms_course_id' => $course2->id, 'is_locked' => true, 'position' => 2]);

        // ── Video ──
        LmsVideo::create([
            'lms_event_id' => $event->id,
            'title' => 'Вебинар: Лучшие практики гостеприимных городов',
            'description' => 'Запись вебинара с разбором успешных кейсов территориального гостеприимства.',
            'source' => 'rutube',
            'url' => 'https://rutube.ru/video/example',
            'is_recording' => true,
            'is_active' => true,
        ]);

        // ── Knowledge Base ──
        $kbRoot = LmsKbSection::create([
            'lms_event_id' => $event->id,
            'title' => 'Методические материалы',
            'description' => 'Основная база знаний программы ВШГР.',
            'in_menu' => true,
            'position' => 1,
        ]);

        $kbChild = LmsKbSection::create([
            'lms_event_id' => $event->id,
            'parent_id' => $kbRoot->id,
            'title' => 'Нормативные документы',
            'position' => 1,
        ]);

        LmsKbItem::create(['lms_kb_section_id' => $kbChild->id, 'title' => 'Стандарт гостеприимного города', 'type' => 'text', 'content' => 'Стандарт описывает основные требования к уровню гостеприимства территории...', 'position' => 1]);
        LmsKbItem::create(['lms_kb_section_id' => $kbChild->id, 'title' => 'Методическое пособие (PDF)', 'type' => 'file', 'position' => 2]);

        // ── Materials ──
        LmsMaterialSection::create([
            'lms_event_id' => $event->id,
            'title' => 'Полезные ссылки',
            'content' => '<ul><li><a href="https://rosatom.ru">Росатом</a></li><li><a href="https://atomgoroda.ru">Атомные города</a></li></ul>',
            'in_menu' => true,
            'position' => 1,
        ]);

        // ── Gamification Rules ──
        foreach ([
            ['title' => 'Прохождение этапа курса', 'action' => 'stage_complete', 'points' => 10, 'max_times' => null],
            ['title' => 'Завершение курса', 'action' => 'course_complete', 'points' => 50, 'max_times' => null],
            ['title' => 'Успешный тест', 'action' => 'test_pass', 'points' => 30, 'max_times' => null],
            ['title' => 'Одобренное задание', 'action' => 'assignment_approved', 'points' => 40, 'max_times' => null],
            ['title' => 'Завершение траектории', 'action' => 'trajectory_complete', 'points' => 100, 'max_times' => null],
            ['title' => 'Ежедневный вход', 'action' => 'login_daily', 'points' => 5, 'max_times' => 1],
        ] as $rule) {
            LmsGamificationRule::create(array_merge($rule, [
                'lms_event_id' => $event->id,
                'is_auto' => true,
                'is_active' => true,
            ]));
        }
    }
}
