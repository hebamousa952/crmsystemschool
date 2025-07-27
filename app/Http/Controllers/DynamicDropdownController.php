<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\Mother;
use App\Models\User;
use App\Helpers\SecurityHelper;

class DynamicDropdownController extends Controller
{
    /**
     * Get all grades for dropdown
     */
    public function getGrades(Request $request)
    {
        $level = $request->get('level'); // primary, preparatory

        $query = Grade::active()->orderBy('grade_number');

        if ($level) {
            $query->where('level', $level);
        }

        $grades = $query->get(['id', 'grade_name', 'grade_code', 'level', 'grade_number']);

        return response()->json([
            'success' => true,
            'data' => $grades->map(function ($grade) {
                return [
                    'value' => $grade->id,
                    'label' => $grade->grade_name,
                    'code' => $grade->grade_code,
                    'level' => $grade->level,
                    'number' => $grade->grade_number
                ];
            })
        ]);
    }

    /**
     * Get classrooms based on selected grade
     */
    public function getClassrooms(Request $request)
    {
        $gradeId = $request->get('grade_id');

        if (!$gradeId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف المرحلة مطلوب'
            ], 400);
        }

        $classrooms = Classroom::active()
            ->where('grade_id', $gradeId)
            ->with('grade')
            ->orderBy('classroom_name')
            ->get(['id', 'classroom_name', 'full_name', 'capacity', 'current_students', 'grade_id']);

        return response()->json([
            'success' => true,
            'data' => $classrooms->map(function ($classroom) {
                return [
                    'value' => $classroom->id,
                    'label' => $classroom->full_name,
                    'name' => $classroom->classroom_name,
                    'capacity' => $classroom->capacity,
                    'current_students' => $classroom->current_students,
                    'available_seats' => $classroom->available_seats,
                    'is_full' => $classroom->is_full
                ];
            })
        ]);
    }

    /**
     * Get students based on filters
     */
    public function getStudents(Request $request)
    {
        if (!SecurityHelper::canAccessStudentData(null)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول'
            ], 403);
        }

        $gradeId = $request->get('grade_id');
        $classroomId = $request->get('classroom_id');
        $status = $request->get('status', 'active');
        $search = $request->get('search');

        $query = Student::where('status', $status);

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_ar', 'LIKE', "%{$search}%")
                  ->orWhere('national_id', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name_ar')
            ->limit(100) // تحديد العدد لتحسين الأداء
            ->get(['id', 'full_name_ar', 'national_id', 'grade_id', 'classroom_id']);

        return response()->json([
            'success' => true,
            'data' => $students->map(function ($student) {
                return [
                    'value' => $student->id,
                    'label' => $student->full_name_ar,
                    'national_id' => $student->national_id,
                    'grade_id' => $student->grade_id,
                    'classroom_id' => $student->classroom_id
                ];
            })
        ]);
    }

    /**
     * Get parents based on selected student
     */
    public function getParents(Request $request)
    {
        $studentId = $request->get('student_id');

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف الطالب مطلوب'
            ], 400);
        }

        // الحصول على ولي الأمر والأم
        $parent = ParentModel::where('student_id', $studentId)->first();
        $mother = Mother::where('student_id', $studentId)->first();

        $parents = [];

        if ($parent) {
            $parents[] = [
                'value' => 'parent_' . $parent->id,
                'label' => $parent->full_name . ' (والد)',
                'type' => 'parent',
                'phone' => $parent->formatted_phone,
                'relationship' => $parent->relationship_in_arabic
            ];
        }

        if ($mother) {
            $parents[] = [
                'value' => 'mother_' . $mother->id,
                'label' => $mother->full_name . ' (والدة)',
                'type' => 'mother',
                'phone' => $mother->formatted_phone,
                'relationship' => 'والدة'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $parents
        ]);
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
}
