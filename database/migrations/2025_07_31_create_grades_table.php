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
            Log::info("=== [CREATE_GRADES_TABLE] MIGRATION STARTED ===");
            
            if (Schema::hasTable('grades')) {
                Log::warning("Table 'grades' already exists, skipping creation");
                return;
            }

            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->string('grade_name'); // اسم المرحلة (الصف الأول الابتدائي)
                $table->string('grade_code')->unique(); // رمز المرحلة (grade_1, prep_1)
                $table->enum('level', ['primary', 'preparatory']); // المرحلة (ابتدائي، إعدادي)
                $table->integer('grade_number'); // رقم الصف (1, 2, 3...)
                $table->boolean('is_active')->default(true); // نشط أم لا
                $table->timestamps();

                // Indexes for better performance
                $table->index('level');
                $table->index('grade_number');
                $table->index('is_active');
                $table->index(['level', 'grade_number']);
            });

            Log::info("Successfully created 'grades' table");
            Log::info("=== [CREATE_GRADES_TABLE] MIGRATION COMPLETED SUCCESSFULLY ===");

        } catch (Exception $e) {
            Log::error("=== [CREATE_GRADES_TABLE] MIGRATION FAILED ===");
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
            Log::info("=== [CREATE_GRADES_TABLE] ROLLBACK STARTED ===");
            
            Schema::dropIfExists('grades');
            
            Log::info("Successfully dropped 'grades' table");
            Log::info("=== [CREATE_GRADES_TABLE] ROLLBACK COMPLETED ===");
            
        } catch (Exception $e) {
            Log::error("=== [CREATE_GRADES_TABLE] ROLLBACK FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            throw $e;
        }
    }
};
