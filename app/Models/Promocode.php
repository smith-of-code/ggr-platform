<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Promocode extends Model
{
    protected $fillable = [
        'name',
        'code',
        'discount_percent',
        'promocodeable_type',
        'promocodeable_id',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function promocodeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && $this->valid_from->startOfDay()->lte(now())
            && $this->valid_until->endOfDay()->gte(now());
    }

    public function isValidForTour(int $tourId): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        if ($this->promocodeable_type === null) {
            return true;
        }

        return $this->promocodeable_type === Tour::class
            && $this->promocodeable_id === $tourId;
    }
}
