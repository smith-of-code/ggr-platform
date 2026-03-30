<?php

namespace App\Http\Controllers;

use App\Models\BlogSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogSubscriptionController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = BlogSubscriber::where('email', $request->input('email'))->first();

        if ($subscriber) {
            if (!$subscriber->is_active) {
                $subscriber->update(['is_active' => true]);
            }

            return redirect()->back()->with('subscribed', 'Вы подписаны на рассылку!');
        }

        BlogSubscriber::create([
            'email' => $request->input('email'),
        ]);

        return redirect()->back()->with('subscribed', 'Вы подписаны на рассылку!');
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = BlogSubscriber::where('token', $token)->first();

        if ($subscriber) {
            $subscriber->update(['is_active' => false]);
        }

        return redirect()->route('blog.index')->with('success', 'Вы отписаны от рассылки');
    }
}
