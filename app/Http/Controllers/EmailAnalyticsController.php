<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailAnalyticsService;
use App\Services\EmailQueueService;
use App\Models\EmailJob;
use Carbon\Carbon;

class EmailAnalyticsController extends Controller
{
    /**
     * Display email analytics dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $filters = [
            'email_type' => $request->get('email_type'),
            'status' => $request->get('status'),
            'priority' => $request->get('priority')
        ];

        $analytics = EmailAnalyticsService::getAnalytics($startDate, $endDate, $filters);
        $realTimeStats = EmailAnalyticsService::getRealTimeStats();
        $queueStats = EmailQueueService::getQueueStats();
        $topPerforming = EmailAnalyticsService::getTopPerformingEmails($startDate, $endDate);

        return view('admin.email-analytics.index', compact(
            'analytics',
            'realTimeStats',
            'queueStats',
            'topPerforming',
            'startDate',
            'endDate',
            'filters'
        ));
    }

    /**
     * Get analytics data as JSON for AJAX requests
     */
    public function getData(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $filters = [
            'email_type' => $request->get('email_type'),
            'status' => $request->get('status'),
            'priority' => $request->get('priority')
        ];

        $analytics = EmailAnalyticsService::getAnalytics($startDate, $endDate, $filters);

        return response()->json($analytics);
    }

    /**
     * Get real-time statistics
     */
    public function getRealTimeStats()
    {
        $stats = EmailAnalyticsService::getRealTimeStats();
        return response()->json($stats);
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats()
    {
        $stats = EmailQueueService::getQueueStats();
        return response()->json($stats);
    }

    /**
     * Get email jobs list
     */
    public function getEmailJobs(Request $request)
    {
        $query = EmailJob::with(['user', 'notification']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('email_type')) {
            $query->where('email_type', $request->get('email_type'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        // Date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->get('start_date'),
                $request->get('end_date')
            ]);
        }

        $emailJobs = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 25));

        return response()->json($emailJobs);
    }

    /**
     * Retry failed email
     */
    public function retryEmail($emailJobId)
    {
        $success = EmailQueueService::retryEmail($emailJobId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'تم إعادة جدولة الإيميل بنجاح'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في إعادة جدولة الإيميل'
        ], 400);
    }

    /**
     * Cancel pending email
     */
    public function cancelEmail($emailJobId)
    {
        $success = EmailQueueService::cancelEmail($emailJobId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الإيميل بنجاح'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في إلغاء الإيميل'
        ], 400);
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats($campaignId)
    {
        $stats = EmailQueueService::getCampaignStats($campaignId);
        return response()->json($stats);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $filters = [
            'email_type' => $request->get('email_type'),
            'status' => $request->get('status'),
            'priority' => $request->get('priority')
        ];

        $analytics = EmailAnalyticsService::getAnalytics($startDate, $endDate, $filters);

        // Generate CSV
        $filename = 'email_analytics_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($analytics) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Metric',
                'Value',
                'Percentage'
            ]);

            // Overview data
            foreach ($analytics['overview'] as $key => $value) {
                fputcsv($file, [
                    ucfirst(str_replace('_', ' ', $key)),
                    $value,
                    is_numeric($value) && strpos($key, 'rate') !== false ? $value . '%' : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
