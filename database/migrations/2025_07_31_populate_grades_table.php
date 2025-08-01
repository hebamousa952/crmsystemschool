<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info("=== [POPULATE_GRADES_TABLE] MIGRATION STARTED ===");
            
            // التحقق من وجود الجدول
            if (!Schema::hasTable('grades')) {
                Log::error("Table 'grades' does not exist");
                throw new Exception("Table 'grades' not found");
            }
            
            Log::info("Grades table exists, proceeding with data population");
            
            // مسح البيانات الموجودة مع التعامل مع Foreign Keys
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('grades')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::info("Cleared existing grades data");

            // بيانات المراحل الدراسية المطلوبة
            $grades = [
                // المرحلة الابتدائية
                [
                    'grade_name' => 'الصف الأول الابتدائي',
                    'grade_code' => 'grade_1',
                    'level' => 'primary',
                    'grade_number' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الثاني الابتدائي',
                    'grade_code' => 'grade_2',
                    'level' => 'primary',
                    'grade_number' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الثالث الابتدائي',
                    'grade_code' => 'grade_3',
                    'level' => 'primary',
                    'grade_number' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الرابع الابتدائي',
                    'grade_code' => 'grade_4',
                    'level' => 'primary',
                    'grade_number' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الخامس الابتدائي',
                    'grade_code' => 'grade_5',
                    'level' => 'primary',
                    'grade_number' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف السادس الابتدائي',
                    'grade_code' => 'grade_6',
                    'level' => 'primary',
                    'grade_number' => 6,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                
                // المرحلة الإعدادية
                [
                    'grade_name' => 'الصف الأول الإعدادي',
                    'grade_code' => 'prep_1',
                    'level' => 'preparatory',
                    'grade_number' => 7,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الثاني الإعدادي',
                    'grade_code' => 'prep_2',
                    'level' => 'preparatory',
                    'grade_number' => 8,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'grade_name' => 'الصف الثالث الإعدادي',
                    'grade_code' => 'prep_3',
                    'level' => 'preparatory',
                    'grade_number' => 9,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            // إدراج البيانات
            DB::table('grades')->insert($grades);
            
            $insertedCount = count($grades);
            Log::info("Successfully inserted {$insertedCount} grades");

            // التحقق من البيانات المدرجة
            $verificationCount = DB::table('grades')->count();
            if ($verificationCount !== $insertedCount) {
                Log::error("Data verification failed. Expected: {$insertedCount}, Found: {$verificationCount}");
                throw new Exception("Data insertion verification failed");
            }

            Log::info("Data verification successful");
            Log::info("=== [POPULATE_GRADES_TABLE] MIGRATION COMPLETED SUCCESSFULLY ===");

        } catch (Exception $e) {
            Log::error("=== [POPULATE_GRADES_TABLE] MIGRATION FAILED ===");
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
            Log::info("=== [POPULATE_GRADES_TABLE] ROLLBACK STARTED ===");
            
            // مسح جميع البيانات المدرجة
            DB::table('grades')->truncate();
            
            Log::info("Successfully cleared grades table");
            Log::info("=== [POPULATE_GRADES_TABLE] ROLLBACK COMPLETED ===");
            
        } catch (Exception $e) {
            Log::error("=== [POPULATE_GRADES_TABLE] ROLLBACK FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            throw $e;
        }
    }
};
