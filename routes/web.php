<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});





// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard.page');

    // Students Routes
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
    
    // Fees Management Routes
    Route::prefix('fees')->name('fees.')->group(function () {
        // Fee Settings Routes
        Route::resource('settings', \App\Http\Controllers\FeeSettingController::class);
        Route::post('settings/{id}/duplicate', [\App\Http\Controllers\FeeSettingController::class, 'duplicate'])->name('settings.duplicate');
        
        // Student Fee Records Routes
        Route::resource('records', \App\Http\Controllers\StudentFeeRecordController::class)->names('records');
        Route::post('records/{id}/apply-settings', [\App\Http\Controllers\StudentFeeRecordController::class, 'applyFeeSettings'])->name('records.apply-settings');
        Route::post('records/get-settings-for-student', [\App\Http\Controllers\StudentFeeRecordController::class, 'getFeeSettingsForStudent'])->name('records.get-settings-for-student');
    });
    
    // Uniform Management Routes
    Route::prefix('uniforms')->name('uniforms.')->group(function () {
        Route::resource('items', \App\Http\Controllers\UniformItemController::class)->names('items');
        Route::post('items/{id}/toggle-active', [\App\Http\Controllers\UniformItemController::class, 'toggleActive'])->name('items.toggle-active');
        Route::post('items/{id}/update-price', [\App\Http\Controllers\UniformItemController::class, 'updatePrice'])->name('items.update-price');
        Route::post('items/get-for-grade-level', [\App\Http\Controllers\StudentFeeRecordController::class, 'getUniformItemsForGradeLevel'])->name('items.get-for-grade-level');
    });
});

// API Routes for Dynamic Dropdowns
Route::prefix('api/dropdown')->group(function () {
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

// Auto-login for development
Route::get('/auto-login', function () {
    if (!auth()->check()) {
        $user = \App\Models\User::first();
        if (!$user) {
            // Create a test user if none exists
            $user = \App\Models\User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]);
        }
        auth()->login($user);
    }
    return redirect('/admin');
})->name('auto.login');

// Logout Route
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Live Reload Route (Development Only)
Route::get('/live-reload-check', function () {
    if (config('app.env') !== 'local') {
        abort(404);
    }

    $files_to_watch = [
        resource_path('views/layouts/admin.blade.php'),
        resource_path('views/admin/dashboard.blade.php'),
        public_path('css/responsive-admin.css'),
        app_path('Http/Controllers/Admin/DashboardController.php'),
        base_path('routes/web.php')
    ];

    $latest_modified = 0;

    foreach ($files_to_watch as $file) {
        if (file_exists($file)) {
            $modified = filemtime($file);
            if ($modified > $latest_modified) {
                $latest_modified = $modified;
            }
        }
    }

    return response()->json([
        'modified' => $latest_modified,
        'timestamp' => date('Y-m-d H:i:s', $latest_modified),
        'status' => 'monitoring'
    ]);
})->name('live.reload.check');
