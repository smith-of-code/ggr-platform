<?php

namespace Database\Seeders;

use App\Http\Controllers\Admin\MainPageController;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class MainPageSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingsService::class);

        $scalars = MainPageController::defaults();

        $json = [
            'block_order' => MainPageController::defaultBlockOrder(),

            'program_stages' => [
                [
                    'step' => 'Этап 1',
                    'title' => 'Исследования туристического потенциала атомных городов',
                    'description' => 'Работе с конкретным городом предшествует комплексное исследование: опросы, интервью и анализ потенциала развития.',
                    'image' => 'https://optim.tildacdn.com/tild3561-3633-4131-b363-376163356263/-/format/webp/1.jpg.webp',
                    'buttonLabel' => 'Перейти к исследованиям',
                    'href' => '/research',
                ],
                [
                    'step' => 'Этап 2',
                    'title' => 'Развитие гостеприимной инфраструктуры',
                    'description' => 'Формируются решения по улучшению городской среды, точек притяжения и сервисов для туристов в атомных городах.',
                    'image' => 'https://optim.tildacdn.com/tild6135-3663-4432-b634-646662353234/-/format/webp/1.jpg.webp',
                    'buttonLabel' => 'Скоро',
                    'href' => null,
                ],
                [
                    'step' => 'Этап 3',
                    'title' => 'Повышение компетенций муниципалитетов',
                    'description' => 'Муниципальные команды усиливают навыки управления, проектирования и продвижения туристических инициатив.',
                    'image' => 'https://optim.tildacdn.com/tild3436-3139-4265-b636-396532643735/-/format/webp/1.jpg.webp',
                    'buttonLabel' => 'Скоро',
                    'href' => null,
                ],
                [
                    'step' => 'Этап 4',
                    'title' => 'Туры возможностей в атомные города',
                    'description' => 'Итоговый этап программы: запуск маршрутов и форматов посещения, объединяющих ключевые возможности городов.',
                    'image' => 'https://optim.tildacdn.com/tild3863-6639-4539-b162-373464633166/-/format/webp/1.jpg.webp',
                    'buttonLabel' => 'Перейти к турам возможностей',
                    'href' => '/opportunity-tours',
                ],
            ],

            'program_cities' => self::programCitiesByYear(),

            'program_results' => self::programResultsByYear(),

            'city_benefits' => [
                ['title' => "Комплексное исследование сферы досуга, отдыха жителей и\u{00a0}потенциала туристической привлекательности", 'image' => 'https://optim.tildacdn.com/tild6664-3264-4361-b931-346231303464/-/cover/720x540/center/center/-/format/webp/1.png.webp'],
                ['title' => "Дополнительное профессиональное образование муниципальной команды, консультации и\u{00a0}работа с\u{00a0}лучшими экспертами отрасли", 'image' => 'https://optim.tildacdn.com/tild3863-3563-4136-b466-323864396336/-/cover/720x540/center/center/-/format/webp/3.png.webp'],
                ['title' => "Гостеприимный акселератор с\u{00a0}грантовой поддержкой для предпринимателей, формирование турпродуктов", 'image' => 'https://optim.tildacdn.com/tild3563-6634-4434-a636-326235366563/-/cover/720x540/center/center/-/format/webp/5.png.webp'],
                ['title' => "Подбор инвестиционной программы и\u{00a0}мер поддержки. Привлечение финансирования из\u{00a0}региональных и\u{00a0}федеральных программ", 'image' => 'https://optim.tildacdn.com/tild6563-3138-4264-a135-303834396533/-/cover/720x540/center/center/-/format/webp/4.png.webp'],
                ['title' => "Формирование гостеприимной среды: создание сообщества, улучшение качества досуга жителей и\u{00a0}гостей, развитие инфраструктуры", 'image' => 'https://optim.tildacdn.com/tild3236-3264-4364-b236-343862356166/-/cover/720x540/center/center/-/format/webp/6.png.webp'],
                ['title' => "Популяризация турпродуктов города на\u{00a0}региональном и\u{00a0}федеральном уровнях: таргетированный турпоток\u{00a0}— потенциальные сотрудники предприятий и\u{00a0}жители атомных городов", 'image' => 'https://optim.tildacdn.com/tild6563-3764-4766-a536-313239376436/-/cover/720x540/center/center/-/format/webp/_3.png.webp'],
            ],

            'additional_initiatives' => [
                ['title' => 'Формирование сети домашних горнолыжных баз', 'image' => 'https://optim.tildacdn.com/tild3431-3935-4066-b135-653938383231/-/cover/520x600/center/center/-/format/webp/___.jpg.webp'],
                ['title' => "Создание сети туристических клубов в\u{00a0}атомных городах", 'image' => 'https://optim.tildacdn.com/tild3832-6433-4266-a161-653737316164/-/cover/520x600/center/center/-/format/webp/__.jpg.webp'],
                ['title' => 'Автомобильный туризм', 'image' => 'https://optim.tildacdn.com/tild3832-3365-4235-b838-666561363566/-/cover/520x600/center/center/-/format/webp/_.jpg.webp'],
                ['title' => 'Гастротуризм', 'image' => 'https://optim.tildacdn.com/tild6232-3331-4662-a434-393066303162/-/cover/520x600/center/center/-/format/webp/photo.jpg.webp'],
                ['title' => 'Водный туризм', 'image' => 'https://optim.tildacdn.com/tild3233-3564-4030-a332-346562636263/-/cover/520x600/center/center/-/format/webp/_.jpg.webp'],
                ['title' => 'Создание сети экологичных «АТОМотелей»', 'image' => 'https://optim.tildacdn.com/tild6632-6637-4131-b030-633935333233/-/cover/520x600/center/center/-/format/webp/photo.jpg.webp'],
                ['title' => 'Создание сувенирной линейки атомных городов', 'image' => 'https://optim.tildacdn.com/tild6166-3735-4531-b561-376461343161/-/cover/520x600/center/center/-/format/webp/__.jpg.webp'],
                ['title' => "Интеграция сферы развития туризма, досуга и\u{00a0}гостеприимства в\u{00a0}мастер-планы городов", 'image' => 'https://optim.tildacdn.com/tild6264-6338-4836-b264-653765373538/-/cover/520x600/center/center/-/format/webp/__-_.jpg.webp'],
            ],

            'videos' => [
                ['title' => 'Гостеприимные города Росатома — о программе', 'thumbnail' => 'https://optim.tildacdn.com/tild3561-3633-4131-b363-376163356263/-/format/webp/1.jpg.webp', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239000&hd=2', 'videoFile' => null],
                ['title' => 'Туры возможностей — как это было', 'thumbnail' => 'https://optim.tildacdn.com/tild3863-6639-4539-b162-373464633166/-/format/webp/1.jpg.webp', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239001&hd=2', 'videoFile' => null],
                ['title' => 'Атомные города — жизнь и перспективы', 'thumbnail' => 'https://optim.tildacdn.com/tild6135-3663-4432-b634-646662353234/-/format/webp/1.jpg.webp', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239002&hd=2', 'videoFile' => null],
            ],

            'contact_bullets' => [
                ['text' => 'Ответ в рабочие дни в течение 1–2 дней'],
                ['text' => 'Консультация без обязательства записи на тур'],
            ],

            'contacts' => [
                ['label' => 'Телефон', 'value' => '+7 (495) 668-28-83', 'href' => 'tel:+74956682883'],
                ['label' => 'E-mail', 'value' => 'info@gostepr.ru', 'href' => 'mailto:info@gostepr.ru'],
                ['label' => 'Адрес', 'value' => 'Москва, ул. Большая Ордынка, 24', 'href' => null],
                ['label' => 'Время работы', 'value' => 'Пн — Пт, 9:00 — 18:00', 'href' => null],
            ],

            'socials' => [
                ['label' => 'VK', 'href' => 'https://vk.com/gostepr', 'icon' => 'vk'],
                ['label' => 'Telegram', 'href' => 'https://t.me/gostepr', 'icon' => 'telegram'],
            ],

            'section_titles' => [
                'program_stages' => ['title' => 'Этапы программы', 'subtitle' => ''],
                'program_cities' => ['title' => 'Города программы', 'subtitle' => 'Города-участники программы «Гостеприимные города Росатома» по годам'],
                'program_results' => ['title' => 'Результаты программы', 'subtitle' => 'Ключевые достижения по годам реализации программы'],
                'city_benefits' => ['title' => 'Что получает город', 'subtitle' => 'Преимущества участия в программе для городов-присутствия Росатома'],
                'additional_initiatives' => ['title' => 'Дополнительные инициативы', 'subtitle' => ''],
                'videos' => ['title' => 'Видеоролики', 'subtitle' => 'Смотрите, как живут и развиваются атомные города'],
                'news' => ['title' => 'Новости', 'subtitle' => 'Последние новости программы'],
                'featured_tours' => ['title' => 'Популярные туры', 'subtitle' => 'Откройте для себя уникальные маршруты'],
                'cities' => ['title' => 'Атомные города', 'subtitle' => 'Современные города с уникальной историей'],
                'map' => ['title' => 'География проекта', 'subtitle' => 'Атомные города на карте России — нажмите на маркер, чтобы узнать о городе и перейти на его страницу'],
                'recipes' => ['title' => 'Книга атомных рецептов', 'subtitle' => 'Блюда из городов атомной отрасли — откройте для себя кулинарные традиции регионов'],
                'timeline' => ['title' => 'Хронология событий', 'subtitle' => 'Ключевые новости, события и вехи развития программы'],
                'contacts' => ['title' => 'Контакты', 'subtitle' => 'Свяжитесь с нами любым удобным способом'],
            ],
        ];

        $values = $scalars;
        foreach ($json as $key => $value) {
            $values[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $settings->setGroup('main_page', $values);
    }

    private static function programCitiesByYear(): array
    {
        $cities = [
            ['name' => 'Саров', 'region' => 'Нижегородская область', 'image' => 'https://optim.tildacdn.com/tild3737-6434-4934-a233-666136616434/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Глазов', 'region' => 'Удмуртская Республика', 'image' => 'https://optim.tildacdn.com/tild3434-6435-4664-b966-383133643365/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Певек', 'region' => 'Чукотский автономный округ', 'image' => 'https://optim.tildacdn.com/tild6466-6638-4634-a336-613335646161/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Билибино', 'region' => 'Чукотский автономный округ', 'image' => 'https://optim.tildacdn.com/tild3066-3934-4232-b065-383932616531/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Советск', 'region' => 'Калининградская область', 'image' => 'https://optim.tildacdn.com/tild3266-3161-4831-b938-653732366261/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Неман', 'region' => 'Калининградская область', 'image' => 'https://optim.tildacdn.com/tild6632-3634-4530-a138-346334333565/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Нововоронеж', 'region' => 'Воронежская область', 'image' => 'https://optim.tildacdn.com/tild3563-3666-4863-b838-393533643238/-/cover/520x600/center/center/-/format/webp/photo.png.webp'],
            ['name' => 'Сосновый Бор', 'region' => 'Ленинградская область', 'image' => 'https://optim.tildacdn.com/tild3330-6438-4566-a263-363731383763/-/cover/520x600/center/center/-/format/webp/_.png.webp'],
        ];

        return [
            ['year' => 2026, 'cities' => $cities],
            ['year' => 2025, 'cities' => $cities],
            ['year' => 2024, 'cities' => $cities],
        ];
    }

    private static function programResultsByYear(): array
    {
        $results = [
            ['value' => '13 млн руб.', 'description' => 'грантовой поддержки от «Росатома» получили четырнадцать лучших предпринимательских проектов «Гостеприимного акселератора „Росатома"»'],
            ['value' => '74 «Тура возможностей»', 'description' => 'в Волгодонск, Полярные Зори и Железногорск совместно с программами «Больше, чем путешествие» и «Студтуризм»'],
            ['value' => '>25 млн руб.', 'description' => 'общая сумма привлечённых инвестиций в проекты'],
            ['value' => '>100 382 654 млн руб.', 'description' => 'доход субъектов туристической деятельности в атомных городах от Туров возможностей'],
            ['value' => '6 туроператоров', 'description' => 'создано в атомных городах Железногорск, Саров, Трёхгорный, Волгодонск, Глазов, Сосновый Бор'],
        ];

        return [
            ['year' => 2025, 'results' => $results],
            ['year' => 2024, 'results' => $results],
        ];
    }
}
