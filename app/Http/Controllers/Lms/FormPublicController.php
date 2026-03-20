<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormResponse;
use App\Models\Lms\LmsFormSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormPublicController extends Controller
{
    public function show(string $slug): Response
    {
        $form = LmsForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        return Inertia::render('Forms/Public', [
            'form' => $form->only(['id', 'title', 'description', 'slug', 'thank_you_message']),
            'fields' => $form->fields->map(fn ($f) => $f->only([
                'id', 'key', 'label', 'type', 'required', 'placeholder', 'options', 'position',
            ])),
        ]);
    }

    public function submit(Request $request, string $slug)
    {
        $form = LmsForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field->type === 'email') {
                $fieldRules[] = 'email';
            } elseif ($field->type === 'number' || $field->type === 'rating') {
                $fieldRules[] = 'numeric';
            }

            $fieldRules[] = 'max:5000';
            $rules["answers.{$field->key}"] = $fieldRules;
        }

        $validated = $request->validate($rules);
        $answers = $validated['answers'] ?? [];

        $submission = LmsFormSubmission::create([
            'lms_form_id' => $form->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        foreach ($form->fields as $field) {
            $value = $answers[$field->key] ?? null;
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                LmsFormResponse::create([
                    'lms_form_submission_id' => $submission->id,
                    'lms_form_field_id' => $field->id,
                    'value' => (string) $value,
                ]);
            }
        }

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return redirect()->back()->with('success', 'Ответ отправлен');
        }

        return response()->json(['message' => 'Ответ отправлен', 'submission_id' => $submission->id]);
    }
}
