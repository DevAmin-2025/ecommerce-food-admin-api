<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\TransactionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResources([
        'sliders' => SliderController::class,
        'features' => FeatureController::class,
        'categories' => CategoryController::class,
        'products' => ProductController::class,
        'coupons' => DiscountController::class,
    ]);

    Route::singleton('about-us', AboutUsController::class)->only(['show', 'update']);
    Route::singleton('footer', FooterController::class)->only(['show', 'update']);

    Route::apiResource('contact-us', ContactUsController::class)
        ->except(['store', 'update'])
        ->parameters(['contact-us' => 'contactUs']);

    Route::controller(OrderController::class)->group(function () {
        Route::get('orders', 'index');
        Route::put('orders/{order}', 'update');
    });
    Route::controller(TransactionController::class)->group(function () {
        Route::get('transactions', 'index');
        Route::get('transactions/chart', 'chart');
    });

    Route::apiResource('users', UserController::class)->except('destroy');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('roles', RoleController::class)->except('destroy', 'show');
});

Route::post('login', [AuthController::class, 'login']);
