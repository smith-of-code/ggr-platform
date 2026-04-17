<?php

namespace App\Http\Controllers;

use App\Models\TourCabinetSupportAttachment;
use App\Models\TourCabinetSupportMessage;
use App\Models\TourCabinetSupportTicket;
use App\Services\TourCabinetSupportMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TourCabinetSupportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tickets = TourCabinetSupportTicket::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->through(fn (TourCabinetSupportTicket $t) => $this->ticketListRow($t));

        return Inertia::render('TourCabinet/Support/Index', [
            'tickets' => $tickets,
            'categoryOptions' => $this->categoryOptions(),
            'supportContactEmail' => config('tour_cabinet.support_contact_email'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TourCabinet/Support/Create', [
            'categoryOptions' => $this->categoryOptions(),
            'supportContactEmail' => config('tour_cabinet.support_contact_email'),
        ]);
    }

    public function store(
        Request $request,
        TourCabinetSupportMessageService $messageService,
    ): RedirectResponse {
        $validated = $this->validateTicketPayload($request);

        $ticket = $messageService->createTicketWithFirstMessage(
            $request->user(),
            $validated['subject'],
            $validated['category'],
            $validated['body'],
            $request->file('attachments', []) ?? [],
        );

        return redirect()
            ->route('tour-cabinet.support.show', $ticket)
            ->with('success', 'Обращение создано. Ответ появится здесь в переписке.');
    }

    public function show(Request $request, TourCabinetSupportTicket $ticket): Response
    {
        $this->authorizeTicketOwner($request, $ticket);

        $ticket->load(['messages' => fn ($q) => $q->orderBy('created_at'), 'messages.author:id,name,email', 'messages.attachments']);

        return Inertia::render('TourCabinet/Support/Show', [
            'ticket' => $this->ticketDetail($ticket),
            'messages' => $ticket->messages->map(fn ($m) => $this->messagePayload($m)),
            'supportContactEmail' => config('tour_cabinet.support_contact_email'),
        ]);
    }

    public function storeMessage(
        Request $request,
        TourCabinetSupportTicket $ticket,
        TourCabinetSupportMessageService $messageService,
    ): RedirectResponse {
        $this->authorizeTicketOwner($request, $ticket);

        if ($ticket->status === TourCabinetSupportTicket::STATUS_CLOSED) {
            return redirect()
                ->back()
                ->with('error', 'Обращение закрыто. Новые сообщения недоступны.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:20000'],
            'attachments' => ['nullable', 'array', 'max:'.TourCabinetSupportMessageService::MAX_ATTACHMENTS],
            'attachments.*' => ['file', 'max:'.TourCabinetSupportMessageService::MAX_FILE_KB, 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx'],
        ], [
            'body.required' => 'Введите текст сообщения.',
            'attachments.max' => 'Не более '.TourCabinetSupportMessageService::MAX_ATTACHMENTS.' файлов.',
        ]);

        $messageService->createUserMessage(
            $ticket,
            $request->user(),
            $validated['body'],
            $request->file('attachments', []) ?? [],
        );

        $ticket->refresh();

        return redirect()
            ->route('tour-cabinet.support.show', $ticket)
            ->with('success', 'Сообщение отправлено.');
    }

    public function downloadAttachment(Request $request, TourCabinetSupportAttachment $attachment): StreamedResponse
    {
        $attachment->load('message.ticket');
        $ticket = $attachment->message->ticket;
        $user = $request->user();

        if ($ticket->user_id !== $user->id && ! $user->is_admin) {
            abort(403);
        }

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404);
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_filename ?: 'file',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTicketPayload(Request $request): array
    {
        return $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(TourCabinetSupportTicket::CATEGORIES)],
            'body' => ['required', 'string', 'min:1', 'max:20000'],
            'attachments' => ['nullable', 'array', 'max:'.TourCabinetSupportMessageService::MAX_ATTACHMENTS],
            'attachments.*' => ['file', 'max:'.TourCabinetSupportMessageService::MAX_FILE_KB, 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx'],
        ], [
            'subject.required' => 'Укажите тему обращения.',
            'category.required' => 'Выберите категорию.',
            'body.required' => 'Опишите вопрос или проблему.',
            'attachments.max' => 'Не более '.TourCabinetSupportMessageService::MAX_ATTACHMENTS.' файлов.',
        ]);
    }

    private function authorizeTicketOwner(Request $request, TourCabinetSupportTicket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return collect(TourCabinetSupportTicket::CATEGORIES)
            ->map(fn (string $c) => ['value' => $c, 'label' => TourCabinetSupportTicket::categoryLabel($c)])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketListRow(TourCabinetSupportTicket $t): array
    {
        return [
            'id' => $t->id,
            'subject' => $t->subject,
            'category' => $t->category,
            'category_label' => TourCabinetSupportTicket::categoryLabel($t->category),
            'status' => $t->status,
            'status_label' => TourCabinetSupportTicket::statusLabel($t->status),
            'last_message_at' => $t->last_message_at?->toIso8601String(),
            'created_at' => $t->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketDetail(TourCabinetSupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'category' => $ticket->category,
            'category_label' => TourCabinetSupportTicket::categoryLabel($ticket->category),
            'status' => $ticket->status,
            'status_label' => TourCabinetSupportTicket::statusLabel($ticket->status),
            'created_at' => $ticket->created_at?->toIso8601String(),
            'can_reply' => $ticket->status !== TourCabinetSupportTicket::STATUS_CLOSED,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(TourCabinetSupportMessage $m): array
    {
        $author = $m->author;

        return [
            'id' => $m->id,
            'body' => $m->body,
            'created_at' => $m->created_at?->toIso8601String(),
            'is_admin' => $m->isFromAdmin(),
            'author_label' => $m->isFromAdmin()
                ? ('Поддержка'.($author ? ' ('.$author->name.')' : ''))
                : 'Вы',
            'attachments' => $m->attachments->map(fn (TourCabinetSupportAttachment $a) => [
                'id' => $a->id,
                'name' => $a->original_filename,
                'url' => route('tour-cabinet.support.attachments.show', $a, absolute: false),
            ])->values()->all(),
        ];
    }
}
