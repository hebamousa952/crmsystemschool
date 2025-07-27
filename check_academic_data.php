<?php
// فحص البيانات الأكاديمية في قاعدة البيانات

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'crmschool',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "🔍 فحص البيانات في قاعدة البيانات:\n\n";
    
    // فحص جدول الطلاب
    echo "👥 جدول الطلاب (students):\n";
    $students = Capsule::table('students')->get();
    echo "عدد الطلاب: " . count($students) . "\n";
    
    if (count($students) > 0) {
        foreach ($students as $student) {
            echo "- ID: {$student->id}, الاسم: {$student->full_name}, رقم الطالب: {$student->student_id}\n";
        }
    } else {
        echo "❌ لا توجد بيانات طلاب!\n";
    }
    
    echo "\n📚 جدول البيانات الأكاديمية (academic_info):\n";
    
    // فحص وجود الجدول
    $tableExists = Capsule::schema()->hasTable('academic_info');
    if (!$tableExists) {
        echo "❌ جدول academic_info غير موجود!\n";
        return;
    }
    
    $academicData = Capsule::table('academic_info')->get();
    echo "عدد السجلات الأكاديمية: " . count($academicData) . "\n";
    
    if (count($academicData) > 0) {
        foreach ($academicData as $academic) {
            echo "- ID: {$academic->id}, Student ID: {$academic->student_id}, الصف: {$academic->grade}, الفصل: {$academic->classroom}\n";
        }
    } else {
        echo "❌ لا توجد بيانات أكاديمية!\n";
    }
    
    echo "\n🔗 فحص الربط بين الجداول:\n";
    
    // فحص الربط
    $joinedData = Capsule::table('students')
        ->leftJoin('academic_info', 'students.id', '=', 'academic_info.student_id')
        ->select('students.id', 'students.full_name', 'students.student_id', 
                'academic_info.grade', 'academic_info.classroom', 'academic_info.academic_year')
        ->get();
    
    if (count($joinedData) > 0) {
        foreach ($joinedData as $data) {
            $grade = $data->grade ?? 'غير محدد';
            $classroom = $data->classroom ?? 'غير محدد';
            $year = $data->academic_year ?? 'غير محدد';
            echo "- {$data->full_name} (ID: {$data->id}) - الصف: {$grade}, الفصل: {$classroom}, العام: {$year}\n";
        }
    }
    
    echo "\n📋 تفاصيل هيكل جدول academic_info:\n";
    $columns = Capsule::select("DESCRIBE academic_info");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type}\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 السطر: " . $e->getLine() . "\n";
}
?>
