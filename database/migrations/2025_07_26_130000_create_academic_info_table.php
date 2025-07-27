<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_info', function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_info');
    }
};
