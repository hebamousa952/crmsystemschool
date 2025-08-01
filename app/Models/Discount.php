<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',

        // معلومات الخصم
        'discount_name',
        'discount_type',
        'discount_category',

        // قيمة الخصم
        'discount_percentage',
        'discount_amount',
        'calculated_discount',
        'max_discount_amount',

        // نطاق تطبيق الخصم
        'applies_to',
        'specific_fees',
        'specific_installments',

        // شروط الخصم
        'conditions',
        'minimum_amount',
        'valid_from',
        'valid_until',

        // حالة الخصم
        'status',
        'is_applied',
        'applied_date',
        'is_recurring',

        // معلومات الموافقة
        'requires_approval',
        'approval_status',
        'approved_by',
        'approval_date',
        'approval_notes',

        // المستندات المطلوبة
        'required_documents',
        'submitted_documents',
        'documents_verified',

        // معلومات إضافية
        'description',
        'notes',
        'reference_number',
        'is_active',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'calculated_discount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'specific_fees' => 'array',
        'specific_installments' => 'array',
        'required_documents' => 'array',
        'submitted_documents' => 'array',
        'is_applied' => 'boolean',
        'is_recurring' => 'boolean',
        'requires_approval' => 'boolean',
        'documents_verified' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'applied_date' => 'date',
        'approval_date' => 'date',
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
     * حساب قيمة الخصم المحسوبة
     */
    public function getCalculatedDiscountAttribute($value)
    {
        if ($this->discount_type === 'نسبة مئوية') {
            $baseAmount = $this->getBaseAmountForDiscount();
            $calculatedDiscount = ($baseAmount * $this->discount_percentage) / 100;

            // تطبيق الحد الأقصى إذا كان محدداً
            if ($this->max_discount_amount && $calculatedDiscount > $this->max_discount_amount) {
                return $this->max_discount_amount;
            }

            return $calculatedDiscount;
        }

        if ($this->discount_type === 'مبلغ ثابت') {
            return $this->discount_amount;
        }

        if ($this->discount_type === 'منحة كاملة') {
            return $this->getBaseAmountForDiscount();
        }

        return $value;
    }

    /**
     * هل الخصم صالح؟
     */
    public function getIsValidAttribute()
    {
        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * هل الخصم مطبق؟
     */
    public function getIsAppliedAttribute($value)
    {
        return $value && $this->status === 'مطبق';
    }

    /**
     * هل الخصم يحتاج موافقة؟
     */
    public function getNeedsApprovalAttribute()
    {
        return $this->requires_approval && $this->approval_status !== 'موافق عليه';
    }

    /**
     * هل المستندات مكتملة؟
     */
    public function getDocumentsCompleteAttribute()
    {
        if (!$this->required_documents) return true;

        $requiredDocs = $this->required_documents;
        $submittedDocs = $this->submitted_documents ?: [];

        foreach ($requiredDocs as $doc) {
            if (!in_array($doc, $submittedDocs)) {
                return false;
            }
        }

        return $this->documents_verified;
    }

    /**
     * نسبة الخصم الفعلية
     */
    public function getEffectiveDiscountPercentageAttribute()
    {
        $baseAmount = $this->getBaseAmountForDiscount();
        if ($baseAmount == 0) return 0;

        return round(($this->calculated_discount / $baseAmount) * 100, 2);
    }

    // ==================== Scopes ====================

    /**
     * الخصومات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الخصومات المطبقة
     */
    public function scopeApplied($query)
    {
        return $query->where('is_applied', true);
    }

    /**
     * الخصومات الصالحة
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
        });
    }

    /**
     * الخصومات المعتمدة
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'موافق عليه');
    }

    /**
     * الخصومات في انتظار الموافقة
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'في انتظار الموافقة');
    }

    /**
     * خصومات نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('discount_type', $type);
    }

    /**
     * خصومات فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('discount_category', $category);
    }

    /**
     * خصومات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // ==================== Methods ====================

    /**
     * حساب المبلغ الأساسي للخصم
     */
    private function getBaseAmountForDiscount()
    {
        $feeRecord = $this->studentFeeRecord;

        switch ($this->applies_to) {
            case 'إجمالي المصروفات':
                return $feeRecord->total_fees;

            case 'المصروفات الأساسية':
                return $feeRecord->basic_fees;

            case 'رسوم محددة':
                $total = 0;
                if ($this->specific_fees) {
                    foreach ($this->specific_fees as $fee) {
                        $total += $feeRecord->{$fee} ?? 0;
                    }
                }
                return $total;

            case 'أقساط محددة':
                $total = 0;
                if ($this->specific_installments) {
                    $installments = $feeRecord->installments()
                        ->whereIn('installment_number', $this->specific_installments)
                        ->get();
                    $total = $installments->sum('amount');
                }
                return $total;

            default:
                return $feeRecord->total_fees;
        }
    }

    /**
     * تطبيق الخصم
     */
    public function apply()
    {
        if (!$this->canBeApplied()) {
            return false;
        }

        $calculatedDiscount = $this->calculated_discount;

        $this->update([
            'calculated_discount' => $calculatedDiscount,
            'is_applied' => true,
            'applied_date' => now(),
            'status' => 'مطبق',
        ]);

        // تحديث سجل المصروفات
        $this->studentFeeRecord->applyDiscount($this);

        return true;
    }

    /**
     * إلغاء تطبيق الخصم
     */
    public function unapply()
    {
        $this->update([
            'is_applied' => false,
            'applied_date' => null,
            'status' => 'نشط',
        ]);

        // تحديث سجل المصروفات
        $this->studentFeeRecord->removeDiscount($this);

        return true;
    }

    /**
     * هل يمكن تطبيق الخصم؟
     */
    public function canBeApplied()
    {
        // التحقق من الحالة
        if (!$this->is_active || $this->is_applied) {
            return false;
        }

        // التحقق من الصلاحية
        if (!$this->is_valid) {
            return false;
        }

        // التحقق من الموافقة
        if ($this->needs_approval) {
            return false;
        }

        // التحقق من المستندات
        if (!$this->documents_complete) {
            return false;
        }

        // التحقق من الحد الأدنى
        if ($this->minimum_amount && $this->getBaseAmountForDiscount() < $this->minimum_amount) {
            return false;
        }

        return true;
    }

    /**
     * طلب الموافقة على الخصم
     */
    public function requestApproval($notes = null)
    {
        $this->update([
            'requires_approval' => true,
            'approval_status' => 'في انتظار الموافقة',
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * الموافقة على الخصم
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'approval_status' => 'موافق عليه',
            'approved_by' => $approvedBy,
            'approval_date' => now(),
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * رفض الخصم
     */
    public function reject($rejectedBy, $notes = null)
    {
        $this->update([
            'approval_status' => 'مرفوض',
            'approved_by' => $rejectedBy,
            'approval_date' => now(),
            'approval_notes' => $notes,
            'status' => 'ملغي',
        ]);

        return $this;
    }

    /**
     * تحديث المستندات المقدمة
     */
    public function updateSubmittedDocuments($documents)
    {
        $this->update([
            'submitted_documents' => $documents,
            'documents_verified' => false,
        ]);

        return $this;
    }

    /**
     * التحقق من المستندات
     */
    public function verifyDocuments($verifiedBy = null)
    {
        $this->update([
            'documents_verified' => true,
            'updated_by' => $verifiedBy,
        ]);

        return $this;
    }

    /**
     * تمديد صلاحية الخصم
     */
    public function extendValidity($newValidUntil, $reason = null)
    {
        $this->update([
            'valid_until' => $newValidUntil,
            'notes' => $this->notes . "\n" . "تم تمديد الصلاحية: " . $reason,
        ]);

        return $this;
    }
}
