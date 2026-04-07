<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormResponse;
use App\Models\Lms\LmsFormSubmission;
use App\Services\ConsentService;
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
            'form' => [
                ...$form->only(['id', 'title', 'description', 'slug', 'thank_you_message', 'require_consent']),
                'consent_document_url' => $form->consent_document_url ?: config('consent.document_url'),
            ],
            'fields' => $form->fields->map(fn ($f) => $f->only([
                'id', 'key', 'label', 'type', 'required', 'placeholder', 'options', 'position',
            ])),
        ]);
    }

    public function apiShow(string $slug): \Illuminate\Http\JsonResponse
    {
        $form = LmsForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        return response()->json([
            'form' => [
                ...$form->only(['id', 'title', 'description', 'slug', 'thank_you_message', 'require_consent']),
                'consent_document_url' => $form->consent_document_url ?: config('consent.document_url'),
            ],
            'fields' => $form->fields->map(fn ($f) => $f->only([
                'id', 'key', 'label', 'type', 'required', 'placeholder', 'options', 'position',
            ])),
        ])->withHeaders($this->corsHeaders());
    }

    public function apiSubmit(Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $form = LmsForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            $fieldRules[] = $field->required ? 'required' : 'nullable';

            if ($field->type === 'email') {
                $fieldRules[] = 'email';
            } elseif (in_array($field->type, ['number', 'rating'])) {
                $fieldRules[] = 'numeric';
            }

            $fieldRules[] = 'max:5000';
            $rules["answers.{$field->key}"] = $fieldRules;
        }

        if ($form->require_consent) {
            $rules['consent'] = ['accepted'];
        }

        $validated = $request->validate($rules, [
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ]);
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

        if ($form->require_consent) {
            $emailKey = $form->email_field_key;
            $phoneKey = $form->phone_field_key;

            ConsentService::log($request, Consent::TYPE_LMS_FORM, [
                'email' => $emailKey ? ($answers[$emailKey] ?? null) : null,
                'phone' => $phoneKey ? ($answers[$phoneKey] ?? null) : null,
            ], ['lms_form_id' => $form->id, 'lms_form_slug' => $form->slug]);
        }

        return response()->json([
            'message' => 'Ответ отправлен',
            'submission_id' => $submission->id,
        ])->withHeaders($this->corsHeaders());
    }

    public function apiCorsOptions(): \Illuminate\Http\Response
    {
        return response('', 204)->withHeaders($this->corsHeaders());
    }

    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept',
        ];
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

        if ($form->require_consent) {
            $rules['consent'] = ['accepted'];
        }

        $validated = $request->validate($rules, [
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ]);
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

        if ($form->require_consent) {
            $emailKey = $form->email_field_key;
            $phoneKey = $form->phone_field_key;

            ConsentService::log($request, Consent::TYPE_LMS_FORM, [
                'email' => $emailKey ? ($answers[$emailKey] ?? null) : null,
                'phone' => $phoneKey ? ($answers[$phoneKey] ?? null) : null,
            ], ['lms_form_id' => $form->id, 'lms_form_slug' => $form->slug]);
        }

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return redirect()->back()->with('success', 'Ответ отправлен');
        }

        return response()->json(['message' => 'Ответ отправлен', 'submission_id' => $submission->id]);
    }
}
