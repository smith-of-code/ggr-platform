<?php

namespace App\Jobs;

use App\Mail\NewPostPublished;
use App\Models\BlogSubscriber;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewPostNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle(): void
    {
        BlogSubscriber::where('is_active', true)
            ->cursor()
            ->each(function (BlogSubscriber $subscriber) {
                Mail::to($subscriber->email)
                    ->send(new NewPostPublished($this->post, $subscriber));
            });
    }
}
