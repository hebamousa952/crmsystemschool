<?php

namespace App\Services;

use App\Models\EmailJob;
use App\Models\User;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailQueueService
{
    /**
     * Queue an email for sending
     */
    public static function queueEmail(
        $mailableClass,
        $recipient,
        $emailType,
        $emailData = [],
        $options = []
    ) {
        // Default options
        $options = array_merge([
            'priority' => 'normal',
            'scheduled_at' => null,
            'track_opens' => true,
            'track_clicks' => true,
            'max_attempts' => 3,
            'notification_id' => null,
            'campaign_id' => null,
            'batch_id' => null
        ], $options);

        // Determine recipient details
        $recipientEmail = is_string($recipient) ? $recipient : $recipient->email;
        $recipientName = is_string($recipient) ? null : $recipient->name;
        $userId = is_string($recipient) ? null : $recipient->id;

        // Generate subject from mailable if not provided
        $subject = $options['subject'] ?? static::generateSubject($mailableClass, $emailData);

        // Create email job record
        $emailJob = EmailJob::create([
            'job_id' => Str::uuid(),
            'email_type' => $emailType,
            'priority' => $options['priority'],
            'user_id' => $userId,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'email_class' => $mailableClass,
            'email_data' => $emailData,
            'scheduled_at' => $options['scheduled_at'],
            'max_attempts' => $options['max_attempts'],
            'notification_id' => $options['notification_id'],
            'track_opens' => $options['track_opens'],
            'track_clicks' => $options['track_clicks'],
            'campaign_id' => $options['campaign_id'],
            'batch_id' => $options['batch_id']
        ]);

        // Queue the job
        $delay = $options['scheduled_at'] ? Carbon::parse($options['scheduled_at']) : null;
        $queueName = static::getQueueName($options['priority']);

        $job = new SendEmailJob($emailJob->id);
        
        if ($delay && $delay->isFuture()) {
            Queue::later($delay, $job, '', $queueName);
        } else {
            Queue::push($job, '', $queueName);
        }

        return $emailJob;
    }

    /**
     * Queue bulk emails
     */
    public static function queueBulkEmails(
        $mailableClass,
        $recipients,
        $emailType,
        $emailData = [],
        $options = []
    ) {
        $campaignId = $options['campaign_id'] ?? Str::uuid();
        $batchSize = $options['batch_size'] ?? 50;
        $delayBetweenBatches = $options['delay_between_batches'] ?? 60; // seconds

        $emailJobs = [];
        $batches = array_chunk($recipients, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            $batchId = Str::uuid();
            $batchDelay = $batchIndex * $delayBetweenBatches;

            foreach ($batch as $recipient) {
                $batchOptions = array_merge($options, [
                    'campaign_id' => $campaignId,
                    'batch_id' => $batchId,
                    'scheduled_at' => now()->addSeconds($batchDelay)
                ]);

                $emailJob = static::queueEmail(
                    $mailableClass,
                    $recipient,
                    $emailType,
                    $emailData,
                    $batchOptions
                );

                $emailJobs[] = $emailJob;
            }
        }

        return [
            'campaign_id' => $campaignId,
            'total_emails' => count($emailJobs),
            'batches' => count($batches),
            'email_jobs' => $emailJobs
        ];
    }

    /**
     * Schedule email for later
     */
    public static function scheduleEmail(
        $mailableClass,
        $recipient,
        $emailType,
        $scheduledAt,
        $emailData = [],
        $options = []
    ) {
        $options['scheduled_at'] = $scheduledAt;
        return static::queueEmail($mailableClass, $recipient, $emailType, $emailData, $options);
    }

    /**
     * Send urgent email (high priority)
     */
    public static function sendUrgentEmail(
        $mailableClass,
        $recipient,
        $emailType,
        $emailData = [],
        $options = []
    ) {
        $options['priority'] = 'urgent';
        return static::queueEmail($mailableClass, $recipient, $emailType, $emailData, $options);
    }

    /**
     * Cancel scheduled email
     */
    public static function cancelEmail($emailJobId)
    {
        $emailJob = EmailJob::find($emailJobId);
        
        if ($emailJob && $emailJob->status === 'pending') {
            $emailJob->update(['status' => 'cancelled']);
            return true;
        }
        
        return false;
    }

    /**
     * Cancel campaign emails
     */
    public static function cancelCampaign($campaignId)
    {
        $cancelled = EmailJob::where('campaign_id', $campaignId)
                            ->where('status', 'pending')
                            ->update(['status' => 'cancelled']);
        
        return $cancelled;
    }

    /**
     * Retry failed email
     */
    public static function retryEmail($emailJobId)
    {
        $emailJob = EmailJob::find($emailJobId);
        
        if ($emailJob && $emailJob->canRetry()) {
            $emailJob->retry();
            
            // Queue again
            $job = new SendEmailJob($emailJob->id);
            $queueName = static::getQueueName($emailJob->priority);
            Queue::push($job, '', $queueName);
            
            return true;
        }
        
        return false;
    }

    /**
     * Retry all failed emails for a campaign
     */
    public static function retryCampaign($campaignId)
    {
        $failedJobs = EmailJob::where('campaign_id', $campaignId)
                             ->where('status', 'failed')
                             ->get();
        
        $retried = 0;
        foreach ($failedJobs as $emailJob) {
            if ($emailJob->canRetry()) {
                static::retryEmail($emailJob->id);
                $retried++;
            }
        }
        
        return $retried;
    }

    /**
     * Get queue statistics
     */
    public static function getQueueStats()
    {
        return [
            'pending' => EmailJob::where('status', 'pending')->count(),
            'processing' => EmailJob::where('status', 'processing')->count(),
            'sent_today' => EmailJob::where('status', 'sent')
                                   ->whereDate('sent_at', today())
                                   ->count(),
            'failed_today' => EmailJob::where('status', 'failed')
                                     ->whereDate('failed_at', today())
                                     ->count(),
            'scheduled' => EmailJob::scheduled()->count(),
            'by_priority' => EmailJob::where('status', 'pending')
                                    ->groupBy('priority')
                                    ->selectRaw('priority, count(*) as count')
                                    ->pluck('count', 'priority'),
            'by_type' => EmailJob::whereDate('created_at', today())
                                ->groupBy('email_type')
                                ->selectRaw('email_type, count(*) as count')
                                ->pluck('count', 'email_type')
        ];
    }

    /**
     * Get campaign statistics
     */
    public static function getCampaignStats($campaignId)
    {
        $jobs = EmailJob::where('campaign_id', $campaignId);
        
        return [
            'total' => $jobs->count(),
            'sent' => $jobs->where('status', 'sent')->count(),
            'pending' => $jobs->where('status', 'pending')->count(),
            'failed' => $jobs->where('status', 'failed')->count(),
            'cancelled' => $jobs->where('status', 'cancelled')->count(),
            'opened' => $jobs->whereNotNull('opened_at')->count(),
            'clicked' => $jobs->whereNotNull('clicked_at')->count(),
            'open_rate' => $jobs->where('status', 'sent')->count() > 0 
                ? round(($jobs->whereNotNull('opened_at')->count() / $jobs->where('status', 'sent')->count()) * 100, 2)
                : 0,
            'click_rate' => $jobs->where('status', 'sent')->count() > 0
                ? round(($jobs->whereNotNull('clicked_at')->count() / $jobs->where('status', 'sent')->count()) * 100, 2)
                : 0
        ];
    }

    /**
     * Clean old email jobs
     */
    public static function cleanOldJobs($daysOld = 30)
    {
        $deleted = EmailJob::where('created_at', '<', now()->subDays($daysOld))
                          ->whereIn('status', ['sent', 'failed', 'cancelled'])
                          ->delete();
        
        return $deleted;
    }

    /**
     * Generate subject from mailable class
     */
    private static function generateSubject($mailableClass, $emailData)
    {
        try {
            $instance = new $mailableClass();
            if (method_exists($instance, 'getSubject')) {
                return $instance->getSubject();
            }
        } catch (\Exception $e) {
            // Fallback to generic subject
        }
        
        return 'إشعار من نظام إدارة المدرسة';
    }

    /**
     * Get queue name based on priority
     */
    private static function getQueueName($priority)
    {
        $queues = [
            'urgent' => 'emails-urgent',
            'high' => 'emails-high',
            'normal' => 'emails',
            'low' => 'emails-low'
        ];

        return $queues[$priority] ?? 'emails';
    }
}
