<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Helpers\SecurityHelper;
use Illuminate\Support\Facades\Log;

class DynamicDropdownController extends Controller
{
    /**
     * Get all grades for dropdown (database-driven system)
     */
    public function getGrades(Request $request)
    {
        try {
            Log::info("=== [GET_GRADES] STARTED ===");
            
            $level = $request->get('level'); // primary, preparatory
            Log::info("Level requested: " . ($level ?? 'all'));

            // التحقق من وجود نموذج Grade
            if (!class_exists(Grade::class)) {
                Log::error("Grade model does not exist");
                throw new \Exception("Grade model not found");
            }

            // بناء الاستعلام
            $query = Grade::where('is_active', true);
            
            if ($level) {
                $query->where('level', $level);
            }
            
            $grades = $query->orderBy('grade_number')->get();
            
            Log::info("Found " . $grades->count() . " grades in database");

            // تحويل البيانات للتنسيق المطلوب
            $result = $grades->map(function ($grade) {
                return [
                    'value' => $grade->grade_code, // grade_1, prep_1, etc.
                    'label' => $grade->grade_name, // الصف الأول الابتدائي
                    'level' => $grade->level, // primary, preparatory
                    'number' => $grade->grade_number, // 1, 2, 3, etc.
                    'id' => $grade->id, // Database ID
                    'classrooms_count' => $grade->classrooms()->count() // عدد الفصول
                ];
            })->toArray();

            Log::info("Returned " . count($result) . " grades for level: " . ($level ?? 'all'));
            Log::info("=== [GET_GRADES] COMPLETED SUCCESSFULLY ===");

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("=== [GET_GRADES] ERROR ===");
            Log::error("Error message: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المراحل الدراسية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get classrooms based on selected grade (database-driven system)
     */
    public function getClassrooms(Request $request)
    {
        try {
            Log::info("=== [GET_CLASSROOMS] STARTED ===");
            
            $grade = $request->get('grade'); // grade_1, prep_1, etc.
            $gradeId = $request->get('grade_id'); // Database ID
            Log::info("Grade requested: " . ($grade ?? 'none') . ", Grade ID: " . ($gradeId ?? 'none'));

            if (!$grade && !$gradeId) {
                Log::warning("Grade parameter not provided");
                return response()->json([
                    'success' => false,
                    'message' => 'معرف الصف مطلوب'
                ], 400);
            }

            // التحقق من وجود النماذج المطلوبة
            if (!class_exists(Grade::class) || !class_exists(Classroom::class)) {
                Log::error("Required models do not exist");
                throw new \Exception("Required models not found");
            }

            // العثور على المرحلة الدراسية
            $gradeModel = null;
            if ($gradeId) {
                $gradeModel = Grade::find($gradeId);
            } elseif ($grade) {
                $gradeModel = Grade::where('grade_code', $grade)->first();
            }

            if (!$gradeModel) {
                Log::warning("Grade not found: " . ($grade ?? $gradeId));
                return response()->json([
                    'success' => false,
                    'message' => 'المرحلة الدراسية غير موجودة'
                ], 404);
            }

            Log::info("Found grade: {$gradeModel->grade_name} (ID: {$gradeModel->id})");

            // الحصول على الفصول
            $classrooms = Classroom::where('grade_id', $gradeModel->id)
                ->where('is_active', true)
                ->orderBy('classroom_name')
                ->get();

            Log::info("Found " . $classrooms->count() . " classrooms for grade: {$gradeModel->grade_name}");

            // تحويل البيانات للتنسيق المطلوب
            $result = $classrooms->map(function ($classroom) {
                return [
                    'value' => $classroom->classroom_name, // 1A, 1B, 1A PRE, etc.
                    'label' => 'فصل ' . $classroom->classroom_name, // فصل 1A
                    'full_name' => $classroom->full_name, // الصف الأول الابتدائي - فصل 1A
                    'capacity' => $classroom->capacity,
                    'current_students' => $classroom->current_students,
                    'available_seats' => $classroom->capacity - $classroom->current_students,
                    'is_full' => $classroom->current_students >= $classroom->capacity,
                    'occupancy_percentage' => $classroom->capacity > 0 ? 
                        round(($classroom->current_students / $classroom->capacity) * 100, 1) : 0,
                    'id' => $classroom->id, // Database ID
                    'grade_id' => $classroom->grade_id
                ];
            })->toArray();

            Log::info("Returned " . count($result) . " classrooms for grade: {$gradeModel->grade_name}");
            Log::info("=== [GET_CLASSROOMS] COMPLETED SUCCESSFULLY ===");

            return response()->json([
                'success' => true,
                'data' => $result,
                'grade_info' => [
                    'id' => $gradeModel->id,
                    'name' => $gradeModel->grade_name,
                    'code' => $gradeModel->grade_code,
                    'level' => $gradeModel->level
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("=== [GET_CLASSROOMS] ERROR ===");
            Log::error("Error message: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الفصول الدراسية: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students based on filters (using academic_info)
     */
    public function getStudents(Request $request)
    {
        try {
            Log::info("=== [GET_STUDENTS] STARTED ===");
            
            if (!SecurityHelper::canAccessStudentData(null)) {
                Log::warning("Access denied for student data");
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول'
                ], 403);
            }

            $grade = $request->get('grade'); // grade_1, grade_2, etc.
            $classroom = $request->get('classroom'); // 1A, 1B, etc.
            $gradeLevel = $request->get('grade_level'); // primary, preparatory
            $status = $request->get('status', 'active');
            $search = $request->get('search');

            Log::info("Filter parameters:", [
                'grade' => $grade,
                'classroom' => $classroom,
                'grade_level' => $gradeLevel,
                'status' => $status,
                'search' => $search
            ]);

            // البحث في الطلاب عبر جدول academic_info
            $query = Student::where('status', $status);

            // إضافة فلاتر من academic_info إذا كانت متوفرة
            if ($grade || $classroom || $gradeLevel) {
                $query->whereHas('academicInfo', function ($q) use ($grade, $classroom, $gradeLevel) {
                    if ($gradeLevel) {
                        $q->where('grade_level', $gradeLevel);
                    }
                    if ($grade) {
                        $q->where('grade', $grade);
                    }
                    if ($classroom) {
                        $q->where('classroom', $classroom);
                    }
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('national_id', 'LIKE', "%{$search}%");
                });
            }

            $students = $query->with('academicInfo')
                ->orderBy('full_name')
                ->limit(100) // تحديد العدد لتحسين الأداء
                ->get(['id', 'full_name', 'national_id', 'status']);

            Log::info("Found " . $students->count() . " students");

            return response()->json([
                'success' => true,
                'data' => $students->map(function ($student) {
                    return [
                        'value' => $student->id,
                        'label' => $student->full_name,
                        'national_id' => $student->national_id,
                        'grade' => $student->academicInfo->grade ?? '',
                        'classroom' => $student->academicInfo->classroom ?? '',
                        'grade_level' => $student->academicInfo->grade_level ?? '',
                        'academic_year' => $student->academicInfo->academic_year ?? ''
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error("=== [GET_STUDENTS] ERROR ===");
            Log::error("Error message: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الطلاب: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parents based on selected student
     */
    public function getParents(Request $request)
    {
        try {
            Log::info("=== [GET_PARENTS] STARTED ===");

            $studentId = $request->get('student_id');
            Log::info("Student ID requested: " . $studentId);

            if (!$studentId) {
                Log::warning("Student ID not provided");
                return response()->json([
                    'success' => false,
                    'message' => 'معرف الطالب مطلوب'
                ], 400);
            }

            // التحقق من وجود الطالب
            $student = Student::find($studentId);
            if (!$student) {
                Log::error("Student not found with ID: " . $studentId);
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود'
                ], 404);
            }

            Log::info("Student found: " . $student->full_name_ar);

            // الحصول على الأوصياء باستخدام النموذج الموحد ParentGuardian
            $father = ParentGuardian::where('student_id', $studentId)
                ->where('guardian_type', 'father')
                ->first();

            $mother = ParentGuardian::where('student_id', $studentId)
                ->where('guardian_type', 'mother')
                ->first();

            $legalGuardian = ParentGuardian::where('student_id', $studentId)
                ->where('guardian_type', 'legal_guardian')
                ->first();

            $parents = [];

            // إضافة الأب إذا وجد
            if ($father) {
                Log::info("Father found: " . $father->full_name);
                $parents[] = [
                    'value' => 'father_' . $father->id,
                    'label' => $father->full_name . ' (والد)',
                    'type' => 'father',
                    'phone' => $father->mobile_phone ?? '',
                    'alternative_phone' => $father->alternative_phone ?? '',
                    'relationship' => $father->relationship ?? 'والد',
                    'guardian_type' => $father->guardian_type
                ];
            }

            // إضافة الأم إذا وجدت
            if ($mother) {
                Log::info("Mother found: " . $mother->full_name);
                $parents[] = [
                    'value' => 'mother_' . $mother->id,
                    'label' => $mother->full_name . ' (والدة)',
                    'type' => 'mother',
                    'phone' => $mother->mobile_phone ?? '',
                    'alternative_phone' => $mother->alternative_phone ?? '',
                    'relationship' => $mother->relationship ?? 'والدة',
                    'guardian_type' => $mother->guardian_type
                ];
            }

            // إضافة الوصي القانوني إذا وجد
            if ($legalGuardian) {
                Log::info("Legal guardian found: " . $legalGuardian->full_name);
                $parents[] = [
                    'value' => 'legal_guardian_' . $legalGuardian->id,
                    'label' => $legalGuardian->full_name . ' (وصي قانوني)',
                    'type' => 'legal_guardian',
                    'phone' => $legalGuardian->mobile_phone ?? '',
                    'alternative_phone' => $legalGuardian->alternative_phone ?? '',
                    'relationship' => $legalGuardian->relationship ?? 'وصي قانوني',
                    'guardian_type' => $legalGuardian->guardian_type
                ];
            }

            Log::info("Total parents found: " . count($parents));
            Log::info("=== [GET_PARENTS] COMPLETED SUCCESSFULLY ===");

            return response()->json([
                'success' => true,
                'data' => $parents,
                'student_info' => [
                    'id' => $student->id,
                    'name' => $student->full_name_ar
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("=== [GET_PARENTS] ERROR ===");
            Log::error("Error message: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الأوصياء: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects based on grade level
     */
    public function getSubjects(Request $request)
    {
        $gradeId = $request->get('grade_id');
        $level = $request->get('level');

        // قائمة المواد حسب المرحلة (يمكن نقلها لقاعدة البيانات لاحقاً)
        $subjects = [
            'primary' => [
                'اللغة العربية',
                'اللغة الإنجليزية',
                'الرياضيات',
                'العلوم',
                'الدراسات الاجتماعية',
                'التربية الدينية',
                'التربية الفنية',
                'التربية الرياضية',
                'التربية الموسيقية'
            ],
            'preparatory' => [
                'اللغة العربية',
                'اللغة الإنجليزية',
                'الرياضيات',
                'العلوم',
                'الدراسات الاجتماعية',
                'التربية الدينية',
                'التربية الفنية',
                'التربية الرياضية',
                'الحاسب الآلي',
                'اللغة الفرنسية'
            ]
        ];

        // تحديد المستوى إذا لم يتم تمريره
        if (!$level && $gradeId) {
            $grade = Grade::find($gradeId);
            $level = $grade ? $grade->level : 'primary';
        }

        $subjectList = $subjects[$level] ?? $subjects['primary'];

        return response()->json([
            'success' => true,
            'data' => collect($subjectList)->map(function ($subject, $index) {
                return [
                    'value' => $subject,
                    'label' => $subject,
                    'id' => $index + 1
                ];
            })->values()
        ]);
    }

    /**
     * Get users based on role
     */
    public function getUsers(Request $request)
    {
        $role = $request->get('role');

        $query = User::orderBy('name');

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->get(['id', 'name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name,
                    'email' => $user->email,
                    'role' => $user->formatted_role
                ];
            })
        ]);
    }

    /**
     * Get academic years
     */
    public function getAcademicYears(Request $request)
    {
        // الحصول على السنوات الدراسية من الطلاب الموجودين
        $years = Student::distinct()
            ->pluck('academic_year')
            ->filter()
            ->sort()
            ->values();

        // إضافة السنة الحالية والقادمة إذا لم تكن موجودة
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $currentAcademicYear = $currentYear . '/' . $nextYear;

        if (!$years->contains($currentAcademicYear)) {
            $years->push($currentAcademicYear);
        }

        return response()->json([
            'success' => true,
            'data' => $years->map(function ($year) {
                return [
                    'value' => $year,
                    'label' => $year
                ];
            })
        ]);
    }

    /**
     * Get fee types
     */
    public function getFeeTypes(Request $request)
    {
        $category = $request->get('category', 'all'); // tuition, other, all

        $feeTypes = [
            'tuition' => [
                ['value' => 'tuition_fee', 'label' => 'مصروفات دراسية', 'category' => 'tuition']
            ],
            'other' => [
                ['value' => 'books', 'label' => 'كتب', 'category' => 'other'],
                ['value' => 'uniform', 'label' => 'زي مدرسي', 'category' => 'other'],
                ['value' => 'transport', 'label' => 'مواصلات', 'category' => 'other'],
                ['value' => 'activities', 'label' => 'أنشطة', 'category' => 'other'],
                ['value' => 'meals', 'label' => 'وجبات', 'category' => 'other'],
                ['value' => 'trips', 'label' => 'رحلات', 'category' => 'other'],
                ['value' => 'medical', 'label' => 'رعاية طبية', 'category' => 'other'],
                ['value' => 'insurance', 'label' => 'تأمين', 'category' => 'other']
            ]
        ];

        $result = [];

        if ($category === 'all') {
            $result = array_merge($feeTypes['tuition'], $feeTypes['other']);
        } else {
            $result = $feeTypes[$category] ?? [];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(Request $request)
    {
        $methods = [
            ['value' => 'cash', 'label' => 'نقدي', 'icon' => 'fas fa-money-bill'],
            ['value' => 'bank_transfer', 'label' => 'تحويل بنكي', 'icon' => 'fas fa-university'],
            ['value' => 'check', 'label' => 'شيك', 'icon' => 'fas fa-file-invoice'],
            ['value' => 'card', 'label' => 'بطاقة ائتمان', 'icon' => 'fas fa-credit-card']
        ];

        return response()->json([
            'success' => true,
            'data' => $methods
        ]);
    }

    /**
     * Get status options for different entities
     */
    public function getStatusOptions(Request $request)
    {
        $entity = $request->get('entity', 'student'); // student, payment, fee

        $statuses = [
            'student' => [
                ['value' => 'active', 'label' => 'نشط', 'color' => 'success'],
                ['value' => 'inactive', 'label' => 'غير نشط', 'color' => 'secondary'],
                ['value' => 'graduated', 'label' => 'متخرج', 'color' => 'info'],
                ['value' => 'transferred', 'label' => 'منقول', 'color' => 'warning']
            ],
            'payment' => [
                ['value' => 'pending', 'label' => 'في الانتظار', 'color' => 'warning'],
                ['value' => 'confirmed', 'label' => 'مؤكد', 'color' => 'success'],
                ['value' => 'cancelled', 'label' => 'ملغي', 'color' => 'danger']
            ],
            'fee' => [
                ['value' => 'pending', 'label' => 'في الانتظار', 'color' => 'warning'],
                ['value' => 'partial', 'label' => 'مدفوع جزئياً', 'color' => 'info'],
                ['value' => 'paid', 'label' => 'مدفوع بالكامل', 'color' => 'success'],
                ['value' => 'overdue', 'label' => 'متأخر', 'color' => 'danger']
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses[$entity] ?? []
        ]);
    }

    /**
     * Search across multiple entities
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // students, parents, users, all
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'students') {
            $students = Student::where('full_name_ar', 'LIKE', "%{$query}%")
                ->orWhere('national_id', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'full_name_ar', 'national_id']);

            foreach ($students as $student) {
                $results[] = [
                    'type' => 'student',
                    'value' => $student->id,
                    'label' => $student->full_name_ar,
                    'subtitle' => $student->national_id,
                    'icon' => 'fas fa-user-graduate'
                ];
            }
        }

        if ($type === 'all' || $type === 'parents') {
            $parents = ParentModel::where('full_name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'full_name', 'phone']);

            foreach ($parents as $parent) {
                $results[] = [
                    'type' => 'parent',
                    'value' => $parent->id,
                    'label' => $parent->full_name,
                    'subtitle' => $parent->formatted_phone,
                    'icon' => 'fas fa-user'
                ];
            }
        }

        if ($type === 'all' || $type === 'users') {
            $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'name', 'email', 'role']);

            foreach ($users as $user) {
                $results[] = [
                    'type' => 'user',
                    'value' => $user->id,
                    'label' => $user->name,
                    'subtitle' => $user->email,
                    'icon' => 'fas fa-user-tie'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => collect($results)->take($limit)
        ]);
    }

    // ==================== وظائف الحماية والتحقق ====================

    /**
     * التحقق من وجود نموذج قبل الاستخدام
     */
    private function verifyModelExists($modelClass)
    {
        try {
            if (!class_exists($modelClass)) {
                Log::error("Model {$modelClass} does not exist");
                throw new \Exception("Model {$modelClass} not found");
            }
            Log::info("Model {$modelClass} verified successfully");
            return true;
        } catch (\Exception $e) {
            Log::error("Model verification failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * التحقق من وجود علاقة في النموذج
     */
    private function verifyRelationExists($model, $relationName)
    {
        try {
            if (!method_exists($model, $relationName)) {
                Log::warning("Relationship '{$relationName}' does not exist in " . get_class($model));
                return false;
            }
            Log::info("Relationship '{$relationName}' verified successfully in " . get_class($model));
            return true;
        } catch (\Exception $e) {
            Log::error("Relationship verification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * التحقق من صحة بيانات الطالب
     */
    private function validateStudentData($student)
    {
        try {
            Log::info("=== [VALIDATE_STUDENT_DATA] STARTED ===");

            if (!$student) {
                Log::error("Student object is null");
                return false;
            }

            if (!isset($student->id)) {
                Log::error("Student ID is missing");
                return false;
            }

            if (!isset($student->full_name_ar)) {
                Log::error("Student name is missing");
                return false;
            }

            Log::info("Student data validation passed for ID: " . $student->id);
            return true;
        } catch (\Exception $e) {
            Log::error("Student data validation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * التحقق من صحة بيانات الوصي
     */
    private function validateGuardianData($guardian)
    {
        try {
            Log::info("=== [VALIDATE_GUARDIAN_DATA] STARTED ===");

            if (!$guardian) {
                Log::error("Guardian object is null");
                return false;
            }

            if (!isset($guardian->id)) {
                Log::error("Guardian ID is missing");
                return false;
            }

            if (!isset($guardian->full_name)) {
                Log::error("Guardian name is missing");
                return false;
            }

            if (!isset($guardian->guardian_type)) {
                Log::error("Guardian type is missing");
                return false;
            }

            Log::info("Guardian data validation passed for ID: " . $guardian->id);
            return true;
        } catch (\Exception $e) {
            Log::error("Guardian data validation failed: " . $e->getMessage());
            return false;
        }
    }
}
