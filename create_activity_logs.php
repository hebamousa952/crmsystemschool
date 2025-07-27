<?php
// إنشاء جدول activity_logs يدوياً

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
    Capsule::schema()->create('activity_logs', function ($table) {
        $table->id();
        $table->string('event_type');
        $table->text('description');
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('causer_type')->nullable();
        $table->unsignedBigInteger('causer_id')->nullable();
        $table->string('category')->nullable();
        $table->string('severity')->default('medium');
        $table->json('properties')->nullable();
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->string('url')->nullable();
        $table->string('method')->nullable();
        $table->timestamps();
        
        // Indexes
        $table->index('event_type');
        $table->index('subject_type');
        $table->index('subject_id');
        $table->index('causer_type');
        $table->index('causer_id');
        $table->index('category');
        $table->index('created_at');
    });
    
    echo "✅ تم إنشاء جدول activity_logs بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الجدول: " . $e->getMessage() . "\n";
}
?>
