<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MemberController; 
use App\Http\Controllers\OrderDetailController; 
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KebutuhanController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\TreatmentController;
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
        // Jika owner nyasar ke sini, lempar ke dashboard owner
        if (auth()->user()->role === 'owner') {
            return redirect()->route('owner.dashboard');
        }
        return view('cek-customer');
    })->name('dashboard');

    Route::get('/owner/laporan-pendapatan', [DashboardController::class, 'laporan'])
    ->name('owner.laporan');

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

    Route::get('/check-customer', [OrderController::class, 'checkCustomer'])->name('check.customer');

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
    // Tambahkan di dalam group middleware ['auth', 'verified']
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::get('/kebutuhan', [KebutuhanController::class, 'index'])->name('kebutuhan.index');
    Route::post('/kebutuhan/store', [KebutuhanController::class, 'store'])->name('kebutuhan.store');

    Route::get('/owner/dashboard', [DashboardController::class, 'index'])
        ->name('owner.dashboard');

    Route::get('/owner/kebutuhan', [KebutuhanController::class, 'ownerIndex'])->name('owner.kebutuhan');

    // Route untuk aksi centang (Hapus)
    Route::delete('/owner/kebutuhan/{id}', [KebutuhanController::class, 'markAsPurchased'])->name('owner.kebutuhan.done');
   
    Route::get('/owner/karyawan', [KaryawanController::class, 'index'])->name('owner.karyawan.index');
    Route::post('/owner/karyawan', [KaryawanController::class, 'store'])->name('owner.karyawan.store');
    Route::put('/owner/karyawan/{id}', [KaryawanController::class, 'update'])->name('owner.karyawan.update');
    Route::delete('/owner/karyawan/{id}', [KaryawanController::class, 'destroy'])->name('owner.karyawan.destroy');    

    Route::get('/owner/treatments', [TreatmentController::class, 'index'])->name('owner.treatments.index');
    Route::post('/owner/treatments', [TreatmentController::class, 'store'])->name('owner.treatments.store');
    Route::put('/owner/treatments/{id}', [TreatmentController::class, 'update'])->name('owner.treatments.update');
    Route::delete('/owner/treatments/{id}', [TreatmentController::class, 'destroy'])->name('owner.treatments.destroy');
    });

require __DIR__.'/auth.php';