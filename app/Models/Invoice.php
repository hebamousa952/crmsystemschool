<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        // العلاقات الأساسية
        'student_fee_record_id',
        'student_id',
        'online_payment_id',

        // معلومات الفاتورة
        'invoice_number',
        'invoice_series',
        'invoice_type',
        'invoice_category',

        // التواريخ
        'invoice_date',
        'due_date',
        'payment_date',
        'payment_terms_days',

        // معلومات العميل
        'customer_name',
        'customer_type',
        'customer_id_number',
        'customer_phone',
        'customer_email',
        'customer_address',

        // المبالغ والحسابات
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'tax_percentage',
        'additional_fees',
        'total_amount',
        'paid_amount',
        'remaining_amount',

        // تفاصيل البنود
        'invoice_items',
        'payment_breakdown',
        'discount_details',

        // حالة الفاتورة
        'status',
        'is_paid',
        'is_overdue',
        'overdue_days',

        // معلومات الطباعة والإرسال
        'is_printed',
        'printed_at',
        'print_count',
        'is_emailed',
        'emailed_at',
        'email_count',

        // معلومات الدفع
        'payment_method',
        'payment_reference',
        'payment_notes',

        // معلومات الاسترداد
        'is_refundable',
        'refunded_amount',
        'refund_count',
        'refund_reason',

        // معلومات المراجعة والاعتماد
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',

        // معلومات الإلغاء
        'is_cancelled',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',

        // معلومات إضافية
        'description',
        'notes',
        'terms_and_conditions',
        'currency',
        'exchange_rate',

        // معلومات الملفات
        'pdf_path',
        'attachments',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
        'issued_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'additional_fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'payment_terms_days' => 'integer',
        'overdue_days' => 'integer',
        'print_count' => 'integer',
        'email_count' => 'integer',
        'refund_count' => 'integer',
        'is_paid' => 'boolean',
        'is_overdue' => 'boolean',
        'is_printed' => 'boolean',
        'is_emailed' => 'boolean',
        'is_refundable' => 'boolean',
        'requires_approval' => 'boolean',
        'is_cancelled' => 'boolean',
        'invoice_items' => 'array',
        'payment_breakdown' => 'array',
        'discount_details' => 'array',
        'attachments' => 'array',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'printed_at' => 'datetime',
        'emailed_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
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
     * العلاقة مع الدفعة الإلكترونية (اختياري)
     */
    public function onlinePayment(): BelongsTo
    {
        return $this->belongsTo(OnlinePayment::class);
    }

    // ==================== Accessors ====================

    /**
     * حساب المبلغ المتبقي تلقائياً
     */
    public function getRemainingAmountAttribute($value)
    {
        return $this->total_amount - $this->paid_amount - $this->refunded_amount;
    }

    /**
     * حساب عدد أيام التأخير
     */
    public function getOverdueDaysAttribute($value)
    {
        if ($this->is_paid || !$this->due_date) {
            return 0;
        }

        $now = Carbon::now();
        if ($now->lte($this->due_date)) {
            return 0;
        }

        return $now->diffInDays($this->due_date);
    }

    /**
     * هل الفاتورة متأخرة؟
     */
    public function getIsOverdueAttribute($value)
    {
        return !$this->is_paid && $this->due_date && Carbon::now()->gt($this->due_date);
    }

    /**
     * هل الفاتورة مدفوعة كاملاً؟
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * هل الفاتورة مدفوعة جزئياً؟
     */
    public function getIsPartiallyPaidAttribute()
    {
        return $this->paid_amount > 0 && $this->remaining_amount > 0;
    }

    /**
     * نسبة السداد
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount == 0) return 100;
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    /**
     * هل يمكن تعديل الفاتورة؟
     */
    public function getCanEditAttribute()
    {
        return in_array($this->status, ['مسودة', 'مرسلة']) && !$this->is_cancelled;
    }

    /**
     * هل يمكن إلغاء الفاتورة؟
     */
    public function getCanCancelAttribute()
    {
        return !$this->is_cancelled && !$this->is_fully_paid;
    }

    /**
     * هل يمكن طباعة الفاتورة؟
     */
    public function getCanPrintAttribute()
    {
        return !in_array($this->status, ['مسودة', 'ملغية']);
    }

    /**
     * هل يمكن إرسال الفاتورة بالبريد؟
     */
    public function getCanEmailAttribute()
    {
        return !in_array($this->status, ['مسودة', 'ملغية']) && $this->customer_email;
    }

    /**
     * المبلغ القابل للاسترداد
     */
    public function getRefundableAmountAttribute()
    {
        if (!$this->is_refundable || !$this->is_paid) {
            return 0;
        }

        return $this->paid_amount - $this->refunded_amount;
    }

    // ==================== Scopes ====================

    /**
     * الفواتير المدفوعة
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * الفواتير غير المدفوعة
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * الفواتير المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)
            ->orWhere(function ($q) {
                $q->where('due_date', '<', now())
                    ->where('is_paid', false);
            });
    }

    /**
     * الفواتير المستحقة اليوم
     */
    public function scopeDueToday($query)
    {
        return $query->where('due_date', today())
            ->where('is_paid', false);
    }

    /**
     * الفواتير المستحقة خلال فترة
     */
    public function scopeDueWithin($query, $days)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->where('is_paid', false);
    }

    /**
     * فواتير نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    /**
     * فواتير فئة محددة
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('invoice_category', $category);
    }

    /**
     * فواتير طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * الفواتير الملغية
     */
    public function scopeCancelled($query)
    {
        return $query->where('is_cancelled', true);
    }

    /**
     * الفواتير النشطة (غير ملغية)
     */
    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }

    /**
     * الفواتير التي تحتاج موافقة
     */
    public function scopeNeedsApproval($query)
    {
        return $query->where('requires_approval', true)
            ->where('approval_status', 'في انتظار الموافقة');
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم فاتورة فريد
     */
    public static function generateInvoiceNumber($series = 'INV')
    {
        $year = date('Y');
        $month = date('m');

        $lastInvoice = self::where('invoice_series', $series)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice ?
            (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $series . '-' . $year . $month . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * حساب إجمالي الفاتورة
     */
    public function calculateTotal()
    {
        $subtotal = 0;

        // حساب المجموع الفرعي من البنود
        if ($this->invoice_items) {
            foreach ($this->invoice_items as $item) {
                $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            }
        }

        // تطبيق الخصم
        $discountAmount = $this->discount_percentage > 0 ?
            ($subtotal * $this->discount_percentage / 100) :
            $this->discount_amount;

        // حساب الضريبة
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $this->tax_percentage > 0 ?
            ($taxableAmount * $this->tax_percentage / 100) :
            $this->tax_amount;

        $totalAmount = $subtotal - $discountAmount + $taxAmount + $this->additional_fees;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'remaining_amount' => $totalAmount - $this->paid_amount - $this->refunded_amount,
        ]);

        return $totalAmount;
    }

    /**
     * إضافة بند للفاتورة
     */
    public function addItem($description, $quantity, $unitPrice, $metadata = [])
    {
        $items = $this->invoice_items ?: [];

        $items[] = [
            'id' => Str::uuid(),
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'metadata' => $metadata,
            'created_at' => now()->toISOString(),
        ];

        $this->update(['invoice_items' => $items]);
        $this->calculateTotal();

        return $this;
    }

    /**
     * حذف بند من الفاتورة
     */
    public function removeItem($itemId)
    {
        $items = collect($this->invoice_items ?: [])
            ->reject(function ($item) use ($itemId) {
                return $item['id'] === $itemId;
            })
            ->values()
            ->toArray();

        $this->update(['invoice_items' => $items]);
        $this->calculateTotal();

        return $this;
    }

    /**
     * تسجيل دفعة للفاتورة
     */
    public function recordPayment($amount, $paymentMethod = null, $reference = null, $notes = null)
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newRemainingAmount = $this->total_amount - $newPaidAmount - $this->refunded_amount;

        // تحديث تفصيل المدفوعات
        $paymentBreakdown = $this->payment_breakdown ?: [];
        $paymentBreakdown[] = [
            'id' => Str::uuid(),
            'amount' => $amount,
            'method' => $paymentMethod,
            'reference' => $reference,
            'notes' => $notes,
            'date' => now()->toISOString(),
        ];

        $this->update([
            'paid_amount' => $newPaidAmount,
            'remaining_amount' => $newRemainingAmount,
            'payment_method' => $paymentMethod ?: $this->payment_method,
            'payment_reference' => $reference ?: $this->payment_reference,
            'payment_notes' => $notes ?: $this->payment_notes,
            'payment_date' => $this->payment_date ?: now(),
            'payment_breakdown' => $paymentBreakdown,
        ]);

        $this->updateStatus();

        return $this;
    }

    /**
     * تحديث حالة الفاتورة
     */
    public function updateStatus()
    {
        $remaining = $this->remaining_amount;

        if ($this->is_cancelled) {
            $status = 'ملغية';
            $isPaid = false;
        } elseif ($remaining <= 0) {
            $status = 'مدفوعة كاملاً';
            $isPaid = true;
        } elseif ($this->paid_amount > 0) {
            $status = 'مدفوعة جزئياً';
            $isPaid = false;
        } elseif ($this->is_overdue) {
            $status = 'متأخرة';
            $isPaid = false;
        } elseif ($this->due_date && Carbon::now()->gte($this->due_date)) {
            $status = 'مستحقة';
            $isPaid = false;
        } else {
            $status = 'مرسلة';
            $isPaid = false;
        }

        $this->update([
            'status' => $status,
            'is_paid' => $isPaid,
            'is_overdue' => $this->is_overdue,
            'overdue_days' => $this->overdue_days,
        ]);

        return $status;
    }

    /**
     * إرسال الفاتورة
     */
    public function send()
    {
        if ($this->status === 'مسودة') {
            $this->update([
                'status' => 'مرسلة',
                'invoice_date' => $this->invoice_date ?: now(),
                'due_date' => $this->due_date ?: now()->addDays($this->payment_terms_days),
            ]);
        }

        return $this;
    }

    /**
     * طباعة الفاتورة
     */
    public function markAsPrinted()
    {
        $this->update([
            'is_printed' => true,
            'printed_at' => now(),
            'print_count' => $this->print_count + 1,
        ]);

        return $this;
    }

    /**
     * إرسال الفاتورة بالبريد الإلكتروني
     */
    public function markAsEmailed()
    {
        $this->update([
            'is_emailed' => true,
            'emailed_at' => now(),
            'email_count' => $this->email_count + 1,
        ]);

        return $this;
    }

    /**
     * إلغاء الفاتورة
     */
    public function cancel($reason = null, $cancelledBy = null)
    {
        if (!$this->can_cancel) {
            return false;
        }

        $this->update([
            'is_cancelled' => true,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
            'status' => 'ملغية',
        ]);

        return true;
    }

    /**
     * استرداد مبلغ من الفاتورة
     */
    public function refund($amount = null, $reason = null)
    {
        if (!$this->is_refundable || !$this->is_paid) {
            return false;
        }

        $refundAmount = $amount ?: $this->refundable_amount;

        if ($refundAmount > $this->refundable_amount) {
            return false;
        }

        $newRefundedAmount = $this->refunded_amount + $refundAmount;
        $newRemainingAmount = $this->total_amount - $this->paid_amount - $newRefundedAmount;

        $this->update([
            'refunded_amount' => $newRefundedAmount,
            'remaining_amount' => $newRemainingAmount,
            'refund_count' => $this->refund_count + 1,
            'refund_reason' => $reason,
        ]);

        // إذا تم استرداد المبلغ كاملاً
        if ($newRefundedAmount >= $this->paid_amount) {
            $this->update(['status' => 'مسترجعة']);
        }

        return true;
    }

    /**
     * طلب الموافقة على الفاتورة
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
     * الموافقة على الفاتورة
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'approval_status' => 'موافق عليها',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * رفض الفاتورة
     */
    public function reject($rejectedBy, $notes = null)
    {
        $this->update([
            'approval_status' => 'مرفوضة',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * تطبيق خصم على الفاتورة
     */
    public function applyDiscount($amount = null, $percentage = null, $details = null)
    {
        $updateData = [];

        if ($amount !== null) {
            $updateData['discount_amount'] = $amount;
            $updateData['discount_percentage'] = 0;
        } elseif ($percentage !== null) {
            $updateData['discount_percentage'] = $percentage;
            $updateData['discount_amount'] = 0;
        }

        if ($details !== null) {
            $updateData['discount_details'] = $details;
        }

        $this->update($updateData);
        $this->calculateTotal();

        return $this;
    }

    /**
     * تحديث معلومات العميل
     */
    public function updateCustomerInfo($name, $type = null, $idNumber = null, $phone = null, $email = null, $address = null)
    {
        $this->update([
            'customer_name' => $name,
            'customer_type' => $type ?: $this->customer_type,
            'customer_id_number' => $idNumber,
            'customer_phone' => $phone,
            'customer_email' => $email,
            'customer_address' => $address,
        ]);

        return $this;
    }

    /**
     * إنشاء نسخة من الفاتورة
     */
    public function duplicate()
    {
        $newInvoice = $this->replicate();
        $newInvoice->invoice_number = self::generateInvoiceNumber($this->invoice_series);
        $newInvoice->status = 'مسودة';
        $newInvoice->is_paid = false;
        $newInvoice->paid_amount = 0;
        $newInvoice->remaining_amount = $newInvoice->total_amount;
        $newInvoice->payment_date = null;
        $newInvoice->save();

        return $newInvoice;
    }
}
