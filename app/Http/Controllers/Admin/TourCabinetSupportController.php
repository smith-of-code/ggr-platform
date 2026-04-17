<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourCabinetSupportAttachment;
use App\Models\TourCabinetSupportMessage;
use App\Models\TourCabinetSupportTicket;
use App\Services\TourCabinetSupportMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TourCabinetSupportController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $category = $request->query('category');

        $query = TourCabinetSupportTicket::query()
            ->with('user:id,name,email')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if (is_string($status) && $status !== '' && in_array($status, TourCabinetSupportTicket::STATUSES, true)) {
            $query->where('status', $status);
        }

        if (is_string($category) && $category !== '' && in_array($category, TourCabinetSupportTicket::CATEGORIES, true)) {
            $query->where('category', $category);
        }

        $tickets = $query->paginate(20)->through(function (TourCabinetSupportTicket $t) {
            return [
                'id' => $t->id,
                'subject' => $t->subject,
                'category' => $t->category,
                'category_label' => TourCabinetSupportTicket::categoryLabel($t->category),
                'status' => $t->status,
                'status_label' => TourCabinetSupportTicket::statusLabel($t->status),
                'last_message_at' => $t->last_message_at?->toIso8601String(),
                'created_at' => $t->created_at?->toIso8601String(),
                'user' => [
                    'id' => $t->user->id,
                    'name' => $t->user->name,
                    'email' => $t->user->email,
                ],
            ];
        });

        return Inertia::render('Admin/TourCabinet/Support/Index', [
            'tickets' => $tickets,
            'filters' => [
                'status' => is_string($status) ? $status : '',
                'category' => is_string($category) ? $category : '',
            ],
            'statusOptions' => collect(TourCabinetSupportTicket::STATUSES)
                ->map(fn (string $s) => ['value' => $s, 'label' => TourCabinetSupportTicket::statusLabel($s)])
                ->values()
                ->all(),
            'categoryOptions' => collect(TourCabinetSupportTicket::CATEGORIES)
                ->map(fn (string $c) => ['value' => $c, 'label' => TourCabinetSupportTicket::categoryLabel($c)])
                ->values()
                ->all(),
        ]);
    }

    public function show(TourCabinetSupportTicket $ticket): Response
    {
        $ticket->load([
            'user:id,name,email,phone',
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.author:id,name,email',
            'messages.attachments',
        ]);

        return Inertia::render('Admin/TourCabinet/Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'category_label' => TourCabinetSupportTicket::categoryLabel($ticket->category),
                'status' => $ticket->status,
                'status_label' => TourCabinetSupportTicket::statusLabel($ticket->status),
                'created_at' => $ticket->created_at?->toIso8601String(),
                'user' => [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                    'email' => $ticket->user->email,
                    'phone' => $ticket->user->phone,
                ],
            ],
            'messages' => $ticket->messages->map(fn (TourCabinetSupportMessage $m) => $this->messagePayload($m)),
            'statusOptions' => collect(TourCabinetSupportTicket::STATUSES)
                ->map(fn (string $s) => ['value' => $s, 'label' => TourCabinetSupportTicket::statusLabel($s)])
                ->values()
                ->all(),
        ]);
    }

    public function storeMessage(
        Request $request,
        TourCabinetSupportTicket $ticket,
        TourCabinetSupportMessageService $messageService,
    ): RedirectResponse {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:20000'],
            'attachments' => ['nullable', 'array', 'max:'.TourCabinetSupportMessageService::MAX_ATTACHMENTS],
            'attachments.*' => ['file', 'max:'.TourCabinetSupportMessageService::MAX_FILE_KB, 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx'],
        ], [
            'body.required' => 'Введите текст ответа.',
        ]);

        $messageService->createAdminMessage(
            $ticket,
            $request->user(),
            $validated['body'],
            $request->file('attachments', []) ?? [],
        );

        $ticket->refresh();

        return redirect()
            ->route('admin.tour-cabinet.support.show', $ticket)
            ->with('success', 'Ответ сохранён в тикете.');
    }

    public function updateStatus(Request $request, TourCabinetSupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(TourCabinetSupportTicket::STATUSES)],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.tour-cabinet.support.show', $ticket)
            ->with('success', 'Статус обновлён.');
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(TourCabinetSupportMessage $m): array
    {
        $author = $m->author;

        if ($m->isFromAdmin()) {
            $label = 'Поддержка'.($author ? ' ('.$author->name.')' : '');
        } else {
            $label = 'Участник'.($author ? ' — '.$author->email : '');
        }

        return [
            'id' => $m->id,
            'body' => $m->body,
            'created_at' => $m->created_at?->toIso8601String(),
            'is_admin' => $m->isFromAdmin(),
            'author_label' => $label,
            'attachments' => $m->attachments->map(fn (TourCabinetSupportAttachment $a) => [
                'id' => $a->id,
                'name' => $a->original_filename,
                'url' => route('tour-cabinet.support.attachments.show', $a, absolute: false),
            ])->values()->all(),
        ];
    }
}
