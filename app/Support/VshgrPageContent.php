<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Дефолты и слияние настроек лендинга /vshgr (группа settings `vshgr_page`).
 */
class VshgrPageContent
{
    public const GROUP = 'vshgr_page';

    public const JSON_KEYS = [
        'socials',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'hero_eyebrow' => 'ВШГР',
            'hero_title' => 'Высшая школа гостеприимного развития',
            'hero_description' => 'Мы готовим профессионалов сферы гостеприимства и сервиса для атомных городов и индустриального туризма — через практику, экспертизу и программы, ориентированные на реальные задачи отрасли.',
            'catalog_title' => 'Программы обучения',
            'catalog_subtitle' => 'Выберите направление и узнайте подробности',
            'catalog_empty_text' => 'Программы скоро появятся в каталоге.',
            'announcements_title' => 'Анонсы и новости',
            'announcements_subtitle' => 'Последние материалы',
            'cta_title' => 'Интересует сотрудничество или вопрос по программам?',
            'cta_body' => 'Оставьте заявку — расскажем подробнее о формате и сроках.',
            'cta_button_label' => 'Хочу узнать подробнее',
            'regulation_url' => 'https://disk.yandex.ru/i/QFSwdBIFTR55EA',
            'regulation_button_label' => 'Положение',
            'regulation_caption' => 'Положение о грантовом конкурсе «Высшая школа гостеприимства Росатома»',
            'form_title' => 'Заявка на консультацию',
            'form_subtitle' => 'Заполните форму — мы свяжемся с вами в ближайшее время.',
            'socials_title' => 'Мы в социальных сетях',
            'socials_subtitle' => 'Следите за нашими новостями',
            'socials' => [
                ['name' => 'ВКонтакте', 'url' => 'https://vk.com/rosatom_travel', 'icon' => 'vk'],
                ['name' => 'Telegram', 'url' => 'https://t.me/rosatom_travel', 'icon' => 'telegram'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $raw  key => value из Setting::getGroup (JSON-поля — строки)
     * @return array<string, mixed>
     */
    public static function mergeFromStored(array $raw): array
    {
        $defaults = self::defaults();
        $merged = $defaults;

        foreach ($defaults as $key => $_) {
            if (! array_key_exists($key, $raw)) {
                continue;
            }

            $value = $raw[$key];

            if (in_array($key, self::JSON_KEYS, true)) {
                $decoded = is_string($value) ? json_decode($value, true) : $value;
                $merged[$key] = is_array($decoded) ? $decoded : $defaults[$key];

                continue;
            }

            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Записать дефолтные значения группы в таблицу settings (для сидов и разовой инициализации).
     */
    public static function seedDefaultsIntoDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $defaults = self::defaults();
        $values = [];
        foreach ($defaults as $key => $value) {
            $values[$key] = in_array($key, self::JSON_KEYS, true)
                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                : $value;
        }

        Setting::setGroup(self::GROUP, $values);
    }
}
