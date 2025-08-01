<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Arrear extends Model
{
    use HasFactory;

    protected $table = 'arrears';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',
        'installment_id',

        // معلومات المتأخرات
        'arrear_type',
        'arrear_category',
        'arrear_description',

        // المبالغ والحسابات
        'original_amount',
        'penalty_rate',
        'penalty_amount',
        'additional_fees',
        'total_arrear_amount',
        'paid_amount',
        'remaining_amount',

        // التواريخ المهمة
        'original_due_date',
        'arrear_start_date',
        'grace_period_end',
        'days_overdue',
        'last_calculation_date',

        // حالة المتأخرات
        'status',
        'is_active',
        'auto_calculate',
        'compound_interest',

        // إعدادات الحساب
        'calculation_method',
        'calculation_frequency',
        'max_penalty_amount',
        'min_penalty_amount',

        // معلومات الإعفاء والتأجيل
        'is_exempted',
        'exemption_reason',
        'exempted_by',
        'exemption_date',
        'is_deferred',
        'deferred_until',
        'deferment_reason',

        // معلومات الدفع
        'first_payment_date',
        'last_payment_date',
        'payment_method',
        'payment_notes',

        // الإشعارات والتذكيرات
        'notification_count',
        'last_notification_date',
        'next_notification_date',
        'legal_action_threatened',
        'legal_action_date',

        // معلومات إضافية
        'notes',
        'reference_number',
        'calculation_history',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'total_arrear_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'max_penalty_amount' => 'decimal:2',
        'min_penalty_amount' => 'decimal:2',
        'days_overdue' => 'integer',
        'calculation_frequency' => 'integer',
        'notification_count' => 'integer',
        'is_active' => 'boolean',
        'auto_calculate' => 'boolean',
        'compound_interest' => 'boolean',
        'is_exempted' => 'boolean',
        'is_deferred' => 'boolean',
        'legal_action_threatened' => 'boolean',
        'calculation_history' => 'array',
        'original_due_date' => 'date',
        'arrear_start_date' => 'date',
        'grace_period_end' => 'date',
        'last_calculation_date' => 'date',
        'exemption_date' => 'date',
        'deferred_until' => 'date',
        'first_payment_date' => 'date',
        'last_payment_date' => 'date',
        'last_notification_date' => 'date',
        'next_notification_date' => 'date',
        'legal_action_date' => 'date',
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
     * حساب عدد أيام التأخير الحالية
     */
    public function getDaysOverdueAttribute($value)
    {
        if ($this->is_exempted || $this->status === 'مدفوع كاملاً') {
            return 0;
        }

        $startDate = $this->grace_period_end ?: $this->original_due_date;
        $now = Carbon::now();

        if ($now->lte($startDate)) {
            return 0;
        }

        return $now->diffInDays($startDate);
    }

    /**
     * حساب المبلغ المتبقي تلقائياً
     */
    public function getRemainingAmountAttribute($value)
    {
        return $this->total_arrear_amount - $this->paid_amount;
    }

    /**
     * حساب إجمالي مبلغ المتأخرات
     */
    public function getTotalArrearAmountAttribute($value)
    {
        return $this->original_amount + $this->penalty_amount + $this->additional_fees;
    }

    /**
     * حساب الغرامة المحدثة
     */
    public function getCalculatedPenaltyAttribute()
    {
        if (!$this->auto_calculate || $this->is_exempted || $this->status === 'مدفوع كاملاً') {
            return $this->penalty_amount;
        }

        $daysOverdue = $this->days_overdue;
        if ($daysOverdue <= 0) {
            return 0;
        }

        $penalty = 0;
        $baseAmount = $this->original_amount;

        switch ($this->calculation_method) {
            case 'يومي':
                $penalty = ($baseAmount * $this->penalty_rate / 100) * $daysOverdue;
                break;

            case 'أسبوعي':
                $weeks = ceil($daysOverdue / 7);
                $penalty = ($baseAmount * $this->penalty_rate / 100) * $weeks;
                break;

            case 'شهري':
                $months = ceil($daysOverdue / 30);
                $penalty = ($baseAmount * $this->penalty_rate / 100) * $months;
                break;

            case 'ثابت':
                $penalty = $this->penalty_rate; // في هذه الحالة penalty_rate هو مبلغ ثابت
                break;
        }

        // تطبيق الحدود الدنيا والعليا
        if ($this->min_penalty_amount && $penalty < $this->min_penalty_amount) {
            $penalty = $this->min_penalty_amount;
        }

        if ($this->max_penalty_amount && $penalty > $this->max_penalty_amount) {
            $penalty = $this->max_penalty_amount;
        }

        return $penalty;
    }

    /**
     * هل المتأخرات نشطة؟
     */
    public function getIsActiveArrearAttribute()
    {
        return $this->is_active &&
            !$this->is_exempted &&
            $this->status !== 'مدفوع كاملاً' &&
            $this->status !== 'ملغي';
    }

    /**
     * هل يحتاج إعادة حساب؟
     */
    public function getNeedsRecalculationAttribute()
    {
        if (!$this->auto_calculate || !$this->is_active_arrear) {
            return false;
        }

        $lastCalculation = $this->last_calculation_date;
        if (!$lastCalculation) {
            return true;
        }

        $daysSinceLastCalculation = Carbon::now()->diffInDays($lastCalculation);
        return $daysSinceLastCalculation >= $this->calculation_frequency;
    }

    /**
     * نسبة السداد
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_arrear_amount == 0) return 100;
        return round(($this->paid_amount / $this->total_arrear_amount) * 100, 2);
    }

    // ==================== Scopes ====================

    /**
     * المتأخرات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * المتأخرات غير المعفاة
     */
    public function scopeNotExempted($query)
    {
        return $query->where('is_exempted', false);
    }

    /**
     * المتأخرات المؤجلة
     */
    public function scopeDeferred($query)
    {
        return $query->where('is_deferred', true);
    }

    /**
     * المتأخرات المستحقة للحساب
     */
    public function scopeNeedsCalculation($query)
    {
        return $query->where('auto_calculate', true)
            ->where('is_active', true)
            ->where('is_exempted', false)
            ->whereNotIn('status', ['مدفوع كاملاً', 'ملغي']);
    }

    /**
     * متأخرات فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('arrear_category', $category);
    }

    /**
     * متأخرات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * المتأخرات المستحقة للإشعار
     */
    public function scopeNeedsNotification($query)
    {
        return $query->where('next_notification_date', '<=', now())
            ->orWhereNull('next_notification_date');
    }

    // ==================== Methods ====================

    /**
     * حساب وتحديث الغرامة
     */
    public function calculatePenalty()
    {
        if (!$this->auto_calculate || $this->is_exempted) {
            return false;
        }

        $newPenalty = $this->calculated_penalty;
        $oldPenalty = $this->penalty_amount;

        // حفظ تاريخ الحساب
        $calculationHistory = $this->calculation_history ?: [];
        $calculationHistory[] = [
            'date' => now()->toDateString(),
            'old_penalty' => $oldPenalty,
            'new_penalty' => $newPenalty,
            'days_overdue' => $this->days_overdue,
            'method' => $this->calculation_method,
        ];

        $this->update([
            'penalty_amount' => $newPenalty,
            'total_arrear_amount' => $this->original_amount + $newPenalty + $this->additional_fees,
            'remaining_amount' => ($this->original_amount + $newPenalty + $this->additional_fees) - $this->paid_amount,
            'days_overdue' => $this->days_overdue,
            'last_calculation_date' => now(),
            'calculation_history' => $calculationHistory,
        ]);

        return true;
    }

    /**
     * تسجيل دفعة للمتأخرات
     */
    public function recordPayment($amount, $paymentMethod = null, $notes = null)
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newRemainingAmount = $this->total_arrear_amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'remaining_amount' => $newRemainingAmount,
            'payment_method' => $paymentMethod ?: $this->payment_method,
            'payment_notes' => $notes ?: $this->payment_notes,
            'last_payment_date' => now(),
            'first_payment_date' => $this->first_payment_date ?: now(),
        ]);

        $this->updateStatus();

        return $this;
    }

    /**
     * تحديث حالة المتأخرات
     */
    public function updateStatus()
    {
        $remaining = $this->remaining_amount;

        if ($this->is_exempted) {
            $status = 'معفى';
        } elseif ($this->is_deferred) {
            $status = 'مؤجل';
        } elseif ($remaining <= 0) {
            $status = 'مدفوع كاملاً';
        } elseif ($this->paid_amount > 0) {
            $status = 'مدفوع جزئياً';
        } else {
            $status = 'نشط';
        }

        $this->update(['status' => $status]);

        return $status;
    }

    /**
     * إعفاء من الغرامة
     */
    public function exempt($reason, $exemptedBy)
    {
        $this->update([
            'is_exempted' => true,
            'exemption_reason' => $reason,
            'exempted_by' => $exemptedBy,
            'exemption_date' => now(),
            'status' => 'معفى',
        ]);

        return $this;
    }

    /**
     * تأجيل المتأخرات
     */
    public function defer($deferredUntil, $reason)
    {
        $this->update([
            'is_deferred' => true,
            'deferred_until' => $deferredUntil,
            'deferment_reason' => $reason,
            'status' => 'مؤجل',
        ]);

        return $this;
    }

    /**
     * إلغاء التأجيل
     */
    public function undefer()
    {
        $this->update([
            'is_deferred' => false,
            'deferred_until' => null,
            'deferment_reason' => null,
        ]);

        $this->updateStatus();

        return $this;
    }

    /**
     * إرسال إشعار
     */
    public function sendNotification($type = 'تذكير', $nextNotificationDays = 7)
    {
        $this->update([
            'notification_count' => $this->notification_count + 1,
            'last_notification_date' => now(),
            'next_notification_date' => now()->addDays($nextNotificationDays),
        ]);

        // هنا يمكن إضافة منطق إرسال الإشعار الفعلي
        // مثل البريد الإلكتروني أو الرسائل النصية

        return $this;
    }

    /**
     * تهديد بإجراء قانوني
     */
    public function threatenLegalAction($actionDate = null)
    {
        $this->update([
            'legal_action_threatened' => true,
            'legal_action_date' => $actionDate ?: now()->addDays(30),
        ]);

        return $this;
    }

    /**
     * إلغاء المتأخرات
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'ملغي',
            'is_active' => false,
            'notes' => $this->notes . "\n" . "تم الإلغاء: " . $reason,
        ]);

        return $this;
    }

    /**
     * تحديث إعدادات الحساب
     */
    public function updateCalculationSettings($method, $rate, $frequency = null, $maxAmount = null, $minAmount = null)
    {
        $this->update([
            'calculation_method' => $method,
            'penalty_rate' => $rate,
            'calculation_frequency' => $frequency ?: $this->calculation_frequency,
            'max_penalty_amount' => $maxAmount,
            'min_penalty_amount' => $minAmount,
        ]);

        // إعادة حساب الغرامة بالإعدادات الجديدة
        $this->calculatePenalty();

        return $this;
    }
}
