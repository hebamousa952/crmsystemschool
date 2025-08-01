<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\ActivityLog;
use App\Events\StudentCreated;
use App\Events\StudentUpdated;
use App\Events\StudentDeleted;
use App\Services\NotificationService;

class StudentObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Student "creating" event (before creation)
     */
    public function creating(Student $student)
    {
        // يمكن إضافة validation أو تعديلات قبل الحفظ
    }

    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student)
    {
        // Log the activity
        $activityLog = ActivityLog::logActivity([
            'event_type' => 'student_created',
            'description' => "تم إنشاء طالب جديد: {$student->full_name}",
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => auth()->id(),
            'category' => 'student_management',
            'severity' => 'medium',
            'properties' => [
                'student_data' => $student->toArray(),
                'academic_info' => $student->academicInfo ? $student->academicInfo->toArray() : null,
            ]
        ]);

        // Fire the event
        event(new StudentCreated($student, auth()->user()));

        // Send notifications (will be implemented in NotificationService)
        // $this->notificationService->sendStudentCreatedNotifications($student, $activityLog);
    }

    /**
     * Handle the Student "updating" event (before update)
     */
    public function updating(Student $student)
    {
        // Store original data for comparison
        $student->originalData = $student->getOriginal();
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student)
    {
        $originalData = $student->originalData ?? $student->getOriginal();

        // Check if important fields changed
        $importantFields = ['full_name', 'national_id', 'status'];
        $hasImportantChanges = false;

        foreach ($importantFields as $field) {
            if ($student->isDirty($field)) {
                $hasImportantChanges = true;
                break;
            }
        }

        if ($hasImportantChanges) {
            // Log the activity
            $activityLog = ActivityLog::logActivity([
                'event_type' => 'student_updated',
                'description' => "تم تعديل بيانات الطالب: {$student->full_name}",
                'subject_type' => Student::class,
                'subject_id' => $student->id,
                'causer_type' => 'App\Models\User',
                'causer_id' => auth()->id(),
                'category' => 'student_management',
                'severity' => 'medium',
                'old_values' => $originalData,
                'new_values' => $student->toArray(),
                'properties' => [
                    'changes' => $this->getDetailedChanges($student, $originalData),
                ]
            ]);

            // Fire the event
            event(new StudentUpdated($student, $originalData, auth()->user()));
        }
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student)
    {
        // Log the activity
        $activityLog = ActivityLog::logActivity([
            'event_type' => 'student_deleted',
            'description' => "تم حذف الطالب: {$student->full_name}",
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => auth()->id(),
            'category' => 'student_management',
            'severity' => 'high',
            'is_sensitive' => true,
            'requires_review' => true,
            'properties' => [
                'deleted_student_data' => $student->toArray(),
            ]
        ]);

        // Fire the event
        event(new StudentDeleted($student, auth()->user()));
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student)
    {
        // Log the activity
        ActivityLog::logActivity([
            'event_type' => 'student_restored',
            'description' => "تم استعادة الطالب: {$student->full_name}",
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => auth()->id(),
            'category' => 'student_management',
            'severity' => 'medium',
            'properties' => [
                'restored_student_data' => $student->toArray(),
            ]
        ]);
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student)
    {
        // Log the activity
        ActivityLog::logActivity([
            'event_type' => 'student_force_deleted',
            'description' => "تم حذف الطالب نهائياً: {$student->full_name}",
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => auth()->id(),
            'category' => 'student_management',
            'severity' => 'critical',
            'is_sensitive' => true,
            'requires_review' => true,
            'properties' => [
                'force_deleted_student_data' => $student->toArray(),
            ]
        ]);
    }

    /**
     * Get detailed changes between old and new data
     */
    private function getDetailedChanges(Student $student, array $originalData): array
    {
        $changes = [];
        $fieldLabels = [
            'full_name' => 'الاسم الكامل',
            'national_id' => 'الرقم القومي',
            'birth_date' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'status' => 'الحالة',
            'address' => 'العنوان'
        ];

        foreach ($student->getDirty() as $field => $newValue) {
            if (isset($originalData[$field])) {
                $oldValue = $originalData[$field];

                $changes[] = [
                    'field' => $fieldLabels[$field] ?? $field,
                    'field_key' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue
                ];
            }
        }

        return $changes;
    }
}
