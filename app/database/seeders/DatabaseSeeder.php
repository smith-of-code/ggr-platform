<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        foreach ($cities as $i => $data) {
            City::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'image' => $data['image'],
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

        $this->call(LmsSeeder::class);
    }
}
