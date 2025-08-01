<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\TuitionFee;
use App\Helpers\SecurityHelper;

class DynamicTableController extends Controller
{
    /**
     * Get students data for dynamic table
     */
    public function getStudents(Request $request)
    {
        // التحقق من الصلاحيات
        if (!SecurityHelper::canAccessStudentData(null)) {
            return response()->json(['error' => 'غير مصرح لك بالوصول'], 403);
        }

        $query = Student::with(['grade', 'classroom', 'parent', 'mother']);

        // تطبيق الفلاتر
        $this->applyFilters($query, $request);

        // تطبيق الفرز
        $this->applySorting($query, $request);

        // تطبيق البحث
        $this->applySearch($query, $request);

        // الحصول على البيانات مع التصفح
        $perPage = $request->get('per_page', 25);
        $students = $query->paginate($perPage);

        // تنسيق البيانات للجدول
        $formattedData = $students->getCollection()->map(function ($student) {
            return $this->formatStudentForTable($student);
        });

        return response()->json([
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem()
            ],
            'columns' => $this->getStudentColumns()
        ]);
    }

    /**
     * Get payments data for dynamic table
     */
    public function getPayments(Request $request)
    {
        if (!SecurityHelper::canPerformAction('read', Payment::class)) {
            return response()->json(['error' => 'غير مصرح لك بالوصول'], 403);
        }

        $query = Payment::with(['student', 'payable', 'approvedBy']);

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        $this->applySearch($query, $request);

        $perPage = $request->get('per_page', 25);
        $payments = $query->paginate($perPage);

        $formattedData = $payments->getCollection()->map(function ($payment) {
            return $this->formatPaymentForTable($payment);
        });

        return response()->json([
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem()
            ],
            'columns' => $this->getPaymentColumns()
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        $filters = $request->get('filters', []);

        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            switch ($field) {
                case 'grade_id':
                    $query->where('grade_id', $value);
                    break;

                case 'classroom_id':
                    $query->where('classroom_id', $value);
                    break;

                case 'status':
                    $query->where('status', $value);
                    break;

                case 'academic_year':
                    $query->where('academic_year', $value);
                    break;

                case 'payment_status':
                    if ($value === 'paid') {
                        $query->where('status', 'confirmed');
                    } elseif ($value === 'pending') {
                        $query->where('status', 'pending');
                    }
                    break;

                case 'date_range':
                    if (isset($value['start']) && isset($value['end'])) {
                        $query->whereBetween('created_at', [$value['start'], $value['end']]);
                    }
                    break;
            }
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        // التأكد من أن العمود موجود وآمن
        $allowedSortFields = [
            'id', 'full_name_ar', 'national_id', 'birth_date',
            'enrollment_date', 'status', 'created_at', 'amount',
            'payment_date', 'grade_id', 'classroom_id'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Apply search to query
     */
    private function applySearch($query, Request $request)
    {
        $search = $request->get('search');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_ar', 'LIKE', "%{$search}%")
                  ->orWhere('national_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('grade', function ($gradeQuery) use ($search) {
                      $gradeQuery->where('grade_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('classroom', function ($classroomQuery) use ($search) {
                      $classroomQuery->where('full_name', 'LIKE', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Format student data for table display
     */
    private function formatStudentForTable($student)
    {
        return [
            'id' => $student->id,
            'national_id' => $student->national_id,
            'full_name_ar' => $student->full_name_ar,
            'age' => $student->age,
            'grade' => $student->grade->grade_name ?? '',
            'classroom' => $student->classroom->full_name ?? '',
            'status' => $student->status_in_arabic,
            'enrollment_date' => $student->enrollment_date->format('d/m/Y'),
            'total_fees' => $student->total_fees,
            'total_paid' => $student->total_paid,
            'remaining_amount' => $student->remaining_amount,
            'payment_percentage' => $student->payment_percentage,
            'parent_phone' => $student->parent->formatted_phone ?? '',
            'mother_phone' => $student->mother->formatted_phone ?? '',
            'actions' => [
                'view' => route('students.show', $student->id),
                'edit' => route('students.edit', $student->id),
                'payments' => route('students.payments', $student->id)
            ]
        ];
    }

    /**
     * Format payment data for table display
     */
    private function formatPaymentForTable($payment)
    {
        return [
            'id' => $payment->id,
            'student_name' => $payment->student->full_name_ar,
            'student_grade' => $payment->student->classroom->full_name ?? '',
            'amount' => $payment->formatted_amount,
            'payment_date' => $payment->formatted_payment_date,
            'method' => $payment->formatted_method,
            'status' => $payment->status_in_arabic,
            'fee_type' => $payment->fee_type,
            'approved_by' => $payment->approvedBy->name ?? '',
            'discount_amount' => $payment->formatted_discount_amount,
            'actions' => [
                'view' => route('payments.show', $payment->id),
                'edit' => route('payments.edit', $payment->id),
                'receipt' => route('payments.receipt', $payment->id)
            ]
        ];
    }

    /**
     * Get student table columns configuration
     */
    private function getStudentColumns()
    {
        return [
            [
                'key' => 'id',
                'title' => 'الرقم',
                'sortable' => true,
                'width' => 80,
                'type' => 'number'
            ],
            [
                'key' => 'national_id',
                'title' => 'الرقم القومي',
                'sortable' => true,
                'width' => 120,
                'type' => 'text'
            ],
            [
                'key' => 'full_name_ar',
                'title' => 'الاسم الكامل',
                'sortable' => true,
                'width' => 200,
                'type' => 'text'
            ],
            [
                'key' => 'age',
                'title' => 'العمر',
                'sortable' => false,
                'width' => 80,
                'type' => 'number'
            ],
            [
                'key' => 'grade',
                'title' => 'المرحلة',
                'sortable' => true,
                'width' => 150,
                'type' => 'text'
            ],
            [
                'key' => 'classroom',
                'title' => 'الفصل',
                'sortable' => true,
                'width' => 100,
                'type' => 'text'
            ],
            [
                'key' => 'status',
                'title' => 'الحالة',
                'sortable' => true,
                'width' => 100,
                'type' => 'badge'
            ],
            [
                'key' => 'total_fees',
                'title' => 'إجمالي المصروفات',
                'sortable' => false,
                'width' => 150,
                'type' => 'currency'
            ],
            [
                'key' => 'payment_percentage',
                'title' => 'نسبة السداد',
                'sortable' => false,
                'width' => 120,
                'type' => 'percentage'
            ],
            [
                'key' => 'parent_phone',
                'title' => 'هاتف ولي الأمر',
                'sortable' => false,
                'width' => 130,
                'type' => 'phone'
            ],
            [
                'key' => 'actions',
                'title' => 'الإجراءات',
                'sortable' => false,
                'width' => 150,
                'type' => 'actions'
            ]
        ];
    }

    /**
     * Get payment table columns configuration
     */
    private function getPaymentColumns()
    {
        return [
            [
                'key' => 'id',
                'title' => 'رقم الدفع',
                'sortable' => true,
                'width' => 100,
                'type' => 'number'
            ],
            [
                'key' => 'student_name',
                'title' => 'اسم الطالب',
                'sortable' => true,
                'width' => 200,
                'type' => 'text'
            ],
            [
                'key' => 'student_grade',
                'title' => 'الفصل',
                'sortable' => true,
                'width' => 100,
                'type' => 'text'
            ],
            [
                'key' => 'amount',
                'title' => 'المبلغ',
                'sortable' => true,
                'width' => 120,
                'type' => 'currency'
            ],
            [
                'key' => 'payment_date',
                'title' => 'تاريخ الدفع',
                'sortable' => true,
                'width' => 120,
                'type' => 'date'
            ],
            [
                'key' => 'method',
                'title' => 'طريقة الدفع',
                'sortable' => true,
                'width' => 120,
                'type' => 'text'
            ],
            [
                'key' => 'status',
                'title' => 'الحالة',
                'sortable' => true,
                'width' => 100,
                'type' => 'badge'
            ],
            [
                'key' => 'actions',
                'title' => 'الإجراءات',
                'sortable' => false,
                'width' => 150,
                'type' => 'actions'
            ]
        ];
    }

    /**
     * Get filter options for students
     */
    public function getStudentFilters()
    {
        return response()->json([
            'grades' => Grade::active()->get(['id', 'grade_name']),
            'classrooms' => Classroom::active()->with('grade')->get(['id', 'full_name', 'grade_id']),
            'statuses' => [
                ['value' => 'active', 'label' => 'نشط'],
                ['value' => 'inactive', 'label' => 'غير نشط'],
                ['value' => 'graduated', 'label' => 'متخرج'],
                ['value' => 'transferred', 'label' => 'منقول']
            ],
            'academic_years' => Student::distinct()->pluck('academic_year')->map(function($year) {
                return ['value' => $year, 'label' => $year];
            })
        ]);
    }

    /**
     * Export table data
     */
    public function exportStudents(Request $request)
    {
        // نفس منطق getStudents لكن بدون pagination
        $query = Student::with(['grade', 'classroom', 'parent', 'mother']);

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        $this->applySearch($query, $request);

        $students = $query->get();

        $data = $students->map(function ($student) {
            return $this->formatStudentForTable($student);
        });

        return response()->json([
            'data' => $data,
            'filename' => 'students_' . date('Y-m-d_H-i-s') . '.xlsx'
        ]);
    }

    /**
     * Get filter options for payments
     */
    public function getPaymentFilters()
    {
        return response()->json([
            'statuses' => [
                ['value' => 'pending', 'label' => 'في الانتظار'],
                ['value' => 'confirmed', 'label' => 'مؤكد'],
                ['value' => 'cancelled', 'label' => 'ملغي']
            ],
            'methods' => [
                ['value' => 'cash', 'label' => 'نقدي'],
                ['value' => 'bank_transfer', 'label' => 'تحويل بنكي'],
                ['value' => 'check', 'label' => 'شيك'],
                ['value' => 'card', 'label' => 'بطاقة ائتمان']
            ],
            'grades' => Grade::active()->get(['id', 'grade_name']),
            'date_ranges' => [
                ['value' => 'today', 'label' => 'اليوم'],
                ['value' => 'week', 'label' => 'هذا الأسبوع'],
                ['value' => 'month', 'label' => 'هذا الشهر'],
                ['value' => 'year', 'label' => 'هذا العام']
            ]
        ]);
    }

    /**
     * Export payments data
     */
    public function exportPayments(Request $request)
    {
        if (!SecurityHelper::canPerformAction('read', Payment::class)) {
            return response()->json(['error' => 'غير مصرح لك بالوصول'], 403);
        }

        $query = Payment::with(['student', 'payable', 'approvedBy']);

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        $this->applySearch($query, $request);

        $payments = $query->get();

        $data = $payments->map(function ($payment) {
            return $this->formatPaymentForTable($payment);
        });

        return response()->json([
            'data' => $data,
            'filename' => 'payments_' . date('Y-m-d_H-i-s') . '.xlsx'
        ]);
    }

    /**
     * Get table configuration for different types
     */
    public function getTableConfig(Request $request)
    {
        $type = $request->get('type', 'students');

        $configs = [
            'students' => [
                'title' => 'إدارة الطلاب',
                'api_endpoint' => '/api/tables/students',
                'permissions' => [
                    'create' => SecurityHelper::canPerformAction('create', Student::class),
                    'edit' => SecurityHelper::canPerformAction('update', Student::class),
                    'delete' => SecurityHelper::canPerformAction('delete', Student::class),
                    'export' => SecurityHelper::canPerformAction('read', Student::class)
                ],
                'bulk_actions' => [
                    'delete' => 'حذف المحدد',
                    'export' => 'تصدير المحدد',
                    'update_status' => 'تحديث الحالة'
                ]
            ],
            'payments' => [
                'title' => 'إدارة المدفوعات',
                'api_endpoint' => '/api/tables/payments',
                'permissions' => [
                    'create' => SecurityHelper::canPerformAction('create', Payment::class),
                    'edit' => SecurityHelper::canPerformAction('update', Payment::class),
                    'approve' => SecurityHelper::canPerformAction('approve_payments'),
                    'export' => SecurityHelper::canPerformAction('read', Payment::class)
                ],
                'bulk_actions' => [
                    'approve' => 'الموافقة على المحدد',
                    'export' => 'تصدير المحدد',
                    'generate_receipts' => 'إنشاء إيصالات'
                ]
            ]
        ];

        return response()->json($configs[$type] ?? $configs['students']);
    }

    /**
     * Bulk actions handler
     */
    public function bulkAction(Request $request)
    {
        $action = $request->get('action');
        $ids = $request->get('ids', []);
        $type = $request->get('type', 'students');

        if (empty($ids)) {
            return response()->json(['error' => 'لم يتم تحديد أي عناصر'], 400);
        }

        switch ($type) {
            case 'students':
                return $this->handleStudentBulkAction($action, $ids, $request);
            case 'payments':
                return $this->handlePaymentBulkAction($action, $ids, $request);
            default:
                return response()->json(['error' => 'نوع غير مدعوم'], 400);
        }
    }

    /**
     * Handle bulk actions for students
     */
    private function handleStudentBulkAction($action, $ids, $request)
    {
        if (!SecurityHelper::canAccessStudentData(null)) {
            return response()->json(['error' => 'غير مصرح لك بالوصول'], 403);
        }

        switch ($action) {
            case 'update_status':
                $status = $request->get('status');
                $updated = Student::whereIn('id', $ids)->update(['status' => $status]);
                return response()->json(['message' => "تم تحديث {$updated} طالب بنجاح"]);

            case 'delete':
                if (!SecurityHelper::canPerformAction('delete', Student::class)) {
                    return response()->json(['error' => 'غير مصرح لك بالحذف'], 403);
                }
                $deleted = Student::whereIn('id', $ids)->delete();
                return response()->json(['message' => "تم حذف {$deleted} طالب بنجاح"]);

            default:
                return response()->json(['error' => 'إجراء غير مدعوم'], 400);
        }
    }

    /**
     * Handle bulk actions for payments
     */
    private function handlePaymentBulkAction($action, $ids, $request)
    {
        if (!SecurityHelper::canPerformAction('read', Payment::class)) {
            return response()->json(['error' => 'غير مصرح لك بالوصول'], 403);
        }

        switch ($action) {
            case 'approve':
                if (!SecurityHelper::canPerformAction('approve_payments')) {
                    return response()->json(['error' => 'غير مصرح لك بالموافقة'], 403);
                }

                $updated = Payment::whereIn('id', $ids)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'confirmed',
                        'approved_by' => auth()->id()
                    ]);

                return response()->json(['message' => "تم الموافقة على {$updated} دفعة بنجاح"]);

            default:
                return response()->json(['error' => 'إجراء غير مدعوم'], 400);
        }
    }
}
