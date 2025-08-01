<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    protected $fillable = [
        // العلاقات الأساسية
        'student_id',
        'guardian_id',
        'student_fee_record_id',
        'invoice_id',
        'installment_id',
        'online_payment_id',

        // معلومات المعاملة الأساسية
        'transaction_number',
        'reference_number',
        'external_transaction_id',
        'transaction_type',
        'category',

        // المبالغ والعملة
        'amount',
        'original_amount',
        'currency',
        'exchange_rate',
        'fees',
        'net_amount',

        // طريقة الدفع
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'gateway_response',

        // معلومات البطاقة/الحساب
        'card_last_four',
        'card_type',
        'bank_name',
        'account_number',
        'check_number',

        // حالة المعاملة
        'status',
        'payment_status',
        'is_verified',
        'is_reconciled',

        // التواريخ المهمة
        'transaction_date',
        'processed_at',
        'completed_at',
        'verified_at',
        'reconciled_at',
        'value_date',

        // معلومات الخطأ والفشل
        'failure_reason',
        'error_code',
        'error_message',
        'retry_count',
        'last_retry_at',

        // معلومات المستخدم والجلسة
        'processed_by',
        'verified_by',
        'ip_address',
        'user_agent',
        'session_id',

        // معلومات الأمان
        'security_hash',
        'is_suspicious',
        'security_notes',
        'fraud_check_result',

        // معلومات التسوية والمحاسبة
        'batch_id',
        'settlement_date',
        'settlement_reference',
        'settlement_amount',
        'merchant_id',

        // معلومات الاسترداد
        'is_refundable',
        'refunded_amount',
        'refund_count',
        'last_refund_at',

        // معلومات التقسيط والدفع المؤجل
        'is_installment',
        'installment_number',
        'total_installments',
        'due_date',
        'days_overdue',

        // معلومات الخصم والعمولة
        'discount_amount',
        'discount_percentage',
        'commission_amount',
        'commission_percentage',

        // معلومات الضريبة
        'tax_amount',
        'tax_percentage',
        'tax_id',

        // معلومات إضافية
        'description',
        'notes',
        'metadata',
        'custom_fields',

        // معلومات التتبع والتحليل
        'source',
        'channel',
        'campaign_id',
        'analytics_data',

        // معلومات الموافقة والامتثال
        'requires_approval',
        'approved_by',
        'approved_at',
        'approval_notes',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'fees' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'settlement_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'retry_count' => 'integer',
        'refund_count' => 'integer',
        'installment_number' => 'integer',
        'total_installments' => 'integer',
        'days_overdue' => 'integer',
        'is_verified' => 'boolean',
        'is_reconciled' => 'boolean',
        'is_suspicious' => 'boolean',
        'is_refundable' => 'boolean',
        'is_installment' => 'boolean',
        'requires_approval' => 'boolean',
        'gateway_response' => 'array',
        'fraud_check_result' => 'array',
        'metadata' => 'array',
        'custom_fields' => 'array',
        'analytics_data' => 'array',
        'transaction_date' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'last_refund_at' => 'datetime',
        'approved_at' => 'datetime',
        'value_date' => 'date',
        'settlement_date' => 'date',
        'due_date' => 'date',
    ];

    protected $hidden = [
        'account_number',
        'security_hash',
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

    /**
     * العلاقة مع الدفعة الإلكترونية
     */
    public function onlinePayment(): BelongsTo
    {
        return $this->belongsTo(OnlinePayment::class);
    }

    // ==================== Accessors ====================

    /**
     * هل المعاملة مكتملة؟
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'مكتمل';
    }

    /**
     * هل المعاملة فاشلة؟
     */
    public function getIsFailedAttribute()
    {
        return $this->status === 'فشل';
    }

    /**
     * هل المعاملة معلقة؟
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'معلق';
    }

    /**
     * هل المعاملة قيد المعالجة؟
     */
    public function getIsProcessingAttribute()
    {
        return $this->status === 'قيد المعالجة';
    }

    /**
     * هل يمكن استرداد المعاملة؟
     */
    public function getCanRefundAttribute()
    {
        return $this->is_refundable &&
            $this->is_completed &&
            $this->refunded_amount < $this->net_amount;
    }

    /**
     * المبلغ المتاح للاسترداد
     */
    public function getRefundableAmountAttribute()
    {
        if (!$this->can_refund) {
            return 0;
        }

        return $this->net_amount - $this->refunded_amount;
    }

    /**
     * هل المعاملة متأخرة؟
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && Carbon::now()->gt($this->due_date) && !$this->is_completed;
    }

    /**
     * عدد أيام التأخير
     */
    public function getDaysOverdueAttribute($value)
    {
        if (!$this->due_date || $this->is_completed) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->due_date));
    }

    /**
     * وقت المعالجة بالدقائق
     */
    public function getProcessingTimeAttribute()
    {
        if (!$this->transaction_date || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInMinutes($this->transaction_date);
    }

    /**
     * معدل نجاح المعاملة
     */
    public function getSuccessRateAttribute()
    {
        if ($this->retry_count === 0) {
            return $this->is_completed ? 100 : 0;
        }

        return $this->is_completed ? round(100 / ($this->retry_count + 1), 2) : 0;
    }

    // ==================== Scopes ====================

    /**
     * المعاملات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'مكتمل');
    }

    /**
     * المعاملات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'فشل');
    }

    /**
     * المعاملات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('status', 'معلق');
    }

    /**
     * المعاملات قيد المعالجة
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'قيد المعالجة');
    }

    /**
     * المعاملات المحققة
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * المعاملات المسواة
     */
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    /**
     * المعاملات المشبوهة
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * المعاملات القابلة للاسترداد
     */
    public function scopeRefundable($query)
    {
        return $query->where('is_refundable', true)
            ->where('status', 'مكتمل')
            ->whereColumn('refunded_amount', '<', 'net_amount');
    }

    /**
     * معاملات نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * معاملات فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * معاملات طريقة دفع محددة
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * معاملات بوابة دفع محددة
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * معاملات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * معاملات ولي أمر محدد
     */
    public function scopeForGuardian($query, $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    /**
     * المعاملات المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'مكتمل');
    }

    /**
     * معاملات فترة زمنية
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * معاملات اليوم
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * معاملات الشهر الحالي
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم معاملة فريد
     */
    public static function generateTransactionNumber()
    {
        do {
            $number = 'TXN-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('transaction_number', $number)->exists());

        return $number;
    }

    /**
     * معالجة المعاملة
     */
    public function process($processedBy = null)
    {
        if ($this->status !== 'معلق') {
            return false;
        }

        $this->update([
            'status' => 'قيد المعالجة',
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        // هنا يتم تنفيذ منطق المعالجة الفعلي
        $result = $this->executePayment();

        if ($result['success']) {
            $this->complete($result);
        } else {
            $this->fail($result['error'], $result['error_code']);
        }

        return $result['success'];
    }

    /**
     * تنفيذ الدفع الفعلي
     */
    private function executePayment()
    {
        try {
            // هنا يتم تنفيذ منطق الدفع حسب البوابة
            switch ($this->payment_gateway) {
                case 'paymob':
                    return $this->processPaymobPayment();
                case 'fawry':
                    return $this->processFawryPayment();
                case 'vodafone_cash':
                    return $this->processVodafoneCashPayment();
                default:
                    return $this->processManualPayment();
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'PROCESSING_ERROR'
            ];
        }
    }

    /**
     * معالجة دفع Paymob
     */
    private function processPaymobPayment()
    {
        // تنفيذ منطق Paymob
        return [
            'success' => true,
            'gateway_transaction_id' => 'paymob_' . Str::uuid(),
            'gateway_response' => ['status' => 'success']
        ];
    }

    /**
     * معالجة دفع Fawry
     */
    private function processFawryPayment()
    {
        // تنفيذ منطق Fawry
        return [
            'success' => true,
            'gateway_transaction_id' => 'fawry_' . Str::uuid(),
            'gateway_response' => ['status' => 'success']
        ];
    }

    /**
     * معالجة دفع فودافون كاش
     */
    private function processVodafoneCashPayment()
    {
        // تنفيذ منطق فودافون كاش
        return [
            'success' => true,
            'gateway_transaction_id' => 'vf_' . Str::uuid(),
            'gateway_response' => ['status' => 'success']
        ];
    }

    /**
     * معالجة دفع يدوي
     */
    private function processManualPayment()
    {
        // للدفع اليدوي (نقدي، شيك، إلخ)
        return [
            'success' => true,
            'gateway_transaction_id' => null,
            'gateway_response' => null
        ];
    }

    /**
     * إكمال المعاملة
     */
    public function complete($result = [])
    {
        $updateData = [
            'status' => 'مكتمل',
            'payment_status' => 'مدفوع بالكامل',
            'completed_at' => now(),
        ];

        if (isset($result['gateway_transaction_id'])) {
            $updateData['gateway_transaction_id'] = $result['gateway_transaction_id'];
        }

        if (isset($result['gateway_response'])) {
            $updateData['gateway_response'] = $result['gateway_response'];
        }

        $this->update($updateData);

        // تحديث السجلات المرتبطة
        $this->updateRelatedRecords();

        // تسجيل في سجل المراجعة
        AuditLog::logEvent(
            'payment_completed',
            'دفع',
            "تم إكمال معاملة الدفع رقم {$this->transaction_number}",
            [
                'table_name' => 'payment_transactions',
                'record_id' => $this->id,
                'student_id' => $this->student_id,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'category' => 'مالي'
            ]
        );

        return $this;
    }

    /**
     * فشل المعاملة
     */
    public function fail($reason, $errorCode = null)
    {
        $this->update([
            'status' => 'فشل',
            'failure_reason' => $reason,
            'error_code' => $errorCode,
            'error_message' => $reason,
        ]);

        // تسجيل في سجل المراجعة
        AuditLog::logEvent(
            'payment_failed',
            'دفع',
            "فشل في معاملة الدفع رقم {$this->transaction_number}: {$reason}",
            [
                'table_name' => 'payment_transactions',
                'record_id' => $this->id,
                'student_id' => $this->student_id,
                'amount' => $this->amount,
                'error_code' => $errorCode,
                'category' => 'مالي',
                'status' => 'فشل'
            ]
        );

        return $this;
    }

    /**
     * إعادة محاولة المعاملة
     */
    public function retry($processedBy = null)
    {
        if ($this->status !== 'فشل' || $this->retry_count >= 3) {
            return false;
        }

        $this->update([
            'status' => 'معلق',
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
            'processed_by' => $processedBy,
        ]);

        return $this->process($processedBy);
    }

    /**
     * التحقق من المعاملة
     */
    public function verify($verifiedBy)
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
        ]);

        return $this;
    }

    /**
     * تسوية المعاملة
     */
    public function reconcile($batchId = null, $settlementDate = null, $settlementAmount = null)
    {
        $this->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'batch_id' => $batchId,
            'settlement_date' => $settlementDate ?: today(),
            'settlement_amount' => $settlementAmount ?: $this->net_amount,
        ]);

        return $this;
    }

    /**
     * استرداد المعاملة
     */
    public function refund($amount = null, $reason = null)
    {
        if (!$this->can_refund) {
            return false;
        }

        $refundAmount = $amount ?: $this->refundable_amount;

        if ($refundAmount > $this->refundable_amount) {
            return false;
        }

        $this->update([
            'refunded_amount' => $this->refunded_amount + $refundAmount,
            'refund_count' => $this->refund_count + 1,
            'last_refund_at' => now(),
            'payment_status' => $this->refunded_amount + $refundAmount >= $this->net_amount ? 'مسترد' : 'مسترد جزئياً',
        ]);

        // إنشاء معاملة استرداد منفصلة
        $refundTransaction = self::create([
            'student_id' => $this->student_id,
            'guardian_id' => $this->guardian_id,
            'student_fee_record_id' => $this->student_fee_record_id,
            'invoice_id' => $this->invoice_id,
            'transaction_number' => self::generateTransactionNumber(),
            'reference_number' => $this->transaction_number,
            'transaction_type' => 'استرداد',
            'category' => $this->category,
            'amount' => -$refundAmount,
            'net_amount' => -$refundAmount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => 'مكتمل',
            'payment_status' => 'مدفوع بالكامل',
            'transaction_date' => now(),
            'completed_at' => now(),
            'description' => "استرداد للمعاملة رقم {$this->transaction_number}" . ($reason ? ": {$reason}" : ''),
            'source' => 'refund',
        ]);

        return $refundTransaction;
    }

    /**
     * إلغاء المعاملة
     */
    public function cancel($reason = null)
    {
        if (!in_array($this->status, ['معلق', 'قيد المعالجة'])) {
            return false;
        }

        $this->update([
            'status' => 'ملغي',
            'notes' => $this->notes . "\n" . "تم الإلغاء: " . $reason,
        ]);

        return $this;
    }

    /**
     * تحديث السجلات المرتبطة
     */
    private function updateRelatedRecords()
    {
        // تحديث سجل المصروفات
        if ($this->student_fee_record_id) {
            $this->studentFeeRecord->recordPayment($this->net_amount, $this->payment_method);
        }

        // تحديث الفاتورة
        if ($this->invoice_id) {
            $this->invoice->recordPayment($this->net_amount, $this->payment_method);
        }

        // تحديث القسط
        if ($this->installment_id) {
            $this->installment->recordPayment($this->net_amount, $this->payment_method);
        }

        // تحديث حساب ولي الأمر
        if ($this->guardian_id) {
            $this->guardian->recordPayment($this->net_amount, $this->payment_method, true);
        }
    }

    /**
     * وضع علامة مشبوه
     */
    public function markAsSuspicious($reason, $notes = null)
    {
        $this->update([
            'is_suspicious' => true,
            'security_notes' => $notes ?: $reason,
            'status' => 'قيد المراجعة',
        ]);

        // تسجيل في سجل المراجعة
        AuditLog::logEvent(
            'transaction_flagged',
            'تحذير',
            "تم وضع علامة مشبوه على المعاملة رقم {$this->transaction_number}: {$reason}",
            [
                'table_name' => 'payment_transactions',
                'record_id' => $this->id,
                'student_id' => $this->student_id,
                'category' => 'أمني',
                'severity' => 'عالي',
                'is_suspicious' => true
            ]
        );

        return $this;
    }

    /**
     * إزالة علامة مشبوه
     */
    public function clearSuspicious($clearedBy, $notes = null)
    {
        $this->update([
            'is_suspicious' => false,
            'security_notes' => $notes,
            'status' => 'معلق',
            'updated_by' => $clearedBy,
        ]);

        return $this;
    }

    /**
     * إحصائيات المعاملات
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return [
            'total_transactions' => $query->count(),
            'completed_transactions' => $query->where('status', 'مكتمل')->count(),
            'failed_transactions' => $query->where('status', 'فشل')->count(),
            'pending_transactions' => $query->where('status', 'معلق')->count(),
            'total_amount' => $query->where('status', 'مكتمل')->sum('net_amount'),
            'total_fees' => $query->where('status', 'مكتمل')->sum('fees'),
            'refunded_amount' => $query->sum('refunded_amount'),
            'suspicious_transactions' => $query->where('is_suspicious', true)->count(),
            'transactions_by_method' => $query->groupBy('payment_method')
                ->selectRaw('payment_method, count(*) as count, sum(net_amount) as total')
                ->get()
                ->pluck(['count', 'total'], 'payment_method'),
            'transactions_by_gateway' => $query->whereNotNull('payment_gateway')
                ->groupBy('payment_gateway')
                ->selectRaw('payment_gateway, count(*) as count, sum(net_amount) as total')
                ->get()
                ->pluck(['count', 'total'], 'payment_gateway'),
            'success_rate' => $query->count() > 0 ?
                round(($query->where('status', 'مكتمل')->count() / $query->count()) * 100, 2) : 0,
        ];
    }
}
