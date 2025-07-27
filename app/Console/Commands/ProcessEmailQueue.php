<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailJob;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Queue;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:process-queue
                            {--limit=50 : Maximum number of emails to process}
                            {--priority=all : Priority level (urgent, high, normal, low, all)}
                            {--dry-run : Show what would be processed without actually processing}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending email jobs in the queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $priority = $this->option('priority');
        $dryRun = $this->option('dry-run');

        $this->info('🚀 بدء معالجة طابور الإيميلات...');

        // Get pending emails
        $query = EmailJob::readyToSend();

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        // Order by priority and creation time
        $priorityOrder = "CASE
            WHEN priority = 'urgent' THEN 1
            WHEN priority = 'high' THEN 2
            WHEN priority = 'normal' THEN 3
            WHEN priority = 'low' THEN 4
            ELSE 5 END";

        $emailJobs = $query->orderByRaw($priorityOrder)
                          ->orderBy('created_at')
                          ->limit($limit)
                          ->get();

        if ($emailJobs->isEmpty()) {
            $this->info('✅ لا توجد إيميلات في الانتظار للمعالجة');
            return 0;
        }

        $this->info("📧 تم العثور على {$emailJobs->count()} إيميل للمعالجة");

        if ($dryRun) {
            $this->warn('🔍 وضع المعاينة - لن يتم إرسال الإيميلات فعلياً');
            $this->table(
                ['ID', 'النوع', 'المستقبل', 'الأولوية', 'المجدول في'],
                $emailJobs->map(function ($job) {
                    return [
                        $job->id,
                        $job->email_type,
                        $job->recipient_email,
                        $job->priority,
                        $job->scheduled_at ? $job->scheduled_at->format('Y-m-d H:i') : 'فوري'
                    ];
                })
            );
            return 0;
        }

        $processed = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($emailJobs->count());
        $progressBar->start();

        foreach ($emailJobs as $emailJob) {
            try {
                // Dispatch to appropriate queue based on priority
                $queueName = $this->getQueueName($emailJob->priority);

                $job = new SendEmailJob($emailJob->id);
                Queue::push($job, '', $queueName);

                $processed++;

            } catch (\Exception $e) {
                $this->error("\n❌ فشل في معالجة الإيميل {$emailJob->id}: {$e->getMessage()}");
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ تم معالجة {$processed} إيميل بنجاح");
        if ($failed > 0) {
            $this->error("❌ فشل في معالجة {$failed} إيميل");
        }

        // Show queue status
        $this->showQueueStatus();

        return 0;
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
     * Show current queue status
     */
    private function showQueueStatus()
    {
        $this->newLine();
        $this->info('📊 حالة الطابور الحالية:');

        $stats = [
            'في الانتظار' => EmailJob::where('status', 'pending')->count(),
            'قيد المعالجة' => EmailJob::where('status', 'processing')->count(),
            'مرسل اليوم' => EmailJob::where('status', 'sent')->whereDate('sent_at', today())->count(),
            'فشل اليوم' => EmailJob::where('status', 'failed')->whereDate('failed_at', today())->count(),
        ];

        foreach ($stats as $label => $count) {
            $this->line("  {$label}: {$count}");
        }

        // Priority breakdown
        $this->newLine();
        $this->info('📋 توزيع الأولويات:');

        $priorities = EmailJob::where('status', 'pending')
                             ->groupBy('priority')
                             ->selectRaw('priority, count(*) as count')
                             ->pluck('count', 'priority');

        foreach (['urgent', 'high', 'normal', 'low'] as $priority) {
            $count = $priorities[$priority] ?? 0;
            $this->line("  {$priority}: {$count}");
        }
    }
}
