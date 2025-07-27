<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\StudentCreatedMail;
use App\Mail\PaymentReceivedMail;
use App\Services\EmailQueueService;

class NotificationService
{
    /**
     * Send notifications for student created event
     */
    public function sendStudentCreatedNotifications(Student $student, ActivityLog $activityLog)
    {
        $recipients = $this->getStudentEventRecipients($student);
        
        $notificationData = [
            'title' => 'تم تسجيل طالب جديد',
            'message' => "تم تسجيل الطالب {$student->full_name_ar} في " . ($student->classroom->full_name ?? 'الفصل'),
            'type' => 'student_created',
            'event_type' => 'student_created',
            'activity_log_id' => $activityLog->id,
            'category' => 'student_management',
            'priority' => 'normal',
            'channels' => ['database', 'email'],
            'action_url' => url('/students/' . $student->id),
            'action_text' => 'عرض الطالب',
            'metadata' => [
                'student_id' => $student->id,
                'student_name' => $student->full_name_ar,
                'grade' => $student->grade ? $student->grade->grade_name : '',
                'classroom' => $student->classroom ? $student->classroom->full_name : '',
                'created_by_id' => auth()->id()
            ]
        ];

        $this->sendToMultipleUsers($recipients, $notificationData);
    }

    /**
     * Send notifications for student updated event
     */
    public function sendStudentUpdatedNotifications(Student $student, array $originalData, ActivityLog $activityLog)
    {
        $recipients = $this->getStudentEventRecipients($student);
        
        $changes = $this->formatChanges($activityLog->properties['changes'] ?? []);
        
        $notificationData = [
            'title' => 'تم تعديل بيانات طالب',
            'message' => "تم تعديل بيانات الطالب {$student->full_name_ar}. التغييرات: {$changes}",
            'type' => 'student_updated',
            'event_type' => 'student_updated',
            'activity_log_id' => $activityLog->id,
            'category' => 'student_management',
            'priority' => 'normal',
            'channels' => ['database'],
            'action_url' => url('/students/' . $student->id),
            'action_text' => 'عرض التفاصيل',
            'metadata' => [
                'student_id' => $student->id,
                'student_name' => $student->full_name_ar,
                'changes' => $activityLog->properties['changes'] ?? [],
            ]
        ];

        $this->sendToMultipleUsers($recipients, $notificationData);
    }

    /**
     * Send notifications for student deleted event
     */
    public function sendStudentDeletedNotifications(Student $student, ActivityLog $activityLog)
    {
        $recipients = $this->getAdminRecipients();
        
        $notificationData = [
            'title' => 'تم حذف طالب',
            'message' => "تم حذف الطالب {$student->full_name_ar} من النظام",
            'type' => 'student_deleted',
            'event_type' => 'student_deleted',
            'activity_log_id' => $activityLog->id,
            'category' => 'student_management',
            'priority' => 'high',
            'channels' => ['database', 'email'],
            'requires_action' => true,
            'metadata' => [
                'student_id' => $student->id,
                'student_name' => $student->full_name_ar,
                'deleted_by' => auth()->user()->name,
            ]
        ];

        $this->sendToMultipleUsers($recipients, $notificationData);
    }

    /**
     * Send notifications for payment received event
     */
    public function sendPaymentReceivedNotifications($payment, ActivityLog $activityLog)
    {
        $recipients = $this->getFinancialEventRecipients();
        
        $notificationData = [
            'title' => 'تم استلام دفعة جديدة',
            'message' => "تم استلام دفعة بمبلغ {$payment->formatted_amount} من الطالب {$payment->student->full_name_ar}",
            'type' => 'payment_received',
            'event_type' => 'payment_received',
            'activity_log_id' => $activityLog->id,
            'category' => 'financial',
            'priority' => 'normal',
            'channels' => ['database', 'email'],
            'action_url' => url('/payments/' . $payment->id),
            'action_text' => 'عرض الدفعة',
            'metadata' => [
                'payment_id' => $payment->id,
                'student_id' => $payment->student_id,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'received_by_id' => auth()->id()
            ]
        ];

        $this->sendToMultipleUsers($recipients, $notificationData);
    }

    /**
     * Send notifications for user login event
     */
    public function sendUserLoginNotifications(User $user, ActivityLog $activityLog)
    {
        // Only send for suspicious logins or admin logins
        if ($this->isSuspiciousLogin($user) || $user->role === 'admin') {
            $recipients = $this->getSecurityEventRecipients();
            
            $notificationData = [
                'title' => 'تسجيل دخول جديد',
                'message' => "تم تسجيل دخول المستخدم {$user->name} ({$user->formatted_role})",
                'type' => 'user_login',
                'event_type' => 'user_logged_in',
                'activity_log_id' => $activityLog->id,
                'category' => 'system',
                'priority' => $this->isSuspiciousLogin($user) ? 'high' : 'low',
                'channels' => ['database'],
                'metadata' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            ];

            $this->sendToMultipleUsers($recipients, $notificationData);
        }
    }

    /**
     * Send notification to multiple users
     */
    private function sendToMultipleUsers(array $recipients, array $notificationData)
    {
        foreach ($recipients as $userId) {
            $this->sendToUser($userId, $notificationData);
        }
    }

    /**
     * Send notification to a single user
     */
    private function sendToUser($userId, array $notificationData)
    {
        try {
            $notification = Notification::create(array_merge($notificationData, [
                'user_id' => $userId,
                'data' => json_encode($notificationData['metadata'] ?? [])
            ]));

            // Add notification ID to data for tracking
            $notificationData['notification_id'] = $notification->id;

            // Send via different channels
            $channels = $notificationData['channels'] ?? ['database'];

            if (in_array('email', $channels)) {
                $this->sendEmailNotification($userId, $notificationData);
            }
            
            if (in_array('sms', $channels)) {
                $this->sendSmsNotification($userId, $notificationData);
            }
            
            if (in_array('push', $channels)) {
                $this->sendPushNotification($userId, $notificationData);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $userId,
                'notification_data' => $notificationData,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($userId, array $notificationData)
    {
        $user = User::find($userId);
        if (!$user || !$user->email) return;

        // Check if user wants to receive this type of email
        $emailType = $notificationData['event_type'] ?? '';
        if (!$user->wantsEmailType($emailType)) {
            return;
        }

        // Check if user should receive email now based on frequency
        if (!$user->shouldReceiveEmailNow($emailType)) {
            // Queue for later if it's a digest type
            if (in_array($user->notification_frequency, ['daily', 'weekly'])) {
                // Will be sent via digest command
                return;
            }
            return;
        }

        try {
            $eventType = $notificationData['event_type'] ?? '';
            $notificationId = $notificationData['notification_id'] ?? null;

            switch ($eventType) {
                case 'student_created':
                    if (isset($notificationData['metadata']['student_id'])) {
                        $student = Student::find($notificationData['metadata']['student_id']);
                        $createdBy = User::find($notificationData['metadata']['created_by_id'] ?? auth()->id());

                        if ($student && $createdBy) {
                            // Use advanced queue system
                            EmailQueueService::queueEmail(
                                StudentCreatedMail::class,
                                $user,
                                'student_created',
                                [
                                    'student' => $student,
                                    'createdBy' => $createdBy,
                                    'activityLog' => null
                                ],
                                [
                                    'priority' => 'normal',
                                    'notification_id' => $notificationId,
                                    'track_opens' => $user->allow_email_tracking,
                                    'track_clicks' => $user->allow_click_tracking
                                ]
                            );
                        }
                    }
                    break;

                case 'payment_received':
                    if (isset($notificationData['metadata']['payment_id'])) {
                        $payment = Payment::find($notificationData['metadata']['payment_id']);
                        $receivedBy = User::find($notificationData['metadata']['received_by_id'] ?? auth()->id());

                        if ($payment && $receivedBy) {
                            // Use advanced queue system with high priority for payments
                            EmailQueueService::queueEmail(
                                PaymentReceivedMail::class,
                                $user,
                                'payment_received',
                                [
                                    'payment' => $payment,
                                    'receivedBy' => $receivedBy,
                                    'activityLog' => null
                                ],
                                [
                                    'priority' => 'high',
                                    'notification_id' => $notificationId,
                                    'track_opens' => $user->allow_email_tracking,
                                    'track_clicks' => $user->allow_click_tracking
                                ]
                            );
                        }
                    }
                    break;

                default:
                    // Send generic email for other types
                    $this->sendGenericEmail($user, $notificationData);
                    break;
            }

            Log::info('Email notification sent successfully', [
                'user_id' => $userId,
                'email' => $user->email,
                'event_type' => $eventType
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $userId,
                'event_type' => $eventType ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send generic email for events without specific templates
     */
    private function sendGenericEmail($user, array $notificationData)
    {
        Mail::raw($notificationData['message'], function ($message) use ($user, $notificationData) {
            $message->to($user->email)
                    ->subject($notificationData['title']);
        });
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification($userId, array $notificationData)
    {
        $user = User::find($userId);
        if (!$user) return;

        try {
            // Will implement SMS sending in next phase
            Log::info('SMS notification queued', [
                'user_id' => $userId,
                'title' => $notificationData['title']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification($userId, array $notificationData)
    {
        try {
            // Will implement push notifications in next phase
            Log::info('Push notification queued', [
                'user_id' => $userId,
                'title' => $notificationData['title']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get recipients for student events
     */
    private function getStudentEventRecipients(Student $student): array
    {
        return User::whereIn('role', ['admin', 'secretary'])
                  ->pluck('id')
                  ->toArray();
    }

    /**
     * Get recipients for financial events
     */
    private function getFinancialEventRecipients(): array
    {
        return User::whereIn('role', ['admin', 'accountant'])
                  ->pluck('id')
                  ->toArray();
    }

    /**
     * Get admin recipients
     */
    private function getAdminRecipients(): array
    {
        return User::where('role', 'admin')
                  ->pluck('id')
                  ->toArray();
    }

    /**
     * Get recipients for security events
     */
    private function getSecurityEventRecipients(): array
    {
        return User::where('role', 'admin')
                  ->pluck('id')
                  ->toArray();
    }

    /**
     * Check if login is suspicious
     */
    private function isSuspiciousLogin(User $user): bool
    {
        // Simple suspicious login detection
        $lastLogin = $user->last_login_at;
        $currentIp = request()->ip();
        
        // Check if login from different IP within short time
        if ($lastLogin && $lastLogin->diffInMinutes(now()) < 30) {
            // Could add IP comparison logic here
            return false;
        }
        
        return false;
    }

    /**
     * Format changes for display
     */
    private function formatChanges(array $changes): string
    {
        if (empty($changes)) return 'لا توجد تغييرات';
        
        $formatted = [];
        foreach ($changes as $change) {
            $formatted[] = $change['field'];
        }
        
        return implode('، ', array_slice($formatted, 0, 3)) . 
               (count($formatted) > 3 ? ' وأخرى' : '');
    }
}
