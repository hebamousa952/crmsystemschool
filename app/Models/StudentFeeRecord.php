<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeRecord extends Model
{
    use HasFactory;

    protected $table = 'student_fee_records';

    protected $fillable = [
        // العلاقات الأساسية
        'student_id',
        'fee_plan_id',

        // معلومات السجل
        'academic_year',
        'semester',

        // المصروفات المخصصة للطالب
        'basic_fees',
        'registration_fees',
        'uniform_fees',
        'uniform_pieces',
        'books_fees',
        'activities_fees',
        'bus_fees',
        'exam_fees',
        'platform_fees',
        'insurance_fees',
        'service_fees',
        'other_fees',
        'other_fees_description',

        // الإجماليات المحسوبة
        'total_fees',
        'total_paid',
        'remaining_amount',

        // معلومات التقسيط
        'is_installment',
        'installments_count',
        'down_payment',

        // حالة السداد
        'payment_status',
        'due_date',
        'last_payment_date',

        // ملاحظات ومعلومات إضافية
        'notes',
        'is_active',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_fees' => 'decimal:2',
        'registration_fees' => 'decimal:2',
        'uniform_fees' => 'decimal:2',
        'uniform_pieces' => 'integer',
        'books_fees' => 'decimal:2',
        'activities_fees' => 'decimal:2',
        'bus_fees' => 'decimal:2',
        'exam_fees' => 'decimal:2',
        'platform_fees' => 'decimal:2',
        'insurance_fees' => 'decimal:2',
        'service_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'is_installment' => 'boolean',
        'installments_count' => 'integer',
        'is_active' => 'boolean',
        'due_date' => 'date',
        'last_payment_date' => 'date',
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
     * العلاقة مع خطة المصروفات
     */
    public function feePlan(): BelongsTo
    {
        return $this->belongsTo(FeePlan::class, 'fee_plan_id');
    }

    /**
     * العلاقة مع الأقساط
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'student_fee_record_id');
    }
    
    /**
     * العلاقة مع قطع الزي المدرسي
     */
    public function studentUniformItems(): HasMany
    {
        return $this->hasMany(StudentUniformItem::class, 'student_fee_record_id');
    }

    /**
     * العلاقة مع الخصومات
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'student_fee_record_id');
    }

    /**
     * العلاقة مع المتأخرات
     */
    public function arrears(): HasMany
    {
        return $this->hasMany(Arrear::class, 'student_fee_record_id');
    }

    /**
     * العلاقة مع الفواتير
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'student_fee_record_id');
    }

    /**
     * العلاقة مع الاستردادات
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'student_fee_record_id');
    }

    // ==================== Accessors ====================

    /**
     * حساب إجمالي المصروفات تلقائياً
     */
    public function getTotalFeesAttribute($value)
    {
        return $this->basic_fees +
            $this->registration_fees +
            $this->uniform_fees +
            $this->books_fees +
            $this->activities_fees +
            $this->bus_fees +
            $this->exam_fees +
            $this->platform_fees +
            $this->insurance_fees +
            $this->service_fees +
            $this->other_fees;
    }

    /**
     * حساب المبلغ المتبقي تلقائياً
     */
    public function getRemainingAmountAttribute($value)
    {
        return $this->total_fees - $this->total_paid;
    }

    /**
     * حساب نسبة السداد
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_fees == 0) return 100;
        return round(($this->total_paid / $this->total_fees) * 100, 2);
    }

    /**
     * حالة السداد بالعربية
     */
    public function getPaymentStatusInArabicAttribute()
    {
        return $this->payment_status;
    }

    /**
     * هل السداد مكتمل؟
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->payment_status === 'مدفوع كاملاً';
    }

    /**
     * هل يوجد متأخرات؟
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date < now() && $this->remaining_amount > 0;
    }

    // ==================== Scopes ====================

    /**
     * السجلات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * سجلات عام دراسي محدد
     */
    public function scopeForAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * سجلات فصل دراسي محدد
     */
    public function scopeForSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * السجلات المدفوعة كاملاً
     */
    public function scopeFullyPaid($query)
    {
        return $query->where('payment_status', 'مدفوع كاملاً');
    }

    /**
     * السجلات المتأخرة
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'متأخر')
            ->orWhere(function ($q) {
                $q->where('due_date', '<', now())
                    ->where('remaining_amount', '>', 0);
            });
    }

    /**
     * السجلات المقسطة
     */
    public function scopeInstallment($query)
    {
        return $query->where('is_installment', true);
    }

    // ==================== Methods ====================

    /**
     * حساب إجمالي المصروفات وحفظها
     */
    public function calculateTotalFees()
    {
        $total = $this->basic_fees +
            $this->registration_fees +
            $this->uniform_fees +
            $this->books_fees +
            $this->activities_fees +
            $this->bus_fees +
            $this->exam_fees +
            $this->platform_fees +
            $this->insurance_fees +
            $this->service_fees +
            $this->other_fees;

        $this->update(['total_fees' => $total]);
        return $total;
    }

    /**
     * حساب المبلغ المتبقي وحفظه
     */
    public function calculateRemainingAmount()
    {
        $remaining = $this->total_fees - $this->total_paid;
        $this->update(['remaining_amount' => $remaining]);
        return $remaining;
    }

    /**
     * تحديث حالة السداد تلقائياً
     */
    public function updatePaymentStatus()
    {
        $remaining = $this->remaining_amount;

        if ($remaining <= 0) {
            $status = 'مدفوع كاملاً';
        } elseif ($this->total_paid > 0) {
            $status = 'مدفوع جزئياً';
        } elseif ($this->is_overdue) {
            $status = 'متأخر';
        } else {
            $status = 'غير مدفوع';
        }

        $this->update(['payment_status' => $status]);
        return $status;
    }

    /**
     * إنشاء أقساط للسجل
     */
    public function createInstallments($installmentsCount, $amounts = null, $startDate = null, $customReasons = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [CREATE_INSTALLMENTS] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Creating {$installmentsCount} installments for fee record #{$this->id}");
            \Illuminate\Support\Facades\Log::info("Custom amounts: " . ($amounts ? json_encode($amounts) : "None (equal distribution)"));
            
            if (!$this->is_installment) {
                $this->update(['is_installment' => true, 'installments_count' => $installmentsCount]);
            }

            $startDate = $startDate ?: now();
            $remainingAmount = $this->total_fees - $this->down_payment;
            
            // التحقق من صحة مجموع الأقساط المخصصة
            if ($amounts) {
                $totalCustomAmount = array_sum($amounts);
                \Illuminate\Support\Facades\Log::info("Total of custom amounts: {$totalCustomAmount}, Remaining amount: {$remainingAmount}");
                
                if (abs($totalCustomAmount - $remainingAmount) > 0.01) {
                    \Illuminate\Support\Facades\Log::error("Sum of installment amounts ({$totalCustomAmount}) does not match remaining amount ({$remainingAmount})");
                    throw new \Exception("مجموع الأقساط المخصصة لا يساوي المبلغ المتبقي");
                }
            }

            // حذف الأقساط السابقة إن وجدت
            $this->installments()->delete();
            
            for ($i = 1; $i <= $installmentsCount; $i++) {
                $dueDate = $startDate->copy()->addMonths($i - 1);
                
                // تحديد قيمة القسط (مخصصة أو متساوية)
                $isCustom = isset($amounts) && isset($amounts[$i-1]);
                $amount = $isCustom ? $amounts[$i-1] : ($remainingAmount / $installmentsCount);
                
                // تحديد سبب المبلغ المخصص إذا وجد
                $customReason = null;
                if ($isCustom && isset($customReasons) && isset($customReasons[$i-1])) {
                    $customReason = $customReasons[$i-1];
                }

                $this->installments()->create([
                    'student_id' => $this->student_id,
                    'installment_number' => $i,
                    'is_custom_amount' => $isCustom,
                    'custom_amount_reason' => $customReason,
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'status' => 'متبقي',
                ]);
            }
            
            \Illuminate\Support\Facades\Log::info("Successfully created {$installmentsCount} installments");
            return $this->installments;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error creating installments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * إضافة قطعة زي مدرسي للطالب
     */
    public function addUniformItem($uniformItemId, $quantity = 1, $customPrice = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [ADD_UNIFORM_ITEM] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Adding uniform item #{$uniformItemId} to fee record #{$this->id}");
            
            // التحقق من وجود قطعة الزي
            $uniformItem = UniformItem::find($uniformItemId);
            
            if (!$uniformItem) {
                \Illuminate\Support\Facades\Log::error("Uniform item #{$uniformItemId} not found");
                throw new \Exception("قطعة الزي غير موجودة");
            }
            
            // تحديد السعر (المخصص أو الافتراضي)
            $price = $customPrice !== null ? $customPrice : $uniformItem->price;
            $totalPrice = $price * $quantity;
            
            // إضافة قطعة الزي لسجل الطالب
            $studentUniformItem = $this->studentUniformItems()->create([
                'student_id' => $this->student_id,
                'uniform_item_id' => $uniformItem->id,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => $totalPrice,
                'created_by' => auth()->user()->name ?? 'System',
            ]);
            
            // تحديث إجمالي رسوم الزي في سجل المصروفات
            $this->updateUniformFeesTotal();
            
            \Illuminate\Support\Facades\Log::info("Uniform item added successfully");
            return $studentUniformItem;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error adding uniform item: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * تحديث إجمالي رسوم الزي المدرسي
     */
    public function updateUniformFeesTotal()
    {
        try {
            $totalUniformFees = $this->studentUniformItems()->sum('total_price');
            
            $this->update(['uniform_fees' => $totalUniformFees]);
            $this->calculateTotalFees();
            
            return $totalUniformFees;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error updating uniform fees total: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * تطبيق إعدادات الرسوم الافتراضية
     */
    public function applyFeeSettings($academicYear = null, $gradeLevel = null, $grade = null, $programType = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [APPLY_FEE_SETTINGS] STARTED ===");
            
            // استخدام قيم السجل الحالي إذا لم يتم تحديد قيم جديدة
            $academicYear = $academicYear ?: $this->academic_year;
            
            // الحصول على معلومات الطالب إذا لم يتم تحديد المرحلة والصف
            if (!$gradeLevel || !$programType) {
                $student = $this->student;
                
                if ($student) {
                    $gradeLevel = $gradeLevel ?: $student->grade_level;
                    $grade = $grade ?: $student->grade;
                    $programType = $programType ?: $student->program_type;
                }
            }
            
            // البحث عن إعدادات الرسوم المناسبة
            $feeSettings = FeeSetting::findSettings($academicYear, $gradeLevel, $grade, $programType);
            
            if (!$feeSettings) {
                \Illuminate\Support\Facades\Log::warning("No fee settings found for the specified criteria");
                return false;
            }
            
            // تطبيق الإعدادات على سجل المصروفات
            return $feeSettings->applyToStudentFeeRecord($this);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying fee settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * نسخ السجل لعام دراسي جديد
     */
    public function duplicateForNewYear($newYear)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [DUPLICATE_FEE_RECORD] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Duplicating fee record #{$this->id} for year {$newYear}");
            
            $newRecord = $this->replicate();
            $newRecord->academic_year = $newYear;
            $newRecord->total_paid = 0;
            $newRecord->remaining_amount = $newRecord->total_fees;
            $newRecord->payment_status = 'غير مدفوع';
            $newRecord->last_payment_date = null;
            $newRecord->created_by = auth()->user()->name ?? 'System';
            $newRecord->save();
            
            \Illuminate\Support\Facades\Log::info("New fee record created with ID: {$newRecord->id}");
            
            // نسخ قطع الزي المدرسي
            foreach ($this->studentUniformItems as $uniformItem) {
                $newRecord->studentUniformItems()->create([
                    'student_id' => $newRecord->student_id,
                    'uniform_item_id' => $uniformItem->uniform_item_id,
                    'quantity' => $uniformItem->quantity,
                    'price' => $uniformItem->price,
                    'total_price' => $uniformItem->total_price,
                    'created_by' => auth()->user()->name ?? 'System',
                ]);
            }
            
            return $newRecord;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error duplicating fee record: " . $e->getMessage());
            throw $e;
        }
    }
}
