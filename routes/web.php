<?php

use App\Http\Controllers\ProductThumbnailController;
use App\Livewire\Audit\AuditList;
use App\Livewire\Expenses\ExpenseList;
use App\Livewire\Inventory\InventoryList;
use App\Livewire\Products\ProductList;
use App\Livewire\Transactions\PointOfSale;
use App\Livewire\Transactions\TransactionList;
use App\Livewire\Users\UserList;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('produk-thumbnail/{path}', ProductThumbnailController::class)
    ->where('path', '.*')
    ->name('products.thumbnail');

Volt::route('/', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('pos', PointOfSale::class)->name('transactions.pos');
    Route::get('transactions', TransactionList::class)->name('transactions.index');
    Route::get('products', ProductList::class)->name('products.index');
    Route::get('inventory', InventoryList::class)->name('inventory.index');
    Route::get('users', UserList::class)
        ->middleware('admin')
        ->name('users.index');
    Route::get('expenses', ExpenseList::class)
        ->middleware('admin')
        ->name('expenses.index');
    Route::get('audit-logs', AuditList::class)
        ->middleware('admin')
        ->name('audit-logs.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

Route::redirect('home', '/')->name('home');
