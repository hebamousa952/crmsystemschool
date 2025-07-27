<?php
// تشغيل هذا الملف لإنشاء الجدول يدوياً

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
    // إنشاء الجدول
    Capsule::schema()->create('academic_info', function ($table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        
        // البيانات الأكاديمية - مطابقة للفورم
        $table->string('academic_year');           // العام الدراسي
        $table->string('grade_level');             // المرحلة الدراسية
        $table->string('grade');                   // الصف الدراسي
        $table->string('classroom');               // الفصل
        $table->string('enrollment_type');         // نوع القيد
        $table->date('enrollment_date');           // تاريخ الالتحاق
        $table->string('previous_school')->nullable(); // المدرسة السابقة
        $table->text('transfer_reason')->nullable();   // سبب التحويل
        $table->string('previous_level');          // مستوى الطالب السابق
        $table->string('second_language');         // اللغة الثانية
        $table->string('curriculum_type');         // نوع المنهج
        $table->string('has_failed')->default('no'); // هل سبق الرسوب
        $table->string('sibling_order');           // ترتيب بين الإخوة
        $table->string('attendance_type')->default('regular'); // منتظم أم مستمع
        
        $table->timestamps();
        
        // Indexes
        $table->index('student_id');
        $table->index('academic_year');
        $table->index('grade_level');
        $table->index('grade');
        $table->index('classroom');
    });
    
    echo "✅ تم إنشاء جدول academic_info بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الجدول: " . $e->getMessage() . "\n";
}
?>
