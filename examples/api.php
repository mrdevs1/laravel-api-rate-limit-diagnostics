
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:demo-api')
    ->get('/rate-limit-demo', function () {
        return response()->json([
            'message' => 'Request successful',
        ]);
    });
