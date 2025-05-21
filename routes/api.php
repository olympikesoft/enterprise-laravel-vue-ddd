<?php

use App\Http\Controllers\Api\Admin\AdminCampaignController as AdminAdminCampaignController;
use App\Http\Controllers\Api\Admin\AdminUserController as AdminAdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Interfaces\Http\Controllers\Api\CampaignController;
use App\Interfaces\Http\Controllers\Api\DonationController;
use App\Interfaces\Http\Controllers\Api\Admin\AdminCampaignController;
use App\Interfaces\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Interfaces\Http\Controllers\Api\Admin\AdminUserController; // Will create this

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CampaignController as ApiCampaignController;
use App\Http\Controllers\Api\DonationController as ApiDonationController;
use App\Http\Resources\UserResource;


// --- Authentication Routes ---
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// --- Authenticated User Routes (require Sanctum authentication) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user'); // Using the method from AuthController

    // Campaign Management by Authenticated User
    Route::post('/campaigns', [ApiCampaignController::class, 'store'])->name('api.campaigns.store');
    Route::put('/campaigns/{campaign}', [ApiCampaignController::class, 'update'])->name('api.campaigns.update');
    Route::get('/my-campaigns', [ApiCampaignController::class, 'myCampaigns'])->name('api.campaigns.my');

    // Donation Management by Authenticated User
    Route::post('/donations', [ApiDonationController::class, 'store'])->name('api.donations.store');
    Route::get('/my-donations', [ApiDonationController::class, 'myDonations'])->name('api.donations.my');
});

// --- Publicly Accessible Campaign Routes ---
Route::get('/campaigns', [ApiCampaignController::class, 'index'])->name('api.campaigns.index');
Route::get('/campaigns/{campaign}', [ApiCampaignController::class, 'show'])->name('api.campaigns.show');


// --- Admin Routes (require Sanctum authentication AND 'admin' role/middleware) ---
Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard-stats', [AdminAdminUserController::class, 'stats'])->name('dashboard.stats');

    Route::get('/campaigns', [AdminAdminCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/pending', [AdminAdminCampaignController::class, 'pending'])->name('campaigns.pending');
    Route::get('/campaigns/{campaign}', [AdminAdminCampaignController::class, 'show'])->name('campaigns.show');
    Route::put('/campaigns/{campaign}', [AdminAdminCampaignController::class, 'update'])->name('campaigns.update');
    Route::post('/campaigns/{campaign}/approve', [AdminAdminCampaignController::class, 'approve'])->name('campaigns.approve');
    Route::post('/campaigns/{campaign}/reject', [AdminAdminCampaignController::class, 'reject'])->name('campaigns.reject');

    Route::apiResource('/users', AdminAdminUserController::class)->except(['create', 'edit']);
});
