<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Classroom;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students with hyperlinks to their profiles
     */
    public function index(Request $request)
    {
        $query = Student::with(['grade', 'classroom', 'parent', 'mother']);

        // Filter by level if specified
        if ($request->has('level')) {
            $query->whereHas('grade', function($q) use ($request) {
                $q->where('level', $request->level);
            });
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->get('q');
            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('full_name_ar', 'like', "%{$searchTerm}%")
                      ->orWhere('national_id', 'like', "%{$searchTerm}%")
                      ->orWhere('student_code', 'like', "%{$searchTerm}%");
                });
            }
        }

        // Financial filter
        if ($request->has('financial')) {
            $query->whereHas('payments', function($q) {
                $q->where('status', 'pending');
            });
        }

        $students = $query->paginate(20);

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $grades = Grade::with('classrooms')->get();
        return view('students.create', compact('grades'));
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        // Validation and storage logic will be implemented
        // This will handle the comprehensive 6-section form

        return redirect()->route('students.index')
                        ->with('success', 'تم تسجيل الطالب بنجاح');
    }

    /**
     * Display the student's complete digital profile
     * This is the MAIN PROFILE PAGE - everything about the student
     */
    public function show(Student $student)
    {
        // Load all relationships for complete profile
        $student->load([
            'grade',
            'classroom',
            'parent',
            'mother',
            'emergencyContacts',
            'comprehensiveFeePlan',
            'installmentDetails.installmentPayments',
            'discountsAndGrants',
            'arrearsAndPenalties',
            'electronicPayments',
            'financialReceipts',
            'refunds',
            'parentAccountingData',
            'internalAudits'
        ]);

        return view('students.profile', compact('student'));
    }

    /**
     * Show the form for editing the student
     */
    public function edit(Student $student)
    {
        $grades = Grade::with('classrooms')->get();
        return view('students.edit', compact('student', 'grades'));
    }

    /**
     * Update the student's information
     */
    public function update(Request $request, Student $student)
    {
        // Update logic will handle all sections

        return redirect()->route('students.show', $student)
                        ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    /**
     * Remove the student from storage
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
                        ->with('success', 'تم حذف الطالب بنجاح');
    }

    /**
     * Display student's complete digital profile (alias for show)
     */
    public function profile(Student $student)
    {
        return $this->show($student);
    }

    /**
     * Handle financial operations for the student
     */
    public function financial(Student $student)
    {
        // Load financial data
        $student->load([
            'comprehensiveFeePlan',
            'installmentDetails.installmentPayments',
            'discountsAndGrants',
            'arrearsAndPenalties',
            'electronicPayments',
            'financialReceipts',
            'refunds'
        ]);

        return view('students.financial', compact('student'));
    }

    /**
     * Update financial information
     */
    public function updateFinancial(Request $request, Student $student)
    {
        // Handle financial updates

        return redirect()->route('students.show', $student)
                        ->with('success', 'تم تحديث البيانات المالية بنجاح');
    }
}
