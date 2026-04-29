<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\TourCabinetHubPageData;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Управление блоком «Твой билет в атомный город» (Разделение) на дашборде ЛК туров.
 * Контент сохраняется как JSON-строка в settings (группа tour_cabinet,
 * ключ atomic_ticket_block); приоритет над дефолтами config/tour_cabinet.php.
 */
class TourCabinetAtomicTicketController extends Controller
{
    private const SETTINGS_GROUP = 'tour_cabinet';

    private const SETTINGS_KEY = 'atomic_ticket_block';

    private const FRAGMENT = 'tour-cabinet-admin-atomic-ticket';

    public function __construct(
        private readonly SettingsService $settings,
        private readonly TourCabinetHubPageData $hubPageData,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'Admin/TourCabinet/AtomicTicket/Index',
            $this->hubPageData->atomicTicketBlockPayload(),
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'free' => ['array'],
            'free.title' => ['nullable', 'string', 'max:255'],
            'free.cta_label' => ['nullable', 'string', 'max:100'],
            'free.steps' => ['array', 'max:10'],
            'free.steps.*.title' => ['nullable', 'string', 'max:255'],
            'free.steps.*.description' => ['nullable', 'string', 'max:1000'],
            'paid' => ['array'],
            'paid.title' => ['nullable', 'string', 'max:255'],
            'paid.cta_label' => ['nullable', 'string', 'max:100'],
            'paid.steps' => ['array', 'max:10'],
            'paid.steps.*.title' => ['nullable', 'string', 'max:255'],
            'paid.steps.*.description' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [
            'enabled' => (bool) ($validated['enabled'] ?? false),
            'title' => isset($validated['title']) ? trim((string) $validated['title']) : '',
            'free' => $this->normalizeCardInput($validated['free'] ?? []),
            'paid' => $this->normalizeCardInput($validated['paid'] ?? []),
        ];

        $this->settings->setGroup(self::SETTINGS_GROUP, [
            self::SETTINGS_KEY => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return redirect()
            ->route('admin.tour-cabinet.index')
            ->withFragment(self::FRAGMENT)
            ->with('success', 'Блок «Твой билет в атомный город» обновлён.');
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array{title:string, cta_label:string, steps: array<int, array{title:string, description:string}>}
     */
    private function normalizeCardInput(array $card): array
    {
        $steps = [];
        foreach ((array) ($card['steps'] ?? []) as $step) {
            $stepTitle = is_array($step) && isset($step['title']) ? trim((string) $step['title']) : '';
            $stepDescription = is_array($step) && isset($step['description']) ? trim((string) $step['description']) : '';
            if ($stepTitle === '' && $stepDescription === '') {
                continue;
            }
            $steps[] = [
                'title' => $stepTitle,
                'description' => $stepDescription,
            ];
        }

        return [
            'title' => isset($card['title']) ? trim((string) $card['title']) : '',
            'cta_label' => isset($card['cta_label']) ? trim((string) $card['cta_label']) : '',
            'steps' => $steps,
        ];
    }
}
