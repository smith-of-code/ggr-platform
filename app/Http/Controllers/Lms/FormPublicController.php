<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Consent;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormField;
use App\Models\Lms\LmsFormResponse;
use App\Models\Lms\LmsFormSubmission;
use App\Services\ConsentService;
use App\Services\Lms\Forms\FieldValidationPresets;
use App\Services\TourCabinetCommerceToursFormLinker;
use App\Services\TourCabinetContestFormLinker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Email;
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
                'id', 'key', 'label', 'type', 'validation', 'required', 'placeholder', 'options', 'position',
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
                'id', 'key', 'label', 'type', 'validation', 'required', 'placeholder', 'options', 'position',
            ])),
        ])->withHeaders($this->corsHeaders());
    }

    public function apiSubmit(Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $form = LmsForm::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        $rules = $this->buildPublicFormAnswerRules($form);
        if ($form->require_consent) {
            $rules['consent'] = ['accepted'];
        }

        $validated = $request->validate($rules, $this->publicFormValidationMessages($form));
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

        TourCabinetContestFormLinker::tryLinkAfterSubmission($form, $submission);
        TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission($form, $submission);

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

        $rules = $this->buildPublicFormAnswerRules($form);
        if ($form->require_consent) {
            $rules['consent'] = ['accepted'];
        }

        $validated = $request->validate($rules, $this->publicFormValidationMessages($form));
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

        TourCabinetContestFormLinker::tryLinkAfterSubmission($form, $submission);
        TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission($form, $submission);

        if ($request->header('X-Inertia')) {
            return redirect()->back()->with('success', 'Ответ отправлен');
        }

        return response()->json([
            'message' => 'Ответ отправлен',
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * @return array<string, list<string|\Closure|Email>>
     */
    private function buildPublicFormAnswerRules(LmsForm $form): array
    {
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($this->fieldIsEmail($field)) {
                $fieldRules[] = (new Email)->rfcCompliant()->withNativeValidation();
            } elseif ($this->fieldIsPhone($field)) {
                $fieldRules[] = $this->phoneDigitsRule();
            } elseif ($field->type === 'number' || $field->type === 'rating') {
                $fieldRules[] = 'numeric';
            }

            if (! empty($field->validation) && $presetRule = FieldValidationPresets::rule((string) $field->validation)) {
                $fieldRules[] = $presetRule;
            }

            $fieldRules[] = 'max:5000';
            $rules['answers.'.$field->key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function publicFormValidationMessages(LmsForm $form): array
    {
        $messages = [
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
        ];

        foreach ($form->fields as $field) {
            $k = $field->key;
            $label = '«'.str_replace(['<', '>'], '', (string) $field->label).'»';
            $messages["answers.{$k}.required"] = "Заполните поле {$label}.";
            if ($this->fieldIsEmail($field)) {
                $messages["answers.{$k}.email"] = 'Укажите корректный адрес электронной почты.';
            }
        }

        return $messages;
    }

    private function fieldIsEmail(LmsFormField $field): bool
    {
        if ($field->type === 'email') {
            return true;
        }
        if (! in_array($field->type, ['text', 'textarea'], true)) {
            return false;
        }
        $k = mb_strtolower((string) $field->key);
        $l = mb_strtolower((string) $field->label);

        return str_contains($k, 'email')
            || str_contains($k, 'mail')
            || str_contains($l, 'электронн')
            || str_contains($l, 'e-mail')
            || str_contains($l, 'email');
    }

    private function fieldIsPhone(LmsFormField $field): bool
    {
        if ($field->type === 'phone') {
            return true;
        }
        if (! in_array($field->type, ['text', 'textarea'], true)) {
            return false;
        }
        $k = mb_strtolower((string) $field->key);
        $l = mb_strtolower((string) $field->label);

        return str_contains($k, 'phone')
            || str_contains($k, 'tel')
            || str_contains($l, 'телефон')
            || (str_contains($l, 'номер') && str_contains($l, 'тел'));
    }

    /**
     * @return Closure(string, mixed, \Closure): void
     */
    private function phoneDigitsRule(): Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                return;
            }
            $digits = preg_replace('/\D/u', '', (string) $value) ?? '';
            if (strlen($digits) < 10) {
                $fail('Укажите номер телефона полностью (не менее 10 цифр, допускаются +7, 8, скобки и пробелы).');
            }
            if (strlen($digits) > 15) {
                $fail('Номер телефона указан некорректно.');
            }
        };
    }
}
