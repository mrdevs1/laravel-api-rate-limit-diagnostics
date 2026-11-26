
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('demo-api', function (Request $request) {
            $key = $request->user()
                ? 'user:' . $request->user()->id
                : 'ip:' . $request->ip();

            return Limit::perMinute(
                $request->user() ? 100 : 60
            )
                ->by($key)
                ->response(function (
                    Request $request,
                    array $headers
                ) {
                    return response()->json([
                        'message' => 'Too many requests. Please try again later.',
                    ], Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });
    }
}
