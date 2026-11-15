<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPortalController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Tanpa Login)
Route::get('/', [PublicPortalController::class, 'home'])->name('public.home');
Route::get('/profil', [PublicPortalController::class, 'profile'])->name('public.profile');
Route::get('/pembangunan', [PublicPortalController::class, 'development'])->name('public.development');
Route::get('/transparansi', [PublicPortalController::class, 'transparency'])->name('public.transparency');

// Dashboard Takmir (Dilindungi Autentikasi)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';