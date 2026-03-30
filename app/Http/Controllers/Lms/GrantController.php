<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGrant;
use App\Models\Lms\LmsGrantEnrollment;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GrantController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $user = auth()->user();
        $request = request();

        $query = LmsGrant::where('lms_event_id', $event->id)
            ->where('is_active', true);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        $grants = $query->orderByRaw("CASE WHEN application_end IS NOT NULL AND application_end < NOW() THEN 1 ELSE 0 END")
            ->orderByRaw("CASE WHEN application_end IS NOT NULL THEN application_end ELSE '2999-12-31'::timestamp END ASC")
            ->get();

        $enrolledIds = LmsGrantEnrollment::where('user_id', $user->id)
            ->whereIn('lms_grant_id', $grants->pluck('id'))
            ->pluck('lms_grant_id')
            ->toArray();

        $grantsData = $grants->map(fn ($g) => [
            'grant' => $g->only(['id', 'title', 'type', 'city', 'description', 'application_start', 'application_end']),
            'enrolled' => in_array($g->id, $enrolledIds),
        ]);

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        return Inertia::render('Lms/Grants/Index', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'grants' => $grantsData,
            'isProfileComplete' => $profile?->isProfileComplete() ?? false,
            'filters' => [
                'type' => $request->input('type', ''),
                'city' => $request->input('city', ''),
            ],
        ]);
    }

    public function show(LmsEvent $event, LmsGrant $grant): Response
    {
        if ($grant->lms_event_id !== $event->id || ! $grant->is_active) {
            abort(404);
        }

        $user = auth()->user();
        $grant->load('documents');

        $enrolled = LmsGrantEnrollment::where('lms_grant_id', $grant->id)
            ->where('user_id', $user->id)
            ->exists();

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        $disk = config('filesystems.upload_disk');
        $documents = $grant->documents->map(fn ($d) => [
            'id' => $d->id,
            'original_name' => $d->original_name,
            'url' => Storage::disk($disk)->url($d->file_path),
        ]);

        return Inertia::render('Lms/Grants/Show', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'grant' => $grant->only(['id', 'title', 'type', 'city', 'description', 'application_start', 'application_end']),
            'documents' => $documents,
            'enrolled' => $enrolled,
            'isProfileComplete' => $profile?->isProfileComplete() ?? false,
        ]);
    }

    public function enroll(LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id || ! $grant->is_active) {
            abort(404);
        }

        $user = auth()->user();

        $profile = LmsProfile::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->first();

        if (! $profile || ! $profile->isProfileComplete()) {
            return redirect()->back()->withErrors([
                'enroll' => 'Для выбора гранта необходимо заполнить профиль.',
            ]);
        }

        LmsGrantEnrollment::firstOrCreate([
            'lms_grant_id' => $grant->id,
            'user_id' => $user->id,
        ]);

        return redirect()->back();
    }

    public function unenroll(LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        LmsGrantEnrollment::where('lms_grant_id', $grant->id)
            ->where('user_id', auth()->id())
            ->delete();

        return redirect()->back();
    }
}
