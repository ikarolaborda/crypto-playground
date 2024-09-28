<?php

namespace App\Services;

use App\Contracts\CoinServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use http\Env;
use Illuminate\Support\Facades\Log;

class CoinGeckoService implements CoinServiceInterface
{
    private Client $client;

    public function __construct(Client $client = null)
    {
        $baseUri = config('coingecko.base_uri');
        $apiKey = config('coingecko.api_key');

        $headers = [];

        if ($apiKey) {
            $headers['x-cg-pro-api-key'] = $apiKey;
        }

        $this->client = $client ?? new Client([
            'base_uri' => $baseUri,
            'timeout'  => 10.0,
            'headers' => $headers,
        ]);
    }

    public function getCurrentPrice(string $coinId): float
    {
        try {
            $response = $this->client->get("coins/{$coinId}", [
                'query' => [
                    'localization' => 'false',
                    'tickers' => 'false',
                    'market_data' => 'true',
                    'community_data' => 'false',
                    'developer_data' => 'false',
                    'sparkline' => 'false',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['market_data']['current_price']['usd'] ?? 0.0;
        } catch (RequestException $e) {
            Log::error("Error fetching current price: {$e->getMessage()}");
            return 0.0;
        } catch (GuzzleException $e) {
            Log::error("Error fetching current price: {$e->getMessage()}");
            return 0.0;
        }
    }

    public function getHistoricalPrice(string $coinId, string $date): float
    {
        try {
            $response = $this->client->get("coins/{$coinId}/history", [
                'query' => [
                    'date' => $date, // Format: dd-mm-yyyy
                    'localization' => 'false',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['market_data']['current_price']['usd'] ?? 0.0;
        } catch (RequestException $e) {
            Log::error("Error fetching historical price: {$e->getMessage()}");
            return 0.0;
        }
    }

    public function getExchangeRates(): array
    {
        try {
            $response = $this->client->get('exchange_rates');

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['rates'] ?? [];
        } catch (RequestException $e) {
            Log::error("Error fetching exchange rates: {$e->getMessage()}");
            return [];
        }
    }

}
