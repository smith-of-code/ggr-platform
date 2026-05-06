<?php

namespace App\Http\Middleware;

use App\Services\TourCabinetProfileCompleteness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Запрещает доступ к участвующим маршрутам ЛК Туры (конкурс, коммерческие туры,
 * стандартная анкета и пр.), пока у пользователя не заполнен профиль и не загружено
 * согласие на обработку персональных данных. Используется как alias `tour-cabinet.profile-complete`.
 */
class EnsureTourCabinetProfileComplete
{
    private const PROFILE_GATE_ERROR = 'Сначала заполните профиль и загрузите согласие на обработку персональных данных, чтобы получить доступ к остальным разделам.';

    public function __construct(
        private readonly TourCabinetProfileCompleteness $profileCompleteness,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->profileCompleteness->isComplete($user)) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return redirect()
                ->route('tour-cabinet.dashboard')
                ->withErrors(['profile' => self::PROFILE_GATE_ERROR])
                ->withFragment('tour-cabinet-profile');
        }

        return back()
            ->withInput()
            ->withErrors(['profile' => self::PROFILE_GATE_ERROR])
            ->withFragment('tour-cabinet-profile');
    }
}
