<?php

namespace App\Mail\Concerns;

use App\Support\MailDisplayName as MailDisplayNameSupport;

trait UsesMailDisplayName
{
    protected function mailDisplayName(): string
    {
        return MailDisplayNameSupport::resolve();
    }
}
