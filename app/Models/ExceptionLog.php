<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExceptionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'exception_class',
        'message',
        'code',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'ip_address',
        'status_code',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'line' => 'integer',
            'status_code' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
