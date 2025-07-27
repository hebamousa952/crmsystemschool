<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicInfo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(): JsonResponse
    {
        try {
            $students = Student::with('academicInfo')->get();
            
            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => 'Students retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation
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
            ]);

            // فصل البيانات
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
                'previous_school' => $validatedData['previous_school'],
                'transfer_reason' => $validatedData['transfer_reason'],
                'previous_level' => $validatedData['previous_level'],
                'second_language' => $validatedData['second_language'],
                'curriculum_type' => $validatedData['curriculum_type'],
                'has_failed' => $validatedData['has_failed'],
                'sibling_order' => $validatedData['sibling_order'],
                'attendance_type' => $validatedData['attendance_type'],
            ];

            // Generate password and student ID
            $password = substr($studentData['national_id'], -6);
            $studentData['password'] = bcrypt($password);
            $studentData['student_id'] = $this->generateStudentId();
            $studentData['status'] = 'active';

            // Create student
            $student = Student::create($studentData);
            
            // Create academic info
            $academicData['student_id'] = $student->id;
            $academicInfo = AcademicInfo::create($academicData);

            // Load relationship
            $student->load('academicInfo');

            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => 'Student created successfully',
                'student_id' => $student->student_id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student
     */
    public function show(string $id): JsonResponse
    {
        try {
            $student = Student::with('academicInfo')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => 'Student retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);
            
            // Validation (similar to store but without unique constraint on national_id for same student)
            $validatedData = $request->validate([
                'full_name' => 'required|string|max:255',
                'national_id' => 'required|string|size:14|unique:students,national_id,' . $student->id,
                // ... other validation rules
            ]);

            // Update logic will be implemented
            
            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => 'Student updated successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique student ID
     */
    private function generateStudentId(): string
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
