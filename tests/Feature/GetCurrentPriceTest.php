<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Contracts\CoinServiceInterface;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetCurrentPriceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetCurrentPriceSuccess(): void
    {
        $coinSymbol = 'BTC';
        $coinId = 'bitcoin';
        $expectedPrice = 50000.00;

        $mockService = Mockery::mock(CoinServiceInterface::class);
        $mockService->shouldReceive('getCurrentPrice')
            ->once()
            ->with($coinId)
            ->andReturn($expectedPrice);

        $this->app->instance(CoinServiceInterface::class, $mockService);

        $this->withoutExceptionHandling();

        $response = $this->getJson("/api/v1/coin/current?coin={$coinSymbol}");

        $response->assertStatus(200)
            ->assertJson([
                'coin' => $coinSymbol,
                'price' => $expectedPrice,
            ]);

        $this->assertDatabaseHas('coin_prices', [
            'coin' => $coinSymbol,
            'price' => $expectedPrice,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
