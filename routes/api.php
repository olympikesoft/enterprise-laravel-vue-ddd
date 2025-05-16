<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\Admin\AdminCampaignController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Auth\AuthController; // Assuming you have an AuthController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes (Example)
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// Route::get('/user', function (Request $request) {
//     return new \App\Http\Resources\UserResource($request->user());
// })->middleware('auth:sanctum');


// Public Campaign Routes
Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaigns/{id}', [CampaignController::class, 'show']);

// Donation Route
Route::post('/donations', [DonationController::class, 'store']);


// Authenticated User Campaign Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    // Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
    // Route::get('/my-campaigns', [CampaignController::class, 'myCampaigns']); // Example
});


// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->name('admin.')->group(function () {
    // Admin Campaign Management
    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/{id}', [AdminCampaignController::class, 'show'])->name('campaigns.show');
    Route::post('/campaigns/{id}/approve', [AdminCampaignController::class, 'approve'])->name('campaigns.approve');
    Route::post('/campaigns/{id}/reject', [AdminCampaignController::class, 'reject'])->name('campaigns.reject');
    Route::put('/campaigns/{id}', [AdminCampaignController::class, 'update'])->name('campaigns.update'); // Admin update

    // Admin User Management
    //Route::apiResource('/users', AdminUserController::class); // Uses index, show, store, update, destroy
});