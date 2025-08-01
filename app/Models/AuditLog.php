<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        // معلومات العملية الأساسية
        'event_type',
        'action',
        'table_name',
        'record_id',

        // معلومات المستخدم
        'user_type',
        'user_id',
        'user_name',
        'user_role',
        'user_email',

        // معلومات الجلسة والاتصال
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',

        // تفاصيل العملية
        'description',
        'old_values',
        'new_values',
        'changed_fields',
        'metadata',

        // معلومات مالية
        'amount',
        'currency',
        'payment_method',
        'transaction_reference',

        // معلومات الطالب المرتبط
        'student_id',
        'student_name',
        'student_code',
        'class_name',

        // معلومات ولي الأمر المرتبط
        'guardian_id',
        'guardian_name',
        'guardian_phone',

        // تصنيف وأولوية العملية
        'category',
        'severity',
        'risk_level',

        // حالة العملية
        'status',
        'error_message',
        'error_code',

        // معلومات التوقيت
        'event_time',
        'execution_time_ms',
        'event_date',
        'event_time_only',

        // معلومات الموقع الجغرافي
        'country',
        'city',
        'latitude',
        'longitude',

        // معلومات المراجعة والتدقيق
        'requires_review',
        'is_reviewed',
        'reviewed_by',
        'reviewed_at',
        'review_notes',

        // معلومات الأرشفة
        'is_archived',
        'archived_at',
        'retention_days',
        'delete_after',

        // معلومات إضافية للتتبع
        'correlation_id',
        'parent_event_id',
        'tags',
        'is_sensitive',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'execution_time_ms' => 'integer',
        'retention_days' => 'integer',
        'requires_review' => 'boolean',
        'is_reviewed' => 'boolean',
        'is_archived' => 'boolean',
        'is_sensitive' => 'boolean',
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'event_time' => 'datetime',
        'event_date' => 'date',
        'event_time_only' => 'datetime:H:i:s',
        'reviewed_at' => 'datetime',
        'archived_at' => 'datetime',
        'delete_after' => 'date',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع الطالب (إن وجد)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * العلاقة مع ولي الأمر (إن وجد)
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(GuardianAccount::class, 'guardian_id');
    }

    // ==================== Accessors ====================

    /**
     * هل الحدث مالي؟
     */
    public function getIsFinancialAttribute()
    {
        return $this->category === 'مالي' || in_array($this->action, ['دفع', 'استرداد']);
    }

    /**
     * هل الحدث أمني؟
     */
    public function getIsSecurityAttribute()
    {
        return $this->category === 'أمني' || in_array($this->action, ['تسجيل دخول', 'تسجيل خروج']);
    }

    /**
     * هل الحدث عالي المخاطر؟
     */
    public function getIsHighRiskAttribute()
    {
        return in_array($this->risk_level, ['عالي', 'خطر']);
    }

    /**
     * هل الحدث حرج؟
     */
    public function getIsCriticalAttribute()
    {
        return $this->severity === 'حرج';
    }

    /**
     * هل الحدث فاشل؟
     */
    public function getIsFailedAttribute()
    {
        return $this->status === 'فشل';
    }

    /**
     * مدة التنفيذ بالثواني
     */
    public function getExecutionTimeSecondsAttribute()
    {
        return $this->execution_time_ms ? round($this->execution_time_ms / 1000, 3) : null;
    }

    /**
     * عمر الحدث بالأيام
     */
    public function getAgeInDaysAttribute()
    {
        return Carbon::now()->diffInDays($this->event_time);
    }

    /**
     * هل الحدث قديم ويحتاج أرشفة؟
     */
    public function getNeedsArchivingAttribute()
    {
        return !$this->is_archived && $this->age_in_days > 30;
    }

    /**
     * هل الحدث يحتاج حذف؟
     */
    public function getNeedsDeletionAttribute()
    {
        return $this->delete_after && Carbon::now()->gt($this->delete_after);
    }

    // ==================== Scopes ====================

    /**
     * الأحداث المالية
     */
    public function scopeFinancial($query)
    {
        return $query->where('category', 'مالي')
            ->orWhereIn('action', ['دفع', 'استرداد']);
    }

    /**
     * الأحداث الأمنية
     */
    public function scopeSecurity($query)
    {
        return $query->where('category', 'أمني')
            ->orWhereIn('action', ['تسجيل دخول', 'تسجيل خروج']);
    }

    /**
     * الأحداث عالية المخاطر
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['عالي', 'خطر']);
    }

    /**
     * الأحداث الحرجة
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'حرج');
    }

    /**
     * الأحداث الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'فشل');
    }

    /**
     * الأحداث التي تحتاج مراجعة
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('requires_review', true)
            ->where('is_reviewed', false);
    }

    /**
     * الأحداث المراجعة
     */
    public function scopeReviewed($query)
    {
        return $query->where('is_reviewed', true);
    }

    /**
     * الأحداث المؤرشفة
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * الأحداث الحساسة
     */
    public function scopeSensitive($query)
    {
        return $query->where('is_sensitive', true);
    }

    /**
     * أحداث مستخدم محدد
     */
    public function scopeForUser($query, $userId, $userType = null)
    {
        $query = $query->where('user_id', $userId);

        if ($userType) {
            $query->where('user_type', $userType);
        }

        return $query;
    }

    /**
     * أحداث طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * أحداث ولي أمر محدد
     */
    public function scopeForGuardian($query, $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    /**
     * أحداث جدول محدد
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * أحداث سجل محدد
     */
    public function scopeForRecord($query, $tableName, $recordId)
    {
        return $query->where('table_name', $tableName)
            ->where('record_id', $recordId);
    }

    /**
     * أحداث فترة زمنية
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * أحداث اليوم
     */
    public function scopeToday($query)
    {
        return $query->whereDate('event_date', today());
    }

    /**
     * أحداث الأسبوع الحالي
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('event_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * أحداث الشهر الحالي
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('event_date', now()->month)
            ->whereYear('event_date', now()->year);
    }

    /**
     * أحداث بعمليات محددة
     */
    public function scopeWithActions($query, array $actions)
    {
        return $query->whereIn('action', $actions);
    }

    /**
     * أحداث بفئات محددة
     */
    public function scopeWithCategories($query, array $categories)
    {
        return $query->whereIn('category', $categories);
    }

    // ==================== Methods ====================

    /**
     * تسجيل حدث جديد
     */
    public static function logEvent($eventType, $action, $description, $data = [])
    {
        $log = new self();

        // معلومات أساسية
        $log->event_type = $eventType;
        $log->action = $action;
        $log->description = $description;
        $log->event_time = now();
        $log->event_date = today();
        $log->event_time_only = now()->format('H:i:s');

        // معلومات المستخدم
        if (auth()->check()) {
            $user = auth()->user();
            $log->user_id = $user->id;
            $log->user_name = $user->name;
            $log->user_email = $user->email ?? null;
            $log->user_type = class_basename($user);
            $log->user_role = $user->role ?? null;
        } else {
            $log->user_name = 'نظام';
            $log->user_type = 'System';
        }

        // معلومات الجلسة
        if (request()) {
            $log->session_id = session()->getId();
            $log->ip_address = request()->ip();
            $log->user_agent = request()->userAgent();
        }

        // بيانات إضافية
        foreach ($data as $key => $value) {
            if (in_array($key, $log->fillable)) {
                $log->$key = $value;
            }
        }

        // تحديد مستوى المخاطر والأهمية تلقائياً
        $log->determineRiskAndSeverity();

        $log->save();

        return $log;
    }

    /**
     * تحديد مستوى المخاطر والأهمية
     */
    private function determineRiskAndSeverity()
    {
        // تحديد مستوى المخاطر
        if (in_array($this->action, ['حذف', 'استرداد']) || $this->category === 'أمني') {
            $this->risk_level = 'عالي';
        } elseif (in_array($this->action, ['تحديث', 'دفع', 'موافقة'])) {
            $this->risk_level = 'متوسط';
        } else {
            $this->risk_level = 'منخفض';
        }

        // تحديد مستوى الأهمية
        if ($this->status === 'فشل' || $this->risk_level === 'عالي') {
            $this->severity = 'عالي';
        } elseif ($this->category === 'مالي' || $this->risk_level === 'متوسط') {
            $this->severity = 'متوسط';
        } else {
            $this->severity = 'منخفض';
        }

        // تحديد إذا كان يحتاج مراجعة
        $this->requires_review = in_array($this->severity, ['عالي', 'حرج']) ||
            $this->status === 'فشل' ||
            $this->risk_level === 'عالي';
    }

    /**
     * مراجعة الحدث
     */
    public function review($reviewedBy, $notes = null)
    {
        $this->update([
            'is_reviewed' => true,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * أرشفة الحدث
     */
    public function archive()
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'delete_after' => now()->addDays($this->retention_days),
        ]);

        return $this;
    }

    /**
     * إلغاء أرشفة الحدث
     */
    public function unarchive()
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'delete_after' => null,
        ]);

        return $this;
    }

    /**
     * إضافة علامة
     */
    public function addTag($tag)
    {
        $tags = $this->tags ?: [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }

        return $this;
    }

    /**
     * إزالة علامة
     */
    public function removeTag($tag)
    {
        $tags = collect($this->tags ?: [])
            ->reject(function ($t) use ($tag) {
                return $t === $tag;
            })
            ->values()
            ->toArray();

        $this->update(['tags' => $tags]);

        return $this;
    }

    /**
     * تحديث البيانات الإضافية
     */
    public function updateMetadata($key, $value)
    {
        $metadata = $this->metadata ?: [];
        $metadata[$key] = $value;

        $this->update(['metadata' => $metadata]);

        return $this;
    }

    /**
     * ربط الحدث بحدث آخر
     */
    public function linkToEvent($parentEventId, $correlationId = null)
    {
        $this->update([
            'parent_event_id' => $parentEventId,
            'correlation_id' => $correlationId ?: Str::uuid(),
        ]);

        return $this;
    }

    /**
     * الحصول على الأحداث المرتبطة
     */
    public function getRelatedEvents()
    {
        if (!$this->correlation_id) {
            return collect();
        }

        return self::where('correlation_id', $this->correlation_id)
            ->where('id', '!=', $this->id)
            ->orderBy('event_time')
            ->get();
    }

    /**
     * الحصول على الأحداث الفرعية
     */
    public function getChildEvents()
    {
        return self::where('parent_event_id', $this->id)
            ->orderBy('event_time')
            ->get();
    }

    /**
     * تنظيف السجلات القديمة
     */
    public static function cleanup()
    {
        $deletedCount = self::where('delete_after', '<', now())->delete();

        return $deletedCount;
    }

    /**
     * أرشفة السجلات القديمة
     */
    public static function archiveOldRecords($days = 30)
    {
        $archivedCount = self::where('is_archived', false)
            ->where('event_date', '<', now()->subDays($days))
            ->update([
                'is_archived' => true,
                'archived_at' => now(),
                'delete_after' => now()->addDays(365),
            ]);

        return $archivedCount;
    }

    /**
     * إحصائيات الأحداث
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('event_date', [$startDate, $endDate]);
        }

        return [
            'total_events' => $query->count(),
            'successful_events' => $query->where('status', 'نجح')->count(),
            'failed_events' => $query->where('status', 'فشل')->count(),
            'high_risk_events' => $query->whereIn('risk_level', ['عالي', 'خطر'])->count(),
            'critical_events' => $query->where('severity', 'حرج')->count(),
            'financial_events' => $query->where('category', 'مالي')->count(),
            'security_events' => $query->where('category', 'أمني')->count(),
            'events_needing_review' => $query->where('requires_review', true)
                ->where('is_reviewed', false)->count(),
            'events_by_action' => $query->groupBy('action')
                ->selectRaw('action, count(*) as count')
                ->pluck('count', 'action'),
            'events_by_category' => $query->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
        ];
    }
}
