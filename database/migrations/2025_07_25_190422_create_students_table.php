<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // القسم الأول: البيانات الشخصية
            $table->string('student_id')->unique()->nullable(); // رقم الطالب المولد تلقائياً
            $table->string('full_name'); // الاسم الكامل بالعربية
            $table->string('national_id', 14)->unique(); // الرقم القومي
            $table->string('password'); // كلمة المرور (آخر 6 أرقام من الرقم القومي)
            $table->date('birth_date'); // تاريخ الميلاد
            $table->string('birth_place'); // مكان الميلاد
            $table->string('nationality')->default('مصرية'); // الجنسية
            $table->enum('gender', ['ذكر', 'أنثى']); // النوع
            $table->string('religion'); // الديانة
            $table->text('address'); // العنوان
            $table->text('special_needs')->nullable(); // الاحتياجات الخاصة
            $table->text('notes')->nullable(); // ملاحظات
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred', 'suspended'])->default('active'); // حالة الطالب

            // Indexes for better performance
            $table->index('national_id');
            $table->index('student_id');
            $table->index('status');
            $table->index(['gender', 'status']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
