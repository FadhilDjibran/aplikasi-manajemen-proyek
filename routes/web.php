<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipeRumahController;
use App\Http\Controllers\HotProspekController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ExportExcelController;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pending', [AuthController::class, 'showPending'])->name('pending');
    Route::get('/users', [UserController::class, 'index'])->name('index');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('update');
    Route::middleware('role:Super_Admin,Admin_Marketing')->group(function () {
        Route::post('/users', [UserController::class, 'store'])->name('store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/users/trigger-cleanup', [UserController::class, 'triggerCleanupUnassigned'])->name('trigger_cleanup');
        Route::resource('tipe_rumah', TipeRumahController::class);
        Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update')->where('id', '[0-9]+');
        });
    Route::middleware('role:Super_Admin')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{id}/enter', [ProjectController::class, 'enterProject'])->name('projects.enter');
        Route::get('/projects/exit', [ProjectController::class, 'exitProject'])->name('projects.exit');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy')->where('id', '[0-9]+');
        });

    Route::middleware('project.active')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::middleware('role:Super_Admin,Admin_Keuangan,Keuangan')->group(function () {
            Route::get('/keuangan/get-saldo-realtime', [KeuanganController::class, 'getSaldoRealtime'])->name('keuangan.get-saldo');
            Route::get('/keuangan/get-coa-by-date', [KeuanganController::class, 'getCoaByDate'])->name('keuangan.get-coa');
            Route::resource('coa', CoaController::class);
            Route::resource('keuangan', KeuanganController::class);
            Route::get('/keuangan/pending-approvals', [KeuanganController::class, 'pendingApprovals'])->name('keuangan.pending');
            Route::get('/keuangan/approve/{id}', [KeuanganController::class, 'approveForm'])->name('keuangan.approve_form');
            Route::post('/keuangan/approve/{id}', [KeuanganController::class, 'processApprove'])->name('keuangan.process_approve');
        });
        Route::middleware('role:Super_Admin,Admin_Keuangan')->group(function () {
            Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan.laba_rugi');
            Route::get('/laporan/laba-rugi/excel', [ExportExcelController::class, 'exportLabaRugi'])->name('laporan.laba_rugi.excel');
            Route::get('/laporan/neraca', [LaporanController::class, 'neraca'])->name('laporan.neraca');
            Route::post('/coa/rollover', [CoaController::class, 'rollover'])->name('coa.rollover');
            Route::get('/jurnal/create', [KeuanganController::class, 'createJurnal'])->name('jurnal.create');
            Route::post('/jurnal/store', [KeuanganController::class, 'storeJurnal'])->name('jurnal.store');
            Route::get('/jurnal/{id}/edit', [KeuanganController::class, 'editJurnal'])->name('jurnal.edit');
            Route::put('/jurnal/{id}', [KeuanganController::class, 'updateJurnal'])->name('jurnal.update');
        });
        Route::middleware('role:Super_Admin,Admin_Marketing,Marketing')->group(function () {
            Route::prefix('leads')->name('leads.')->group(function () {
                Route::get('/', [LeadController::class, 'index'])->name('index');
                Route::get('/create', [LeadController::class, 'create'])->name('create');
                Route::post('/store', [LeadController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('edit');
                Route::put('/{id}', [LeadController::class, 'update'])->name('update');
                Route::delete('/{id}', [LeadController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/detail', [LeadController::class, 'detail'])->name('show');
                Route::get('/export', [LeadController::class, 'export'])->name('export');
                Route::post('/trigger-update-status', [LeadController::class, 'triggerUpdateStatus'])->name('trigger_update');
            });
            Route::prefix('followup')->name('followup.')->group(function () {
                Route::get('/', [FollowUpController::class, 'index'])->name('index');
                Route::get('/{id}', [LeadController::class, 'show'])->name('followup_execute');
                Route::post('/process/{id}', [FollowUpController::class, 'process'])->name('process');
                Route::get('/{id}/edit', [FollowUpController::class, 'edit'])->name('edit');
                Route::put('/{id}', [FollowUpController::class, 'update'])->name('update');
            });
            Route::get('/hot-prospek', [HotProspekController::class, 'index'])->name('hot_prospek.index');
            Route::post('/hot-prospek/transaksi', [HotProspekController::class, 'storeTransaksi'])->name('hot_prospek.store_transaksi');
        });
    });
});
