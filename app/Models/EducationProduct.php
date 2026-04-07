<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationProduct extends Model
{
    public const TYPE_EDUCATION = 'education';
    public const TYPE_PARTNER = 'partner';
    public const TYPE_INTERNATIONAL = 'international';

    public const TYPES = [
        self::TYPE_EDUCATION,
        self::TYPE_PARTNER,
        self::TYPE_INTERNATIONAL,
    ];

    public const SECTION_DEFINITIONS = [
        self::TYPE_EDUCATION => [
            'description_goal',
            'results',
            'streams_formats',
            'target_audience',
            'directions',
            'what_we_offer',
            'selection_criteria',
            'experts',
            'best_cases',
            'regulation',
            'application_form',
            'training_request',
        ],
        self::TYPE_PARTNER => [
            'description_goal',
            'participation_conditions',
        ],
        self::TYPE_INTERNATIONAL => [
            'description_goal',
        ],
    ];

    public const SECTION_LABELS = [
        'description_goal' => 'Описание и цель',
        'results' => 'Результаты работы',
        'streams_formats' => 'Потоки / форматы',
        'target_audience' => 'Целевая аудитория',
        'directions' => 'Направления',
        'what_we_offer' => 'Что мы предлагаем',
        'selection_criteria' => 'Критерии участия / отбора',
        'experts' => 'Эксперты',
        'best_cases' => 'Лучшие кейсы / прошлый опыт',
        'regulation' => 'Положение',
        'application_form' => 'Подача заявки',
        'training_request' => 'Заявка на обучение в регионе',
        'participation_conditions' => 'Условия участия',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'duration',
        'format',
        'target_audience',
        'price_info',
        'position',
        'is_active',
        'type',
        'sections',
        'countries',
        'regulation_file',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sections' => 'array',
        'countries' => 'array',
    ];

    public function getTypeAttribute($value): string
    {
        return $value ?? self::TYPE_EDUCATION;
    }

    public function getAllowedSections(): array
    {
        return self::SECTION_DEFINITIONS[$this->type] ?? [];
    }

    public function getSectionContent(string $slug): ?string
    {
        return $this->sections[$slug]['content'] ?? null;
    }

    public function isSectionEnabled(string $slug): bool
    {
        return (bool) ($this->sections[$slug]['enabled'] ?? false);
    }
}
