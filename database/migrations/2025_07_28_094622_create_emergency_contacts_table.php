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
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();

            // ربط مع الطالب
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            // بيانات جهة الاتصال في الطوارئ
            $table->string('contact_name'); // اسم جهة الاتصال
            $table->string('relationship'); // صلة القرابة
            $table->string('phone'); // رقم الهاتف
            $table->text('address')->nullable(); // العنوان (اختياري)

            // Indexes for better performance
            $table->index('student_id');
            $table->index('phone');

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
        Schema::dropIfExists('emergency_contacts');
    }
};
