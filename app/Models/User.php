<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_preferences',
        'email_notifications_enabled',
        'newsletter_subscribed',
        'email_preferences_updated_at',
        'notification_frequency',
        'preferred_email_time',
        'timezone',
        'allow_email_tracking',
        'allow_click_tracking',
        'unsubscribe_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that are guarded from mass assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_preferences' => 'array',
        'email_notifications_enabled' => 'boolean',
        'newsletter_subscribed' => 'boolean',
        'email_preferences_updated_at' => 'datetime',
        'preferred_email_time' => 'datetime:H:i',
        'allow_email_tracking' => 'boolean',
        'allow_click_tracking' => 'boolean'
    ];

    // Accessor: تنسيق الدور
    public function getFormattedRoleAttribute()
    {
        $roles = [
            'admin' => 'مدير النظام',
            'teacher' => 'مدرس',
            'accountant' => 'محاسب',
            'secretary' => 'سكرتير'
        ];

        return $roles[$this->role] ?? $this->role;
    }

    // Scope: المديرين
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Scope: المدرسين
    public function scopeTeachers($query)
    {
        return $query->where('role', 'teacher');
    }

    // ==================== Email Preferences Methods ====================

    /**
     * Get default email preferences
     */
    public function getDefaultEmailPreferences()
    {
        return [
            'student_created' => true,
            'payment_received' => true,
            'grade_report' => true,
            'attendance_alert' => true,
            'fee_reminder' => true,
            'emergency_notification' => true,
            'newsletter' => false,
            'system_updates' => true,
            'bulk_notifications' => true
        ];
    }

    /**
     * Get email preferences with defaults
     */
    public function getEmailPreferencesAttribute($value)
    {
        $preferences = $value ? json_decode($value, true) : [];
        return array_merge($this->getDefaultEmailPreferences(), $preferences);
    }

    /**
     * Check if user wants to receive specific email type
     */
    public function wantsEmailType($emailType)
    {
        if (!$this->email_notifications_enabled) {
            return false;
        }

        $preferences = $this->email_preferences;
        return $preferences[$emailType] ?? true;
    }

    /**
     * Update email preferences
     */
    public function updateEmailPreferences($preferences)
    {
        $this->update([
            'email_preferences' => $preferences,
            'email_preferences_updated_at' => now()
        ]);
    }

    /**
     * Generate unsubscribe token
     */
    public function generateUnsubscribeToken()
    {
        if (!$this->unsubscribe_token) {
            $this->update([
                'unsubscribe_token' => \Illuminate\Support\Str::random(32)
            ]);
        }
        return $this->unsubscribe_token;
    }

    /**
     * Get unsubscribe URL
     */
    public function getUnsubscribeUrl()
    {
        $token = $this->generateUnsubscribeToken();
        return route('email.unsubscribe', ['token' => $token]);
    }

    /**
     * Unsubscribe from all emails
     */
    public function unsubscribeFromAll()
    {
        $this->update([
            'email_notifications_enabled' => false,
            'newsletter_subscribed' => false,
            'email_preferences_updated_at' => now()
        ]);
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeToNewsletter()
    {
        $this->update([
            'newsletter_subscribed' => true,
            'email_preferences_updated_at' => now()
        ]);
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribeFromNewsletter()
    {
        $this->update([
            'newsletter_subscribed' => false,
            'email_preferences_updated_at' => now()
        ]);
    }

    /**
     * Check if user is in quiet hours
     */
    public function isInQuietHours()
    {
        if (!$this->preferred_email_time) {
            return false;
        }

        $userTime = now()->setTimezone($this->timezone);
        $preferredTime = $userTime->copy()->setTimeFromTimeString($this->preferred_email_time);

        // Define quiet hours (10 PM to 8 AM)
        $quietStart = $userTime->copy()->setTime(22, 0);
        $quietEnd = $userTime->copy()->addDay()->setTime(8, 0);

        return $userTime->between($quietStart, $quietEnd);
    }

    /**
     * Get formatted notification frequency
     */
    public function getFormattedNotificationFrequencyAttribute()
    {
        $frequencies = [
            'immediate' => 'فوري',
            'daily' => 'يومي',
            'weekly' => 'أسبوعي',
            'never' => 'أبداً'
        ];

        return $frequencies[$this->notification_frequency] ?? 'فوري';
    }

    /**
     * Should receive email now based on frequency
     */
    public function shouldReceiveEmailNow($emailType)
    {
        if (!$this->wantsEmailType($emailType)) {
            return false;
        }

        switch ($this->notification_frequency) {
            case 'never':
                return false;
            case 'immediate':
                return !$this->isInQuietHours();
            case 'daily':
                // Check if daily digest time
                return $this->isDailyDigestTime();
            case 'weekly':
                // Check if weekly digest time
                return $this->isWeeklyDigestTime();
            default:
                return true;
        }
    }

    /**
     * Check if it's daily digest time
     */
    private function isDailyDigestTime()
    {
        $userTime = now()->setTimezone($this->timezone);
        $preferredTime = $this->preferred_email_time
            ? $userTime->copy()->setTimeFromTimeString($this->preferred_email_time)
            : $userTime->copy()->setTime(9, 0); // Default 9 AM

        return $userTime->format('H:i') === $preferredTime->format('H:i');
    }

    /**
     * Check if it's weekly digest time
     */
    private function isWeeklyDigestTime()
    {
        $userTime = now()->setTimezone($this->timezone);

        // Send weekly digest on Sunday at preferred time
        if ($userTime->dayOfWeek !== 0) { // 0 = Sunday
            return false;
        }

        return $this->isDailyDigestTime();
    }


}
