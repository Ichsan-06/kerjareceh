<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::group(['middleware' => ['auth:sanctum']], function () {
Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('users', \App\Http\Controllers\UserController::class);
    Route::get('roles/permissions', [\App\Http\Controllers\RoleController::class, 'permissions']);
    Route::apiResource('roles', \App\Http\Controllers\RoleController::class);

    Route::get('job-types', [\App\Http\Controllers\JobController::class, 'types']);
    Route::get('posted-jobs', [\App\Http\Controllers\JobController::class, 'postedJobs']);
    Route::apiResource('jobs', \App\Http\Controllers\JobController::class);

    Route::get('wallet', [\App\Http\Controllers\WalletController::class, 'index']);
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    Route::post('jobs/take', [\App\Http\Controllers\JobSlotController::class, 'store']);
    Route::get('my-jobs', [\App\Http\Controllers\JobSlotController::class, 'index']);

    // Comments
    Route::get('jobs/{id}/comments', [\App\Http\Controllers\JobCommentController::class, 'index']);
    Route::post('comments', [\App\Http\Controllers\JobCommentController::class, 'store']);

    // Leaderboard
    Route::get('leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index']);

    Route::get('jobs/{id}/slots', [\App\Http\Controllers\JobSlotController::class, 'getJobSlots']);
    Route::get('jobs/{id}/participants', [\App\Http\Controllers\JobSlotController::class, 'publicList']);
    Route::get('my-jobs/{id}', [\App\Http\Controllers\JobSlotController::class, 'show']);
    Route::post('approvals', [\App\Http\Controllers\ApprovalController::class, 'store']);
    Route::post('disputes', [\App\Http\Controllers\DisputeController::class, 'store']);

    Route::apiResource('feedback', \App\Http\Controllers\FeedbackController::class)->only(['index', 'store', 'destroy']);

    Route::post('topup', [\App\Http\Controllers\TopUpController::class, 'store']);
    Route::get('topup', [\App\Http\Controllers\TopUpController::class, 'index']);
    Route::get('admin/topup', [\App\Http\Controllers\AdminTopUpController::class, 'index']);
    Route::post('admin/topup/{id}/approve', [\App\Http\Controllers\AdminTopUpController::class, 'approve']);
    Route::post('admin/topup/{id}/reject', [\App\Http\Controllers\AdminTopUpController::class, 'reject']);

    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

    Route::post('profile', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::post('profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword']);
});
// });
