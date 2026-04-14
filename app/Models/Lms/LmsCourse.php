<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class LmsCourse extends Model
{
    protected $table = 'lms_courses';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }

    protected $fillable = [
        'lms_event_id',
        'title',
        'slug',
        'description',
        'image',
        'sequential',
        'is_active',
        'requires_approval',
        'is_mandatory',
        'unlocks_gamification',
        'position',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'sequential' => 'boolean',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'is_mandatory' => 'boolean',
        'unlocks_gamification' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsCourseStage> */
    public function stages(): HasMany
    {
        return $this->hasMany(LmsCourseStage::class, 'lms_course_id')->orderBy('position');
    }

    /** @return HasMany<LmsCourseEnrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsCourseEnrollment::class, 'lms_course_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(LmsCourseModule::class, 'lms_course_id')->orderBy('position');
    }

    public function roleAccess(): BelongsToMany
    {
        return $this->belongsToMany(LmsRole::class, 'lms_course_role_access', 'lms_course_id', 'lms_role_id');
    }

    /** @return BelongsToMany<LmsVideo> */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(LmsVideo::class, 'lms_video_course_access', 'lms_course_id', 'lms_video_id');
    }

    /**
     * Порядок прохождения: этапы без модуля (по position), затем модули по position и этапы внутри модуля по position.
     * Сортировка только по position этапа без учёта модуля ломает «Далее» (перескоки между модулями).
     *
     * @return Collection<int, LmsCourseStage>
     */
    public function stagesInCurriculumOrder(): Collection
    {
        $this->loadMissing(['modules', 'stages']);

        $modulePositions = $this->modules->pluck('position', 'id');

        return $this->stages->sortBy(function ($s) use ($modulePositions) {
            $modulePos = $s->lms_course_module_id
                ? ($modulePositions[$s->lms_course_module_id] ?? 9999)
                : -1;

            return [$modulePos, $s->position];
        })->values();
    }

    /**
     * Доступность этапов для участника (даты модуля/этапа, sequential, флаг is_locked).
     *
     * @return array<int, bool>
     */
    public function stageAvailabilityForUser(User $user): array
    {
        $ordered = $this->stagesInCurriculumOrder();
        if ($ordered->isEmpty()) {
            return [];
        }

        $stageProgress = LmsStageProgress::whereIn('lms_course_stage_id', $ordered->pluck('id'))
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lms_course_stage_id');

        $isSequential = (bool) $this->sequential;
        $availability = [];
        $prevCompleted = true;

        foreach ($ordered as $stage) {
            $progress = $stageProgress->get($stage->id);

            $isAvailable = true;

            if ($stage->is_locked) {
                $isAvailable = false;
            }

            if ($stage->available_from && now()->lt($stage->available_from)) {
                $isAvailable = false;
            }

            if ($stage->lms_course_module_id) {
                $mod = $this->modules->firstWhere('id', $stage->lms_course_module_id);
                if ($mod && ! $mod->isAvailable()) {
                    $isAvailable = false;
                }
            }

            if ($isSequential && ! $prevCompleted) {
                $isAvailable = false;
            }

            $availability[$stage->id] = $isAvailable;

            $prevCompleted = $progress?->status === 'completed';
        }

        return $availability;
    }
}
