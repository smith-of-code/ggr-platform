<?php

namespace App\Jobs;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string|array $to,
        public ?Mailable $mailable = null,
        public ?string $rawBody = null,
        public ?string $subject = null,
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        if ($this->mailable) {
            Mail::to($this->to)->send($this->mailable);
            return;
        }

        if ($this->rawBody) {
            Mail::raw($this->rawBody, function ($message) {
                $message->to($this->to)->subject($this->subject ?? '');
            });
        }
    }
}
