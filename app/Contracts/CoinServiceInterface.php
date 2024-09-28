<?php

namespace App\Contracts;

interface CoinServiceInterface
{
    public function getCurrentPrice(string $coinId): float;

    public function getHistoricalPrice(string $coinId, string $date): float;

}
