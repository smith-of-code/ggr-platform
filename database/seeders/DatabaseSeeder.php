<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\City;
use App\Models\Post;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rosatom-travel.ru'],
            [
                'name' => 'Администратор',
                'password' => bcrypt('T7qK8oQEksH5yq'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $cities = [
            [
                'name' => 'Сосновый Бор',
                'slug' => 'sosnovy-bor',
                'description' => 'Город на южном побережье Финского залива в 80 км от Санкт-Петербурга. Здесь расположена Ленинградская атомная станция — один из флагманов отечественной атомной энергетики. Сосновые леса, пляжи с белым песком и уютная городская среда создают атмосферу для жизни, работы и отдыха.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Leningrad_Nuclear_Power_Plant_II_01.jpg/1280px-Leningrad_Nuclear_Power_Plant_II_01.jpg',
            ],
            [
                'name' => 'Нововоронеж',
                'slug' => 'novovoronezh',
                'description' => 'Город атомщиков на берегу Дона в Воронежской области. Нововоронежская АЭС — первая в России станция с реактором ВВЭР, ставшим мировым стандартом. Живописная природа Черноземья, исторические достопримечательности и современная инфраструктура делают город привлекательным для туристов.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Novovoronezh_Nuclear_Power_Plant_II.png/960px-Novovoronezh_Nuclear_Power_Plant_II.png',
            ],
            [
                'name' => 'Обнинск',
                'slug' => 'obninsk',
                'description' => 'Первый наукоград России и город, где была запущена первая в мире атомная электростанция в 1954 году. Сегодня — крупный научный и образовательный центр Калужской области с институтами, лабораториями и инновационными предприятиями Росатома.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/RIAN_archive_409173_World%27s_first_nuclear_power_plant_in_Obninsk.jpg/960px-RIAN_archive_409173_World%27s_first_nuclear_power_plant_in_Obninsk.jpg',
            ],
            [
                'name' => 'Саров',
                'slug' => 'sarov',
                'description' => 'Закрытый город в Нижегородской области — родина российского ядерного щита. Саров (бывший Арзамас-16) — это РФЯЦ-ВНИИЭФ, уникальная история и Серафимо-Дивеевский монастырь. Город высокой науки и духовности.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Diveevo_Monastery_-_panoramio.jpg/1280px-Diveevo_Monastery_-_panoramio.jpg',
            ],
            [
                'name' => 'Снежинск',
                'slug' => 'snezhinks',
                'description' => 'Закрытый город на Урале в окружении озёр и хвойных лесов. Здесь расположен РФЯЦ-ВНИИТФ — один из двух ядерных оружейных центров России. Уральская природа, горнолыжные склоны и чистейший воздух привлекают любителей активного отдыха.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Ural_Mountains_Winter_woods_%2832035729862%29.jpg/1280px-Ural_Mountains_Winter_woods_%2832035729862%29.jpg',
            ],
            [
                'name' => 'Заречный',
                'slug' => 'zarechny',
                'description' => 'Город при Белоярской АЭС в Свердловской области — единственной в мире станции с реактором на быстрых нейтронах БН-800. Современный город с развитой инфраструктурой в окружении живописных уральских пейзажей.',
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/26/%D0%91%D0%B5%D0%BB%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B0%D1%8F_%D0%90%D0%AD%D0%A1_2019_%D0%B3%D0%BE%D0%B4.jpg/1280px-%D0%91%D0%B5%D0%BB%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B0%D1%8F_%D0%90%D0%AD%D0%A1_2019_%D0%B3%D0%BE%D0%B4.jpg',
            ],
        ];

        $infrastructure = [
            'sosnovy-bor' => ['work' => 85, 'housing' => 78, 'leisure' => 72, 'education' => 80, 'medicine' => 75],
            'novovoronezh' => ['work' => 80, 'housing' => 82, 'leisure' => 65, 'education' => 70, 'medicine' => 72],
            'obninsk' => ['work' => 88, 'housing' => 75, 'leisure' => 78, 'education' => 92, 'medicine' => 80],
            'sarov' => ['work' => 90, 'housing' => 80, 'leisure' => 68, 'education' => 85, 'medicine' => 78],
            'snezhinks' => ['work' => 82, 'housing' => 76, 'leisure' => 74, 'education' => 78, 'medicine' => 70],
            'zarechny' => ['work' => 84, 'housing' => 80, 'leisure' => 70, 'education' => 74, 'medicine' => 73],
        ];

        foreach ($cities as $i => $data) {
            City::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'image' => $data['image'],
                'infrastructure' => $infrastructure[$data['slug']] ?? null,
                'position' => $i + 1,
                'is_active' => true,
            ]);
        }

        $tours = [
            [
                'title' => 'Зелёный Атом: экотур в сердце энергетики',
                'slug' => 'zelenyy-atom',
                'start_city' => 'Санкт-Петербург',
                'duration' => '2 дня, 1 ночь',
                'project' => 'start_atomgrad',
                'participation_type' => 'paid',
                'season' => 'spring',
                'for_children' => true,
                'price_from' => 55000,
                'description' => 'Двухдневный экотур по атомграду Сосновый Бор — единение природы и передовых технологий. Посещение Ленинградской АЭС, экскурсия по Финскому заливу, мастер-классы по экологии и энергосбережению.',
                'is_featured' => true,
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Seabord_of_Gulf_of_Finland_Komarovo%2C_St_Petersburg%2C_Russia.jpg/1280px-Seabord_of_Gulf_of_Finland_Komarovo%2C_St_Petersburg%2C_Russia.jpg',
            ],
            [
                'title' => 'Атомная энергия знаний',
                'slug' => 'atomnaya-energiya-znaniy',
                'start_city' => 'Москва',
                'duration' => '3 дня, 2 ночи',
                'project' => 'start_atomgrad',
                'participation_type' => 'contest',
                'season' => 'summer',
                'for_children' => false,
                'price_from' => 0,
                'description' => 'Образовательный тур для студентов и молодых специалистов. Посещение первой в мире АЭС в Обнинске, лекции от учёных, квест по научным лабораториям и встречи с ведущими инженерами отрасли.',
                'is_featured' => true,
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Obninsk_04710062_%288383320538%29.jpg/1280px-Obninsk_04710062_%288383320538%29.jpg',
            ],
            [
                'title' => 'Тайны закрытых городов',
                'slug' => 'tayny-zakrytyh-gorodov',
                'start_city' => 'Нижний Новгород',
                'duration' => '4 дня, 3 ночи',
                'project' => 'start_atomgrad',
                'participation_type' => 'paid',
                'season' => 'autumn',
                'for_children' => false,
                'closed_city' => true,
                'price_from' => 85000,
                'description' => 'Уникальный тур в Саров — закрытый город с богатейшей историей. Знакомство с наследием великих учёных, посещение музеев ядерного оружия и Серафимо-Дивеевского монастыря.',
                'is_featured' => true,
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Diveevo_Monastery_-_panoramio.jpg/1280px-Diveevo_Monastery_-_panoramio.jpg',
            ],
            [
                'title' => 'Атомы вкуса: гастротур',
                'slug' => 'atomy-vkusa-gastrotur',
                'start_city' => 'Воронеж',
                'duration' => '2 дня, 1 ночь',
                'project' => 'atoms_vkusa',
                'participation_type' => 'paid',
                'season' => 'summer',
                'for_children' => true,
                'price_from' => 35000,
                'description' => 'Гастрономический тур по Нововоронежу и Воронежской области. Донская кухня, фермерские продукты, мастер-классы от шеф-поваров и знакомство с экопроизводствами атомграда.',
                'is_featured' => false,
                'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&h=450&fit=crop',
            ],
            [
                'title' => 'Уральские горизонты',
                'slug' => 'uralskie-gorizonty',
                'start_city' => 'Екатеринбург',
                'duration' => '3 дня, 2 ночи',
                'project' => 'start_atomgrad',
                'participation_type' => 'paid',
                'season' => 'winter',
                'for_children' => true,
                'price_from' => 65000,
                'description' => 'Зимний тур на Урал: горнолыжные склоны Снежинска, ледяные скульптуры, экскурсия на Белоярскую АЭС с уникальным реактором на быстрых нейтронах и посещение города Заречный.',
                'is_featured' => false,
                'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Ural_Mountains_Winter_woods_%2832035729862%29.jpg/1280px-Ural_Mountains_Winter_woods_%2832035729862%29.jpg',
            ],
        ];

        $cityMap = [
            'zelenyy-atom' => 'sosnovy-bor',
            'atomnaya-energiya-znaniy' => 'obninsk',
            'tayny-zakrytyh-gorodov' => 'sarov',
            'atomy-vkusa-gastrotur' => 'novovoronezh',
            'uralskie-gorizonty' => 'snezhinks',
        ];

        foreach ($tours as $i => $data) {
            $tour = Tour::create(array_merge($data, [
                'position' => $i + 1,
                'is_active' => true,
                'group_size' => 'до 30 человек',
                'min_age' => $data['for_children'] ? 10 : 18,
            ]));

            $citySlug = $cityMap[$data['slug']] ?? 'sosnovy-bor';
            $city = City::where('slug', $citySlug)->first();
            if ($city) {
                $tour->cities()->attach($city->id);
            }

            TourDeparture::create([
                'tour_id' => $tour->id,
                'start_date' => now()->addMonths(2)->startOfMonth(),
                'end_date' => now()->addMonths(2)->startOfMonth()->addDays(
                    str_contains($data['duration'], '2 дня') ? 1 :
                    (str_contains($data['duration'], '3 дня') ? 2 : 3)
                ),
                'price_per_person' => $tour->price_from ?: 0,
            ]);

            TourDeparture::create([
                'tour_id' => $tour->id,
                'start_date' => now()->addMonths(4)->startOfMonth(),
                'end_date' => now()->addMonths(4)->startOfMonth()->addDays(
                    str_contains($data['duration'], '2 дня') ? 1 :
                    (str_contains($data['duration'], '3 дня') ? 2 : 3)
                ),
                'price_per_person' => $tour->price_from ?: 0,
            ]);
        }

        // ── Posts ──
        $posts = [
            [
                'title' => 'Программа «Гостеприимные города Росатома» вошла в топ-10 лучших региональных инициатив',
                'slug' => 'programma-ggr-top-10',
                'excerpt' => 'По итогам всероссийского рейтинга программа «Гостеприимные города Росатома» признана одной из лучших инициатив в области внутреннего туризма.',
                'content' => '<p>Программа «Гостеприимные города Росатома» вошла в десятку лучших региональных инициатив по развитию внутреннего туризма в России. Рейтинг был составлен Министерством экономического развития совместно с экспертным советом по туризму.</p><p>Программа охватывает шесть атомных городов и предлагает уникальные туристические маршруты, знакомящие гостей с историей и современностью отечественной атомной отрасли.</p><p>«Мы рады, что наша работа получила высокую оценку. Это подтверждает, что атомные города обладают огромным туристическим потенциалом», — отметил координатор программы.</p>',
                'category' => 'news',
                'tags' => ['достижения', 'рейтинг', 'туризм'],
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Стартовал набор на летний тур «Зелёный Атом»',
                'slug' => 'nabor-zelenyy-atom-leto',
                'excerpt' => 'Открыт приём заявок на летний экотур в Сосновый Бор. Ограниченное количество мест — успейте забронировать!',
                'content' => '<p>Рады сообщить, что открыт приём заявок на летний экотур «Зелёный Атом» в город Сосновый Бор. Тур пройдёт с 15 по 16 июля 2026 года.</p><p>В программе: посещение Ленинградской АЭС, прогулка по побережью Финского залива, мастер-класс по экологическому мониторингу и знакомство с городской инфраструктурой.</p><p>Количество мест ограничено — 30 участников в группе. Стоимость участия — от 55 000 рублей. В стоимость входит проживание, питание, трансфер и экскурсионная программа.</p>',
                'category' => 'announcements',
                'tags' => ['набор', 'Сосновый Бор', 'экотур', 'лето'],
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Обнинск: от первой АЭС до наукограда будущего',
                'slug' => 'obninsk-ot-pervoy-aes-do-naukograda',
                'excerpt' => 'Как маленький город в Калужской области стал колыбелью мирного атома и центром научного туризма.',
                'content' => '<p>В 1954 году в Обнинске была запущена первая в мире атомная электростанция. Этот исторический момент определил не только судьбу города, но и всей мировой энергетики.</p><p>Сегодня Обнинск — первый наукоград России с населением около 120 тысяч человек. Здесь расположены десятки научно-исследовательских институтов, включая НИФХИ им. Карпова и ФЭИ.</p><p>Туристам город предлагает уникальную возможность прикоснуться к истории мирного атома, посетить музей первой АЭС и познакомиться с современными разработками в области ядерных технологий.</p>',
                'category' => 'partner_articles',
                'tags' => ['Обнинск', 'история', 'наукоград', 'первая АЭС'],
                'is_published' => true,
                'published_at' => now()->subWeek(),
            ],
            [
                'title' => 'Итоги зимнего сезона: более 500 туристов посетили атомные города',
                'slug' => 'itogi-zimnego-sezona',
                'excerpt' => 'Подводим итоги зимнего туристического сезона программы «Гостеприимные города Росатома».',
                'content' => '<p>Зимний сезон 2025–2026 стал рекордным для программы «Гостеприимные города Росатома». За три месяца атомные города посетили более 500 туристов из 42 регионов России.</p><p>Самым популярным направлением стал тур «Уральские горизонты» в Снежинск и Заречный — его выбрали 180 участников. На втором месте — «Тайны закрытых городов» с посещением Сарова (150 человек).</p><p>Средняя оценка удовлетворённости туристов составила 4,8 из 5 баллов. Особенно высоко были оценены экскурсионная программа и качество размещения.</p>',
                'category' => 'news',
                'tags' => ['итоги', 'статистика', 'зимний сезон'],
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Новый маршрут: гастрономический тур по Нововоронежу',
                'slug' => 'novyy-marshrut-gastrotur-novovoronezh',
                'excerpt' => 'В каталог программы добавлен новый гастрономический маршрут «Атомы вкуса» в Нововоронеже.',
                'content' => '<p>С радостью представляем новый маршрут в рамках проекта «Атомы вкуса» — гастрономический тур по Нововоронежу и Воронежской области.</p><p>Участники познакомятся с традиционной донской кухней, посетят фермерские хозяйства и экопроизводства, приготовят блюда под руководством шеф-поваров и узнают, как атомная энергия способствует развитию экологически чистого сельского хозяйства в регионе.</p><p>Тур рассчитан на 2 дня и доступен для участников с детьми от 10 лет.</p>',
                'category' => 'announcements',
                'tags' => ['новый маршрут', 'гастротур', 'Нововоронеж'],
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Саров: город, где создавался ядерный щит страны',
                'slug' => 'sarov-gorod-yadernogo-shchita',
                'excerpt' => 'История и современность Сарова — от Арзамаса-16 до современного научного центра мирового уровня.',
                'content' => '<p>Саров — один из самых засекреченных городов России, где в 1940-х годах началась работа над созданием отечественного ядерного оружия. Именно здесь, в Конструкторском бюро №11 (ныне РФЯЦ-ВНИИЭФ), под руководством Юлия Харитона была создана первая советская атомная бомба.</p><p>Сегодня Саров — современный научный центр, сочетающий передовые технологии и богатую духовную традицию. Серафимо-Дивеевский монастырь, расположенный рядом с городом, ежегодно привлекает тысячи паломников.</p><p>Тур «Тайны закрытых городов» предоставляет уникальную возможность заглянуть за ограду и увидеть город, который десятилетиями был скрыт от внешнего мира.</p>',
                'category' => 'partner_articles',
                'tags' => ['Саров', 'ЗАТО', 'история', 'РФЯЦ-ВНИИЭФ'],
                'is_published' => true,
                'published_at' => now()->subWeeks(2),
            ],
        ];

        foreach ($posts as $data) {
            Post::create($data);
        }

        // ── Applications ──
        $firstTour = Tour::first();
        $firstDeparture = TourDeparture::first();

        $applications = [
            [
                'type' => 'tour',
                'name' => 'Иванова Мария Сергеевна',
                'email' => 'ivanova@example.com',
                'phone' => '+7 (916) 123-45-67',
                'tour_id' => $firstTour?->id,
                'tour_departure_id' => $firstDeparture?->id,
                'data' => ['participants' => 2, 'comment' => 'Едем с ребёнком 12 лет'],
                'status' => 'new',
            ],
            [
                'type' => 'tour',
                'name' => 'Петров Алексей Николаевич',
                'email' => 'petrov@example.com',
                'phone' => '+7 (926) 987-65-43',
                'tour_id' => $firstTour?->id,
                'tour_departure_id' => $firstDeparture?->id,
                'data' => ['participants' => 1, 'comment' => 'Интересует программа для инженеров'],
                'status' => 'new',
            ],
            [
                'type' => 'research',
                'name' => 'Сидорова Елена Владимировна',
                'email' => 'sidorova@example.com',
                'phone' => '+7 (905) 555-12-34',
                'data' => ['organization' => 'МГУ им. Ломоносова', 'topic' => 'Экотуризм в моногородах'],
                'status' => 'new',
            ],
            [
                'type' => 'program_info',
                'name' => 'Козлов Дмитрий Андреевич',
                'email' => 'kozlov@example.com',
                'phone' => '+7 (912) 333-22-11',
                'data' => ['question' => 'Есть ли программы для корпоративных групп?'],
                'status' => 'new',
            ],
        ];

        foreach ($applications as $data) {
            Application::create($data);
        }

        $this->call(LmsSeeder::class);
        $this->call(PortalSeeder::class);
        $this->call(OpportunityToursSeeder::class);
        $this->call(ResearchPageSeeder::class);
    }
}
