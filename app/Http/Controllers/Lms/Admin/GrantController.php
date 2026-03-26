<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsGrant;
use App\Models\Lms\LmsGrantDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GrantController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $grants = LmsGrant::where('lms_event_id', $event->id)
            ->withCount('enrollments')
            ->orderBy('position')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Grants/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'grants' => $grants,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Grants/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $grant = LmsGrant::create([
            ...$validated,
            'lms_event_id' => $event->id,
        ]);

        $this->syncDocuments($request, $grant);

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    public function edit(LmsEvent $event, LmsGrant $grant): Response
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $grant->load('documents');

        return Inertia::render('Lms/Admin/Grants/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'grant' => $grant,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'application_start' => ['nullable', 'date'],
            'application_end' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $grant->update($validated);
        $this->syncDocuments($request, $grant);

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    public function destroy(LmsEvent $event, LmsGrant $grant): RedirectResponse
    {
        if ($grant->lms_event_id !== $event->id) {
            abort(404);
        }

        $grant->delete();

        return redirect()->route('lms.admin.grants.index', $event->slug);
    }

    private function syncDocuments(Request $request, LmsGrant $grant): void
    {
        $keepIds = $request->input('keep_document_ids', []);

        $grant->documents()
            ->whereNotIn('id', $keepIds)
            ->each(function (LmsGrantDocument $doc) {
                $disk = config('filesystems.upload_disk');
                Storage::disk($disk)->delete($doc->file_path);
                $doc->delete();
            });

        if ($request->hasFile('new_documents')) {
            $disk = config('filesystems.upload_disk');
            $maxPos = $grant->documents()->max('position') ?? -1;

            foreach ($request->file('new_documents') as $file) {
                $path = $file->store('grant-documents/' . $grant->id, $disk);
                $grant->documents()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'position' => ++$maxPos,
                ]);
            }
        }
    }
}
