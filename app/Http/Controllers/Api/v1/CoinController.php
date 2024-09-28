<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\CoinServiceInterface;
use App\Http\Controllers\Controller;
use App\Services\CoinGeckoService;
use App\Repositories\CoinPriceRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CoinController extends Controller
{
    private CoinServiceInterface $coinGeckoService;
    private CoinPriceRepository $coinPriceRepository;

    public function __construct(
        CoinServiceInterface $coinGeckoService,
        CoinPriceRepository $coinPriceRepository
    ) {
        $this->coinGeckoService = $coinGeckoService;
        $this->coinPriceRepository = $coinPriceRepository;
    }

    public function getCurrentPrice(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'coin' => 'required|string',
        ]);

        $coinSymbol = strtoupper($validatedData['coin']);
        $coinId = config("coins.{$coinSymbol}");

        if (!$coinId) {
            return response()->json(
                [
                    'error' => 'Unsupported coin'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $price = $this->coinGeckoService->getCurrentPrice($coinId);
        $retrievedAt = now();

        $this->coinPriceRepository->save($coinSymbol, $price, $retrievedAt);

        return response()->json([
            'coin' => $coinSymbol,
            'price' => $price,
            'retrieved_at' => $retrievedAt->toDateTimeString(),
        ]);
    }

    public function getPriceAt(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'coin' => 'required|string',
            'datetime' => 'required|date',
        ]);

        $coinSymbol = strtoupper($validatedData['coin']);
        $coinId = config("coins.{$coinSymbol}");

        if (!$coinId) {
            return response()->json(
                [
                    'error' => 'Unsupported coin'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $dateTime = new \DateTime($validatedData['datetime']);
        $date = $dateTime->format('d-m-Y');

        $price = $this->coinGeckoService->getHistoricalPrice($coinId, $date);

        $this->coinPriceRepository->save($coinSymbol, $price, $dateTime);

        return response()->json(
            [
                'coin' => $coinSymbol,
                'price' => $price,
                'retrieved_at' => $dateTime->format('Y-m-d H:i:s'),
            ],
            Response::HTTP_OK
        );
    }

    public function getExchangeRates(): JsonResponse
    {
        $rates = $this->coinGeckoService->getExchangeRates();

        return response()->json($rates);
    }
}
