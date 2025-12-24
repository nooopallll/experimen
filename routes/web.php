<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/manajemen-pesanan', function () {
    // Nantinya data $orders akan diambil dari database di sini
    $orders = []; // Sementara kosong
    return view('manajemen-pesanan', compact('orders'));
})->middleware(['auth'])->name('pesanan.index');

Route::get('/kebutuhan', function () {
    return view('kebutuhan');
})->middleware(['auth'])->name('kebutuhan.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {
    // Jalur untuk mengecek nomor (Baru/Repeat)
    Route::post('/order/check', [OrderController::class, 'checkNumber'])->name('order.check');
    
    // Jalur untuk menyimpan data order final
    Route::post('/order/store', [OrderController::class, 'store'])->name('orders.store');
    
    // Jalur untuk halaman manajemen pesanan (tabel)
    Route::get('/manajemen-pesanan', [OrderController::class, 'index'])->name('pesanan.index');
});

require __DIR__.'/auth.php';
