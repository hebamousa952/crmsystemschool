<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $table = 'refunds';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',
        'invoice_id',
        'online_payment_id',

        // معلومات الاسترداد
        'refund_number',
        'refund_series',
        'refund_type',
        'refund_category',

        // المبالغ
        'original_amount',
        'refund_amount',
        'refund_percentage',
        'processing_fee',
        'penalty_amount',
        'net_refund_amount',

        // التواريخ
        'refund_date',
        'requested_date',
        'approved_date',
        'processed_date',
        'completed_date',

        // معلومات الطلب
        'refund_reason',
        'refund_details',
        'supporting_documents',
        'requested_by',
        'customer_name',
        'customer_phone',
        'customer_email',

        // حالة الاسترداد
        'status',
        'is_approved',
        'is_processed',
        'is_completed',

        // معلومات الموافقة
        'requires_approval',
        'approved_by',
        'approval_notes',
        'rejection_reason',

        // معلومات المعالجة
        'refund_method',
        'refund_reference',
        'bank_account',
        'bank_name',
        'refund_instructions',

        // معلومات المعالج
        'processed_by',
        'processing_notes',
        'processing_details',

        // الرسوم والخصومات
        'fee_breakdown',
        'admin_fee',
        'cancellation_fee',
        'is_fee_waived',
        'fee_waiver_reason',

        // معلومات التدقيق
        'is_audited',
        'audited_by',
        'audited_at',
        'audit_notes',

        // معلومات إضافية
        'notes',
        'metadata',
        'currency',
        'exchange_rate',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refund_percentage' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'net_refund_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'is_approved' => 'boolean',
        'is_processed' => 'boolean',
        'is_completed' => 'boolean',
        'requires_approval' => 'boolean',
        'is_fee_waived' => 'boolean',
        'is_audited' => 'boolean',
        'supporting_documents' => 'array',
        'processing_details' => 'array',
        'fee_breakdown' => 'array',
        'metadata' => 'array',
        'refund_date' => 'date',
        'requested_date' => 'date',
        'approved_date' => 'date',
        'processed_date' => 'date',
        'completed_date' => 'date',
        'audited_at' => 'datetime',
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
     * العلاقة مع الفاتورة (اختياري)
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * العلاقة مع الدفعة الإلكترونية (اختياري)
     */
    public function onlinePayment(): BelongsTo
    {
        return $this->belongsTo(OnlinePayment::class);
    }

    // ==================== Accessors ====================

    /**
     * حساب صافي مبلغ الاسترداد
     */
    public function getNetRefundAmountAttribute($value)
    {
        return $this->refund_amount - $this->processing_fee - $this->penalty_amount - $this->admin_fee - $this->cancellation_fee;
    }

    /**
     * حساب نسبة الاسترداد
     */
    public function getRefundPercentageAttribute($value)
    {
        if ($this->original_amount == 0) return 0;
        return round(($this->refund_amount / $this->original_amount) * 100, 2);
    }

    /**
     * هل الاسترداد في انتظار الموافقة؟
     */
    public function getIsPendingApprovalAttribute()
    {
        return $this->requires_approval && !$this->is_approved && $this->status === 'قيد المراجعة';
    }

    /**
     * هل يمكن معالجة الاسترداد؟
     */
    public function getCanProcessAttribute()
    {
        return $this->is_approved && !$this->is_processed && $this->status === 'موافق عليه';
    }

    /**
     * هل يمكن إكمال الاسترداد؟
     */
    public function getCanCompleteAttribute()
    {
        return $this->is_processed && !$this->is_completed && $this->status === 'قيد المعالجة';
    }

    /**
     * هل يمكن إلغاء الاسترداد؟
     */
    public function getCanCancelAttribute()
    {
        return !$this->is_completed && !in_array($this->status, ['مكتمل', 'ملغي']);
    }

    /**
     * هل يمكن تعديل الاسترداد؟
     */
    public function getCanEditAttribute()
    {
        return in_array($this->status, ['مطلوب', 'قيد المراجعة']) && !$this->is_approved;
    }

    /**
     * إجمالي الرسوم المخصومة
     */
    public function getTotalFeesAttribute()
    {
        return $this->processing_fee + $this->penalty_amount + $this->admin_fee + $this->cancellation_fee;
    }

    /**
     * عدد أيام المعالجة
     */
    public function getProcessingDaysAttribute()
    {
        if (!$this->requested_date || !$this->completed_date) {
            return null;
        }

        return $this->completed_date->diffInDays($this->requested_date);
    }

    // ==================== Scopes ====================

    /**
     * الاستردادات المطلوبة
     */
    public function scopeRequested($query)
    {
        return $query->where('status', 'مطلوب');
    }

    /**
     * الاستردادات قيد المراجعة
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'قيد المراجعة');
    }

    /**
     * الاستردادات الموافق عليها
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * الاستردادات المرفوضة
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'مرفوض');
    }

    /**
     * الاستردادات قيد المعالجة
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'قيد المعالجة');
    }

    /**
     * الاستردادات المكتملة
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * الاستردادات الملغية
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'ملغي');
    }

    /**
     * الاستردادات في انتظار الموافقة
     */
    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
            ->where('is_approved', false)
            ->where('status', 'قيد المراجعة');
    }

    /**
     * استردادات نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('refund_type', $type);
    }

    /**
     * استردادات فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('refund_category', $category);
    }

    /**
     * استردادات طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * الاستردادات التي تحتاج تدقيق
     */
    public function scopeNeedsAudit($query)
    {
        return $query->where('is_audited', false)
            ->where('is_completed', true);
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم استرداد فريد
     */
    public static function generateRefundNumber($series = 'REF')
    {
        $year = date('Y');
        $month = date('m');

        $lastRefund = self::where('refund_series', $series)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastRefund ?
            (int)substr($lastRefund->refund_number, -4) + 1 : 1;

        return $series . '-' . $year . $month . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * حساب مبلغ الاسترداد الصافي
     */
    public function calculateNetAmount()
    {
        $totalFees = $this->processing_fee + $this->penalty_amount + $this->admin_fee + $this->cancellation_fee;

        if ($this->is_fee_waived) {
            $totalFees = 0;
        }

        $netAmount = $this->refund_amount - $totalFees;

        $this->update([
            'net_refund_amount' => $netAmount,
        ]);

        return $netAmount;
    }

    /**
     * تقديم طلب الاسترداد
     */
    public function submit()
    {
        $this->update([
            'status' => 'قيد المراجعة',
            'requested_date' => $this->requested_date ?: now(),
        ]);

        return $this;
    }

    /**
     * الموافقة على الاسترداد
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'is_approved' => true,
            'status' => 'موافق عليه',
            'approved_by' => $approvedBy,
            'approved_date' => now(),
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * رفض الاسترداد
     */
    public function reject($rejectedBy, $reason, $notes = null)
    {
        $this->update([
            'is_approved' => false,
            'status' => 'مرفوض',
            'approved_by' => $rejectedBy,
            'approved_date' => now(),
            'rejection_reason' => $reason,
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * بدء معالجة الاسترداد
     */
    public function startProcessing($processedBy, $method = null, $reference = null, $instructions = null)
    {
        if (!$this->can_process) {
            return false;
        }

        $this->update([
            'is_processed' => true,
            'status' => 'قيد المعالجة',
            'processed_by' => $processedBy,
            'processed_date' => now(),
            'refund_method' => $method,
            'refund_reference' => $reference,
            'refund_instructions' => $instructions,
        ]);

        return true;
    }

    /**
     * إكمال الاسترداد
     */
    public function complete($completedBy = null, $notes = null)
    {
        if (!$this->can_complete) {
            return false;
        }

        $this->update([
            'is_completed' => true,
            'status' => 'مكتمل',
            'completed_date' => now(),
            'processing_notes' => $notes,
            'updated_by' => $completedBy,
        ]);

        // تحديث السجلات المرتبطة
        if ($this->invoice_id) {
            $this->invoice->refund($this->net_refund_amount, $this->refund_reason);
        }

        if ($this->online_payment_id) {
            $this->onlinePayment->refund($this->net_refund_amount, $this->refund_reason);
        }

        return true;
    }

    /**
     * إلغاء الاسترداد
     */
    public function cancel($cancelledBy, $reason = null)
    {
        if (!$this->can_cancel) {
            return false;
        }

        $this->update([
            'status' => 'ملغي',
            'updated_by' => $cancelledBy,
            'notes' => $this->notes . "\n" . "تم الإلغاء: " . $reason,
        ]);

        return true;
    }

    /**
     * تعليق الاسترداد
     */
    public function suspend($suspendedBy, $reason)
    {
        $this->update([
            'status' => 'معلق',
            'updated_by' => $suspendedBy,
            'notes' => $this->notes . "\n" . "تم التعليق: " . $reason,
        ]);

        return $this;
    }

    /**
     * إلغاء تعليق الاسترداد
     */
    public function resume($resumedBy, $reason = null)
    {
        $previousStatus = $this->is_approved ? 'موافق عليه' : 'قيد المراجعة';

        $this->update([
            'status' => $previousStatus,
            'updated_by' => $resumedBy,
            'notes' => $this->notes . "\n" . "تم إلغاء التعليق: " . $reason,
        ]);

        return $this;
    }

    /**
     * تحديث معلومات الرسوم
     */
    public function updateFees($processingFee = null, $adminFee = null, $cancellationFee = null, $penaltyAmount = null)
    {
        $updateData = [];

        if ($processingFee !== null) $updateData['processing_fee'] = $processingFee;
        if ($adminFee !== null) $updateData['admin_fee'] = $adminFee;
        if ($cancellationFee !== null) $updateData['cancellation_fee'] = $cancellationFee;
        if ($penaltyAmount !== null) $updateData['penalty_amount'] = $penaltyAmount;

        $this->update($updateData);
        $this->calculateNetAmount();

        return $this;
    }

    /**
     * إعفاء من الرسوم
     */
    public function waiveFees($reason, $waivedBy)
    {
        $this->update([
            'is_fee_waived' => true,
            'fee_waiver_reason' => $reason,
            'updated_by' => $waivedBy,
        ]);

        $this->calculateNetAmount();

        return $this;
    }

    /**
     * إلغاء إعفاء الرسوم
     */
    public function unwaiveFees($reason, $unwaivedBy)
    {
        $this->update([
            'is_fee_waived' => false,
            'fee_waiver_reason' => null,
            'updated_by' => $unwaivedBy,
            'notes' => $this->notes . "\n" . "تم إلغاء إعفاء الرسوم: " . $reason,
        ]);

        $this->calculateNetAmount();

        return $this;
    }

    /**
     * تدقيق الاسترداد
     */
    public function audit($auditedBy, $notes = null)
    {
        $this->update([
            'is_audited' => true,
            'audited_by' => $auditedBy,
            'audited_at' => now(),
            'audit_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * تحديث معلومات البنك
     */
    public function updateBankInfo($bankName, $accountNumber, $instructions = null)
    {
        $this->update([
            'bank_name' => $bankName,
            'bank_account' => $accountNumber,
            'refund_instructions' => $instructions,
        ]);

        return $this;
    }

    /**
     * إضافة مستند مؤيد
     */
    public function addSupportingDocument($documentType, $documentPath, $description = null)
    {
        $documents = $this->supporting_documents ?: [];

        $documents[] = [
            'id' => Str::uuid(),
            'type' => $documentType,
            'path' => $documentPath,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];

        $this->update(['supporting_documents' => $documents]);

        return $this;
    }

    /**
     * حذف مستند مؤيد
     */
    public function removeSupportingDocument($documentId)
    {
        $documents = collect($this->supporting_documents ?: [])
            ->reject(function ($doc) use ($documentId) {
                return $doc['id'] === $documentId;
            })
            ->values()
            ->toArray();

        $this->update(['supporting_documents' => $documents]);

        return $this;
    }

    /**
     * تحديث تفاصيل المعالجة
     */
    public function updateProcessingDetails($details)
    {
        $processingDetails = $this->processing_details ?: [];
        $processingDetails[] = [
            'timestamp' => now()->toISOString(),
            'details' => $details,
        ];

        $this->update(['processing_details' => $processingDetails]);

        return $this;
    }

    /**
     * إنشاء نسخة من الاسترداد
     */
    public function duplicate()
    {
        $newRefund = $this->replicate();
        $newRefund->refund_number = self::generateRefundNumber($this->refund_series);
        $newRefund->status = 'مطلوب';
        $newRefund->is_approved = false;
        $newRefund->is_processed = false;
        $newRefund->is_completed = false;
        $newRefund->requested_date = now();
        $newRefund->approved_date = null;
        $newRefund->processed_date = null;
        $newRefund->completed_date = null;
        $newRefund->save();

        return $newRefund;
    }
}
