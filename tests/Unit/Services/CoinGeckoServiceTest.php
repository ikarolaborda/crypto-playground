<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CoinGeckoService;
use GuzzleHttp\ClientInterface;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Illuminate\Support\Facades\Cache;

class CoinGeckoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    public function testGetCurrentPriceReturnsPrice(): void
    {
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
        $mockClient = Mockery::mock(ClientInterface::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with("coins/{$coinId}", Mockery::any())
            ->andReturn($mockResponse);

        // Mock the Cache::remember method to execute the closure
        Cache::shouldReceive('remember')
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

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
