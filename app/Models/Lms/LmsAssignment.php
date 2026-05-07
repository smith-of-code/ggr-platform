<?php

namespace App\Models\Lms;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignment extends Model
{
    protected $table = 'lms_assignments';

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->utc()->format('Y-m-d\TH:i:s\Z');
    }

    protected $fillable = [
        'lms_event_id',
        'title',
        'description',
        'template_file',
        'template_file_name',
        'completion_mode',
        'deadline',
        'is_active',
        'gamification_points',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_active' => 'boolean',
        'gamification_points' => 'integer',
    ];

    /** @return BelongsTo<LmsEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    /** @return HasMany<LmsAssignmentSubmission> */
    public function submissions(): HasMany
    {
        return $this->hasMany(LmsAssignmentSubmission::class, 'lms_assignment_id');
    }

    /** @return HasMany<LmsAssignmentTask> */
    public function tasks(): HasMany
    {
        return $this->hasMany(LmsAssignmentTask::class, 'lms_assignment_id')->orderBy('position');
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    public function templateFiles(): array
    {
        if (! $this->template_file) {
            return [];
        }

        $decoded = json_decode($this->template_file, true);
        if (is_array($decoded)) {
            return collect($decoded)
                ->map(fn ($file) => $this->normalizeTemplateFile($file))
                ->filter()
                ->values()
                ->all();
        }

        return [[
            'name' => $this->template_file_name ?: basename(parse_url($this->template_file, PHP_URL_PATH) ?: 'template'),
            'path' => $this->template_file,
        ]];
    }

    /**
     * @param  array<int, array{name?: string, path?: string}>  $files
     */
    public function setTemplateFiles(array $files): void
    {
        $normalized = collect($files)
            ->map(fn ($file) => $this->normalizeTemplateFile($file))
            ->filter()
            ->values()
            ->all();

        $this->template_file = $normalized === [] ? null : json_encode($normalized, JSON_UNESCAPED_UNICODE);
        $this->template_file_name = $normalized[0]['name'] ?? null;
    }

    /**
     * @param  mixed  $file
     * @return array{name: string, path: string}|null
     */
    private function normalizeTemplateFile(mixed $file): ?array
    {
        if (! is_array($file)) {
            return null;
        }

        $path = $file['path'] ?? $file['url'] ?? null;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $name = is_string($file['name'] ?? null) && trim($file['name']) !== ''
            ? trim($file['name'])
            : basename(parse_url($path, PHP_URL_PATH) ?: 'template');

        return ['name' => $name, 'path' => $path];
    }
}
