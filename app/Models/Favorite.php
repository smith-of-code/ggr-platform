<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'favorable_type',
        'favorable_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favorable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array{cities: Collection<int, City>, tours: Collection<int, Tour>}
     */
    public static function groupedFavorablesFor(int $userId): array
    {
        $favorites = self::query()
            ->where('user_id', $userId)
            ->with('favorable')
            ->get();

        $cities = $favorites
            ->where('favorable_type', City::class)
            ->pluck('favorable')
            ->filter()
            ->values();

        $tours = EloquentCollection::make(
            $favorites
                ->where('favorable_type', Tour::class)
                ->pluck('favorable')
                ->filter()
                ->values()
                ->all()
        );
        $tours->loadMissing('cities:id,name');

        return [
            'cities' => $cities,
            'tours' => $tours,
        ];
    }
}
