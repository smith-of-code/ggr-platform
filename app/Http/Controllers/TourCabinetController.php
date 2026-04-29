<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Consent;
use App\Models\Favorite;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormSubmission;
use App\Models\TourCabinetDocument;
use App\Models\User;
use App\Services\ConsentService;
use App\Services\SettingsService;
use App\Services\TourCabinetCommerceToursDashboardData;
use App\Services\TourCabinetContestDashboardData;
use App\Support\PostAuthRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('TourCabinet/Login');
    }

    public function showRegister(): Response
    {
        return Inertia::render('TourCabinet/Register');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email', ''))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        if (! PostAuthRedirect::canAccessTourCabinet(Auth::user())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Нет доступа к этому разделу. Войдите через общую форму на сайте (если у вас аккаунт ВШГР / портала) или зарегистрируйтесь для участия в турах.',
            ]);
        }

        PostAuthRedirect::rememberLoginPortal($request, 'client');

        return redirect()->intended(route('tour-cabinet.dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => Str::lower(trim((string) $request->input('email', ''))),
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,strict', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
            'email.email' => 'Укажите корректный адрес электронной почты (например, user@mail.ru).',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'password.confirmed' => 'Пароль и поле подтверждения не совпадают.',
            'password.min' => 'Пароль должен быть не короче :min символов.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_tour_cabinet_user' => true,
        ]);

        event(new Registered($user));

        ConsentService::log($request, Consent::TYPE_REGISTRATION, [
            'user_id' => $user->id,
            'email' => $user->email,
        ], ['context' => 'tour_cabinet']);

        Auth::login($user);
        $request->session()->regenerate();

        PostAuthRedirect::rememberLoginPortal($request, 'client');

        return redirect()->route('tour-cabinet.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', [
            'redirect' => route('tour-cabinet.dashboard', absolute: false),
        ]);
    }

    public function dashboard(
        Request $request,
        TourCabinetContestDashboardData $contestDashboardData,
        TourCabinetCommerceToursDashboardData $commerceToursDashboardData,
        SettingsService $settings,
    ): Response {
        $user = $request->user();

        $composed = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));

        $favorites = Schema::hasTable('favorites')
            ? Favorite::groupedFavorablesFor($user->id)
            : ['cities' => collect(), 'tours' => collect()];

        $profileDocuments = [];
        if (Schema::hasTable('tour_cabinet_documents')) {
            $user->loadMissing('tourCabinetDocuments');
            $profileDocuments = $user->tourCabinetDocuments
                ->map(fn (TourCabinetDocument $d) => [
                    'id' => $d->id,
                    'type' => $d->type,
                    'file_path' => $d->file_path,
                    'original_name' => $d->original_name,
                    'status' => $d->status,
                    'admin_comment' => $d->admin_comment,
                ])
                ->values()
                ->all();
        }

        $dashboardStandardForm = $this->dashboardStandardFormForUser($settings, $user);

        return Inertia::render('TourCabinet/Dashboard', [
            ...$contestDashboardData->forUser($user),
            'commerceTours' => $commerceToursDashboardData->buildPayload($user),
            'tourApplications' => $this->tourApplicationsForUser($user),
            'favorites' => $favorites,
            'profileDocuments' => $profileDocuments,
            'dashboardStandardForm' => $dashboardStandardForm,
            'profile' => [
                'user_id' => $user->id,
                'display_name' => $composed !== '' ? $composed : (string) ($user->name ?: 'Участник'),
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'patronymic' => $user->patronymic,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'phone' => $user->phone,
                'email' => $user->email,
                'avatar_url' => $this->tourCabinetAvatarPublicUrl($user),
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => Str::lower(trim((string) $request->input('email', ''))),
            ]);
        }

        $namePattern = '/^[\p{L}\p{M}\s\-\.\']+$/u';
        $validated = $request->validate([
            'last_name' => ['nullable', 'string', 'max:255', Rule::when($request->filled('last_name'), ['regex:'.$namePattern])],
            'first_name' => ['nullable', 'string', 'max:255', Rule::when($request->filled('first_name'), ['regex:'.$namePattern])],
            'patronymic' => ['nullable', 'string', 'max:255', Rule::when($request->filled('patronymic'), ['regex:'.$namePattern])],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', ''])],
            'birth_date' => ['nullable', 'date', 'before:today', 'after_or_equal:1900-01-01'],
            'phone' => [
                'nullable',
                'string',
                'max:32',
                Rule::when($request->filled('phone'), ['regex:/^\+?[0-9\s\-\(\)]{7,32}$/']),
            ],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,strict', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
        ], [
            'last_name.regex' => 'Фамилия может содержать только буквы, пробелы, дефис, точку и апостроф.',
            'first_name.regex' => 'Имя может содержать только буквы, пробелы, дефис, точку и апостроф.',
            'patronymic.regex' => 'Отчество может содержать только буквы, пробелы, дефис, точку и апостроф.',
            'phone.regex' => 'Телефон: от 7 до 32 символов, допустимы цифры, +, пробелы, скобки и дефис.',
            'email.email' => 'Укажите корректный адрес электронной почты.',
            'email.unique' => 'Этот email уже занят.',
            'avatar.image' => 'Аватар должен быть изображением.',
            'avatar.mimes' => 'Допустимые форматы: JPEG, PNG, WebP, GIF.',
            'avatar.max' => 'Размер файла аватара не более 2 МБ.',
            'birth_date.before' => 'Дата рождения должна быть в прошлом.',
            'birth_date.after_or_equal' => 'Укажите реалистичную дату рождения.',
            'gender.in' => 'Выберите значение пола из списка или оставьте «Не указано».',
        ]);

        $user = $request->user();
        $gender = isset($validated['gender']) && $validated['gender'] !== '' ? $validated['gender'] : null;

        $user->fill([
            'last_name' => filled($validated['last_name'] ?? null) ? trim((string) $validated['last_name']) : null,
            'first_name' => filled($validated['first_name'] ?? null) ? trim((string) $validated['first_name']) : null,
            'patronymic' => filled($validated['patronymic'] ?? null) ? trim((string) $validated['patronymic']) : null,
            'gender' => $gender,
            'birth_date' => ! empty($validated['birth_date']) ? $validated['birth_date'] : null,
            'phone' => filled($validated['phone'] ?? null) ? trim((string) $validated['phone']) : null,
            'email' => $validated['email'],
        ]);

        if ($request->filled('avatar_url')) {
            $disk = config('filesystems.upload_disk', 'public');
            if ($user->avatar_path) {
                Storage::disk($disk)->delete($user->avatar_path);
            }
            $user->avatar_path = $request->input('avatar_url');
        } elseif ($request->hasFile('avatar')) {
            $disk = config('filesystems.upload_disk', 'public');
            if ($user->avatar_path) {
                Storage::disk($disk)->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('tour-cabinet/avatars/'.$user->id, $disk);
            $user->avatar_path = $path;
        }

        $composed = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));
        if ($composed !== '') {
            $user->name = $composed;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('tour-cabinet.dashboard')
            ->with('success', 'Профиль сохранён.')
            ->withFragment('tour-cabinet-profile');
    }

    public function uploadProfileDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(TourCabinetDocument::allowedTypes())],
            'file' => ['required_without:file_url', 'nullable', 'file', 'max:51200', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'file_url' => ['required_without:file', 'nullable', 'string', 'url'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $type = $validated['type'];

        $existing = TourCabinetDocument::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($existing && $existing->isLockedForParticipant()) {
            return redirect()
                ->route('tour-cabinet.dashboard')
                ->withErrors([
                    'file' => 'Этот документ подтверждён модератором. Для изменения обратитесь в поддержку.',
                ])
                ->withFragment('tour-cabinet-documents');
        }

        $disk = config('filesystems.upload_disk', 'public');
        if ($existing && $existing->hasFile()) {
            Storage::disk($disk)->delete($existing->file_path);
        }

        if ($request->filled('file_url')) {
            $filePath = $request->input('file_url');
            $originalName = $request->input('file_name', basename(parse_url($filePath, PHP_URL_PATH) ?: 'document'));
        } else {
            $filePath = $request->file('file')->store('tour-cabinet/documents/'.$user->id, $disk);
            $originalName = $request->file('file')->getClientOriginalName();
        }

        TourCabinetDocument::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
            ],
            [
                'file_path' => $filePath,
                'original_name' => $originalName,
                'status' => TourCabinetDocument::STATUS_PENDING_REVIEW,
                'admin_comment' => null,
                'reviewed_at' => null,
            ]
        );

        return redirect()
            ->route('tour-cabinet.dashboard')
            ->with('success', 'Документ сохранён.')
            ->withFragment('tour-cabinet-documents');
    }

    public function deleteProfileDocument(Request $request, TourCabinetDocument $document): RedirectResponse
    {
        if ($document->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($document->isLockedForParticipant()) {
            return redirect()
                ->route('tour-cabinet.dashboard')
                ->withErrors([
                    'file' => 'Подтверждённый документ можно удалить только через поддержку.',
                ])
                ->withFragment('tour-cabinet-documents');
        }

        $disk = config('filesystems.upload_disk', 'public');
        if ($document->hasFile()) {
            Storage::disk($disk)->delete($document->file_path);
        }
        $document->delete();

        return redirect()
            ->route('tour-cabinet.dashboard')
            ->with('success', 'Документ удалён.')
            ->withFragment('tour-cabinet-documents');
    }

    private function tourCabinetAvatarPublicUrl(User $user): ?string
    {
        if (! $user->avatar_path) {
            return null;
        }

        $disk = config('filesystems.upload_disk', 'public');

        return Storage::disk($disk)->url($user->avatar_path);
    }

    /**
     * Резолвит проп `dashboardStandardForm` для дашборда ЛК туров.
     * Возвращает массив `{slug, title, submitted}` или null, если slug не настроен/форма неактивна/удалена.
     *
     * @return array{slug: string, title: string, submitted: bool}|null
     */
    private function dashboardStandardFormForUser(SettingsService $settings, User $user): ?array
    {
        $slug = $settings->getTourCabinetDashboardStandardFormSlug();
        if (! is_string($slug) || trim($slug) === '') {
            return null;
        }

        $form = LmsForm::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $form) {
            return null;
        }

        $submitted = LmsFormSubmission::query()
            ->where('lms_form_id', $form->id)
            ->where('user_id', $user->id)
            ->exists();

        return [
            'slug' => (string) $form->slug,
            'title' => (string) $form->title,
            'submitted' => $submitted,
        ];
    }

    /**
     * @return list<array{id: int, tour_title: string, date_range: ?string, status_label: string, status_key: 'pending'|'approved'|'rejected'}>
     */
    private function tourApplicationsForUser(User $user): array
    {
        return Application::query()
            ->where('type', 'tour')
            ->whereRaw('LOWER(TRIM(email)) = LOWER(TRIM(?))', [$user->email])
            ->with([
                'tour:id,title',
                'tourDeparture:id,tour_id,start_date,end_date',
                'tourDeparture.tour:id,title',
            ])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function (Application $app): array {
                $statusKey = match ($app->status) {
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                    default => 'pending',
                };

                return [
                    'id' => $app->id,
                    'tour_title' => $this->tourApplicationTitle($app),
                    'date_range' => $this->formatTourApplicationDateRange($app),
                    'status_key' => $statusKey,
                    'status_label' => match ($app->status) {
                        'new', 'in_progress' => 'На проверке',
                        'approved' => 'Одобрена',
                        'rejected' => 'Отклонена',
                        default => 'На проверке',
                    },
                ];
            })
            ->all();
    }

    private function tourApplicationTitle(Application $app): string
    {
        foreach ([
            $app->tour?->title,
            $app->tourDeparture?->tour?->title,
            data_get($app->data, 'tour_title'),
        ] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return Application::TYPES['tour'] ?? 'Заявка на тур';
    }

    private function formatTourApplicationDateRange(Application $app): ?string
    {
        $dep = $app->tourDeparture;
        if (! $dep || ! $dep->start_date || ! $dep->end_date) {
            return null;
        }

        $start = $dep->start_date;
        $end = $dep->end_date;
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('d.m').'–'.$end->format('d.m.Y');
        }

        return $start->format('d.m.Y').'–'.$end->format('d.m.Y');
    }
}
