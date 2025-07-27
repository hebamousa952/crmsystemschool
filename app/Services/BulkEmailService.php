<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\EmailJob;
use App\Services\EmailQueueService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BulkEmailService
{
    /**
     * Send bulk email to all users
     */
    public static function sendToAllUsers(
        $mailableClass,
        $emailType,
        $subject,
        $emailData = [],
        $options = []
    ) {
        $users = User::where('email', '!=', null)->get();
        
        return static::sendBulkEmail(
            $mailableClass,
            $users,
            $emailType,
            $subject,
            $emailData,
            $options
        );
    }

    /**
     * Send bulk email to users by role
     */
    public static function sendToUsersByRole(
        $mailableClass,
        $roles,
        $emailType,
        $subject,
        $emailData = [],
        $options = []
    ) {
        $roles = is_array($roles) ? $roles : [$roles];
        
        $users = User::whereIn('role', $roles)
                    ->where('email', '!=', null)
                    ->get();
        
        return static::sendBulkEmail(
            $mailableClass,
            $users,
            $emailType,
            $subject,
            $emailData,
            $options
        );
    }

    /**
     * Send bulk email to parents of students in specific grades
     */
    public static function sendToParentsByGrade(
        $mailableClass,
        $gradeIds,
        $emailType,
        $subject,
        $emailData = [],
        $options = []
    ) {
        $gradeIds = is_array($gradeIds) ? $gradeIds : [$gradeIds];
        
        // Get students in specified grades
        $students = Student::whereIn('grade_id', $gradeIds)
                          ->with('parent')
                          ->get();
        
        // Extract unique parent emails
        $parentEmails = $students->pluck('parent.email')
                                ->filter()
                                ->unique()
                                ->values();
        
        $parents = User::whereIn('email', $parentEmails)->get();
        
        return static::sendBulkEmail(
            $mailableClass,
            $parents,
            $emailType,
            $subject,
            $emailData,
            $options
        );
    }

    /**
     * Send bulk email to students in specific classrooms
     */
    public static function sendToStudentsByClassroom(
        $mailableClass,
        $classroomIds,
        $emailType,
        $subject,
        $emailData = [],
        $options = []
    ) {
        $classroomIds = is_array($classroomIds) ? $classroomIds : [$classroomIds];
        
        $students = Student::whereIn('classroom_id', $classroomIds)
                          ->with('parent')
                          ->get();
        
        // Send to parents of these students
        $parentEmails = $students->pluck('parent.email')
                                ->filter()
                                ->unique()
                                ->values();
        
        $parents = User::whereIn('email', $parentEmails)->get();
        
        return static::sendBulkEmail(
            $mailableClass,
            $parents,
            $emailType,
            $subject,
            $emailData,
            $options
        );
    }

    /**
     * Send bulk email with advanced options
     */
    public static function sendBulkEmail(
        $mailableClass,
        $recipients,
        $emailType,
        $subject,
        $emailData = [],
        $options = []
    ) {
        // Default options
        $options = array_merge([
            'priority' => 'normal',
            'batch_size' => 50,
            'delay_between_batches' => 60, // seconds
            'rate_limit_per_minute' => 100,
            'scheduled_at' => null,
            'track_opens' => true,
            'track_clicks' => true,
            'max_attempts' => 3,
            'personalize' => false,
            'test_mode' => false
        ], $options);

        // Generate campaign ID
        $campaignId = Str::uuid();
        
        // Test mode - send to first 5 recipients only
        if ($options['test_mode']) {
            $recipients = $recipients->take(5);
            $options['batch_size'] = 5;
        }

        // Calculate rate limiting
        $delayBetweenEmails = static::calculateRateLimit(
            $options['rate_limit_per_minute'],
            $options['batch_size']
        );

        // Prepare email data
        $emailData = array_merge($emailData, [
            'subject' => $subject,
            'campaign_id' => $campaignId,
            'total_recipients' => $recipients->count()
        ]);

        // Queue bulk emails
        $result = EmailQueueService::queueBulkEmails(
            $mailableClass,
            $recipients,
            $emailType,
            $emailData,
            array_merge($options, [
                'campaign_id' => $campaignId,
                'subject' => $subject
            ])
        );

        // Log campaign
        Log::info('Bulk email campaign created', [
            'campaign_id' => $campaignId,
            'email_type' => $emailType,
            'total_recipients' => $recipients->count(),
            'batch_size' => $options['batch_size'],
            'test_mode' => $options['test_mode']
        ]);

        return array_merge($result, [
            'subject' => $subject,
            'options' => $options
        ]);
    }

    /**
     * Send newsletter to subscribers
     */
    public static function sendNewsletter(
        $subject,
        $content,
        $options = []
    ) {
        // Get newsletter subscribers
        $subscribers = User::where('newsletter_subscribed', true)
                          ->where('email', '!=', null)
                          ->get();

        return static::sendBulkEmail(
            \App\Mail\NewsletterMail::class,
            $subscribers,
            'newsletter',
            $subject,
            ['content' => $content],
            array_merge($options, [
                'priority' => 'low',
                'batch_size' => 100,
                'delay_between_batches' => 120
            ])
        );
    }

    /**
     * Send emergency notification
     */
    public static function sendEmergencyNotification(
        $subject,
        $message,
        $recipients = 'all',
        $options = []
    ) {
        // Get recipients based on type
        switch ($recipients) {
            case 'all':
                $users = User::where('email', '!=', null)->get();
                break;
            case 'staff':
                $users = static::getStaffUsers();
                break;
            case 'parents':
                $users = static::getParentUsers();
                break;
            default:
                $users = collect($recipients);
        }

        return static::sendBulkEmail(
            \App\Mail\EmergencyNotificationMail::class,
            $users,
            'emergency_notification',
            $subject,
            ['message' => $message],
            array_merge($options, [
                'priority' => 'urgent',
                'batch_size' => 20,
                'delay_between_batches' => 10,
                'rate_limit_per_minute' => 200
            ])
        );
    }

    /**
     * Send payment reminders
     */
    public static function sendPaymentReminders($options = [])
    {
        // Get students with outstanding payments
        $students = Student::where('remaining_amount', '>', 0)
                          ->with(['parent', 'grade', 'classroom'])
                          ->get();

        $parents = $students->pluck('parent')
                           ->filter()
                           ->unique('email');

        return static::sendBulkEmail(
            \App\Mail\PaymentReminderMail::class,
            $parents,
            'payment_reminder',
            'تذكير بالمصروفات المدرسية المستحقة',
            ['students' => $students],
            array_merge($options, [
                'priority' => 'high',
                'batch_size' => 30,
                'delay_between_batches' => 90
            ])
        );
    }

    /**
     * Send grade reports to parents
     */
    public static function sendGradeReports($gradeIds = null, $options = [])
    {
        $query = Student::with(['parent', 'grade', 'classroom']);
        
        if ($gradeIds) {
            $gradeIds = is_array($gradeIds) ? $gradeIds : [$gradeIds];
            $query->whereIn('grade_id', $gradeIds);
        }

        $students = $query->get();
        $parents = $students->pluck('parent')
                           ->filter()
                           ->unique('email');

        return static::sendBulkEmail(
            \App\Mail\GradeReportMail::class,
            $parents,
            'grade_report',
            'تقرير درجات الطالب',
            ['students' => $students],
            array_merge($options, [
                'priority' => 'normal',
                'batch_size' => 40,
                'delay_between_batches' => 60,
                'personalize' => true
            ])
        );
    }

    /**
     * Get campaign status
     */
    public static function getCampaignStatus($campaignId)
    {
        return EmailQueueService::getCampaignStats($campaignId);
    }

    /**
     * Cancel campaign
     */
    public static function cancelCampaign($campaignId)
    {
        $cancelled = EmailQueueService::cancelCampaign($campaignId);
        
        Log::info('Bulk email campaign cancelled', [
            'campaign_id' => $campaignId,
            'cancelled_emails' => $cancelled
        ]);

        return $cancelled;
    }

    /**
     * Retry failed emails in campaign
     */
    public static function retryCampaign($campaignId)
    {
        $retried = EmailQueueService::retryCampaign($campaignId);
        
        Log::info('Bulk email campaign retried', [
            'campaign_id' => $campaignId,
            'retried_emails' => $retried
        ]);

        return $retried;
    }

    /**
     * Get active campaigns
     */
    public static function getActiveCampaigns()
    {
        return EmailJob::whereNotNull('campaign_id')
                      ->where('created_at', '>=', now()->subDays(7))
                      ->groupBy('campaign_id')
                      ->selectRaw('
                          campaign_id,
                          email_type,
                          subject,
                          COUNT(*) as total_emails,
                          SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_emails,
                          SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_emails,
                          SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_emails,
                          MIN(created_at) as started_at,
                          MAX(sent_at) as last_sent_at
                      ')
                      ->orderBy('started_at', 'desc')
                      ->get();
    }

    /**
     * Calculate rate limit delay
     */
    private static function calculateRateLimit($ratePerMinute, $batchSize)
    {
        $emailsPerSecond = $ratePerMinute / 60;
        $delayPerEmail = 1 / $emailsPerSecond;
        
        return max(1, $delayPerEmail * $batchSize);
    }

    /**
     * Get staff users
     */
    private static function getStaffUsers()
    {
        return User::whereIn('role', ['admin', 'teacher', 'staff'])
                  ->where('email', '!=', null)
                  ->get();
    }

    /**
     * Get parent users
     */
    private static function getParentUsers()
    {
        return User::where('role', 'parent')
                  ->where('email', '!=', null)
                  ->get();
    }
}
