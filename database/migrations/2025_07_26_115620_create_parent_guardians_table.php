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
        Schema::create('parent_guardians', function (Blueprint $table) {
            $table->id();
            
            // ربط مع الطالب
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // نوع ولي الأمر (أب، أم، وصي قانوني)
            $table->enum('guardian_type', ['father', 'mother', 'legal_guardian'])->default('father');
            
            // القسم الثالث: بيانات ولي الأمر
            $table->string('full_name'); // الاسم الكامل
            $table->string('relationship'); // صلة القرابة
            $table->string('national_id', 14)->unique(); // الرقم القومي
            $table->string('job_title')->nullable(); // الوظيفة
            $table->string('workplace')->nullable(); // جهة العمل
            $table->string('education_level')->nullable(); // المؤهل الدراسي
            $table->string('mobile_phone'); // رقم الهاتف المحمول
            $table->string('alternative_phone')->nullable(); // رقم هاتف آخر (احتياطي)
            $table->string('email')->nullable(); // البريد الإلكتروني
            $table->text('address'); // عنوان السكن
            $table->string('marital_status')->nullable(); // الحالة الاجتماعية
            $table->boolean('has_legal_guardian')->default(false); // هل يوجد وصي قانوني؟
            $table->json('social_media_accounts')->nullable(); // حسابات التواصل الاجتماعي (اختياري)
            
            // Indexes for better performance
            $table->index('student_id');
            $table->index('national_id');
            $table->index('guardian_type');
            $table->index(['student_id', 'guardian_type']);
            
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
        Schema::dropIfExists('parent_guardians');
    }
};
