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
        Schema::create('legal_guardians', function (Blueprint $table) {
            $table->id();
            
            // ربط مع ولي الأمر
            $table->unsignedBigInteger('parent_guardian_id');
            $table->foreign('parent_guardian_id')->references('id')->on('parent_guardians')->onDelete('cascade');
            
            // بيانات الوصي القانوني
            $table->string('full_name'); // الاسم الكامل
            $table->string('national_id', 14)->unique(); // الرقم القومي
            $table->string('relationship'); // صلة القرابة
            $table->string('phone'); // رقم الهاتف
            $table->text('address'); // العنوان
            $table->string('legal_document_number')->nullable(); // رقم الوثيقة القانونية
            $table->text('legal_document_details')->nullable(); // تفاصيل الوثيقة القانونية
            
            // Indexes
            $table->index('parent_guardian_id');
            $table->index('national_id');
            
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
        Schema::dropIfExists('legal_guardians');
    }
};
