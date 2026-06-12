<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\TshirtImageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TshirtImageController::class, 'shopIndex'])->name('home');
Route::get('shop', [TshirtImageController::class, 'shopIndex'])->name('shop.index');
Route::get('shop/{tshirtImage}', [TshirtImageController::class, 'shopShow'])->name('shop.tshirt-details');
Route::get('private/tshirt-images/{tshirtImage}/image', [TshirtImageController::class, 'privateImage'])
    ->middleware(['auth', 'verified'])
    ->withTrashed()
    ->name('tshirt-images.private-image');

Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::post('cart', [CartController::class, 'add'])->name('cart.add');
Route::put('cart/{line}', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/{line}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('cart', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('checkout', [OrderController::class, 'checkout'])->name('checkout.show');
    Route::post('checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/receipts', [OrderController::class, 'receipts'])->name('orders.receipts');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/items/{item}/image', [OrderController::class, 'itemImage'])->name('orders.items.image');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order}/close', [OrderController::class, 'close'])->name('orders.close');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    Route::get('personal-tshirt-images', [TshirtImageController::class, 'personalIndex'])
        ->name('personal-tshirt-images.index');
    Route::get('personal-tshirt-images/create', [TshirtImageController::class, 'personalCreate'])
        ->name('personal-tshirt-images.create');
    Route::post('personal-tshirt-images', [TshirtImageController::class, 'personalStore'])
        ->name('personal-tshirt-images.store');
    Route::get('personal-tshirt-images/{personal_tshirt_image}/edit', [TshirtImageController::class, 'personalEdit'])
        ->name('personal-tshirt-images.edit');
    Route::put('personal-tshirt-images/{personal_tshirt_image}', [TshirtImageController::class, 'personalUpdate'])
        ->name('personal-tshirt-images.update');
    Route::delete('personal-tshirt-images/{personal_tshirt_image}', [TshirtImageController::class, 'personalDestroy'])
        ->name('personal-tshirt-images.destroy');

    Route::middleware('can:manage-catalog')->group(function () {
        Route::delete('categories/{category}/image', [CategoryController::class, 'destroyImage'])
            ->name('categories.image.destroy');
        Route::resource('categories', CategoryController::class);
        Route::resource('colors', ColorController::class);
        Route::resource('tshirt-images', TshirtImageController::class)
            ->parameters(['tshirt-images' => 'tshirtImage']);
        Route::get('prices', [PriceController::class, 'edit'])->name('prices.edit');
        Route::put('prices', [PriceController::class, 'update'])->name('prices.update');
        Route::patch('users/{user}/block-unblock', [UserController::class, 'blockUnblock'])
            ->name('users.block-unblock');
        Route::patch('users/{user}/change-type', [UserController::class, 'changeType'])
            ->name('users.change-type');
        Route::delete('users/{user}/photo', [UserController::class, 'destroyPhoto'])
            ->name('users.photo.destroy');
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';
