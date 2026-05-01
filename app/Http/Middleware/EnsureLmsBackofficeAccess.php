<?php

namespace App\Http\Middleware;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLmsBackofficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $event = $request->route('event');

        if ($event instanceof LmsEvent) {
            $accessLevel = LmsProfile::backofficeAccessForEvent($user, $event);
            if ($accessLevel === 'admin') {
                return $next($request);
            }

            if ($accessLevel === 'gamification_points_only' && $this->isGamificationAndLearningReviewRoute($request)) {
                return $next($request);
            }

            if ($accessLevel === 'none') {
                abort(403, 'Недостаточно прав для доступа к администрированию LMS.');
            }

            abort(403, 'Доступ ограничен: доступны только геймификация и просмотр ответов на тесты/ДЗ.');
        }

        if (! LmsProfile::userHasAnyLmsAdminProfile($user)) {
            abort(403, 'Недостаточно прав для доступа к администрированию LMS.');
        }

        return $next($request);
    }

    private function isGamificationAndLearningReviewRoute(Request $request): bool
    {
        $routeName = $request->route() ? $request->route()->getName() : null;

        return in_array($routeName, [
            'lms.admin.gamification.index',
            'lms.admin.gamification.manual-points',
            'lms.admin.gamification.points.destroy',
            'lms.admin.tests.index',
            'lms.admin.tests.results',
            'lms.admin.assignments.index',
            'lms.admin.assignments.show',
            'lms.admin.assignments.review',
            'lms.admin.assignments.comment',
            'lms.admin.assignments.mark-read',
            'lms.admin.groups.index',
            'lms.admin.groups.create',
            'lms.admin.groups.store',
            'lms.admin.groups.edit',
            'lms.admin.groups.update',
            'lms.admin.groups.destroy',
        ], true);
    }
}
