<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string|\Closure>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return;
                    }
                    if (str_contains($value, '@')) {
                        $v = Validator::make(
                            ['email' => $value],
                            ['email' => ['required', 'email:rfc,strict']]
                        );
                        if ($v->fails()) {
                            $fail('Укажите корректный адрес электронной почты.');
                        }

                        return;
                    }
                    if (User::normalizePhoneDigitsForLogin($value) === null) {
                        $fail('Введите корректный телефон (не менее 10 цифр, например +7 916 123-45-67 или 8 916 1234567).');
                    }
                },
            ],
            'password' => ['required', 'string'],
            'portal' => ['nullable', 'string', 'in:client,student'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('login')) {
            $this->merge([
                'login' => trim((string) $this->input('login', '')),
            ]);
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = (string) $this->input('login');
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        $authenticated = false;

        if (str_contains($login, '@')) {
            $authenticated = Auth::attempt(
                ['email' => Str::lower($login), 'password' => $password],
                $remember
            );
        } else {
            $user = User::findSingleUserByLoginPhone($login);
            if ($user !== null && Hash::check($password, $user->getAuthPassword())) {
                Auth::login($user, $remember);
                $authenticated = true;
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed', [], 'ru'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ], 'ru'),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
