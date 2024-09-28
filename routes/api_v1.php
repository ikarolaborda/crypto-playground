<?php

use App\Http\Controllers\Api\v1\CoinController;
use Illuminate\Support\Facades\Route;

Route::get('/coin/current', [CoinController::class, 'getCurrentPrice'])->name('coin.current_price');
Route::get('/coin/historical', [CoinController::class, 'getPriceAt'])->name('coin.historical_price');
Route::get('/exchange-rates', [CoinController::class, 'getExchangeRates'])->name('exchange.rates');
