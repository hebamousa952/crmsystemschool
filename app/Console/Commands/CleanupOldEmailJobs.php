<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailJob;
use App\Services\EmailQueueService;
use Carbon\Carbon;

class CleanupOldEmailJobs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:cleanup
                            {--days=30 : Number of days to keep email jobs}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old email jobs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🧹 بدء تنظيف سجلات الإيميلات الأقدم من {$days} يوم...");

        $cutoffDate = Carbon::now()->subDays($days);

        // Get jobs to be deleted
        $query = EmailJob::where('created_at', '<', $cutoffDate)
                         ->whereIn('status', ['sent', 'failed', 'cancelled']);

        $totalJobs = $query->count();

        if ($totalJobs === 0) {
            $this->info('✅ لا توجد سجلات قديمة للحذف');
            return 0;
        }

        // Show breakdown by status
        $breakdown = $query->groupBy('status')
                          ->selectRaw('status, count(*) as count')
                          ->pluck('count', 'status');

        $this->info("📊 تم العثور على {$totalJobs} سجل للحذف:");
        foreach ($breakdown as $status => $count) {
            $this->line("  {$status}: {$count}");
        }

        if ($dryRun) {
            $this->warn('🔍 وضع المعاينة - لن يتم حذف أي سجلات فعلياً');

            // Show sample of what would be deleted
            $sampleJobs = $query->limit(10)->get(['id', 'email_type', 'recipient_email', 'status', 'created_at']);

            $this->table(
                ['ID', 'النوع', 'المستقبل', 'الحالة', 'تاريخ الإنشاء'],
                $sampleJobs->map(function ($job) {
                    return [
                        $job->id,
                        $job->email_type,
                        $job->recipient_email,
                        $job->status,
                        $job->created_at->format('Y-m-d H:i')
                    ];
                })
            );

            if ($totalJobs > 10) {
                $remaining = $totalJobs - 10;
                $this->line("... و {$remaining} سجل آخر");
            }

            return 0;
        }

        // Confirmation
        if (!$force) {
            if (!$this->confirm("هل أنت متأكد من حذف {$totalJobs} سجل؟")) {
                $this->info('تم إلغاء العملية');
                return 0;
            }
        }

        // Perform cleanup
        $this->info('🗑️ جاري الحذف...');

        $progressBar = $this->output->createProgressBar($totalJobs);
        $progressBar->start();

        $deleted = 0;
        $batchSize = 1000;

        // Delete in batches to avoid memory issues
        do {
            $batch = EmailJob::where('created_at', '<', $cutoffDate)
                            ->whereIn('status', ['sent', 'failed', 'cancelled'])
                            ->limit($batchSize)
                            ->get();

            if ($batch->isEmpty()) {
                break;
            }

            foreach ($batch as $job) {
                $job->delete();
                $deleted++;
                $progressBar->advance();
            }

        } while ($batch->count() === $batchSize);

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ تم حذف {$deleted} سجل بنجاح");

        // Show remaining statistics
        $this->showRemainingStats();

        return 0;
    }

    /**
     * Show remaining email job statistics
     */
    private function showRemainingStats()
    {
        $this->newLine();
        $this->info('📊 الإحصائيات المتبقية:');

        $stats = [
            'إجمالي السجلات' => EmailJob::count(),
            'في الانتظار' => EmailJob::where('status', 'pending')->count(),
            'قيد المعالجة' => EmailJob::where('status', 'processing')->count(),
            'مرسل' => EmailJob::where('status', 'sent')->count(),
            'فشل' => EmailJob::where('status', 'failed')->count(),
            'ملغي' => EmailJob::where('status', 'cancelled')->count(),
        ];

        foreach ($stats as $label => $count) {
            $this->line("  {$label}: {$count}");
        }

        // Show oldest and newest records
        $oldest = EmailJob::orderBy('created_at')->first();
        $newest = EmailJob::orderBy('created_at', 'desc')->first();

        if ($oldest && $newest) {
            $this->newLine();
            $this->info('📅 نطاق التواريخ:');
            $this->line("  الأقدم: {$oldest->created_at->format('Y-m-d H:i')}");
            $this->line("  الأحدث: {$newest->created_at->format('Y-m-d H:i')}");
        }

        // Show disk space saved (approximate)
        $avgRecordSize = 2; // KB per record (approximate)
        $spaceSaved = $this->argument('deleted') * $avgRecordSize;

        if ($spaceSaved > 1024) {
            $spaceSaved = round($spaceSaved / 1024, 2) . ' MB';
        } else {
            $spaceSaved = $spaceSaved . ' KB';
        }

        $this->info("💾 مساحة تقريبية تم توفيرها: {$spaceSaved}");
    }
}
