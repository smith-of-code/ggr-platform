<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\TourCabinetContestProgress;
use App\Models\User;
use App\Services\Admin\TourCabinetContestProgressResetter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestProgressResetController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $directionMap = Direction::allProjectMap();

        $query = User::query()
            ->where(function (Builder $outer): void {
                $outer->whereHas('tourCabinetContestProgress')
                    ->orWhereHas('tourCabinetContestCitySubmissions')
                    ->orWhereExists(function ($sub): void {
                        $sub->selectRaw('1')
                            ->from('tour_cabinet_contest_stage2_answers')
                            ->whereColumn('tour_cabinet_contest_stage2_answers.user_id', 'users.id');
                    });
            });

        if ($q !== '') {
            $query->where(function (Builder $sub) use ($q): void {
                $sub->where('users.email', 'like', '%'.$q.'%')
                    ->orWhere('users.name', 'like', '%'.$q.'%')
                    ->orWhere('users.last_name', 'like', '%'.$q.'%')
                    ->orWhere('users.first_name', 'like', '%'.$q.'%')
                    ->orWhere('users.patronymic', 'like', '%'.$q.'%');

                if (ctype_digit($q)) {
                    $sub->orWhere('users.id', (int) $q);
                }
            });
        }

        $query
            ->with(['tourCabinetContestProgress'])
            ->leftJoin(
                'tour_cabinet_contest_progress as tccp',
                'tccp.user_id',
                '=',
                'users.id'
            )
            ->select('users.*')
            ->orderByRaw('tccp.updated_at DESC NULLS LAST')
            ->orderByDesc('users.id');

        $users = $query
            ->paginate(25)
            ->withQueryString()
            ->through(fn (User $user) => $this->userRow($user, $directionMap));

        return Inertia::render('Admin/Settings/ContestReset/Index', [
            'users' => $users,
            'filters' => [
                'q' => $q,
            ],
        ]);
    }

    public function reset(User $user, TourCabinetContestProgressResetter $resetter): RedirectResponse
    {
        $email = (string) ($user->email ?? '');
        $resetter->reset($user);

        $label = $email !== '' ? $email : ('ID '.$user->id);

        return back()->with('success', "Прогресс конкурса для {$label} сброшен.");
    }

    /**
     * @param  array<int, string>  $directionMap
     * @return array<string, mixed>
     */
    private function userRow(User $user, array $directionMap): array
    {
        $progress = $user->tourCabinetContestProgress;

        $fio = trim(implode(' ', array_filter(
            [$user->last_name, $user->first_name, $user->patronymic],
            fn ($v) => $v !== null && $v !== ''
        )));

        $directionLabel = null;
        if ($progress?->direction_id) {
            $directionLabel = $directionMap[$progress->direction_id] ?? null;
        }

        return [
            'id' => $user->id,
            'email' => $user->email,
            'fio' => $fio !== '' ? $fio : (string) ($user->name ?? ''),
            'direction_label' => $directionLabel,
            'current_stage' => $progress?->current_stage,
            'stage2_submitted_at' => $progress?->stage2_submitted_at?->toIso8601String(),
        ];
    }
}
