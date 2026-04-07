<?php

namespace App\Services;

use App\Models\Consent;
use Illuminate\Http\Request;

class ConsentService
{
    /**
     * @param array{user_id?: int, email?: string, phone?: string} $identifiers
     * @param array<string, mixed> $additional
     */
    public static function log(
        Request $request,
        string $eventType,
        array $identifiers = [],
        array $additional = [],
    ): Consent {
        return Consent::create([
            'event_type' => $eventType,
            'datetime' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page_url' => $request->header('Referer') ?? $request->fullUrl(),
            'policy_version' => config('consent.policy_version', '1.0'),
            'checkbox_value' => true,
            'user_id' => $identifiers['user_id'] ?? $request->user()?->id,
            'email' => $identifiers['email'] ?? null,
            'phone' => $identifiers['phone'] ?? null,
            'session_id' => $request->session()->getId(),
            'additional_data' => !empty($additional) ? $additional : null,
        ]);
    }
}
