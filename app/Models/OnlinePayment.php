<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $table = 'online_payments';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',
        'installment_id',

        // معلومات الدفعة
        'payment_reference',
        'transaction_id',
        'amount',
        'currency',
        'exchange_rate',
        'amount_in_base_currency',

        // معلومات البوابة والطريقة
        'payment_gateway',
        'payment_method',
        'payment_channel',

        // تفاصيل البطاقة
        'card_type',
        'card_last_four',
        'card_brand',
        'card_country',

        // حالة الدفعة
        'status',
        'gateway_status',
        'failure_reason',
        'retry_count',

        // التواريخ والأوقات
        'initiated_at',
        'completed_at',
        'failed_at',
        'expires_at',

        // الرسوم والعمولات
        'gateway_fee',
        'processing_fee',
        'total_fees',
        'net_amount',

        // معلومات العميل
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',

        // معلومات الجهاز والموقع
        'ip_address',
        'user_agent',
        'device_type',
        'location_country',
        'location_city',

        // الأمان ومكافحة الاحتيال
        'risk_score',
        'is_suspicious',
        'fraud_checks',
        'requires_3ds',
        'is_3ds_verified',

        // معلومات الاسترداد
        'is_refundable',
        'refunded_amount',
        'remaining_refundable',
        'refund_count',

        // البيانات الإضافية
        'gateway_response',
        'webhook_data',
        'metadata',
        'notes',

        // معلومات التدقيق
        'processed_by',
        'verified_by',
        'verified_at',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_in_base_currency' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'remaining_refundable' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'retry_count' => 'integer',
        'refund_count' => 'integer',
        'is_suspicious' => 'boolean',
        'requires_3ds' => 'boolean',
        'is_3ds_verified' => 'boolean',
        'is_refundable' => 'boolean',
        'customer_address' => 'array',
        'fraud_checks' => 'array',
        'gateway_response' => 'array',
        'webhook_data' => 'array',
        'metadata' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع سجل مصروفات الطالب
     */
    public function studentFeeRecord(): BelongsTo
    {
        return $this->belongsTo(StudentFeeRecord::class, 'student_fee_record_id');
    }

    /**
     * العلاقة مع الطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * العلاقة مع القسط (اختياري)
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    // ==================== Accessors ====================

    /**
     * حساب المبلغ المتبقي للاسترداد
     */
    public function getRemainingRefundableAttribute($value)
    {
        if (!$this->is_refundable || $this->status !== 'مكتملة') {
            return 0;
        }

        return $this->net_amount - $this->refunded_amount;
    }

    /**
     * هل الدفعة مكتملة؟
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'مكتملة' && $this->completed_at !== null;
    }

    /**
     * هل الدفعة فاشلة؟
     */
    public function getIsFailedAttribute()
    {
        return in_array($this->status, ['فاشلة', 'ملغية']);
    }

    /**
     * هل الدفعة قيد المعالجة؟
     */
    public function getIsPendingAttribute()
    {
        return in_array($this->status, ['في انتظار', 'قيد المعالجة']);
    }

    /**
     * هل الدفعة منتهية الصلاحية؟
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    /**
     * هل يمكن إعادة المحاولة؟
     */
    public function getCanRetryAttribute()
    {
        return $this->is_failed && $this->retry_count < 3 && !$this->is_expired;
    }

    /**
     * هل يمكن الاسترداد؟
     */
    public function getCanRefundAttribute()
    {
        return $this->is_refundable &&
            $this->is_completed &&
            $this->remaining_refundable > 0;
    }

    /**
     * نسبة المخاطر كنص
     */
    public function getRiskLevelAttribute()
    {
        if (!$this->risk_score) return 'غير محدد';

        if ($this->risk_score <= 30) return 'منخفض';
        if ($this->risk_score <= 60) return 'متوسط';
        if ($this->risk_score <= 80) return 'عالي';
        return 'خطر جداً';
    }

    /**
     * وقت المعالجة بالثواني
     */
    public function getProcessingTimeAttribute()
    {
        if (!$this->initiated_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->initiated_at);
    }

    // ==================== Scopes ====================

    /**
     * الدفعات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'مكتملة');
    }

    /**
     * الدفعات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['فاشلة', 'ملغية']);
    }

    /**
     * الدفعات قيد المعالجة
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['في انتظار', 'قيد المعالجة']);
    }

    /**
     * الدفعات المشبوهة
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * الدفعات عالية المخاطر
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_score', '>', 60);
    }

    /**
     * دفعات بوابة محددة
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * دفعات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * الدفعات المنتهية الصلاحية
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * الدفعات القابلة للاسترداد
     */
    public function scopeRefundable($query)
    {
        return $query->where('is_refundable', true)
            ->where('status', 'مكتملة')
            ->where('refunded_amount', '<', 'net_amount');
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم مرجعي فريد
     */
    public static function generatePaymentReference()
    {
        do {
            $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        } while (self::where('payment_reference', $reference)->exists());

        return $reference;
    }

    /**
     * بدء عملية الدفع
     */
    public function initiate()
    {
        $this->update([
            'status' => 'قيد المعالجة',
            'initiated_at' => now(),
            'expires_at' => now()->addMinutes(30), // انتهاء الصلاحية بعد 30 دقيقة
        ]);

        return $this;
    }

    /**
     * تأكيد نجاح الدفعة
     */
    public function markAsCompleted($transactionId = null, $gatewayResponse = null)
    {
        $updateData = [
            'status' => 'مكتملة',
            'completed_at' => now(),
            'net_amount' => $this->amount - $this->total_fees,
            'remaining_refundable' => $this->amount - $this->total_fees,
        ];

        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }

        if ($gatewayResponse) {
            $updateData['gateway_response'] = $gatewayResponse;
        }

        $this->update($updateData);

        // تحديث سجل المصروفات
        if ($this->installment_id) {
            $this->installment->recordPayment($this->net_amount, $this->payment_method);
        } else {
            $this->studentFeeRecord->recordPayment($this->net_amount, $this->payment_method);
        }

        return $this;
    }

    /**
     * تسجيل فشل الدفعة
     */
    public function markAsFailed($reason = null, $gatewayResponse = null)
    {
        $updateData = [
            'status' => 'فاشلة',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ];

        if ($gatewayResponse) {
            $updateData['gateway_response'] = $gatewayResponse;
        }

        $this->update($updateData);

        return $this;
    }

    /**
     * إلغاء الدفعة
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'ملغية',
            'failure_reason' => $reason,
            'failed_at' => now(),
        ]);

        return $this;
    }

    /**
     * إعادة محاولة الدفع
     */
    public function retry()
    {
        if (!$this->can_retry) {
            return false;
        }

        $this->update([
            'status' => 'في انتظار',
            'retry_count' => $this->retry_count + 1,
            'failure_reason' => null,
            'failed_at' => null,
            'expires_at' => now()->addMinutes(30),
        ]);

        return true;
    }

    /**
     * تحديث معلومات الرسوم
     */
    public function updateFees($gatewayFee, $processingFee = 0)
    {
        $totalFees = $gatewayFee + $processingFee;
        $netAmount = $this->amount - $totalFees;

        $this->update([
            'gateway_fee' => $gatewayFee,
            'processing_fee' => $processingFee,
            'total_fees' => $totalFees,
            'net_amount' => $netAmount,
            'remaining_refundable' => $netAmount,
        ]);

        return $this;
    }

    /**
     * تحديث معلومات الأمان
     */
    public function updateSecurityInfo($riskScore = null, $fraudChecks = null, $requires3ds = false)
    {
        $updateData = [];

        if ($riskScore !== null) {
            $updateData['risk_score'] = $riskScore;
            $updateData['is_suspicious'] = $riskScore > 70;
        }

        if ($fraudChecks !== null) {
            $updateData['fraud_checks'] = $fraudChecks;
        }

        if ($requires3ds) {
            $updateData['requires_3ds'] = true;
        }

        $this->update($updateData);

        return $this;
    }

    /**
     * تأكيد التحقق بـ 3D Secure
     */
    public function confirm3dsVerification()
    {
        $this->update([
            'is_3ds_verified' => true,
        ]);

        return $this;
    }

    /**
     * معالجة webhook من البوابة
     */
    public function processWebhook($webhookData)
    {
        $this->update([
            'webhook_data' => $webhookData,
            'gateway_status' => $webhookData['status'] ?? null,
        ]);

        // معالجة حسب حالة البوابة
        switch ($webhookData['status'] ?? null) {
            case 'success':
            case 'completed':
                $this->markAsCompleted(
                    $webhookData['transaction_id'] ?? null,
                    $webhookData
                );
                break;

            case 'failed':
            case 'declined':
                $this->markAsFailed(
                    $webhookData['failure_reason'] ?? 'فشل من البوابة',
                    $webhookData
                );
                break;
        }

        return $this;
    }

    /**
     * إجراء استرداد جزئي أو كامل
     */
    public function refund($amount = null, $reason = null)
    {
        if (!$this->can_refund) {
            return false;
        }

        $refundAmount = $amount ?: $this->remaining_refundable;

        if ($refundAmount > $this->remaining_refundable) {
            return false;
        }

        $newRefundedAmount = $this->refunded_amount + $refundAmount;
        $newRemainingRefundable = $this->remaining_refundable - $refundAmount;

        $this->update([
            'refunded_amount' => $newRefundedAmount,
            'remaining_refundable' => $newRemainingRefundable,
            'refund_count' => $this->refund_count + 1,
            'notes' => $this->notes . "\n" . "استرداد: {$refundAmount} - {$reason}",
        ]);

        // إذا تم استرداد المبلغ كاملاً
        if ($newRemainingRefundable <= 0) {
            $this->update(['status' => 'مسترجعة']);
        }

        return true;
    }

    /**
     * تحديث معلومات العميل
     */
    public function updateCustomerInfo($name, $email = null, $phone = null, $address = null)
    {
        $this->update([
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'customer_address' => $address,
        ]);

        return $this;
    }

    /**
     * تحديث معلومات الجهاز والموقع
     */
    public function updateDeviceInfo($ipAddress, $userAgent, $deviceType = null, $country = null, $city = null)
    {
        $this->update([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'location_country' => $country,
            'location_city' => $city,
        ]);

        return $this;
    }

    /**
     * تحديث معلومات البطاقة
     */
    public function updateCardInfo($type, $lastFour, $brand, $country = null)
    {
        $this->update([
            'card_type' => $type,
            'card_last_four' => $lastFour,
            'card_brand' => $brand,
            'card_country' => $country,
        ]);

        return $this;
    }

    /**
     * التحقق من الدفعة
     */
    public function verify($verifiedBy)
    {
        $this->update([
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);

        return $this;
    }
}
