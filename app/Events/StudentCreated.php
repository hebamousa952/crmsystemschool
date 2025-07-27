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

class StudentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $student;
    public $createdBy;
    public $timestamp;
    public $eventType = 'student_created';

    /**
     * Create a new event instance.
     */
    public function __construct(Student $student, User $createdBy = null)
    {
        $this->student = $student;
        $this->createdBy = $createdBy ?: auth()->user();
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
        return [
            'event_type' => $this->eventType,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->full_name,
                'national_id' => $this->student->national_id,
                'academic_info' => $this->student->academicInfo ? [
                    'grade' => $this->student->academicInfo->grade,
                    'classroom' => $this->student->academicInfo->classroom,
                ] : null,
            ],
            'created_by' => [
                'id' => $this->createdBy->id ?? null,
                'name' => $this->createdBy->name ?? 'System',
                'role' => $this->createdBy->role ?? 'system',
            ],
            'timestamp' => $this->timestamp->toISOString(),
            'message' => "تم تسجيل طالب جديد: {$this->student->full_name}",
            'priority' => 'normal',
            'category' => 'student_management'
        ];
    }

    /**
     * Get notification recipients
     */
    public function getNotificationRecipients(): array
    {
        return [
            'admins' => User::where('role', 'admin')->pluck('id')->toArray(),
            'secretaries' => User::where('role', 'secretary')->pluck('id')->toArray(),
            'grade_teachers' => [], // يمكن إضافة مدرسي المرحلة لاحقاً
        ];
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
            'causer_id' => $this->createdBy->id,
            'description' => "تم إنشاء طالب جديد",
            'properties' => [
                'student_data' => $this->student->toArray(),
                'created_by' => $this->createdBy->only(['id', 'name', 'role']),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        ];
    }
}
