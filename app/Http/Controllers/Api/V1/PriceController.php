<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\RateProviderInterface;

class PriceController extends Controller
{
    public function convert(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric'],
            'from' => ['required', 'string', 'max:10'],
            'to' => ['required', 'string', 'max:10'],
        ]);

        $from = strtoupper($data['from']);
        $to = strtoupper($data['to']);
        $amount = (float) $data['amount'];

        if ($from === $to) {
            return response()->json([
                'data' => [
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'converted' => $amount,
                    'fx_rate' => 1,
                    'fx_rated_at' => now()->toIso8601String(),
                ],
            ]);
        }

        $rateTable = app(RateProviderInterface::class)->getRates($from);
        $rate = (float) ($rateTable['rates'][$to] ?? 0);
        if ($rate <= 0) {
            abort(422, 'Unsupported currency conversion');
        }

        $converted = $amount * $rate;
        $decimals = app(CurrencyCatalogInterface::class)->isCrypto($to) ? 8 : 2;
        $rounded = round($converted, $decimals);

        return response()->json([
            'data' => [
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'converted' => $rounded,
                'fx_rate' => $rate,
                'fx_rated_at' => $rateTable['rated_at'] ?? null,
            ],
        ]);
    }
}
