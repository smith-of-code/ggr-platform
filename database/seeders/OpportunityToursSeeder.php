<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class OpportunityToursSeeder extends Seeder
{
    public function run(): void
    {
        $group = 'opportunity_tours';

        Setting::setGroup($group, [
            'hero_title' => 'Туры возможностей',
            'hero_description' => 'Программа развития внутреннего туризма в атомных городах России',

            'stats' => json_encode([
                ['value' => '47', 'label' => 'Туров реализовано'],
                ['value' => '12 000+', 'label' => 'Гостей посетило атомные города'],
                ['value' => '15', 'label' => 'Городов участвовало в 2025 году'],
                ['value' => '20', 'label' => 'Городов участвует в 2026 году'],
            ], JSON_UNESCAPED_UNICODE),

            'emotions' => json_encode([
                ['icon' => 'heart', 'count' => '8 540', 'label' => 'Нравится'],
                ['icon' => 'eye', 'count' => '3 210', 'label' => 'Удивление'],
                ['icon' => 'fire', 'count' => '5 780', 'label' => 'Огонь'],
                ['icon' => 'thumbs-up', 'count' => '4 120', 'label' => 'Круто'],
                ['icon' => 'star', 'count' => '6 350', 'label' => 'Восторг'],
            ], JSON_UNESCAPED_UNICODE),

            'partners' => json_encode([
                ['name' => 'Росмолодёжь', 'url' => 'https://myrosmol.ru/', 'logo' => '/images/partners/rosmolodezh.png'],
                ['name' => 'Больше чем путешествие', 'url' => 'https://morethantrip.ru/', 'logo' => '/images/partners/morethantrip.png'],
                ['name' => 'ДОБРО.РФ', 'url' => 'https://dobro.ru/', 'logo' => '/images/partners/dobro.png'],
                ['name' => 'Минобрнауки', 'url' => 'https://www.minobrnauki.gov.ru/', 'logo' => '/images/partners/minobrnauki.png'],
                ['name' => 'Студтуризм', 'url' => 'https://студтуризм.рф/', 'logo' => '/images/partners/studtourism.svg'],
            ], JSON_UNESCAPED_UNICODE),

            'socials' => json_encode([
                ['name' => 'ВКонтакте', 'url' => 'https://vk.com/rosatom_travel', 'icon' => 'vk'],
                ['name' => 'Telegram', 'url' => 'https://t.me/rosatom_travel', 'icon' => 'telegram'],
                ['name' => 'YouTube', 'url' => 'https://youtube.com/@rosatom_travel', 'icon' => 'youtube'],
            ], JSON_UNESCAPED_UNICODE),

            'faq' => json_encode([
                [
                    'question' => 'Кто может принять участие в турах?',
                    'answer' => 'Участие в турах открыто для всех желающих старше 18 лет. Для некоторых туров в закрытые города может потребоваться предварительное оформление пропуска, о чём мы информируем заранее.',
                ],
                [
                    'question' => 'Как подать заявку на тур?',
                    'answer' => 'Зарегистрируйтесь на платформе, перейдите в каталог туров, выберите интересующий тур и нажмите кнопку «Оставить заявку». Наш менеджер свяжется с вами для уточнения деталей.',
                ],
                [
                    'question' => 'Включено ли проживание в стоимость тура?',
                    'answer' => 'В большинстве туров проживание включено в стоимость. Точная информация о том, что входит в стоимость, указана в описании каждого конкретного тура.',
                ],
                [
                    'question' => 'Можно ли участвовать с детьми?',
                    'answer' => 'Да, некоторые туры адаптированы для семейного посещения. В каталоге используйте фильтр «Для детей», чтобы найти подходящие программы.',
                ],
                [
                    'question' => 'Как отменить или перенести участие?',
                    'answer' => 'Вы можете отменить участие не позднее чем за 7 дней до начала тура через личный кабинет или связавшись с нашей службой поддержки. Перенос на другие даты возможен при наличии свободных мест.',
                ],
                [
                    'question' => 'Предоставляется ли трансфер?',
                    'answer' => 'В большинстве туров трансфер от вокзала/аэропорта до места размещения включён. Подробности указаны в описании каждого тура.',
                ],
            ], JSON_UNESCAPED_UNICODE),

            'videos' => json_encode([
                ['title' => 'Тур в Саров', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239000&hd=2'],
                ['title' => 'Тур в Обнинск', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239001&hd=2'],
                ['title' => 'Тур в Озёрск', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239002&hd=2'],
                ['title' => 'Тур в Северск', 'embedUrl' => 'https://vk.com/video_ext.php?oid=-200000000&id=456239003&hd=2'],
            ], JSON_UNESCAPED_UNICODE),

            'participation_steps' => json_encode([
                [
                    'title' => 'Зарегистрируйтесь',
                    'description' => 'Создайте личный кабинет на платформе, заполните профиль и укажите свои интересы для подбора подходящего тура.',
                ],
                [
                    'title' => 'Выберите тур',
                    'description' => 'Ознакомьтесь с каталогом доступных туров, выберите подходящие даты и направление. Подайте заявку на участие.',
                ],
                [
                    'title' => 'Отправляйтесь в путешествие',
                    'description' => 'Получите подтверждение участия, подготовьтесь к поездке по нашим рекомендациям и наслаждайтесь незабываемым путешествием.',
                ],
            ], JSON_UNESCAPED_UNICODE),

            'featured_tour_ids' => json_encode(
                Tour::where('is_featured', true)->pluck('id')->toArray()
            ),
        ]);
    }
}
