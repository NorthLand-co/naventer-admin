<?php

namespace App\Http\Controllers\Api;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends ApiController
{
    public function store(Request $request)
    {
        // Rate limiting: Allow up to 3 requests per minute per IP
        $key = 'subscribe:'.$request->getClientRealIp();
        $maxAttempts = 5; // Max allowed attempts
        $decaySeconds = 60; // Time period (in seconds)

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Increment rate limiter count
        RateLimiter::hit($key, $decaySeconds);

        // Validate input
        $validated = $request->validate([
            'email' => 'nullable|email|max:255|unique:subscriptions,email',
            'phone' => 'nullable|string|min:10|max:15|unique:subscriptions,phone',
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {

            return response()->json([
                'message' => 'Either email or phone is required.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Store subscription
        Subscription::create($validated);

        return response()->json([
            'message' => 'Subscription successful.',
        ], Response::HTTP_OK);
    }
}
