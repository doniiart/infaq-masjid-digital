<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\ReconciliationController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Tanpa Login)
Route::get('/', [PublicPortalController::class, 'home'])->name('public.home');
Route::get('/profil', [PublicPortalController::class, 'profile'])->name('public.profile');
Route::get('/pembangunan', [PublicPortalController::class, 'development'])->name('public.development');
Route::get('/transparansi', [PublicPortalController::class, 'transparency'])->name('public.transparency');

// Panel Administrasi Takmir (Terproteksi Auth + RBAC)
Route::middleware(['auth', 'role:super_admin,bendahara,auditor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Transaksi
    Route::get('/admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/admin/transactions/export/csv', [TransactionController::class, 'exportCsv'])->name('admin.transactions.export.csv');
    Route::get('/admin/transactions/export/print', [TransactionController::class, 'exportPrint'])->name('admin.transactions.export.print');
    Route::get('/admin/transactions/{transaction}', [TransactionController::class, 'show'])->name('admin.transactions.show');

    // Kategori & Pengeluaran (Read)
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/admin/expenses', [ExpenseController::class, 'index'])->name('admin.expenses.index');

    // Laporan Keuangan
    Route::get('/admin/reports', [FinancialReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/export/csv', [FinancialReportController::class, 'exportCsv'])->name('admin.reports.export.csv');
    Route::get('/admin/reports/export/print', [FinancialReportController::class, 'printView'])->name('admin.reports.export.print');

    // Rekonsiliasi
    Route::get('/admin/reconciliations', [ReconciliationController::class, 'index'])->name('admin.reconciliations.index');
    Route::get('/admin/reconciliations/{reconciliation}', [ReconciliationController::class, 'show'])->name('admin.reconciliations.show');

    // Audit Log
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    // Aksi Mutasi (Khusus Super Admin & Bendahara; Auditor hanya Read-Only)
    Route::middleware('role:super_admin,bendahara')->group(function () {
        Route::patch('/admin/transactions/{transaction}/review', [TransactionController::class, 'updateReview'])->name('admin.transactions.review');
        Route::post('/admin/transactions/{transaction}/allocate', [TransactionController::class, 'allocate'])->name('admin.transactions.allocate');

        Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');

        Route::post('/admin/expenses', [ExpenseController::class, 'store'])->name('admin.expenses.store');
        Route::put('/admin/expenses/{expense}', [ExpenseController::class, 'update'])->name('admin.expenses.update');

        Route::post('/admin/reconciliations/import', [ReconciliationController::class, 'importManual'])->name('admin.reconciliations.import');
        Route::patch('/admin/reconciliations/items/{item}/resolve', [ReconciliationController::class, 'resolveItem'])->name('admin.reconciliations.items.resolve');
    });

    // Hapus Pengeluaran (Dibatasi Khusus Super Admin)
    Route::delete('/admin/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('role:super_admin')
        ->name('admin.expenses.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';