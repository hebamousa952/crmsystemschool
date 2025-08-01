<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FinancialNotification extends Model
{
    use HasFactory;

    protected $table = 'financial_notifications';

    protected $fillable = [
        // العلاقات الأساسية
        'student_id',
        'guardian_id',
        'student_fee_record_id',
        'invoice_id',
        'installment_id',

        // معلومات الإشعار الأساسية
        'notification_number',
        'notification_type',
        'category',
        'priority',

        // محتوى الإشعار
        'title',
        'message',
        'details',
        'message_data',
        'action_url',
        'action_text',

        // معلومات مالية
        'amount',
        'due_amount',
        'paid_amount',
        'remaining_amount',
        'currency',
        'due_date',
        'days_overdue',

        // معلومات المستلم
        'recipient_name',
        'recipient_phone',
        'recipient_email',
        'recipient_type',

        // قنوات الإرسال
        'channels',
        'sms_enabled',
        'email_enabled',
        'push_enabled',
        'whatsapp_enabled',

        // حالة الإرسال
        'status',
        'is_sent',
        'is_delivered',
        'is_read',
        'is_clicked',

        // تواريخ الإرسال والاستلام
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'clicked_at',
        'expires_at',

        // تفاصيل الإرسال لكل قناة
        'sms_details',
        'email_details',
        'push_details',
        'whatsapp_details',

        // معلومات الأخطاء
        'error_message',
        'error_code',
        'retry_count',
        'last_retry_at',
        'next_retry_at',

        // إعدادات التكرار
        'is_recurring',
        'recurrence_type',
        'recurrence_settings',
        'recurrence_end_date',
        'recurrence_count',

        // معلومات القالب
        'template_name',
        'template_variables',
        'language',

        // معلومات التتبع والتحليل
        'campaign_id',
        'tracking_id',
        'analytics_data',
        'source',
        'tags',

        // معلومات الموافقة والامتثال
        'requires_consent',
        'consent_given',
        'consent_given_at',
        'can_unsubscribe',
        'unsubscribed_at',

        // معلومات إضافية
        'notes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'days_overdue' => 'integer',
        'retry_count' => 'integer',
        'recurrence_count' => 'integer',
        'sms_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'is_sent' => 'boolean',
        'is_delivered' => 'boolean',
        'is_read' => 'boolean',
        'is_clicked' => 'boolean',
        'is_recurring' => 'boolean',
        'requires_consent' => 'boolean',
        'consent_given' => 'boolean',
        'can_unsubscribe' => 'boolean',
        'channels' => 'array',
        'message_data' => 'array',
        'sms_details' => 'array',
        'email_details' => 'array',
        'push_details' => 'array',
        'whatsapp_details' => 'array',
        'recurrence_settings' => 'array',
        'template_variables' => 'array',
        'analytics_data' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'due_date' => 'date',
        'recurrence_end_date' => 'date',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'consent_given_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع الطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * العلاقة مع ولي الأمر
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(GuardianAccount::class, 'guardian_id');
    }

    /**
     * العلاقة مع سجل المصروفات
     */
    public function studentFeeRecord(): BelongsTo
    {
        return $this->belongsTo(StudentFeeRecord::class);
    }

    /**
     * العلاقة مع الفاتورة
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * العلاقة مع القسط
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    // ==================== Accessors ====================

    /**
     * هل الإشعار عاجل؟
     */
    public function getIsUrgentAttribute()
    {
        return in_array($this->priority, ['عاجل', 'حرج']);
    }

    /**
     * هل الإشعار منتهي الصلاحية؟
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    /**
     * هل يمكن إرسال الإشعار؟
     */
    public function getCanSendAttribute()
    {
        return $this->status === 'معلق' &&
            !$this->is_expired &&
            ($this->scheduled_at === null || Carbon::now()->gte($this->scheduled_at));
    }

    /**
     * هل يحتاج إعادة محاولة؟
     */
    public function getNeedsRetryAttribute()
    {
        return $this->status === 'فشل' &&
            $this->retry_count < 3 &&
            ($this->next_retry_at === null || Carbon::now()->gte($this->next_retry_at));
    }

    /**
     * معدل النجاح
     */
    public function getSuccessRateAttribute()
    {
        $totalChannels = count($this->channels ?: []);
        if ($totalChannels === 0) return 0;

        $successfulChannels = 0;
        if ($this->sms_enabled && isset($this->sms_details['status']) && $this->sms_details['status'] === 'sent') {
            $successfulChannels++;
        }
        if ($this->email_enabled && isset($this->email_details['status']) && $this->email_details['status'] === 'sent') {
            $successfulChannels++;
        }
        if ($this->push_enabled && isset($this->push_details['status']) && $this->push_details['status'] === 'sent') {
            $successfulChannels++;
        }
        if ($this->whatsapp_enabled && isset($this->whatsapp_details['status']) && $this->whatsapp_details['status'] === 'sent') {
            $successfulChannels++;
        }

        return round(($successfulChannels / $totalChannels) * 100, 2);
    }

    /**
     * وقت الاستجابة (من الإرسال للقراءة)
     */
    public function getResponseTimeAttribute()
    {
        if (!$this->sent_at || !$this->read_at) {
            return null;
        }

        return $this->read_at->diffInMinutes($this->sent_at);
    }

    /**
     * هل الإشعار متأخر؟
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && Carbon::now()->gt($this->due_date) && !$this->is_sent;
    }

    // ==================== Scopes ====================

    /**
     * الإشعارات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('status', 'معلق');
    }

    /**
     * الإشعارات المرسلة
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * الإشعارات المقروءة
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * الإشعارات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'فشل');
    }

    /**
     * الإشعارات العاجلة
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', ['عاجل', 'حرج']);
    }

    /**
     * الإشعارات منتهية الصلاحية
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * الإشعارات التي تحتاج إعادة محاولة
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status', 'فشل')
            ->where('retry_count', '<', 3)
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    /**
     * الإشعارات المجدولة
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * الإشعارات الجاهزة للإرسال
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'معلق')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * إشعارات نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * إشعارات فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * إشعارات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * إشعارات ولي أمر محدد
     */
    public function scopeForGuardian($query, $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    /**
     * الإشعارات المتكررة
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * إشعارات حملة محددة
     */
    public function scopeForCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم إشعار فريد
     */
    public static function generateNotificationNumber()
    {
        do {
            $number = 'FN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('notification_number', $number)->exists());

        return $number;
    }

    /**
     * إنشاء معرف تتبع فريد
     */
    public static function generateTrackingId()
    {
        return 'track_' . Str::uuid();
    }

    /**
     * إرسال الإشعار
     */
    public function send()
    {
        if (!$this->can_send) {
            return false;
        }

        $this->update([
            'status' => 'جاري الإرسال',
            'sent_at' => now(),
        ]);

        $results = [];

        // إرسال SMS
        if ($this->sms_enabled && $this->recipient_phone) {
            $results['sms'] = $this->sendSMS();
        }

        // إرسال Email
        if ($this->email_enabled && $this->recipient_email) {
            $results['email'] = $this->sendEmail();
        }

        // إرسال Push Notification
        if ($this->push_enabled) {
            $results['push'] = $this->sendPushNotification();
        }

        // إرسال WhatsApp
        if ($this->whatsapp_enabled && $this->recipient_phone) {
            $results['whatsapp'] = $this->sendWhatsApp();
        }

        // تحديث الحالة بناءً على النتائج
        $this->updateStatusFromResults($results);

        return $results;
    }

    /**
     * إرسال SMS
     */
    private function sendSMS()
    {
        try {
            // هنا يتم تنفيذ إرسال SMS الفعلي
            // يمكن استخدام خدمات مثل Twilio أو Nexmo

            $result = [
                'status' => 'sent',
                'message_id' => 'sms_' . Str::uuid(),
                'sent_at' => now(),
                'cost' => 0.05, // تكلفة الرسالة
            ];

            $this->update(['sms_details' => $result]);

            return $result;
        } catch (\Exception $e) {
            $error = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
            ];

            $this->update(['sms_details' => $error]);

            return $error;
        }
    }

    /**
     * إرسال Email
     */
    private function sendEmail()
    {
        try {
            // هنا يتم تنفيذ إرسال Email الفعلي

            $result = [
                'status' => 'sent',
                'message_id' => 'email_' . Str::uuid(),
                'sent_at' => now(),
                'subject' => $this->title,
            ];

            $this->update(['email_details' => $result]);

            return $result;
        } catch (\Exception $e) {
            $error = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
            ];

            $this->update(['email_details' => $error]);

            return $error;
        }
    }

    /**
     * إرسال Push Notification
     */
    private function sendPushNotification()
    {
        try {
            // هنا يتم تنفيذ إرسال Push Notification الفعلي

            $result = [
                'status' => 'sent',
                'message_id' => 'push_' . Str::uuid(),
                'sent_at' => now(),
            ];

            $this->update(['push_details' => $result]);

            return $result;
        } catch (\Exception $e) {
            $error = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
            ];

            $this->update(['push_details' => $error]);

            return $error;
        }
    }

    /**
     * إرسال WhatsApp
     */
    private function sendWhatsApp()
    {
        try {
            // هنا يتم تنفيذ إرسال WhatsApp الفعلي

            $result = [
                'status' => 'sent',
                'message_id' => 'whatsapp_' . Str::uuid(),
                'sent_at' => now(),
            ];

            $this->update(['whatsapp_details' => $result]);

            return $result;
        } catch (\Exception $e) {
            $error = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
            ];

            $this->update(['whatsapp_details' => $error]);

            return $error;
        }
    }

    /**
     * تحديث الحالة بناءً على نتائج الإرسال
     */
    private function updateStatusFromResults($results)
    {
        $successCount = 0;
        $totalCount = count($results);

        foreach ($results as $result) {
            if (isset($result['status']) && $result['status'] === 'sent') {
                $successCount++;
            }
        }

        if ($successCount === $totalCount) {
            $status = 'مرسل';
            $isSent = true;
        } elseif ($successCount > 0) {
            $status = 'مرسل جزئياً';
            $isSent = true;
        } else {
            $status = 'فشل';
            $isSent = false;
        }

        $this->update([
            'status' => $status,
            'is_sent' => $isSent,
        ]);
    }

    /**
     * إعادة محاولة الإرسال
     */
    public function retry()
    {
        if (!$this->needs_retry) {
            return false;
        }

        $this->update([
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
            'next_retry_at' => now()->addMinutes(pow(2, $this->retry_count) * 5), // Exponential backoff
            'status' => 'معلق',
        ]);

        return $this->send();
    }

    /**
     * تأكيد التسليم
     */
    public function markAsDelivered()
    {
        $this->update([
            'is_delivered' => true,
            'delivered_at' => now(),
            'status' => 'مستلم',
        ]);

        return $this;
    }

    /**
     * تأكيد القراءة
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
            'status' => 'مقروء',
        ]);

        return $this;
    }

    /**
     * تأكيد النقر
     */
    public function markAsClicked()
    {
        $this->update([
            'is_clicked' => true,
            'clicked_at' => now(),
        ]);

        // تحديث بيانات التحليل
        $this->updateAnalytics('click');

        return $this;
    }

    /**
     * إلغاء الإشعار
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'ملغي',
            'notes' => $this->notes . "\n" . "تم الإلغاء: " . $reason,
        ]);

        return $this;
    }

    /**
     * جدولة الإشعار
     */
    public function schedule($dateTime)
    {
        $this->update([
            'scheduled_at' => $dateTime,
            'status' => 'معلق',
        ]);

        return $this;
    }

    /**
     * تحديث بيانات التحليل
     */
    public function updateAnalytics($event, $data = [])
    {
        $analytics = $this->analytics_data ?: [];

        $analytics[] = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'data' => $data,
        ];

        $this->update(['analytics_data' => $analytics]);

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
     * إنشاء إشعار متكرر
     */
    public function createRecurringNotification()
    {
        if (!$this->is_recurring) {
            return null;
        }

        $nextDate = $this->calculateNextRecurrenceDate();

        if (!$nextDate || ($this->recurrence_end_date && $nextDate->gt($this->recurrence_end_date))) {
            return null;
        }

        $newNotification = $this->replicate();
        $newNotification->notification_number = self::generateNotificationNumber();
        $newNotification->tracking_id = self::generateTrackingId();
        $newNotification->scheduled_at = $nextDate;
        $newNotification->status = 'معلق';
        $newNotification->is_sent = false;
        $newNotification->is_delivered = false;
        $newNotification->is_read = false;
        $newNotification->is_clicked = false;
        $newNotification->sent_at = null;
        $newNotification->delivered_at = null;
        $newNotification->read_at = null;
        $newNotification->clicked_at = null;
        $newNotification->retry_count = 0;
        $newNotification->recurrence_count = $this->recurrence_count + 1;
        $newNotification->save();

        return $newNotification;
    }

    /**
     * حساب تاريخ التكرار التالي
     */
    private function calculateNextRecurrenceDate()
    {
        $baseDate = $this->scheduled_at ?: $this->created_at;

        switch ($this->recurrence_type) {
            case 'يومي':
                return $baseDate->addDay();
            case 'أسبوعي':
                return $baseDate->addWeek();
            case 'شهري':
                return $baseDate->addMonth();
            case 'سنوي':
                return $baseDate->addYear();
            case 'مخصص':
                // يتم تحديد التكرار المخصص من recurrence_settings
                $settings = $this->recurrence_settings;
                if (isset($settings['interval']) && isset($settings['unit'])) {
                    return $baseDate->add($settings['interval'], $settings['unit']);
                }
                break;
        }

        return null;
    }

    /**
     * إحصائيات الإشعارات
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total_notifications' => $query->count(),
            'sent_notifications' => $query->where('is_sent', true)->count(),
            'delivered_notifications' => $query->where('is_delivered', true)->count(),
            'read_notifications' => $query->where('is_read', true)->count(),
            'clicked_notifications' => $query->where('is_clicked', true)->count(),
            'failed_notifications' => $query->where('status', 'فشل')->count(),
            'pending_notifications' => $query->where('status', 'معلق')->count(),
            'urgent_notifications' => $query->whereIn('priority', ['عاجل', 'حرج'])->count(),
            'notifications_by_type' => $query->groupBy('notification_type')
                ->selectRaw('notification_type, count(*) as count')
                ->pluck('count', 'notification_type'),
            'notifications_by_channel' => [
                'sms' => $query->where('sms_enabled', true)->count(),
                'email' => $query->where('email_enabled', true)->count(),
                'push' => $query->where('push_enabled', true)->count(),
                'whatsapp' => $query->where('whatsapp_enabled', true)->count(),
            ],
        ];
    }
}
