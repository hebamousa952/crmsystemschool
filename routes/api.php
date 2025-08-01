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

// Dynamic Dropdown API Routes
Route::prefix('dropdown')->group(function () {
    Route::get('/grades', [App\Http\Controllers\DynamicDropdownController::class, 'getGrades']);
    Route::get('/classrooms', [App\Http\Controllers\DynamicDropdownController::class, 'getClassrooms']);
    Route::get('/students', [App\Http\Controllers\DynamicDropdownController::class, 'getStudents']);
    Route::get('/parents', [App\Http\Controllers\DynamicDropdownController::class, 'getParents']);
    Route::get('/subjects', [App\Http\Controllers\DynamicDropdownController::class, 'getSubjects']);
    Route::get('/users', [App\Http\Controllers\DynamicDropdownController::class, 'getUsers']);
    Route::get('/academic-years', [App\Http\Controllers\DynamicDropdownController::class, 'getAcademicYears']);
    Route::get('/fee-types', [App\Http\Controllers\DynamicDropdownController::class, 'getFeeTypes']);
    Route::get('/payment-methods', [App\Http\Controllers\DynamicDropdownController::class, 'getPaymentMethods']);
    Route::get('/status-options', [App\Http\Controllers\DynamicDropdownController::class, 'getStatusOptions']);
    Route::get('/search', [App\Http\Controllers\DynamicDropdownController::class, 'search']);
});
