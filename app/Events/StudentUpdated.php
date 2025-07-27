<?php

namespace App\Events;

use App\Models\Student;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $student;
    public $originalData;
    public $updatedBy;
    public $timestamp;
    public $eventType = 'student_updated';

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, array $originalData, User $updatedBy = null)
    {
        $this->student = $student;
        $this->originalData = $originalData;
        $this->updatedBy = $updatedBy ?: auth()->user();
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications'),
            new PrivateChannel('admin-notifications'),
            new PrivateChannel('student-management'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $changes = $this->getChanges();

        return [
            'event_type' => $this->eventType,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->full_name_ar,
                'national_id' => $this->student->national_id,
            ],
            'updated_by' => [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'role' => $this->updatedBy->formatted_role,
            ],
            'changes' => $changes,
            'timestamp' => $this->timestamp->toISOString(),
            'message' => "تم تعديل بيانات الطالب: {$this->student->full_name_ar}",
            'priority' => 'normal',
            'category' => 'student_management'
        ];
    }

    /**
     * Get detailed changes
     */
    public function getChanges(): array
    {
        $changes = [];
        $fieldLabels = [
            'full_name_ar' => 'الاسم الكامل',
            'national_id' => 'الرقم القومي',
            'birth_date' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'grade_id' => 'المرحلة',
            'classroom_id' => 'الفصل',
            'status' => 'الحالة',
            'phone' => 'رقم الهاتف',
            'address' => 'العنوان'
        ];

        foreach ($this->student->getDirty() as $field => $newValue) {
            if (isset($this->originalData[$field])) {
                $changes[] = [
                    'field' => $fieldLabels[$field] ?? $field,
                    'old_value' => $this->originalData[$field],
                    'new_value' => $newValue
                ];
            }
        }

        return $changes;
    }

    /**
     * Get activity log data
     */
    public function getActivityData(): array
    {
        return [
            'event_type' => $this->eventType,
            'subject_type' => Student::class,
            'subject_id' => $this->student->id,
            'causer_type' => User::class,
            'causer_id' => $this->updatedBy->id,
            'description' => "تم تعديل بيانات طالب",
            'properties' => [
                'original_data' => $this->originalData,
                'updated_data' => $this->student->toArray(),
                'changes' => $this->getChanges(),
                'updated_by' => $this->updatedBy->only(['id', 'name', 'role']),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        ];
    }
}
