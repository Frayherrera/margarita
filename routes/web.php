<?php

use App\Http\Controllers\Panel\CashController;
use App\Http\Controllers\Panel\CategoryController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\InventoryController;
use App\Http\Controllers\Panel\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'))->name('home');

Route::get('/gestion', function () {
    return view('gestion');
})->middleware('auth')->name('gestion');

Route::middleware('auth')->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('productos', ProductController::class)
        ->except(['show'])
        ->parameters(['productos' => 'producto']);

    Route::resource('categorias', CategoryController::class)
        ->except(['show'])
        ->parameters(['categorias' => 'categoria']);

    Route::get('inventario', [InventoryController::class, 'index'])->name('inventario.index');
    Route::post('inventario', [InventoryController::class, 'store'])->name('inventario.store');

    Route::get('caja', [CashController::class, 'index'])->name('caja.index');
    Route::post('caja', [CashController::class, 'store'])->name('caja.store');
    Route::delete('caja/{cashMovement}', [CashController::class, 'destroy'])->name('caja.destroy');
});

require __DIR__.'/auth.php';
