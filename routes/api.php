<?php

use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Comments\CommentController;
use App\Http\Controllers\Api\Ecommerce\CategoryController;
use App\Http\Controllers\Api\Ecommerce\CouponController;
use App\Http\Controllers\Api\Ecommerce\ProductController;
use App\Http\Controllers\Api\Ecommerce\ShipmentController;
use App\Http\Controllers\Api\Locations\LocationController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\User\PaymentController;
use App\Http\Controllers\Ecommerce\Cart\UserCartController;
use App\Http\Controllers\Ecommerce\Order\UserOrderController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\UserAddressController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\WalletController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Public Routes
Route::middleware('api')->group(function () {
    // Authentication routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('authenticate', [AuthenticationController::class, 'authenticate'])->name('authenticate');
        Route::post('register', [AuthenticationController::class, 'register'])->name('register');
        Route::post('change-password', [AuthenticationController::class, 'changePassword'])->name('change.password');
        Route::post('login', [AuthenticationController::class, 'login'])->name('login');
        Route::post('otp', [AuthenticationController::class, 'otp'])->name('otp');
        Route::get('me', [AuthenticationController::class, 'me'])->name('me');
    });

    // Category routes
    Route::prefix('category')->group(function () {
        Route::get('list', [CategoryController::class, 'list'])->name('category.list');
        Route::get('{category}/filters', [CategoryController::class, 'filters'])->name('category.filters');
        Route::get('{category}/products', [CategoryController::class, 'products'])->name('category.products');
    });

    // Product routes
    Route::prefix('products/get')->group(function () {
        Route::get('best-sellers', [ProductController::class, 'bestSellers'])->name('product.bestSeller');
        Route::get('trends', [ProductController::class, 'trends'])->name('product.trends');
        Route::post('special-prices', [ProductController::class, 'specialPrices'])->name('product.specialPrices');
        Route::group(['prefix' => 'prices'], function () {
            Route::get('{slug}', [ProductController::class, 'getPrices'])->name('product.prices.get');
        });
    });

    // Public comment routes
    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');

    // User cart routes
    Route::group(['prefix' => 'cart'], function () {
        Route::post('add', [UserCartController::class, 'addToCart']);
        Route::put('{id}/update', [UserCartController::class, 'updateCart']);
        Route::get('/', [UserCartController::class, 'getCart']);
        Route::delete('{id}', [UserCartController::class, 'removeFromCart']);
    });

    // Geo list
    Route::group(['prefix' => 'locations'], function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::get('/{location}', [LocationController::class, 'show']);
    });

    // Recommendation
    Route::group(['prefix' => 'recommendations'], function () {
        Route::get('/questions', [RecommendationController::class, 'getQuestion']);
        Route::post('/result', [RecommendationController::class, 'suggestion']);
    });

    // Search
    Route::get('/search', [SearchController::class, 'search']);

    // Subscribe
    Route::post('/subscribe', [SubscriptionController::class, 'store']);
});

// Route::middleware(['web'])->group(function () {
//     Route::post('cart/add', [UserCartController::class, 'addToCart']);
//     Route::put('cart/{id}/update', [UserCartController::class, 'updateCart']);
//     Route::get('cart', [UserCartController::class, 'getCart']);
//     Route::delete('cart/{id}', [UserCartController::class, 'removeFromCart']);
// });

// Authenticated Routes
Route::middleware(['auth:sanctum', EnsureFrontendRequestsAreStateful::class])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthenticationController::class, 'logout'])->name('auth.logout');
    });

    // Comment resource routes
    Route::apiResource('comments', CommentController::class)
        ->except(['index', 'store'])
        ->names([
            'show' => 'comments.show',
            'update' => 'comments.update',
            'destroy' => 'comments.destroy',
        ]);

    // Comment voting
    Route::post('comments/{comment}/upvote', [CommentController::class, 'upvote'])->name('comments.upvote');
    Route::post('comments/{comment}/downvote', [CommentController::class, 'downvote'])->name('comments.downvote');

    Route::prefix('shipment')->group(function () {
        Route::get('/methods', [ShipmentController::class, 'methods']);
        Route::get('/methods', [ShipmentController::class, 'methods']);
    });

    // Orders route
    Route::prefix('orders')->group(function () {
        Route::get('/number/{orderNumber}', [UserOrderController::class, 'showByOrderNumber']);
        Route::get('/full-count', [UserOrderController::class, 'fullCount']);
    });

    // Coupon Routes
    Route::prefix('coupons')->group(function () {
        Route::post('/apply', [CouponController::class, 'apply']);
    });

    // Payments Routes
    Route::prefix('payments')->group(function () {
        Route::get('/verify', [PaymentController::class, 'verify']);
    });

    // Api Resources
    Route::apiResources([
        'profiles' => UserProfileController::class,
        'addresses' => UserAddressController::class,
        'orders' => UserOrderController::class,
        'payments' => PaymentController::class,
        'wallets' => WalletController::class,
        'transactions' => TransactionController::class,
    ]);
});

// Fallback route for unauthorized access
Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
})->name('fallback');

// Custom route for unauthenticated access
Route::get('/login', function () {
    return response()->json(['message' => 'Authentication required'], 401);
})->name('login');
