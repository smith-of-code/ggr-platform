<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogSubscriber extends Model
{
    protected $fillable = [
        'email',
        'is_active',
        'token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscriber) {
            if (empty($subscriber->token)) {
                $subscriber->token = Str::random(64);
            }
        });
    }
}
