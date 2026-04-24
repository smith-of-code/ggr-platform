<?php

namespace App\Http\Controllers;

use App\Models\Promocode;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'tour_id' => 'required|exists:tours,id',
        ]);

        $promocode = Promocode::query()
            ->where('code', $request->input('code'))
            ->first();

        if (! $promocode) {
            return response()->json([
                'valid' => false,
                'message' => 'Промокод не найден.',
            ]);
        }

        if (! $promocode->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Промокод неактивен.',
            ]);
        }

        if (! $promocode->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Срок действия промокода истёк.',
            ]);
        }

        if (! $promocode->isValidForTour((int) $request->input('tour_id'))) {
            return response()->json([
                'valid' => false,
                'message' => 'Промокод не применим к данному туру.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount_percent' => $promocode->discount_percent,
            'promocode_id' => $promocode->id,
            'message' => "Скидка {$promocode->discount_percent}% применена!",
        ]);
    }
}
