<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsForm;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LmsFormTrashController extends Controller
{
    public function index(): Response
    {
        $forms = LmsForm::onlyTrashed()
            ->with('event:id,slug,title')
            ->withCount(['fields', 'submissions'])
            ->orderByDesc('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Settings/FormsTrash', [
            'forms' => $forms,
        ]);
    }

    public function restore(int $form): RedirectResponse
    {
        $model = LmsForm::onlyTrashed()->findOrFail($form);
        $title = $model->title;
        $model->restore();

        return back()->with('success', "Форма «{$title}» восстановлена");
    }

    public function forceDelete(int $form): RedirectResponse
    {
        $model = LmsForm::onlyTrashed()->findOrFail($form);
        $title = $model->title;
        $model->forceDelete();

        return back()->with('success', "Форма «{$title}» удалена навсегда");
    }
}
