<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Students API Routes
Route::prefix('students')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\StudentController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\StudentController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\StudentController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\StudentController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\StudentController::class, 'destroy']);
});
