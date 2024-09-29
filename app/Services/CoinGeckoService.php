<?php

namespace App\Services;

use App\Contracts\CoinServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CoinGeckoService implements CoinServiceInterface
{

    private ClientInterface $client;

    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?? $this->createDefaultClient();
    }

    public function getCurrentPrice(string $coinId): float
    {
        if (app()->environment('testing')) {
            return $this->fetchCurrentPrice($coinId);
        }

        $cacheKey = "coin_price_{$coinId}_current";
        $cacheTTL = 300; // Cache for 5 minutes

        return Cache::remember($cacheKey, $cacheTTL, function () use ($coinId) {
            return $this->fetchCurrentPrice($coinId);
        });
    }

    public function getHistoricalPrice(string $coinId, string $date): float
    {
        $cacheKey = "coin_price_{$coinId}_{$date}_historical";
        $cacheTTL = 86400; // Cache for 24 hours

        return Cache::remember($cacheKey, $cacheTTL, function () use ($coinId, $date) {
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
            } catch (GuzzleException $e) {
                Log::error("Error fetching historical price: {$e->getMessage()}");
                return 0.0;
            }
        });
    }

    public function getExchangeRates(): array
    {
        $cacheKey = "exchange_rates";
        $cacheTTL = 3600; // Cache for 1 hour

        return Cache::remember($cacheKey, $cacheTTL, function () {
            try {
                $response = $this->client->get('exchange_rates');

                $data = json_decode($response->getBody()->getContents(), true);

                return $data['rates'] ?? [];
            } catch (RequestException $e) {
                Log::error("Error fetching exchange rates: {$e->getMessage()}");
                return [];
            } catch (GuzzleException $e) {
                Log::error("Error fetching exchange rates: {$e->getMessage()}");
                return [];
            }
        });
    }

    private function fetchCurrentPrice(string $coinId): float
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

    private function createDefaultClient(): ClientInterface
    {
        $baseUri = config('coingecko.base_uri');
        $apiKey = config('coingecko.api_key');

        $headers = [];

        if ($apiKey) {
            $headers['x-cg-pro-api-key'] = $apiKey;
        }

        return new Client([
            'base_uri' => $baseUri,
            'timeout'  => 10.0,
            'headers' => $headers,
        ]);
    }

}
