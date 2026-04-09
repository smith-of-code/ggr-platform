<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailDisplayName;
use App\Models\BlogSubscriber;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPostPublished extends Mailable
{
    use Queueable, SerializesModels, UsesMailDisplayName;

    public Post $post;

    public BlogSubscriber $subscriber;

    public function __construct(Post $post, BlogSubscriber $subscriber)
    {
        $this->post = $post;
        $this->subscriber = $subscriber;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая статья: '.$this->post->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-post-published',
            with: [
                'post' => $this->post,
                'unsubscribeUrl' => route('blog.unsubscribe', $this->subscriber->token),
                'mailFromName' => $this->mailDisplayName(),
            ],
        );
    }
}
