<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info("=== [CREATE_CLASSROOMS_TABLE] MIGRATION STARTED ===");
            
            if (Schema::hasTable('classrooms')) {
                Log::warning("Table 'classrooms' already exists, skipping creation");
                return;
            }

            Schema::create('classrooms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grade_id'); // مرجع للمرحلة الدراسية
                $table->string('classroom_name'); // اسم الفصل (1A, 1B, 1A PRE)
                $table->string('full_name'); // الاسم الكامل (الصف الأول الابتدائي - فصل 1A)
                $table->integer('capacity')->default(30); // سعة الفصل
                $table->integer('current_students')->default(0); // عدد الطلاب الحالي
                $table->boolean('is_active')->default(true); // نشط أم لا
                $table->timestamps();

                // Foreign key constraint
                $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');

                // Indexes for better performance
                $table->index('grade_id');
                $table->index('is_active');
                $table->index(['grade_id', 'classroom_name']);
                $table->unique(['grade_id', 'classroom_name']); // منع تكرار اسم الفصل في نفس المرحلة
            });

            Log::info("Successfully created 'classrooms' table");
            Log::info("=== [CREATE_CLASSROOMS_TABLE] MIGRATION COMPLETED SUCCESSFULLY ===");

        } catch (Exception $e) {
            Log::error("=== [CREATE_CLASSROOMS_TABLE] MIGRATION FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info("=== [CREATE_CLASSROOMS_TABLE] ROLLBACK STARTED ===");
            
            Schema::dropIfExists('classrooms');
            
            Log::info("Successfully dropped 'classrooms' table");
            Log::info("=== [CREATE_CLASSROOMS_TABLE] ROLLBACK COMPLETED ===");
            
        } catch (Exception $e) {
            Log::error("=== [CREATE_CLASSROOMS_TABLE] ROLLBACK FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            throw $e;
        }
    }
};
