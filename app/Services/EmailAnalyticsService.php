<?php

namespace App\Services;

use App\Models\EmailJob;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsService
{
    /**
     * Get comprehensive email analytics
     */
    public static function getAnalytics($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subDays(30);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['email_type'])) {
            $query->where('email_type', $filters['email_type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $totalEmails = $query->count();
        $sentEmails = $query->where('status', 'sent')->count();
        $openedEmails = $query->whereNotNull('opened_at')->count();
        $clickedEmails = $query->whereNotNull('clicked_at')->count();

        return [
            'overview' => [
                'total_emails' => $totalEmails,
                'sent_emails' => $sentEmails,
                'pending_emails' => $query->where('status', 'pending')->count(),
                'failed_emails' => $query->where('status', 'failed')->count(),
                'delivery_rate' => $totalEmails > 0 ? round(($sentEmails / $totalEmails) * 100, 2) : 0,
                'open_rate' => $sentEmails > 0 ? round(($openedEmails / $sentEmails) * 100, 2) : 0,
                'click_rate' => $sentEmails > 0 ? round(($clickedEmails / $sentEmails) * 100, 2) : 0,
                'click_to_open_rate' => $openedEmails > 0 ? round(($clickedEmails / $openedEmails) * 100, 2) : 0
            ],
            'daily_stats' => static::getDailyStats($startDate, $endDate, $filters),
            'email_types' => static::getEmailTypeStats($startDate, $endDate, $filters),
            'priority_stats' => static::getPriorityStats($startDate, $endDate, $filters),
            'provider_stats' => static::getProviderStats($startDate, $endDate, $filters),
            'engagement_stats' => static::getEngagementStats($startDate, $endDate, $filters),
            'performance_stats' => static::getPerformanceStats($startDate, $endDate, $filters)
        ];
    }

    /**
     * Get daily statistics
     */
    public static function getDailyStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply filters
        static::applyFilters($query, $filters);

        return $query->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->open_rate = $item->sent > 0 ? round(($item->opened / $item->sent) * 100, 2) : 0;
                $item->click_rate = $item->sent > 0 ? round(($item->clicked / $item->sent) * 100, 2) : 0;
                return $item;
            });
    }

    /**
     * Get email type statistics
     */
    public static function getEmailTypeStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate]);
        static::applyFilters($query, $filters);

        return $query->selectRaw('
                email_type,
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked,
                AVG(processing_time_ms) as avg_processing_time
            ')
            ->groupBy('email_type')
            ->get()
            ->map(function ($item) {
                $item->open_rate = $item->sent > 0 ? round(($item->opened / $item->sent) * 100, 2) : 0;
                $item->click_rate = $item->sent > 0 ? round(($item->clicked / $item->sent) * 100, 2) : 0;
                $item->avg_processing_time = round($item->avg_processing_time, 2);
                return $item;
            });
    }

    /**
     * Get priority statistics
     */
    public static function getPriorityStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate]);
        static::applyFilters($query, $filters);

        return $query->selectRaw('
                priority,
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                AVG(processing_time_ms) as avg_processing_time
            ')
            ->groupBy('priority')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0 ? round(($item->sent / $item->total) * 100, 2) : 0;
                $item->avg_processing_time = round($item->avg_processing_time, 2);
                return $item;
            });
    }

    /**
     * Get SMTP provider statistics
     */
    public static function getProviderStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotNull('smtp_provider');
        static::applyFilters($query, $filters);

        return $query->selectRaw('
                smtp_provider,
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                AVG(processing_time_ms) as avg_processing_time
            ')
            ->groupBy('smtp_provider')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0 ? round(($item->sent / $item->total) * 100, 2) : 0;
                $item->failure_rate = $item->total > 0 ? round(($item->failed / $item->total) * 100, 2) : 0;
                $item->avg_processing_time = round($item->avg_processing_time, 2);
                return $item;
            });
    }

    /**
     * Get engagement statistics
     */
    public static function getEngagementStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'sent');
        static::applyFilters($query, $filters);

        $totalSent = $query->count();
        $opened = $query->whereNotNull('opened_at')->count();
        $clicked = $query->whereNotNull('clicked_at')->count();

        // Time to open analysis
        $timeToOpen = $query->whereNotNull('opened_at')
                           ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, sent_at, opened_at)) as avg_minutes')
                           ->first();

        // Best performing times
        $bestHours = $query->whereNotNull('opened_at')
                          ->selectRaw('HOUR(sent_at) as hour, COUNT(*) as opens')
                          ->groupBy('hour')
                          ->orderBy('opens', 'desc')
                          ->limit(5)
                          ->get();

        // Best performing days
        $bestDays = $query->whereNotNull('opened_at')
                         ->selectRaw('DAYNAME(sent_at) as day, COUNT(*) as opens')
                         ->groupBy('day')
                         ->orderBy('opens', 'desc')
                         ->get();

        return [
            'total_sent' => $totalSent,
            'total_opened' => $opened,
            'total_clicked' => $clicked,
            'unique_opens' => $opened, // Since we track per email job
            'unique_clicks' => $clicked,
            'avg_time_to_open_minutes' => $timeToOpen ? round($timeToOpen->avg_minutes, 2) : 0,
            'best_hours' => $bestHours,
            'best_days' => $bestDays,
            'engagement_score' => static::calculateEngagementScore($totalSent, $opened, $clicked)
        ];
    }

    /**
     * Get performance statistics
     */
    public static function getPerformanceStats($startDate, $endDate, $filters = [])
    {
        $query = EmailJob::whereBetween('created_at', [$startDate, $endDate]);
        static::applyFilters($query, $filters);

        $avgProcessingTime = $query->whereNotNull('processing_time_ms')
                                  ->avg('processing_time_ms');

        $slowestEmails = $query->whereNotNull('processing_time_ms')
                              ->orderBy('processing_time_ms', 'desc')
                              ->limit(10)
                              ->get(['email_type', 'processing_time_ms', 'recipient_email']);

        $fastestEmails = $query->whereNotNull('processing_time_ms')
                              ->orderBy('processing_time_ms', 'asc')
                              ->limit(10)
                              ->get(['email_type', 'processing_time_ms', 'recipient_email']);

        return [
            'avg_processing_time_ms' => round($avgProcessingTime, 2),
            'avg_processing_time_seconds' => round($avgProcessingTime / 1000, 2),
            'slowest_emails' => $slowestEmails,
            'fastest_emails' => $fastestEmails,
            'performance_grade' => static::calculatePerformanceGrade($avgProcessingTime)
        ];
    }

    /**
     * Get real-time statistics
     */
    public static function getRealTimeStats()
    {
        return [
            'emails_in_queue' => EmailJob::where('status', 'pending')->count(),
            'emails_processing' => EmailJob::where('status', 'processing')->count(),
            'emails_sent_today' => EmailJob::where('status', 'sent')
                                          ->whereDate('sent_at', today())
                                          ->count(),
            'emails_failed_today' => EmailJob::where('status', 'failed')
                                            ->whereDate('failed_at', today())
                                            ->count(),
            'emails_opened_today' => EmailJob::whereDate('opened_at', today())->count(),
            'emails_clicked_today' => EmailJob::whereDate('clicked_at', today())->count(),
            'queue_health' => static::getQueueHealth()
        ];
    }

    /**
     * Get top performing emails
     */
    public static function getTopPerformingEmails($startDate, $endDate, $limit = 10)
    {
        return EmailJob::whereBetween('created_at', [$startDate, $endDate])
                      ->where('status', 'sent')
                      ->selectRaw('
                          email_type,
                          subject,
                          COUNT(*) as total_sent,
                          SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opens,
                          SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicks
                      ')
                      ->groupBy('email_type', 'subject')
                      ->having('total_sent', '>=', 5) // Minimum 5 emails for statistical significance
                      ->get()
                      ->map(function ($item) {
                          $item->open_rate = round(($item->opens / $item->total_sent) * 100, 2);
                          $item->click_rate = round(($item->clicks / $item->total_sent) * 100, 2);
                          $item->engagement_score = ($item->open_rate * 0.6) + ($item->click_rate * 0.4);
                          return $item;
                      })
                      ->sortByDesc('engagement_score')
                      ->take($limit);
    }

    /**
     * Apply filters to query
     */
    private static function applyFilters($query, $filters)
    {
        if (!empty($filters['email_type'])) {
            $query->where('email_type', $filters['email_type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
    }

    /**
     * Calculate engagement score
     */
    private static function calculateEngagementScore($sent, $opened, $clicked)
    {
        if ($sent == 0) return 0;
        
        $openRate = ($opened / $sent) * 100;
        $clickRate = ($clicked / $sent) * 100;
        
        // Weighted score: 60% open rate, 40% click rate
        return round(($openRate * 0.6) + ($clickRate * 0.4), 2);
    }

    /**
     * Calculate performance grade
     */
    private static function calculatePerformanceGrade($avgProcessingTime)
    {
        if ($avgProcessingTime < 1000) return 'A+'; // Under 1 second
        if ($avgProcessingTime < 3000) return 'A';  // Under 3 seconds
        if ($avgProcessingTime < 5000) return 'B';  // Under 5 seconds
        if ($avgProcessingTime < 10000) return 'C'; // Under 10 seconds
        return 'D'; // Over 10 seconds
    }

    /**
     * Get queue health status
     */
    private static function getQueueHealth()
    {
        $pending = EmailJob::where('status', 'pending')->count();
        $processing = EmailJob::where('status', 'processing')->count();
        $recentFailures = EmailJob::where('status', 'failed')
                                 ->where('failed_at', '>=', now()->subHour())
                                 ->count();

        if ($recentFailures > 10) return 'critical';
        if ($pending > 100) return 'warning';
        if ($processing > 0) return 'active';
        return 'healthy';
    }
}
