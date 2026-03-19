<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsInvitation;
use App\Models\Lms\LmsRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'lms_role_id' => ['nullable', 'exists:lms_roles,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        LmsInvitation::create([
            'lms_event_id' => $event->id,
            'token' => LmsInvitation::generateToken(),
            'label' => $validated['label'] ?? null,
            'lms_role_id' => $validated['lms_role_id'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Пригласительная ссылка создана');
    }

    public function toggle(LmsEvent $event, LmsInvitation $invitation): RedirectResponse
    {
        if ($invitation->lms_event_id !== $event->id) {
            abort(404);
        }

        $invitation->update(['is_active' => !$invitation->is_active]);

        $status = $invitation->is_active ? 'активирована' : 'деактивирована';

        return redirect()->back()->with('success', "Ссылка {$status}");
    }

    public function destroy(LmsEvent $event, LmsInvitation $invitation): RedirectResponse
    {
        if ($invitation->lms_event_id !== $event->id) {
            abort(404);
        }

        $invitation->delete();

        return redirect()->back()->with('success', 'Пригласительная ссылка удалена');
    }
}
