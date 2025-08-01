<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Installment extends Model
{
    use HasFactory;

    protected $table = 'installments';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',

        // معلومات القسط
        'installment_number',
        'installment_name',
        'amount',
        'is_custom_amount',
        'custom_amount_reason',
        'paid_amount',
        'remaining_amount',

        // تواريخ القسط
        'due_date',
        'paid_date',
        'grace_period_end',

        // حالة القسط
        'status',
        'is_overdue',
        'overdue_days',

        // رسوم التأخير
        'late_fee',
        'late_fee_rate',
        'late_fee_applied',

        // معلومات الدفع
        'payment_method',
        'payment_reference',
        'payment_notes',

        // معلومات إضافية
        'notes',
        'is_active',
        'auto_calculate_late_fee',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
        'paid_by',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'late_fee_rate' => 'decimal:2',
        'overdue_days' => 'integer',
        'is_overdue' => 'boolean',
        'late_fee_applied' => 'boolean',
        'is_active' => 'boolean',
        'auto_calculate_late_fee' => 'boolean',
        'is_custom_amount' => 'boolean',
        'due_date' => 'date',
        'paid_date' => 'date',
        'grace_period_end' => 'date',
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

    // ==================== Accessors ====================

    /**
     * حساب المبلغ المتبقي تلقائياً
     */
    public function getRemainingAmountAttribute($value)
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * حساب نسبة السداد
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->amount == 0) return 100;
        return round(($this->paid_amount / $this->amount) * 100, 2);
    }

    /**
     * هل القسط مدفوع كاملاً؟
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->status === 'مدفوع كاملاً';
    }

    /**
     * هل القسط متأخر؟
     */
    public function getIsOverdueAttribute($value)
    {
        if ($this->is_fully_paid) return false;

        $gracePeriodEnd = $this->grace_period_end ?: $this->due_date;
        return Carbon::now()->gt($gracePeriodEnd);
    }

    /**
     * عدد أيام التأخير
     */
    public function getOverdueDaysAttribute($value)
    {
        if ($this->is_fully_paid) return 0;

        $gracePeriodEnd = $this->grace_period_end ?: $this->due_date;
        $now = Carbon::now();

        if ($now->lte($gracePeriodEnd)) return 0;

        return $now->diffInDays($gracePeriodEnd);
    }

    /**
     * حساب رسوم التأخير
     */
    public function getCalculatedLateFeeAttribute()
    {
        if (!$this->auto_calculate_late_fee || $this->is_fully_paid) return 0;

        $overdueDays = $this->overdue_days;
        if ($overdueDays <= 0) return 0;

        // حساب رسوم التأخير بناءً على المعدل والأيام
        return ($this->remaining_amount * $this->late_fee_rate / 100) * ($overdueDays / 30);
    }

    /**
     * إجمالي المبلغ المطلوب (مع رسوم التأخير)
     */
    public function getTotalAmountDueAttribute()
    {
        return $this->remaining_amount + $this->calculated_late_fee;
    }

    // ==================== Scopes ====================

    /**
     * الأقساط النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الأقساط المستحقة
     */
    public function scopeDue($query)
    {
        return $query->where('due_date', '<=', now())
            ->whereNotIn('status', ['مدفوع كاملاً', 'ملغي']);
    }

    /**
     * الأقساط المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)
            ->orWhere(function ($q) {
                $q->where('due_date', '<', now())
                    ->whereNotIn('status', ['مدفوع كاملاً', 'ملغي']);
            });
    }

    /**
     * الأقساط المدفوعة كاملاً
     */
    public function scopeFullyPaid($query)
    {
        return $query->where('status', 'مدفوع كاملاً');
    }

    /**
     * الأقساط المدفوعة جزئياً
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'مدفوع جزئياً');
    }

    /**
     * الأقساط غير المدفوعة
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'متبقي');
    }

    /**
     * أقساط طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * أقساط مستحقة خلال فترة محددة
     */
    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    // ==================== Methods ====================

    /**
     * حساب وتحديث حالة القسط
     */
    public function updateStatus()
    {
        $remaining = $this->remaining_amount;

        if ($remaining <= 0) {
            $status = 'مدفوع كاملاً';
            $this->paid_date = $this->paid_date ?: now();
        } elseif ($this->paid_amount > 0) {
            $status = 'مدفوع جزئياً';
        } elseif ($this->is_overdue) {
            $status = 'متأخر';
        } else {
            $status = 'متبقي';
        }

        $this->update([
            'status' => $status,
            'remaining_amount' => $remaining,
            'is_overdue' => $this->is_overdue,
            'overdue_days' => $this->overdue_days,
        ]);

        return $status;
    }

    /**
     * تطبيق رسوم التأخير
     */
    public function applyLateFee()
    {
        if ($this->late_fee_applied || $this->is_fully_paid) {
            return false;
        }

        $calculatedLateFee = $this->calculated_late_fee;

        if ($calculatedLateFee > 0) {
            $this->update([
                'late_fee' => $calculatedLateFee,
                'late_fee_applied' => true,
            ]);

            return true;
        }

        return false;
    }

    /**
     * تسجيل دفعة للقسط
     */
    public function recordPayment($amount, $paymentMethod = null, $reference = null, $notes = null, $paidBy = null)
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newRemainingAmount = $this->amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'remaining_amount' => $newRemainingAmount,
            'payment_method' => $paymentMethod ?: $this->payment_method,
            'payment_reference' => $reference ?: $this->payment_reference,
            'payment_notes' => $notes ?: $this->payment_notes,
            'paid_by' => $paidBy ?: $this->paid_by,
        ]);

        $this->updateStatus();

        // تحديث سجل المصروفات الأساسي
        $this->studentFeeRecord->updateTotalPaid();

        return $this;
    }

    /**
     * إلغاء القسط
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
     * تمديد تاريخ الاستحقاق
     */
    public function extendDueDate($newDueDate, $reason = null)
    {
        $this->update([
            'due_date' => $newDueDate,
            'notes' => $this->notes . "\n" . "تم تمديد التاريخ: " . $reason,
        ]);

        $this->updateStatus();

        return $this;
    }

    /**
     * تعيين فترة سماح
     */
    public function setGracePeriod($gracePeriodEnd, $reason = null)
    {
        $this->update([
            'grace_period_end' => $gracePeriodEnd,
            'notes' => $this->notes . "\n" . "فترة سماح: " . $reason,
        ]);

        $this->updateStatus();

        return $this;
    }
}
