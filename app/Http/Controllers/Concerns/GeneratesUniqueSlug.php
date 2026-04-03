<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    private function uniqueSlug(string $model, string $title, ?string $explicitSlug = null, ?int $excludeId = null): string
    {
        $base = Str::slug($explicitSlug ?? $title) ?: Str::slug($title);

        $candidate = $base;
        $counter = 1;

        while (
            $model::where('slug', $candidate)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }
}
