<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MemberController; 
use App\Http\Controllers\OrderDetailController; // Tambahkan ini agar tidak error di baris paling bawah
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Semua route di dalam grup ini WAJIB Login dulu
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('cek-customer');
    })->name('dashboard');

    // ==========================================
    // MODULE: MANAJEMEN PESANAN (Order List)
    // ==========================================
    // Menggunakan [OrderController::class, 'index'] agar data diambil dari database
    Route::get('/manajemen-pesanan', [OrderController::class, 'index'])->name('pesanan.index');

    // ==========================================
    // MODULE: INPUT ORDER (Alur Baru)
    // ==========================================
    
    // 1. Tampilkan Form Cek Nomor HP (Awal)
    Route::get('/input-order', function () {
        return view('cek-customer'); 
    })->name('order.search');

    // 2. Proses Cek No HP (Controller: check)
    Route::post('/order/check', [OrderController::class, 'check'])->name('order.check');

    Route::get('/order/check', function () {
            return redirect()->route('order.search'); 
    });

    // 3. Simpan Order Final (Controller: store)
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // ==========================================
    // MODULE: MEMBER (Pop Up)
    // ==========================================
    // Route untuk simpan member dari Pop-Up Modal
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    
    Route::post('/members/claim', [MemberController::class, 'claimPoints'])->name('members.claim');


    // ==========================================
    // MODULE: DETAIL & UPDATE PESANAN
    // ==========================================
    
    Route::get('/pesanan/{id}', [OrderController::class, 'show'])->name('pesanan.show');

    // [BARU] Route untuk Update Data Utama Pesanan (via Modal Pop-up Edit)
    // Route ini WAJIB ADA agar tombol "Simpan Perubahan" di pop-up berfungsi
    Route::patch('/pesanan/update/{id}', [OrderController::class, 'update'])->name('pesanan.update');

    Route::post('/pesanan/{id}/toggle-wa/{type}', [OrderController::class, 'toggleWa'])->name('pesanan.toggle-wa');

    // Route ini untuk update status per-item (detail sepatu)
    Route::post('/pesanan/detail/{id}/update', [OrderController::class, 'updateDetail'])->name('pesanan.detail.update');

    Route::post('/check-customer', [OrderController::class, 'checkCustomer'])->name('check.customer');

    // ==========================================
    // LAIN-LAIN
    // ==========================================
    Route::get('/kebutuhan', function () {
        return view('kebutuhan');
    })->name('kebutuhan.index');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Menggunakan OrderDetailController yang sudah di-use di atas
    Route::patch('/order-details/{id}/status', [OrderDetailController::class, 'updateStatus'])->name('order-details.update-status');
});

require __DIR__.'/auth.php';