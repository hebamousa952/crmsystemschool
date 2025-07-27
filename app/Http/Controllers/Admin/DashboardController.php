<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Auto-login for development
        if (!auth()->check()) {
            $user = User::first();
            if (!$user) {
                // Create a test user if none exists
                $user = User::create([
                    'name' => 'مدير النظام',
                    'email' => 'admin@test.com',
                    'password' => bcrypt('password'),
                    'role' => 'admin'
                ]);
            }
            auth()->login($user);
        }

        // Get dashboard statistics
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
