
<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::for('test-api', function (Request $request) {
            return Limit::perMinute(3)
                ->by('test-client')
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many requests.',
                    ], 429, $headers);
                });
        });

        Route::middleware('throttle:test-api')
            ->get('/testing/rate-limit', function () {
                return response()->json([
                    'message' => 'Request successful',
                ]);
            });
    }

    public function test_requests_within_limit_are_successful(): void
    {
        $this->getJson('/testing/rate-limit')
            ->assertOk();

        $this->getJson('/testing/rate-limit')
            ->assertOk();

        $this->getJson('/testing/rate-limit')
            ->assertOk();
    }

    public function test_requests_over_limit_return_429(): void
    {
        $this->getJson('/testing/rate-limit');
        $this->getJson('/testing/rate-limit');
        $this->getJson('/testing/rate-limit');

        $this->getJson('/testing/rate-limit')
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Too many requests.',
            ]);
    }

    public function test_rate_limit_headers_are_returned(): void
    {
        $response = $this->getJson('/testing/rate-limit');

        $response->assertOk();

        $response->assertHeader('X-RateLimit-Limit', '3');
        $response->assertHeader('X-RateLimit-Remaining', '2');
    }
}
