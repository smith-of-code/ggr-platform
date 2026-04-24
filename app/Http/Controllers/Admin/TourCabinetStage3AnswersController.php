<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\TourCabinetContestProgress;
use App\Models\TourCabinetContestStage3Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TourCabinetStage3AnswersController extends Controller
{
    /**
     * Ответы участников по этапу 3 (сохранённые в ЛК).
     */
    public function index(): Response
    {
        $rows = TourCabinetContestProgress::query()
            ->with(['user:id,name,email,first_name,last_name,patronymic'])
            ->whereNotNull('stage3_text')
            ->where('stage3_text', '!=', '')
            ->orderByDesc('updated_at')
            ->paginate(30)
            ->through(function (TourCabinetContestProgress $p): array {
                $config = TourCabinetContestStage3Config::forDirection($p->direction_id);
                $composed = trim(implode(' ', array_filter(
                    [$p->user->last_name, $p->user->first_name, $p->user->patronymic],
                    fn ($v) => $v !== null && $v !== ''
                )));
                $userDisplay = $composed !== '' ? $composed : (string) ($p->user->name ?: $p->user->email);
                $dirMap = Direction::allProjectMap();
                $directionLabel = $p->direction_id
                    ? ($dirMap[$p->direction_id] ?? '—')
                    : '—';

                return [
                    'id' => $p->id,
                    'user_display' => $userDisplay,
                    'user_email' => $p->user->email,
                    'direction_label' => $directionLabel,
                    'assignment_title' => $config?->title ?? 'Проверочное задание',
                    'response_format' => $config?->response_format ?? TourCabinetContestStage3Config::FORMAT_VIDEO_LINK,
                    'stage3_text_preview' => Str::limit((string) $p->stage3_text, 500),
                    'stage3_text' => $p->stage3_text,
                    'stage3_video_url' => $p->stage3_video_url,
                    'stage3_attachment_original_name' => $p->stage3_attachment_original_name,
                    'stage3_has_attachment' => filled($p->stage3_attachment_path),
                    'attachment_download_url' => filled($p->stage3_attachment_path)
                        ? route('admin.tour-cabinet.stage3-answers.attachment', ['id' => $p->id], false)
                        : null,
                    'updated_at' => $p->updated_at?->toIso8601String(),
                ];
            });

        return Inertia::render('Admin/TourCabinet/Stage3Answers/Index', [
            'rows' => $rows,
        ]);
    }

    public function downloadAttachment(int $id): BinaryFileResponse
    {
        $progress = TourCabinetContestProgress::query()->findOrFail($id);
        if (! $progress->stage3_attachment_path) {
            abort(404);
        }

        $disk = config('filesystems.upload_disk', 'public');

        return Storage::disk($disk)->download(
            $progress->stage3_attachment_path,
            $progress->stage3_attachment_original_name ?: 'file'
        );
    }
}
