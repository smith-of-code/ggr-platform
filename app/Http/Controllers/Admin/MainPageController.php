<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MainPageController extends Controller
{
    private const GROUP = 'main_page';

    private const JSON_KEYS = [
        'block_order',
        'program_stages',
        'program_cities',
        'program_results',
        'city_benefits',
        'additional_initiatives',
        'videos',
        'contact_bullets',
        'contacts',
        'socials',
        'section_titles',
        'stats_cards',
        'video_presentation',
    ];

    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    public function index(): Response
    {
        $raw = $this->settings->getGroupFresh(self::GROUP);

        $data = [];
        foreach ($raw as $key => $value) {
            $data[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_decode($value, true) ?? []
                : $value;
        }

        if (empty($data['block_order'])) {
            $data['block_order'] = self::defaultBlockOrder();
        } else {
            $existingIds = array_column($data['block_order'], 'id');
            foreach (self::defaultBlockOrder() as $block) {
                if (!in_array($block['id'], $existingIds, true)) {
                    $data['block_order'][] = $block;
                }
            }
        }

        return Inertia::render('Admin/MainPage/Index', [
            'pageData' => $data,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_description' => 'required|string|max:1000',
            'hero_bg_image' => 'nullable|string|max:500',
            'hero_bg_color_from' => 'nullable|string|max:20',
            'hero_bg_color_via' => 'nullable|string|max:20',
            'hero_bg_color_to' => 'nullable|string|max:20',
            'hero_text_color' => 'nullable|string|max:20',
            'hero_bg_color_enabled' => 'boolean',

            'program_stages' => 'nullable|array',
            'program_stages.*.step' => 'required|string|max:50',
            'program_stages.*.title' => 'required|string|max:255',
            'program_stages.*.description' => 'required|string|max:2000',
            'program_stages.*.image' => 'nullable|string|max:500',
            'program_stages.*.buttonLabel' => 'required|string|max:100',
            'program_stages.*.href' => 'nullable|string|max:500',

            'program_cities' => 'nullable|array',
            'program_cities.*.year' => 'required|integer|min:2020|max:2100',
            'program_cities.*.cities' => 'required|array|min:1',
            'program_cities.*.cities.*.name' => 'required|string|max:255',
            'program_cities.*.cities.*.region' => 'required|string|max:255',
            'program_cities.*.cities.*.image' => 'nullable|string|max:500',

            'program_results' => 'nullable|array',
            'program_results.*.year' => 'required|integer|min:2020|max:2100',
            'program_results.*.results' => 'required|array|min:1',
            'program_results.*.results.*.value' => 'required|string|max:100',
            'program_results.*.results.*.description' => 'required|string|max:2000',
            'program_results_image' => 'nullable|string|max:500',

            'city_benefits' => 'nullable|array',
            'city_benefits.*.title' => 'required|string|max:500',
            'city_benefits.*.image' => 'nullable|string|max:500',

            'additional_initiatives' => 'nullable|array',
            'additional_initiatives.*.title' => 'required|string|max:500',
            'additional_initiatives.*.image' => 'nullable|string|max:500',

            'videos' => 'nullable|array',
            'videos.*.title' => 'required|string|max:255',
            'videos.*.thumbnail' => 'nullable|string|max:500',
            'videos.*.embedUrl' => 'nullable|string|max:500',
            'videos.*.videoFile' => 'nullable|string|max:500',

            'video_presentation' => 'nullable|array',
            'video_presentation.video_embed_url' => 'nullable|string|max:500',
            'video_presentation.video_file' => 'nullable|string|max:500',
            'video_presentation.video_thumbnail' => 'nullable|string|max:500',
            'video_presentation.video_title' => 'nullable|string|max:255',
            'video_presentation.mission' => 'nullable|string|max:2000',
            'video_presentation.goals' => 'nullable|array',
            'video_presentation.goals.*.text' => 'required|string|max:500',
            'video_presentation.values' => 'nullable|array',
            'video_presentation.values.*.text' => 'required|string|max:500',
            'video_presentation.organizers' => 'nullable|array',
            'video_presentation.organizers.*.name' => 'required|string|max:255',
            'video_presentation.organizers.*.role' => 'nullable|string|max:255',
            'video_presentation.organizers.*.image' => 'nullable|string|max:500',
            'video_presentation.history' => 'nullable|string|max:5000',
            'video_presentation.facts' => 'nullable|array',
            'video_presentation.facts.*.value' => 'required|string|max:100',
            'video_presentation.facts.*.label' => 'required|string|max:255',
            'video_presentation.audience' => 'nullable|string|max:2000',

            'moving_title' => 'required|string|max:255',
            'moving_description' => 'required|string|max:1000',

            'stats_cards' => 'nullable|array',
            'stats_cards.*.icon' => 'required|string|max:50',
            'stats_cards.*.label' => 'required|string|max:100',
            'stats_cards.*.source' => 'required|string|in:cities,tours,events,custom',
            'stats_cards.*.value' => 'nullable|string|max:100',

            'cta_title' => 'required|string|max:255',
            'cta_description' => 'required|string|max:1000',

            'contact_title' => 'required|string|max:255',
            'contact_description' => 'required|string|max:1000',
            'contact_left_text' => 'required|string|max:2000',
            'contact_bullets' => 'nullable|array',
            'contact_bullets.*.text' => 'required|string|max:500',

            'contacts' => 'nullable|array',
            'contacts.*.label' => 'required|string|max:100',
            'contacts.*.value' => 'required|string|max:255',
            'contacts.*.href' => 'nullable|string|max:500',

            'socials' => 'nullable|array',
            'socials.*.label' => 'required|string|max:100',
            'socials.*.href' => 'required|string|max:500',
            'socials.*.icon' => 'required|string|max:50',

            'section_titles' => 'nullable|array',

            'block_order' => 'required|array|min:1',
            'block_order.*.id' => 'required|string|max:50',
            'block_order.*.enabled' => 'required|boolean',
        ]);

        $validated['hero_bg_color_enabled'] = $request->boolean('hero_bg_color_enabled') ? '1' : '0';

        $values = [];
        foreach ($validated as $key => $value) {
            $values[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        $this->settings->setGroup(self::GROUP, $values);

        return redirect()->back()->with('success', 'Главная страница сохранена');
    }

    public static function defaultBlockOrder(): array
    {
        return [
            ['id' => 'hero', 'enabled' => true],
            ['id' => 'program_stages', 'enabled' => true],
            ['id' => 'program_cities', 'enabled' => true],
            ['id' => 'program_results', 'enabled' => true],
            ['id' => 'city_benefits', 'enabled' => true],
            ['id' => 'additional_initiatives', 'enabled' => true],
            ['id' => 'videos', 'enabled' => true],
            ['id' => 'video_presentation', 'enabled' => true],
            ['id' => 'news', 'enabled' => true],
            ['id' => 'moving', 'enabled' => true],
            ['id' => 'stats', 'enabled' => true],
            ['id' => 'featured_tours', 'enabled' => true],
            ['id' => 'cities', 'enabled' => true],
            ['id' => 'map', 'enabled' => true],
            ['id' => 'recipes', 'enabled' => true],
            ['id' => 'timeline', 'enabled' => true],
            ['id' => 'cta', 'enabled' => true],
            ['id' => 'contact_form', 'enabled' => true],
            ['id' => 'contacts', 'enabled' => true],
        ];
    }

    public static function defaultStatsCards(): array
    {
        return [
            ['icon' => 'building', 'label' => 'Атомных городов', 'source' => 'cities', 'value' => ''],
            ['icon' => 'map', 'label' => 'Туров возможностей', 'source' => 'tours', 'value' => ''],
            ['icon' => 'calendar', 'label' => 'Событий в хронологии', 'source' => 'events', 'value' => ''],
            ['icon' => 'users', 'label' => 'Гостей', 'source' => 'custom', 'value' => '3000+'],
        ];
    }

    public static function defaults(): array
    {
        return [
            'hero_title' => 'Гостеприимные города Росатома',
            'hero_description' => 'Цифровая экосистема для развития туристического, образовательного и предпринимательского потенциала атомных городов',
            'hero_bg_image' => '/images/unsplash/hero-bg.jpg',
            'hero_bg_color_from' => '',
            'hero_bg_color_via' => '',
            'hero_bg_color_to' => '',
            'hero_text_color' => '',
            'hero_bg_color_enabled' => '0',
            'moving_title' => 'Переезжаем',
            'moving_description' => 'Узнайте о возможностях переезда в атомные города — программа поддержки, условия и перспективы',
            'cta_title' => 'Хотите узнать подробнее о программе?',
            'cta_description' => 'Оставьте заявку, и мы свяжемся с вами в ближайшее время',
            'contact_title' => 'Хочу узнать подробнее',
            'contact_description' => 'Заполните форму — мы ответим на вопросы о турах, городах и возможностях программы',
            'contact_left_text' => 'Команда проекта поможет подобрать маршрут, расскажет о датах и условиях участия.',
            'program_results_image' => 'https://optim.tildacdn.com/tild3735-3663-4333-b331-333938383739/-/format/webp/Mask_group.png.webp',
            'video_presentation' => json_encode([
                'video_embed_url' => 'https://vk.com/video_ext.php?oid=-227560448&id=456239017&hd=2',
                'video_file' => '',
                'video_thumbnail' => 'https://optim.tildacdn.com/tild6665-6230-4637-b062-623564353066/-/format/webp/Frame_427320811.png.webp',
                'video_title' => 'О программе «Гостеприимные города Росатома»',
                'mission' => 'Развитие туристического, образовательного и предпринимательского потенциала атомных городов России. Мы создаём условия для устойчивого роста территорий присутствия Росатома, объединяя жителей, бизнес и власть вокруг общей цели — сделать атомные города лучшими местами для жизни.',
                'goals' => [
                    ['text' => 'Повышение туристической привлекательности атомных городов'],
                    ['text' => 'Развитие городской среды и инфраструктуры гостеприимства'],
                    ['text' => 'Поддержка местных предпринимателей и креативных индустрий'],
                    ['text' => 'Создание образовательных программ для жителей и гостей'],
                    ['text' => 'Формирование позитивного имиджа атомной отрасли'],
                    ['text' => 'Укрепление межгородского сотрудничества и обмена опытом'],
                ],
                'values' => [
                    ['text' => 'Открытость и прозрачность'],
                    ['text' => 'Устойчивое развитие'],
                    ['text' => 'Инновации и технологии'],
                    ['text' => 'Забота о людях'],
                ],
                'organizers' => [
                    ['name' => 'Госкорпорация «Росатом»', 'role' => 'Генеральный партнёр программы', 'image' => ''],
                    ['name' => 'АНО «Гостеприимные города»', 'role' => 'Оператор программы', 'image' => ''],
                    ['name' => 'Администрации атомных городов', 'role' => 'Региональные партнёры', 'image' => ''],
                ],
                'history' => 'Программа «Гостеприимные города Росатома» стартовала в 2022 году как инициатива по развитию внутреннего туризма в городах присутствия Госкорпорации «Росатом». За время существования программа объединила десятки атомных городов по всей России, запустила образовательные проекты, туры возможностей и гастрономические фестивали. Сегодня это масштабная экосистема, которая помогает раскрывать потенциал территорий и создавать новые точки роста.',
                'facts' => [
                    ['value' => '50+', 'label' => 'городов-участников'],
                    ['value' => '3 000+', 'label' => 'гостей программы'],
                    ['value' => '100+', 'label' => 'проведённых мероприятий'],
                    ['value' => '25+', 'label' => 'туров возможностей'],
                ],
                'audience' => 'Жители атомных городов, молодые специалисты, предприниматели, представители креативных индустрий, туристы, студенты и выпускники вузов, а также все, кто хочет узнать больше о жизни и возможностях в городах Росатома.',
            ], JSON_UNESCAPED_UNICODE),
        ];
    }
}
