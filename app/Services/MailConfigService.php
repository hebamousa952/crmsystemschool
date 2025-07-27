<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    /**
     * Configure mail settings dynamically
     */
    public static function configureMail($provider = 'primary')
    {
        try {
            $config = self::getMailConfig($provider);
            
            // Set mail configuration
            Config::set('mail.mailers.smtp', $config);
            Config::set('mail.from.address', $config['from']['address']);
            Config::set('mail.from.name', $config['from']['name']);
            
            // Purge mail manager to apply new config
            Mail::purge('smtp');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to configure mail', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get mail configuration for different providers
     */
    private static function getMailConfig($provider)
    {
        $configs = [
            'primary' => [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'port' => env('MAIL_PORT', 587),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
                'from' => [
                    'address' => env('MAIL_FROM_ADDRESS', 'noreply@schoolsystem.com'),
                    'name' => env('MAIL_FROM_NAME', 'نظام إدارة المدرسة'),
                ]
            ],
            'backup' => [
                'transport' => 'smtp',
                'host' => env('BACKUP_MAIL_HOST', 'smtp.outlook.com'),
                'port' => env('BACKUP_MAIL_PORT', 587),
                'encryption' => 'tls',
                'username' => env('BACKUP_MAIL_USERNAME'),
                'password' => env('BACKUP_MAIL_PASSWORD'),
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
                'from' => [
                    'address' => env('BACKUP_MAIL_USERNAME', 'backup@outlook.com'),
                    'name' => env('MAIL_FROM_NAME', 'نظام إدارة المدرسة'),
                ]
            ],
            'gmail' => [
                'transport' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => env('GMAIL_USERNAME'),
                'password' => env('GMAIL_PASSWORD'),
                'timeout' => null,
                'from' => [
                    'address' => env('GMAIL_USERNAME'),
                    'name' => env('MAIL_FROM_NAME', 'نظام إدارة المدرسة'),
                ]
            ],
            'outlook' => [
                'transport' => 'smtp',
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => env('OUTLOOK_USERNAME'),
                'password' => env('OUTLOOK_PASSWORD'),
                'timeout' => null,
                'from' => [
                    'address' => env('OUTLOOK_USERNAME'),
                    'name' => env('MAIL_FROM_NAME', 'نظام إدارة المدرسة'),
                ]
            ]
        ];

        return $configs[$provider] ?? $configs['primary'];
    }

    /**
     * Test mail configuration
     */
    public static function testMailConfig($provider = 'primary', $testEmail = null)
    {
        try {
            // Configure mail
            self::configureMail($provider);
            
            $testEmail = $testEmail ?: env('MAIL_USERNAME');
            
            if (!$testEmail) {
                throw new \Exception('No test email provided');
            }

            // Send test email
            Mail::raw('هذا اختبار لإعدادات البريد الإلكتروني. إذا وصلتك هذه الرسالة، فإن الإعدادات تعمل بشكل صحيح.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('اختبار إعدادات البريد الإلكتروني - نظام إدارة المدرسة');
            });

            Log::info('Mail test successful', [
                'provider' => $provider,
                'test_email' => $testEmail
            ]);

            return [
                'success' => true,
                'message' => 'تم إرسال الاختبار بنجاح',
                'provider' => $provider
            ];

        } catch (\Exception $e) {
            Log::error('Mail test failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'فشل في إرسال الاختبار: ' . $e->getMessage(),
                'provider' => $provider
            ];
        }
    }

    /**
     * Get school information for email templates
     */
    public static function getSchoolInfo()
    {
        return [
            'name' => env('SCHOOL_NAME', 'مدرسة المستقبل النموذجية'),
            'logo_url' => env('SCHOOL_LOGO_URL', 'https://via.placeholder.com/200x80?text=School+Logo'),
            'address' => env('SCHOOL_ADDRESS', 'شارع التعليم، المدينة، المحافظة'),
            'phone' => env('SCHOOL_PHONE', '01234567890'),
            'email' => env('SCHOOL_EMAIL', 'info@schoolsystem.com'),
            'website' => env('SCHOOL_WEBSITE', 'https://yourschool.com'),
            'colors' => [
                'primary' => '#2563eb',
                'secondary' => '#64748b',
                'success' => '#059669',
                'warning' => '#d97706',
                'danger' => '#dc2626'
            ]
        ];
    }

    /**
     * Switch to backup mail provider
     */
    public static function switchToBackup()
    {
        return self::configureMail('backup');
    }

    /**
     * Get available mail providers
     */
    public static function getAvailableProviders()
    {
        return [
            'primary' => [
                'name' => 'الخادم الأساسي',
                'host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'status' => self::checkProviderStatus('primary')
            ],
            'backup' => [
                'name' => 'الخادم الاحتياطي',
                'host' => env('BACKUP_MAIL_HOST', 'smtp.outlook.com'),
                'status' => self::checkProviderStatus('backup')
            ],
            'gmail' => [
                'name' => 'Gmail',
                'host' => 'smtp.gmail.com',
                'status' => env('GMAIL_USERNAME') ? 'configured' : 'not_configured'
            ],
            'outlook' => [
                'name' => 'Outlook',
                'host' => 'smtp-mail.outlook.com',
                'status' => env('OUTLOOK_USERNAME') ? 'configured' : 'not_configured'
            ]
        ];
    }

    /**
     * Check provider status
     */
    private static function checkProviderStatus($provider)
    {
        $config = self::getMailConfig($provider);
        
        if (empty($config['username']) || empty($config['password'])) {
            return 'not_configured';
        }
        
        // Could add actual connectivity test here
        return 'configured';
    }

    /**
     * Get email tracking settings
     */
    public static function getTrackingSettings()
    {
        return [
            'enabled' => env('EMAIL_TRACKING_ENABLED', true),
            'open_tracking' => env('EMAIL_OPEN_TRACKING', true),
            'click_tracking' => env('EMAIL_CLICK_TRACKING', true),
            'tracking_domain' => env('EMAIL_TRACKING_DOMAIN', request()->getHost())
        ];
    }

    /**
     * Generate tracking pixel URL
     */
    public static function generateTrackingPixel($notificationId)
    {
        return route('email.track.open', [
            'notification' => $notificationId,
            'token' => hash('sha256', $notificationId . config('app.key'))
        ]);
    }

    /**
     * Generate click tracking URL
     */
    public static function generateClickTrackingUrl($originalUrl, $notificationId)
    {
        return route('email.track.click', [
            'notification' => $notificationId,
            'url' => base64_encode($originalUrl),
            'token' => hash('sha256', $notificationId . $originalUrl . config('app.key'))
        ]);
    }
}
