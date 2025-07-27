<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmailJob;
use App\Services\EmailQueueService;
use Carbon\Carbon;

class SendDigestEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:send-digest
                            {--type=daily : Type of digest (daily, weekly)}
                            {--dry-run : Show what would be sent without actually sending}
                            {--force : Force send even if not the right time}';

    /**
     * The console command description.
     */
    protected $description = 'Send digest emails to users based on their preferences';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("📧 بدء إرسال الملخص {$type}...");

        // Get users who should receive digest
        $users = $this->getUsersForDigest($type, $force);

        if ($users->isEmpty()) {
            $this->info('✅ لا يوجد مستخدمين لإرسال الملخص لهم في هذا الوقت');
            return 0;
        }

        $this->info("👥 تم العثور على {$users->count()} مستخدم لإرسال الملخص");

        if ($dryRun) {
            $this->warn('🔍 وضع المعاينة - لن يتم إرسال الإيميلات فعلياً');
            $this->table(
                ['الاسم', 'البريد الإلكتروني', 'تكرار الإشعارات', 'الوقت المفضل'],
                $users->map(function ($user) {
                    return [
                        $user->name,
                        $user->email,
                        $user->formatted_notification_frequency,
                        $user->preferred_email_time ? $user->preferred_email_time->format('H:i') : 'غير محدد'
                    ];
                })
            );
            return 0;
        }

        $sent = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                $digestData = $this->prepareDigestData($user, $type);

                if (empty($digestData['notifications'])) {
                    // Skip if no notifications to digest
                    $progressBar->advance();
                    continue;
                }

                // Queue digest email
                EmailQueueService::queueEmail(
                    \App\Mail\DigestMail::class,
                    $user,
                    'digest_' . $type,
                    $digestData,
                    [
                        'priority' => 'low',
                        'track_opens' => $user->allow_email_tracking,
                        'track_clicks' => $user->allow_click_tracking
                    ]
                );

                $sent++;

            } catch (\Exception $e) {
                $this->error("\n❌ فشل في إرسال الملخص للمستخدم {$user->email}: {$e->getMessage()}");
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ تم إرسال {$sent} ملخص بنجاح");
        if ($failed > 0) {
            $this->error("❌ فشل في إرسال {$failed} ملخص");
        }

        return 0;
    }

    /**
     * Get users who should receive digest emails
     */
    private function getUsersForDigest($type, $force)
    {
        $query = User::where('email_notifications_enabled', true)
                    ->where('notification_frequency', $type)
                    ->whereNotNull('email');

        if (!$force) {
            // Check if it's the right time for each user
            $query->where(function ($q) use ($type) {
                if ($type === 'daily') {
                    // Send daily digest at user's preferred time or 9 AM default
                    $q->whereRaw('TIME(NOW()) = COALESCE(preferred_email_time, "09:00:00")');
                } else {
                    // Send weekly digest on Sunday at preferred time
                    $q->whereRaw('DAYOFWEEK(NOW()) = 1') // Sunday
                      ->whereRaw('TIME(NOW()) = COALESCE(preferred_email_time, "09:00:00")');
                }
            });
        }

        return $query->get();
    }

    /**
     * Prepare digest data for a user
     */
    private function prepareDigestData($user, $type)
    {
        $startDate = $type === 'daily'
            ? Carbon::yesterday()
            : Carbon::now()->subWeek();

        $endDate = Carbon::now();

        // Get notifications for this user in the time period
        $notifications = EmailJob::where('recipient_email', $user->email)
                                ->where('status', 'sent')
                                ->whereBetween('sent_at', [$startDate, $endDate])
                                ->orderBy('sent_at', 'desc')
                                ->get();

        // Group by email type
        $groupedNotifications = $notifications->groupBy('email_type');

        // Prepare summary data
        $summary = [
            'total_emails' => $notifications->count(),
            'opened_emails' => $notifications->whereNotNull('opened_at')->count(),
            'clicked_emails' => $notifications->whereNotNull('clicked_at')->count(),
            'by_type' => $groupedNotifications->map->count(),
        ];

        return [
            'user' => $user,
            'type' => $type,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'label' => $type === 'daily' ? 'أمس' : 'الأسبوع الماضي'
            ],
            'notifications' => $notifications,
            'grouped_notifications' => $groupedNotifications,
            'summary' => $summary
        ];
    }
}
