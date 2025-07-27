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
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Students Routes
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
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
