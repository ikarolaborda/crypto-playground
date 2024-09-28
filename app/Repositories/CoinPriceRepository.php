<?php

namespace App\Repositories;

use App\CoinPrice;

class CoinPriceRepository
{
    public function save(string $coinSymbol, float $price, \DateTime $retrievedAt): CoinPrice
    {
        return CoinPrice::create([
            'coin' => $coinSymbol,
            'price' => $price,
            'retrieved_at' => $retrievedAt,
        ]);
    }

    public function getLatestPrice(string $coin): ?CoinPrice
    {
        return CoinPrice::where('coin', $coin)->orderBy('retrieved_at', 'desc')->first();
    }

    public function getPriceAt(string $coin, \DateTime $dateTime): ?CoinPrice
    {
        return CoinPrice::where('coin', $coin)
            ->where('retrieved_at', '<=', $dateTime)
            ->orderBy('retrieved_at', 'desc')
            ->first();
    }
}
