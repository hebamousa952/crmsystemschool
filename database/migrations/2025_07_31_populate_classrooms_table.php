<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Grade;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info("=== [POPULATE_CLASSROOMS_TABLE] MIGRATION STARTED ===");
            
            // التحقق من وجود الجداول المطلوبة
            if (!Schema::hasTable('classrooms')) {
                Log::error("Table 'classrooms' does not exist");
                throw new Exception("Table 'classrooms' not found");
            }
            
            if (!Schema::hasTable('grades')) {
                Log::error("Table 'grades' does not exist");
                throw new Exception("Table 'grades' not found");
            }
            
            Log::info("Required tables exist, proceeding with classroom data population");
            
            // مسح البيانات الموجودة
            DB::table('classrooms')->truncate();
            Log::info("Cleared existing classroom data");

            // الحصول على IDs المراحل الدراسية
            $grades = DB::table('grades')->select('id', 'grade_code', 'grade_name')->get()->keyBy('grade_code');
            
            if ($grades->isEmpty()) {
                Log::error("No grades found in database");
                throw new Exception("Grades table is empty. Please run grades migration first.");
            }
            
            Log::info("Found " . $grades->count() . " grades in database");

            // بيانات الفصول المطلوبة بناءً على المواصفات المحددة
            $classroomsData = [
                // الصف الأول الابتدائي: 1A, 1B, 1C, 1D
                'grade_1' => ['1A', '1B', '1C', '1D'],
                
                // الصف الثاني الابتدائي: 2A, 2B, 2C, 2D, 2E
                'grade_2' => ['2A', '2B', '2C', '2D', '2E'],
                
                // الصف الثالث الابتدائي: 3A, 3B, 3C, 3D
                'grade_3' => ['3A', '3B', '3C', '3D'],
                
                // الصف الرابع الابتدائي: 4A, 4B, 4C, 4D
                'grade_4' => ['4A', '4B', '4C', '4D'],
                
                // الصف الخامس الابتدائي: 5A, 5B, 5C, 5D
                'grade_5' => ['5A', '5B', '5C', '5D'],
                
                // الصف السادس الابتدائي: 6A, 6B
                'grade_6' => ['6A', '6B'],
                
                // الصف الأول الإعدادي: 1A PRE, 1B PRE
                'prep_1' => ['1A PRE', '1B PRE'],
                
                // الصف الثاني الإعدادي: 2A PRE, 2B PRE
                'prep_2' => ['2A PRE', '2B PRE'],
                
                // الصف الثالث الإعدادي: 3A PRE, 3B PRE
                'prep_3' => ['3A PRE', '3B PRE'],
            ];

            $classrooms = [];
            $totalClassrooms = 0;

            // إنشاء بيانات الفصول
            foreach ($classroomsData as $gradeCode => $classroomNames) {
                if (!isset($grades[$gradeCode])) {
                    Log::warning("Grade code '{$gradeCode}' not found in database, skipping its classrooms");
                    continue;
                }

                $grade = $grades[$gradeCode];
                Log::info("Processing classrooms for grade: {$grade->grade_name}");

                foreach ($classroomNames as $classroomName) {
                    $classrooms[] = [
                        'grade_id' => $grade->id,
                        'classroom_name' => $classroomName,
                        'full_name' => $grade->grade_name . ' - فصل ' . $classroomName,
                        'capacity' => 30, // السعة الافتراضية
                        'current_students' => 0, // لا يوجد طلاب حالياً
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalClassrooms++;
                }

                Log::info("Added " . count($classroomNames) . " classrooms for {$grade->grade_name}");
            }

            // إدراج البيانات
            if (!empty($classrooms)) {
                DB::table('classrooms')->insert($classrooms);
                Log::info("Successfully inserted {$totalClassrooms} classrooms");

                // التحقق من البيانات المدرجة
                $verificationCount = DB::table('classrooms')->count();
                if ($verificationCount !== $totalClassrooms) {
                    Log::error("Data verification failed. Expected: {$totalClassrooms}, Found: {$verificationCount}");
                    throw new Exception("Classroom data insertion verification failed");
                }

                Log::info("Classroom data verification successful");

                // طباعة ملخص الفصول المنشأة
                $summary = DB::table('classrooms')
                    ->join('grades', 'classrooms.grade_id', '=', 'grades.id')
                    ->select('grades.grade_name', DB::raw('COUNT(*) as classroom_count'))
                    ->groupBy('grades.grade_name', 'grades.id')
                    ->get();

                Log::info("Classroom creation summary:");
                foreach ($summary as $item) {
                    Log::info("- {$item->grade_name}: {$item->classroom_count} فصول");
                }

            } else {
                Log::warning("No classrooms to insert");
            }

            Log::info("=== [POPULATE_CLASSROOMS_TABLE] MIGRATION COMPLETED SUCCESSFULLY ===");

        } catch (Exception $e) {
            Log::error("=== [POPULATE_CLASSROOMS_TABLE] MIGRATION FAILED ===");
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
            Log::info("=== [POPULATE_CLASSROOMS_TABLE] ROLLBACK STARTED ===");
            
            // مسح جميع البيانات المدرجة
            DB::table('classrooms')->truncate();
            
            Log::info("Successfully cleared classrooms table");
            Log::info("=== [POPULATE_CLASSROOMS_TABLE] ROLLBACK COMPLETED ===");
            
        } catch (Exception $e) {
            Log::error("=== [POPULATE_CLASSROOMS_TABLE] ROLLBACK FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            throw $e;
        }
    }
};
