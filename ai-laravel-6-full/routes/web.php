<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogTshirtImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PersonalTshirtImageController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\TshirtImageController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TshirtImageController::class, 'index'])->name('home');
Route::get('catalog', [TshirtImageController::class, 'index'])->name('tshirt-images.index');
Route::get('catalog/{tshirtImage}', [TshirtImageController::class, 'show'])->name('tshirt-images.show');
Route::get('private/tshirt-images/{tshirtImage}/image', [TshirtImageController::class, 'privateImage'])
    ->middleware(['auth', 'verified'])
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
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order}/close', [OrderController::class, 'close'])->name('orders.close');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    Route::resource('personal-tshirt-images', PersonalTshirtImageController::class)
        ->only(['index', 'create', 'store', 'destroy']);

    Route::middleware('can:manage-catalog')->group(function () {
        Route::delete('categories/{category}/image', [CategoryController::class, 'destroyImage'])
            ->name('categories.image.destroy');
        Route::resource('categories', CategoryController::class);
        Route::resource('colors', ColorController::class);
        Route::resource('catalog-images', CatalogTshirtImageController::class)
            ->parameters(['catalog-images' => 'catalogImage']);
        Route::get('prices', [PriceController::class, 'edit'])->name('prices.edit');
        Route::put('prices', [PriceController::class, 'update'])->name('prices.update');
        Route::patch('users/{user}/block-unblock', [UserManagementController::class, 'blockUnblock'])
            ->name('users.block-unblock');
        Route::patch('users/{user}/change-type', [UserManagementController::class, 'changeType'])
            ->name('users.change-type');
        Route::resource('users', UserManagementController::class);
    });
});

require __DIR__.'/settings.php';
