<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradesAndClassroomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // إنشاء المراحل الدراسية
        $grades = [
            // المرحلة الابتدائية
            ['grade_name' => 'الصف الأول الابتدائي', 'grade_code' => '1_PRIMARY', 'level' => 'primary', 'grade_number' => 1],
            ['grade_name' => 'الصف الثاني الابتدائي', 'grade_code' => '2_PRIMARY', 'level' => 'primary', 'grade_number' => 2],
            ['grade_name' => 'الصف الثالث الابتدائي', 'grade_code' => '3_PRIMARY', 'level' => 'primary', 'grade_number' => 3],
            ['grade_name' => 'الصف الرابع الابتدائي', 'grade_code' => '4_PRIMARY', 'level' => 'primary', 'grade_number' => 4],
            ['grade_name' => 'الصف الخامس الابتدائي', 'grade_code' => '5_PRIMARY', 'level' => 'primary', 'grade_number' => 5],
            ['grade_name' => 'الصف السادس الابتدائي', 'grade_code' => '6_PRIMARY', 'level' => 'primary', 'grade_number' => 6],

            // المرحلة الإعدادية
            ['grade_name' => 'الصف الأول الإعدادي', 'grade_code' => '1_PREPARATORY', 'level' => 'preparatory', 'grade_number' => 1],
            ['grade_name' => 'الصف الثاني الإعدادي', 'grade_code' => '2_PREPARATORY', 'level' => 'preparatory', 'grade_number' => 2],
            ['grade_name' => 'الصف الثالث الإعدادي', 'grade_code' => '3_PREPARATORY', 'level' => 'preparatory', 'grade_number' => 3],
        ];

        foreach ($grades as $grade) {
            \App\Models\Grade::create($grade);
        }

        // إنشاء الفصول لكل مرحلة
        $classroomsData = [
            1 => ['A', 'B', 'C', 'D'], // الصف الأول الابتدائي
            2 => ['A', 'B', 'C', 'D', 'E'], // الصف الثاني الابتدائي
            3 => ['A', 'B', 'C', 'D'], // الصف الثالث الابتدائي
            4 => ['A', 'B', 'C', 'D'], // الصف الرابع الابتدائي
            5 => ['A', 'B', 'C', 'D'], // الصف الخامس الابتدائي
            6 => ['A', 'B'], // الصف السادس الابتدائي
            7 => ['A PRE', 'B'], // الصف الأول الإعدادي
            8 => ['A PRE', 'B'], // الصف الثاني الإعدادي
            9 => ['A PRE', 'B'], // الصف الثالث الإعدادي
        ];

        foreach ($classroomsData as $gradeId => $classrooms) {
            foreach ($classrooms as $classroom) {
                $grade = \App\Models\Grade::find($gradeId);
                $fullName = $grade->grade_number . $classroom;

                \App\Models\Classroom::create([
                    'grade_id' => $gradeId,
                    'classroom_name' => $classroom,
                    'full_name' => $fullName,
                    'capacity' => 30,
                    'current_students' => 0,
                ]);
            }
        }
    }
}
