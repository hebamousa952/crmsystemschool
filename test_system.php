<?php

require_once 'vendor/autoload.php';

use App\Models\Student;
use App\Models\ParentGuardian;
use App\Models\EmergencyContact;
use App\Helpers\ModelValidator;

echo "=== اختبار النظام ===\n";

// Test 1: Check if models exist
echo "1. فحص الـ Models:\n";
echo "   - Student Model: " . (class_exists('App\Models\Student') ? '✅' : '❌') . "\n";
echo "   - ParentGuardian Model: " . (class_exists('App\Models\ParentGuardian') ? '✅' : '❌') . "\n";
echo "   - EmergencyContact Model: " . (class_exists('App\Models\EmergencyContact') ? '✅' : '❌') . "\n";
echo "   - ModelValidator Helper: " . (class_exists('App\Helpers\ModelValidator') ? '✅' : '❌') . "\n";

// Test 2: Check database connection
echo "\n2. فحص قاعدة البيانات:\n";
try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $pdo = DB::connection()->getPdo();
    echo "   - الاتصال بقاعدة البيانات: ✅\n";

    // Check tables
    $tables = ['students', 'parent_guardians', 'emergency_contacts', 'academic_info'];
    foreach ($tables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo "   - جدول {$table}: " . ($exists ? '✅' : '❌') . "\n";
    }
} catch (Exception $e) {
    echo "   - خطأ في قاعدة البيانات: ❌ " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
