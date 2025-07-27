<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'category',
        'severity',
        'is_sensitive',
        'requires_review',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'is_sensitive' => 'boolean',
        'requires_review' => 'boolean',
        'reviewed_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'reviewed_at'
    ];

    // ==================== العلاقات ====================

    /**
     * Get the subject model (what was affected)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer model (who did the action)
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who reviewed this log
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== Scopes ====================

    /**
     * Scope for specific event types
     */
    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for specific categories
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific severity levels
     */
    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for sensitive operations
     */
    public function scopeSensitive($query)
    {
        return $query->where('is_sensitive', true);
    }

    /**
     * Scope for operations requiring review
     */
    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true);
    }

    /**
     * Scope for unreviewed operations
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('requires_review', true)
                    ->whereNull('reviewed_at');
    }

    /**
     * Scope for specific causer
     */
    public function scopeByCauser($query, $causerId)
    {
        return $query->where('causer_id', $causerId);
    }

    /**
     * Scope for specific subject
     */
    public function scopeForSubject($query, $subjectType, $subjectId)
    {
        return $query->where('subject_type', $subjectType)
                    ->where('subject_id', $subjectId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== Accessors ====================

    /**
     * Get formatted event type
     */
    public function getFormattedEventTypeAttribute()
    {
        $eventTypes = [
            'student_created' => 'إنشاء طالب',
            'student_updated' => 'تعديل طالب',
            'student_deleted' => 'حذف طالب',
            'payment_received' => 'استلام دفعة',
            'payment_updated' => 'تعديل دفعة',
            'grade_added' => 'إضافة درجة',
            'grade_updated' => 'تعديل درجة',
            'user_logged_in' => 'تسجيل دخول',
            'user_created' => 'إنشاء مستخدم',
            'classroom_created' => 'إنشاء فصل'
        ];

        return $eventTypes[$this->event_type] ?? $this->event_type;
    }

    /**
     * Get formatted category
     */
    public function getFormattedCategoryAttribute()
    {
        $categories = [
            'student_management' => 'إدارة الطلاب',
            'financial' => 'الشؤون المالية',
            'academic' => 'الشؤون الأكاديمية',
            'system' => 'النظام',
            'user_management' => 'إدارة المستخدمين',
            'general' => 'عام'
        ];

        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get formatted severity
     */
    public function getFormattedSeverityAttribute()
    {
        $severities = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
            'critical' => 'حرج'
        ];

        return $severities[$this->severity] ?? $this->severity;
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger'
        ];

        return $colors[$this->severity] ?? 'secondary';
    }

    /**
     * Get time ago in Arabic
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    // ==================== Static Methods ====================

    /**
     * Log an activity
     */
    public static function logActivity(array $data)
    {
        return static::create(array_merge($data, [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]));
    }

    /**
     * Get activity statistics
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total' => $query->count(),
            'by_category' => $query->groupBy('category')->selectRaw('category, count(*) as count')->pluck('count', 'category'),
            'by_severity' => $query->groupBy('severity')->selectRaw('severity, count(*) as count')->pluck('count', 'severity'),
            'sensitive_count' => $query->where('is_sensitive', true)->count(),
            'requires_review_count' => $query->where('requires_review', true)->whereNull('reviewed_at')->count(),
        ];
    }
}
