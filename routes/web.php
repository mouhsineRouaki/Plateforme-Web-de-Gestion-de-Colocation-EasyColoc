<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Member\MemberColocationController;
use App\Http\Controllers\Member\MemberDashboardController;
use App\Http\Controllers\Owner\OwnerColocationController;
use App\Http\Controllers\Owner\OwnerDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/refuse', [InvitationController::class, 'refuse'])->name('invitations.refuse');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/colocations/create', [ColocationController::class, 'create'])->name('colocations.create');
    Route::post('/colocations', [ColocationController::class, 'store'])->name('colocations.store');

    Route::post('/invitations/join', [InvitationController::class, 'joinFromLink'])->name('invitations.join.link');
    Route::post('/invitations/send', [InvitationController::class, 'send'])->name('invitations.send');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::post('/debts/{debt}/mark-paid', [DebtController::class, 'markAsPaid'])->name('debts.markPaid');
});

Route::prefix('owner')->name('owner.')->middleware(['auth', 'verified', 'owner'])->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/colocations', [OwnerColocationController::class, 'index'])->name('colocations.index');
    Route::get('/colocations/{colocation}', [OwnerColocationController::class, 'show'])->name('colocations.show');
    Route::post('/colocations/{colocation}/leave', [OwnerColocationController::class, 'leave'])->name('colocations.leave');
    Route::post('/colocations/{colocation}/members/{user}/remove', [OwnerColocationController::class, 'removeMember'])->name('members.remove');
    Route::post('/colocations/{colocation}/categories', [CategoryController::class, 'store'])->name('categories.store');
});

Route::prefix('member')->name('member.')->middleware(['auth', 'verified', 'member'])->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/colocations', [MemberColocationController::class, 'index'])->name('colocations.index');
    Route::get('/colocations/{colocation}', [MemberColocationController::class, 'show'])->name('colocations.show');
    Route::post('/colocations/{colocation}/leave', [MemberColocationController::class, 'leave'])->name('colocations.leave');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'globalAdmin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::patch('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
});

require __DIR__ . '/auth.php';
