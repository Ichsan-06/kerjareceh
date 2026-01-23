<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::group(['middleware' => ['auth:sanctum']], function () {
Route::get('/user', function (Request $request) {
    return $request->user();
});
Route::apiResource('users', \App\Http\Controllers\UserController::class);
// });
