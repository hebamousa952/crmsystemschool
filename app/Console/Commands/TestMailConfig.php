<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailConfigService;

class TestMailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test
                            {--provider=primary : Mail provider to test (primary, backup, gmail, outlook)}
                            {--email= : Test email address}';

    /**
     * The console command description.
     */
    protected $description = 'Test mail configuration and send test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->option('provider');
        $testEmail = $this->option('email');

        $this->info("🔧 اختبار إعدادات البريد الإلكتروني...");
        $this->info("📧 المزود: {$provider}");

        // Get available providers
        $providers = MailConfigService::getAvailableProviders();

        if (!isset($providers[$provider])) {
            $this->error("❌ مزود غير صحيح: {$provider}");
            $this->info("المزودين المتاحين:");
            foreach ($providers as $key => $info) {
                $this->line("  - {$key}: {$info['name']} ({$info['status']})");
            }
            return 1;
        }

        // Ask for email if not provided
        if (!$testEmail) {
            $testEmail = $this->ask('📧 أدخل البريد الإلكتروني للاختبار');
        }

        if (!$testEmail) {
            $this->error("❌ يجب إدخال بريد إلكتروني للاختبار");
            return 1;
        }

        // Test configuration
        $this->info("🚀 جاري إرسال رسالة اختبار...");

        $result = MailConfigService::testMailConfig($provider, $testEmail);

        if ($result['success']) {
            $this->info("✅ {$result['message']}");
            $this->info("📧 تم الإرسال إلى: {$testEmail}");
            $this->info("🔧 المزود المستخدم: {$result['provider']}");

            // Show school info
            $schoolInfo = MailConfigService::getSchoolInfo();
            $this->info("\n📋 معلومات المدرسة:");
            $this->line("  اسم المدرسة: {$schoolInfo['name']}");
            $this->line("  البريد الإلكتروني: {$schoolInfo['email']}");
            $this->line("  الهاتف: {$schoolInfo['phone']}");

        } else {
            $this->error("❌ {$result['message']}");

            // Suggest trying backup
            if ($provider === 'primary') {
                $this->warn("💡 جرب الخادم الاحتياطي: php artisan mail:test --provider=backup --email={$testEmail}");
            }

            return 1;
        }

        return 0;
    }
}
