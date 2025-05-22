<?php

use App\Interfaces\Http\Controllers\Api\Admin\AdminCampaignController;
use App\Interfaces\Http\Controllers\Api\Admin\AdminUserController;
use App\Interfaces\Http\Controllers\Api\Auth\AuthController;
use App\Interfaces\Http\Controllers\Api\CampaignController;
use App\Interfaces\Http\Controllers\Api\DonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// --- Authentication Routes ---
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// --- Authenticated User Routes (require Sanctum authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user'); // Using the method from AuthController

    // Campaign Management by Authenticated User
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('api.campaigns.store');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('api.campaigns.update');
    Route::get('/my-campaigns', [CampaignController::class, 'myCampaigns'])->name('api.campaigns.my');

    // Donation Management by Authenticated User
    Route::post('/donations', [DonationController::class, 'store'])->name('api.donations.store');
    Route::get('/my-donations', [DonationController::class, 'myDonations'])->name('api.donations.my');
});

Route::get('/campaigns', [CampaignController::class, 'index'])->name('api.campaigns.index');
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('api.campaigns.show');


Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard-stats', [AdminUserController::class, 'stats'])->name('dashboard.stats');

    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/pending', [AdminCampaignController::class, 'pending'])->name('campaigns.pending');
    Route::get('/campaigns/{campaign}', [AdminCampaignController::class, 'show'])->name('campaigns.show');
    Route::put('/campaigns/{campaign}', [AdminCampaignController::class, 'update'])->name('campaigns.update');
    Route::post('/campaigns/{campaign}/approve', [AdminCampaignController::class, 'approve'])->name('campaigns.approve');
    Route::post('/campaigns/{campaign}/reject', [AdminCampaignController::class, 'reject'])->name('campaigns.reject');

    Route::apiResource('/users', AdminUserController::class)->except(['create', 'edit']);
});
