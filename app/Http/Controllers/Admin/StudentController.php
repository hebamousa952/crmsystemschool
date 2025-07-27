<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AcademicInfo;
use App\Models\ParentGuardian;
use App\Models\LegalGuardian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $students = Student::latest()->paginate(20);
        // return view('admin.students.index', compact('students'));
        
        // Temporary: Return empty view for now
        return view('admin.students.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('=== STUDENT CREATION STARTED ===');
        Log::info('Request Data:', $request->all());

        // Validation rules - متطابقة مع الجدول الفعلي
        try {
            $validatedData = $request->validate([
            // البيانات الشخصية
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|size:14|unique:students,national_id',
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'nationality' => 'string|max:255',
            'gender' => 'required|in:ذكر,أنثى',
            'religion' => 'required|string|max:255',
            'address' => 'required|string',
            'special_needs' => 'nullable|string',
            'notes' => 'nullable|string',

            // البيانات الأكاديمية
            'academic_year' => 'required|string|max:20',
            'grade_level' => 'required|in:primary,preparatory',
            'grade' => 'required|string|max:50',
            'classroom' => 'required|string|max:50',
            'enrollment_type' => 'required|in:new,transfer,return',
            'enrollment_date' => 'required|date',
            'previous_school' => 'nullable|string|max:255',
            'transfer_reason' => 'nullable|string|max:1000',
            'previous_level' => 'required|in:excellent,good,needs_support',
            'second_language' => 'required|in:french,german,italian',
            'curriculum_type' => 'required|in:national,international,languages',
            'has_failed' => 'required|in:yes,no',
            'sibling_order' => 'required|in:first,second,third,fourth,fifth,other',
            'attendance_type' => 'required|in:regular,listener',

            // بيانات ولي الأمر
            'guardian_full_name' => 'required|string|max:255',
            'guardian_relationship' => 'required|string|max:255',
            'guardian_national_id' => 'required|string|size:14|unique:parent_guardians,national_id',
            'guardian_job_title' => 'nullable|string|max:255',
            'guardian_workplace' => 'nullable|string|max:255',
            'guardian_education_level' => 'nullable|string|max:255',
            'guardian_mobile_phone' => 'required|string|max:20',
            'guardian_alternative_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_address' => 'required|string',
            'guardian_marital_status' => 'nullable|string|max:255',
            'has_legal_guardian' => 'boolean',
            'guardian_social_media' => 'nullable|array',

            // بيانات الوصي القانوني (اختياري)
            'legal_guardian_full_name' => 'nullable|string|max:255',
            'legal_guardian_national_id' => 'nullable|string|size:14|unique:legal_guardians,national_id',
            'legal_guardian_relationship' => 'nullable|string|max:255',
            'legal_guardian_phone' => 'nullable|string|max:20',
            'legal_guardian_address' => 'nullable|string',
            'legal_guardian_document_number' => 'nullable|string|max:255',
            'legal_guardian_document_details' => 'nullable|string',
        ], [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'full_name.max' => 'الاسم الكامل لا يجب أن يتجاوز 255 حرف',
            'national_id.required' => 'الرقم القومي مطلوب',
            'national_id.size' => 'الرقم القومي يجب أن يكون 14 رقم بالضبط',
            'national_id.unique' => 'هذا الرقم القومي مسجل مسبقاً',
            'birth_date.required' => 'تاريخ الميلاد مطلوب',
            'birth_date.date' => 'تاريخ الميلاد غير صحيح',
            'birth_date.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'birth_place.required' => 'مكان الميلاد مطلوب',
            'nationality.required' => 'الجنسية مطلوبة',
            'gender.required' => 'النوع مطلوب',
            'gender.in' => 'النوع يجب أن يكون ذكر أو أنثى',
            'religion.required' => 'الديانة مطلوبة',
            'address.required' => 'العنوان مطلوب',

            // رسائل البيانات الأكاديمية
            'academic_year.required' => 'العام الدراسي مطلوب',
            'grade_level.required' => 'المرحلة الدراسية مطلوبة',
            'grade.required' => 'الصف الدراسي مطلوب',
            'classroom.required' => 'الفصل مطلوب',
            'enrollment_type.required' => 'نوع القيد مطلوب',
            'enrollment_date.required' => 'تاريخ الالتحاق مطلوب',
            'previous_level.required' => 'مستوى الطالب السابق مطلوب',
            'second_language.required' => 'اللغة الثانية مطلوبة',
            'curriculum_type.required' => 'نوع المنهج مطلوب',
            'has_failed.required' => 'حالة الرسوب مطلوبة',
            'sibling_order.required' => 'ترتيب الطالب مطلوب',
            'attendance_type.required' => 'نوع الحضور مطلوب',

            // رسائل بيانات ولي الأمر
            'guardian_full_name.required' => 'اسم ولي الأمر مطلوب',
            'guardian_relationship.required' => 'صلة القرابة مطلوبة',
            'guardian_national_id.required' => 'الرقم القومي لولي الأمر مطلوب',
            'guardian_national_id.size' => 'الرقم القومي لولي الأمر يجب أن يكون 14 رقم',
            'guardian_national_id.unique' => 'هذا الرقم القومي لولي الأمر مسجل مسبقاً',
            'guardian_mobile_phone.required' => 'رقم الهاتف المحمول مطلوب',
            'guardian_address.required' => 'عنوان ولي الأمر مطلوب',
            'guardian_email.email' => 'البريد الإلكتروني غير صحيح',
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        Log::info('Validation passed successfully');

        try {
            // إزالة Transaction مؤقتاً للتشخيص
            Log::info('Starting student creation without transaction');
                // فصل البيانات الشخصية عن الأكاديمية
            $studentData = [
                'full_name' => $validatedData['full_name'],
                'national_id' => $validatedData['national_id'],
                'birth_date' => $validatedData['birth_date'],
                'birth_place' => $validatedData['birth_place'],
                'nationality' => $validatedData['nationality'],
                'gender' => $validatedData['gender'],
                'religion' => $validatedData['religion'],
                'address' => $validatedData['address'],
                'special_needs' => $validatedData['special_needs'],
                'notes' => $validatedData['notes'],
            ];

            $academicData = [
                'academic_year' => $validatedData['academic_year'],
                'grade_level' => $validatedData['grade_level'],
                'grade' => $validatedData['grade'],
                'classroom' => $validatedData['classroom'],
                'enrollment_type' => $validatedData['enrollment_type'],
                'enrollment_date' => $validatedData['enrollment_date'],
                'previous_school' => $validatedData['previous_school'] ?? null,
                'transfer_reason' => $validatedData['transfer_reason'] ?? null,
                'previous_level' => $validatedData['previous_level'],
                'second_language' => $validatedData['second_language'],
                'curriculum_type' => $validatedData['curriculum_type'],
                'has_failed' => $validatedData['has_failed'],
                'sibling_order' => $validatedData['sibling_order'],
                'attendance_type' => $validatedData['attendance_type'],
            ];

            // بيانات ولي الأمر
            $guardianData = [
                'guardian_type' => 'father', // افتراضي
                'full_name' => $validatedData['guardian_full_name'],
                'relationship' => $validatedData['guardian_relationship'],
                'national_id' => $validatedData['guardian_national_id'],
                'job_title' => $validatedData['guardian_job_title'] ?? null,
                'workplace' => $validatedData['guardian_workplace'] ?? null,
                'education_level' => $validatedData['guardian_education_level'] ?? null,
                'mobile_phone' => $validatedData['guardian_mobile_phone'],
                'alternative_phone' => $validatedData['guardian_alternative_phone'] ?? null,
                'email' => $validatedData['guardian_email'] ?? null,
                'address' => $validatedData['guardian_address'],
                'marital_status' => $validatedData['guardian_marital_status'] ?? null,
                'has_legal_guardian' => $validatedData['has_legal_guardian'] ?? false,
                'social_media_accounts' => $validatedData['guardian_social_media'] ?? null,
            ];

            // بيانات الوصي القانوني (إن وجد)
            $legalGuardianData = null;
            if (!empty($validatedData['legal_guardian_full_name'])) {
                $legalGuardianData = [
                    'full_name' => $validatedData['legal_guardian_full_name'],
                    'national_id' => $validatedData['legal_guardian_national_id'],
                    'relationship' => $validatedData['legal_guardian_relationship'],
                    'phone' => $validatedData['legal_guardian_phone'],
                    'address' => $validatedData['legal_guardian_address'],
                    'legal_document_number' => $validatedData['legal_guardian_document_number'] ?? null,
                    'legal_document_details' => $validatedData['legal_guardian_document_details'] ?? null,
                ];
            }

            // Generate password from last 6 digits of national ID
            $password = substr($studentData['national_id'], -6);

            // Add additional fields
            $studentData['password'] = bcrypt($password);
            $studentData['student_id'] = $this->generateStudentId();
            $studentData['status'] = 'active';

            Log::info('Student Data prepared:', $studentData);

            // Create student
            $student = Student::create($studentData);

            Log::info('Student created successfully:', ['id' => $student->id, 'student_id' => $student->student_id]);

            // Create academic info
            $academicData['student_id'] = $student->id;

            Log::info('Academic Data:', $academicData);

            try {
                $academicInfo = AcademicInfo::create($academicData);
                Log::info('Academic Info Created:', ['id' => $academicInfo->id]);
            } catch (\Exception $academicError) {
                Log::error('Academic Info Creation Failed:', [
                    'error' => $academicError->getMessage(),
                    'data' => $academicData
                ]);
                throw $academicError;
            }

            // Create parent guardian
            $guardianData['student_id'] = $student->id;
            Log::info('Guardian Data:', $guardianData);

            try {
                $parentGuardian = ParentGuardian::create($guardianData);
                Log::info('Parent Guardian Created:', ['id' => $parentGuardian->id]);

                // Create legal guardian if provided
                if ($legalGuardianData) {
                    $legalGuardianData['parent_guardian_id'] = $parentGuardian->id;
                    Log::info('Legal Guardian Data:', $legalGuardianData);

                    try {
                        $legalGuardian = LegalGuardian::create($legalGuardianData);
                        Log::info('Legal Guardian Created:', ['id' => $legalGuardian->id]);
                    } catch (\Exception $legalError) {
                        Log::error('Legal Guardian Creation Failed:', [
                            'error' => $legalError->getMessage(),
                            'data' => $legalGuardianData
                        ]);
                        throw $legalError;
                    }
                }
            } catch (\Exception $guardianError) {
                Log::error('Parent Guardian Creation Failed:', [
                    'error' => $guardianError->getMessage(),
                    'data' => $guardianData
                ]);
                throw $guardianError;
            }

            // Clear form after success
            return redirect()
                ->route('admin.students.create')
                ->with('success', 'تم تسجيل الطالب بنجاح! رقم الطالب: ' . $student->student_id);

        } catch (\Exception $e) {
            Log::error('Student creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء حفظ بيانات الطالب. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $student = Student::findOrFail($id);
        // return view('admin.students.show', compact('student'));
        
        // Temporary: Return to dashboard
        return redirect()
            ->route('admin.dashboard');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $student = Student::findOrFail($id);
        // return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Update logic will be implemented later
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Delete logic will be implemented later
    }

    /**
     * Generate unique student ID
     */
    private function generateStudentId()
    {
        $year = date('Y');
        $lastStudent = Student::whereYear('created_at', $year)
                             ->orderBy('student_id', 'desc')
                             ->first();
        
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
