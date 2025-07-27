<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmailPreferencesController extends Controller
{
    /**
     * Display email preferences page
     */
    public function index()
    {
        $user = Auth::user();

        return view('profile.email-preferences', compact('user'));
    }

    /**
     * Update email preferences
     */
    public function update(Request $request)
    {
        $request->validate([
            'email_notifications_enabled' => 'boolean',
            'newsletter_subscribed' => 'boolean',
            'notification_frequency' => 'in:immediate,daily,weekly,never',
            'preferred_email_time' => 'nullable|date_format:H:i',
            'timezone' => 'string|max:50',
            'allow_email_tracking' => 'boolean',
            'allow_click_tracking' => 'boolean',
            'preferences' => 'array',
            'preferences.*' => 'boolean'
        ]);

        $user = Auth::user();

        $user->update([
            'email_notifications_enabled' => $request->boolean('email_notifications_enabled'),
            'newsletter_subscribed' => $request->boolean('newsletter_subscribed'),
            'notification_frequency' => $request->notification_frequency ?? 'immediate',
            'preferred_email_time' => $request->preferred_email_time,
            'timezone' => $request->timezone ?? 'Africa/Cairo',
            'allow_email_tracking' => $request->boolean('allow_email_tracking'),
            'allow_click_tracking' => $request->boolean('allow_click_tracking'),
            'email_preferences' => $request->preferences ?? [],
            'email_preferences_updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث تفضيلات البريد الإلكتروني بنجاح'
        ]);
    }

    /**
     * Get email preferences as JSON
     */
    public function getPreferences()
    {
        $user = Auth::user();

        return response()->json([
            'email_notifications_enabled' => $user->email_notifications_enabled,
            'newsletter_subscribed' => $user->newsletter_subscribed,
            'notification_frequency' => $user->notification_frequency,
            'preferred_email_time' => $user->preferred_email_time,
            'timezone' => $user->timezone,
            'allow_email_tracking' => $user->allow_email_tracking,
            'allow_click_tracking' => $user->allow_click_tracking,
            'preferences' => $user->email_preferences,
            'last_updated' => $user->email_preferences_updated_at
        ]);
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeNewsletter(Request $request)
    {
        $user = Auth::user();
        $user->subscribeToNewsletter();

        return response()->json([
            'success' => true,
            'message' => 'تم الاشتراك في النشرة الإخبارية بنجاح'
        ]);
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribeNewsletter(Request $request)
    {
        $user = Auth::user();
        $user->unsubscribeFromNewsletter();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الاشتراك في النشرة الإخبارية'
        ]);
    }

    /**
     * Unsubscribe from all emails (public route with token)
     */
    public function unsubscribeAll($token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            abort(404, 'رابط إلغاء الاشتراك غير صحيح');
        }

        $user->unsubscribeFromAll();

        return view('emails.unsubscribed', compact('user'));
    }

    /**
     * Resubscribe to emails (public route with token)
     */
    public function resubscribe($token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            abort(404, 'رابط إعادة الاشتراك غير صحيح');
        }

        $user->update([
            'email_notifications_enabled' => true,
            'email_preferences_updated_at' => now()
        ]);

        return view('emails.resubscribed', compact('user'));
    }

    /**
     * Email preferences management page (public with token)
     */
    public function managePreferences($token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            abort(404, 'رابط إدارة التفضيلات غير صحيح');
        }

        return view('emails.manage-preferences', compact('user'));
    }

    /**
     * Update preferences via public link
     */
    public function updatePublicPreferences(Request $request, $token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            abort(404, 'رابط إدارة التفضيلات غير صحيح');
        }

        $request->validate([
            'email_notifications_enabled' => 'boolean',
            'newsletter_subscribed' => 'boolean',
            'notification_frequency' => 'in:immediate,daily,weekly,never',
            'preferences' => 'array',
            'preferences.*' => 'boolean'
        ]);

        $user->update([
            'email_notifications_enabled' => $request->boolean('email_notifications_enabled'),
            'newsletter_subscribed' => $request->boolean('newsletter_subscribed'),
            'notification_frequency' => $request->notification_frequency ?? 'immediate',
            'email_preferences' => $request->preferences ?? [],
            'email_preferences_updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث تفضيلاتك بنجاح'
        ]);
    }

    /**
     * Test email preferences
     */
    public function testPreferences(Request $request)
    {
        $user = Auth::user();

        // Send test email based on current preferences
        try {
            \Mail::to($user->email)->send(new \App\Mail\TestPreferencesMail($user));

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال إيميل اختبار بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في إرسال إيميل الاختبار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email statistics for user
     */
    public function getEmailStats()
    {
        $user = Auth::user();

        $stats = [
            'total_emails_sent' => \App\Models\EmailJob::where('recipient_email', $user->email)->count(),
            'emails_opened' => \App\Models\EmailJob::where('recipient_email', $user->email)
                                                  ->whereNotNull('opened_at')
                                                  ->count(),
            'emails_clicked' => \App\Models\EmailJob::where('recipient_email', $user->email)
                                                   ->whereNotNull('clicked_at')
                                                   ->count(),
            'last_email_sent' => \App\Models\EmailJob::where('recipient_email', $user->email)
                                                    ->where('status', 'sent')
                                                    ->latest('sent_at')
                                                    ->first()?->sent_at,
            'preferences_last_updated' => $user->email_preferences_updated_at
        ];

        return response()->json($stats);
    }
}
