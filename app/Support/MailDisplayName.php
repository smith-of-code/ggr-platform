<?php

namespace App\Support;

final class MailDisplayName
{
    public static function resolve(): string
    {
        return (string) config('mail.from.name');
    }
}
