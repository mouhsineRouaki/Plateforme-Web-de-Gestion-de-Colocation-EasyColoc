<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ColocationController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/colocations/create', [ColocationController::class, 'create'])->name('colocations.create');
    Route::post('/colocations', [ColocationController::class, 'store'])->name('colocations.store');
    Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/join', [InvitationController::class, 'joinFromLink'])->name('invitations.join.link');
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/refuse', [InvitationController::class, 'refuse'])->name('invitations.refuse');
});
// route role admin 

Route::middleware(['auth' , 'verified' , 'globalAdmin'])->group(function(){
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::patch('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
});

require __DIR__ . '/auth.php';
