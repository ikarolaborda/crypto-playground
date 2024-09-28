<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Services\CoinGeckoService;
use GuzzleHttp\Client;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CoinGeckoServiceTest extends TestCase
{
    public function testGetCurrentPriceReturnsPrice(): void
    {

        // We set the cache to array for test purposes
        Cache::shouldReceive('remember')
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        $coinId = 'bitcoin';
        $expectedPrice = 50000.00;

        $mockResponseData = [
            'market_data' => [
                'current_price' => ['usd' => $expectedPrice],
            ],
        ];

        // Mock the StreamInterface
        $mockStream = Mockery::mock(StreamInterface::class);
        $mockStream->shouldReceive('getContents')
            ->andReturn(json_encode($mockResponseData));

        // Mock the ResponseInterface
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody')
            ->andReturn($mockStream);

        // Mock the Guzzle Client
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with("coins/{$coinId}", Mockery::any())
            ->andReturn($mockResponse);

        $service = new CoinGeckoService($mockClient);
        $price = $service->getCurrentPrice($coinId);

        $this->assertEquals($expectedPrice, $price);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
