<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EmailJob;
use App\Services\MailConfigService;
use Carbon\Carbon;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emailJobId;
    public $tries = 3;
    public $timeout = 120; // 2 minutes
    public $backoff = [30, 60, 120]; // Exponential backoff

    /**
     * Create a new job instance.
     */
    public function __construct($emailJobId)
    {
        $this->emailJobId = $emailJobId;

        // Set queue based on priority
        $emailJob = EmailJob::find($emailJobId);
        if ($emailJob) {
            $queueName = $this->getQueueName($emailJob->priority);
            $this->onQueue($queueName);
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $startTime = microtime(true);

        $emailJob = EmailJob::find($this->emailJobId);

        if (!$emailJob) {
            Log::error('EmailJob not found', ['email_job_id' => $this->emailJobId]);
            return;
        }

        // Check if already sent or cancelled
        if (in_array($emailJob->status, ['sent', 'cancelled'])) {
            Log::info('EmailJob already processed', [
                'email_job_id' => $this->emailJobId,
                'status' => $emailJob->status
            ]);
            return;
        }

        // Mark as processing
        $emailJob->update(['status' => 'processing']);

        try {
            // Configure mail provider
            $provider = $this->selectBestProvider();
            MailConfigService::configureMail($provider);

            // Create the mailable instance
            $mailableClass = $emailJob->email_class;
            $emailData = $emailJob->email_data;

            // Instantiate the mailable with data
            $mailable = $this->createMailableInstance($mailableClass, $emailData, $emailJob->notification_id);

            // Send the email
            Mail::to($emailJob->recipient_email)->send($mailable);

            // Calculate processing time
            $processingTime = round((microtime(true) - $startTime) * 1000);

            // Mark as sent
            $emailJob->markAsSent($processingTime, $provider);

            Log::info('Email sent successfully', [
                'email_job_id' => $this->emailJobId,
                'recipient' => $emailJob->recipient_email,
                'type' => $emailJob->email_type,
                'processing_time_ms' => $processingTime,
                'provider' => $provider
            ]);

        } catch (\Exception $e) {
            $this->handleFailure($emailJob, $e);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        $emailJob = EmailJob::find($this->emailJobId);

        if ($emailJob) {
            $emailJob->markAsFailed(
                $exception->getMessage(),
                [
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ]
            );
        }

        Log::error('Email job failed permanently', [
            'email_job_id' => $this->emailJobId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts
        ]);
    }

    /**
     * Handle temporary failure
     */
    private function handleFailure($emailJob, $exception)
    {
        $emailJob->markAsFailed(
            $exception->getMessage(),
            [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'attempt' => $this->attempts
            ]
        );

        Log::warning('Email job failed, will retry', [
            'email_job_id' => $this->emailJobId,
            'error' => $exception->getMessage(),
            'attempt' => $this->attempts,
            'max_attempts' => $this->tries
        ]);

        // If we can retry, try backup provider
        if ($this->attempts < $this->tries) {
            $this->tryBackupProvider($emailJob);
        }

        throw $exception; // Re-throw to trigger Laravel's retry mechanism
    }

    /**
     * Try backup provider on failure
     */
    private function tryBackupProvider($emailJob)
    {
        try {
            MailConfigService::switchToBackup();
            Log::info('Switched to backup mail provider', [
                'email_job_id' => $this->emailJobId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to switch to backup provider', [
                'email_job_id' => $this->emailJobId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Select best mail provider
     */
    private function selectBestProvider()
    {
        // Smart provider selection logic
        $providers = MailConfigService::getAvailableProviders();

        // Check recent failures and select best provider
        $recentFailures = EmailJob::where('status', 'failed')
                                 ->where('created_at', '>=', now()->subHour())
                                 ->groupBy('smtp_provider')
                                 ->selectRaw('smtp_provider, count(*) as failures')
                                 ->pluck('failures', 'smtp_provider');

        // Select provider with least failures
        $bestProvider = 'primary';
        $minFailures = $recentFailures['primary'] ?? 0;

        foreach ($providers as $provider => $info) {
            if ($info['status'] === 'configured') {
                $failures = $recentFailures[$provider] ?? 0;
                if ($failures < $minFailures) {
                    $bestProvider = $provider;
                    $minFailures = $failures;
                }
            }
        }

        return $bestProvider;
    }

    /**
     * Create mailable instance from class name and data
     */
    private function createMailableInstance($className, $data, $notificationId = null)
    {
        if (!class_exists($className)) {
            throw new \Exception("Mailable class {$className} not found");
        }

        // For now, return a simple instance - will be enhanced
        return new $className($notificationId);
    }

    /**
     * Get queue name based on priority
     */
    private function getQueueName($priority)
    {
        $queues = [
            'urgent' => 'emails-urgent',
            'high' => 'emails-high',
            'normal' => 'emails',
            'low' => 'emails-low'
        ];

        return $queues[$priority] ?? 'emails';
    }

    /**
     * Calculate delay based on attempt
     */
    public function backoff()
    {
        return $this->backoff[$this->attempts - 1] ?? 300; // Default 5 minutes
    }
}
