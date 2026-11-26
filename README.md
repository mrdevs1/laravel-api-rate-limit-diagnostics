# Laravel API Rate Limit Diagnostics

A practical Laravel project for implementing, testing, and troubleshooting API rate limits.

## Overview

API rate limiting helps protect applications from excessive requests, automated abuse, and accidental traffic spikes.

Laravel provides built-in rate limiting through the `RateLimiter` facade and the `throttle` middleware.

This repository demonstrates how to configure rate limits, identify clients, handle HTTP 429 responses, inspect rate limit headers, and test throttling behavior.

## Features

- Per-user API rate limits
- Per-IP rate limits for guest requests
- Custom rate limiter configuration
- HTTP 429 response handling
- Rate limit response headers
- Laravel feature tests
- Troubleshooting examples
- Production deployment checklist

## Requirements

- PHP 8.2 or compatible with your Laravel version
- Laravel 11 or later
- Composer
- A configured Laravel cache store

The examples use the modern Laravel application structure. Some configuration locations may differ in older Laravel versions.

## Installation

Create a Laravel application:

```bash
composer create-project laravel/laravel laravel-api-rate-limit
```

Move into the project directory:

```bash
cd laravel-api-rate-limit
```

Copy the example files from this repository into their corresponding locations.

Run the application:

```bash
php artisan serve
```

## How Rate Limiting Works

A rate limiter identifies a client using a unique key and tracks the number of requests within a defined time window.

For example:

```php
Limit::perMinute(60)->by($request->ip());
```

This allows up to 60 requests per minute for each IP address.

For authenticated users, a user ID can be used instead:

```php
Limit::perMinute(100)->by($request->user()->id);
```

A request that exceeds the configured limit receives an HTTP 429 response.

## Configuration

Register a named rate limiter in your application's `AppServiceProvider`.

The example configuration is available in:

`examples/RateLimitServiceProvider.php`

The configuration demonstrates:

- A maximum of 60 requests per minute for guests
- A maximum of 100 requests per minute for authenticated users
- Client identification using user ID or IP address
- A custom response when the limit is exceeded

## Applying Rate Limits to Routes

Use the named limiter with Laravel's `throttle` middleware.

Example:

```php
Route::middleware('throttle:demo-api')
    ->get('/rate-limit-demo', function () {
        return response()->json([
            'message' => 'Request successful',
        ]);
    });
```

The complete route example is available in:

`examples/api.php`

## Rate Limit Response

When the request limit is exceeded, Laravel returns a 429 response.

Example response:

```json
{
    "message": "Too many requests. Please try again later."
}
```

The response may include headers such as:

- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After`

These headers help clients understand their current request allowance and when they can retry.

## Testing Rate Limits

This repository includes a feature test that verifies:

1. Requests within the configured limit succeed.
2. The response contains rate limit headers.
3. Requests beyond the limit return HTTP 429.
4. The custom error response is returned.

Run the test suite:

```bash
php artisan test
```

Run only the rate limit feature test:

```bash
php artisan test --filter=RateLimitTest
```

The test file is available in:

`tests/Feature/RateLimitTest.php`

## Troubleshooting

### Requests Are Never Limited

Check that the route uses the correct middleware:

```php
->middleware('throttle:demo-api')
```

Verify that the named limiter is registered with:

```php
RateLimiter::for('demo-api', function ($request) {
    // Limiter configuration
});
```

### Every User Shares the Same Limit

Check the key used in `Limit::by()`.

Using a static key can cause unrelated clients to share the same counter.

Use a user ID for authenticated clients and an IP address for guests.

### Rate Limits Reset Unexpectedly

Check the configured cache store and its expiration behavior.

For production applications, use a reliable shared cache such as Redis when multiple application servers need to share rate limit counters.

### Rate Limits Differ Between Servers

If the application runs on multiple servers, make sure they use the same shared cache backend and consistent cache configuration.

A local file or in-memory cache on each server will not provide a shared request counter.

### HTTP 429 Is Returned Too Early

Check whether multiple rate limits are applied to the same route.

Laravel can evaluate more than one limit for a route. A restrictive limit may be responsible for the response.

Also check whether the same limiter is applied globally and again at the route level.

### Rate Limit Headers Are Missing

Inspect the actual response returned by the throttling middleware.

Verify that custom response handling preserves the headers provided by Laravel.

## Production Checklist

- [ ] Rate limits are configured for sensitive API endpoints.
- [ ] Authenticated users are identified by user ID.
- [ ] Guest requests are identified by IP address.
- [ ] The application uses a shared cache where required.
- [ ] HTTP 429 responses are handled by API clients.
- [ ] Retry information is preserved in response headers.
- [ ] Rate limits are tested before deployment.
- [ ] Login and password reset endpoints have appropriate limits.
- [ ] Rate limit configuration is reviewed after traffic changes.
- [ ] Monitoring is available for repeated throttling.

## Security Considerations

Rate limiting is one layer of API protection. It does not replace authentication, authorization, input validation, or abuse monitoring.

Do not rely on IP addresses as the only identity for authenticated users.

When an application is behind a proxy or load balancer, configure trusted proxies correctly so Laravel receives the intended client IP address.

Avoid setting extremely high limits without understanding the expected traffic and resource cost.

## Repository Structure

```text
laravel-api-rate-limit-diagnostics/
├── README.md
├── examples/
│   ├── RateLimitServiceProvider.php
│   └── api.php
└── tests/
    └── Feature/
        └── RateLimitTest.php
```

## References

- [Laravel Rate Limiting](https://laravel.com/docs/13.x/rate-limiting)
- [Laravel Routing and Throttling](https://laravel.com/docs/13.x/routing#rate-limiting)

## Contributing

Contributions are welcome.

Please include the Laravel version, PHP version, reproduction steps, and expected behavior when reporting a rate limiting issue.

## License

This project is available under the MIT License.
