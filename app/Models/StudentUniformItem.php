<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentUniformItem extends Model
{
    use HasFactory;

    protected $table = 'student_uniform_items';

    protected $fillable = [
        'student_id',
        'student_fee_record_id',
        'uniform_item_id',
        'quantity',
        'price',
        'total_price',
        'is_delivered',
        'delivery_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_delivered' => 'boolean',
        'delivery_date' => 'date',
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
     * العلاقة مع سجل مصروفات الطالب
     */
    public function studentFeeRecord(): BelongsTo
    {
        return $this->belongsTo(StudentFeeRecord::class);
    }

    /**
     * العلاقة مع قطعة الزي
     */
    public function uniformItem(): BelongsTo
    {
        return $this->belongsTo(UniformItem::class);
    }

    // ==================== Accessors ====================

    /**
     * حساب السعر الإجمالي تلقائياً
     */
    public function getTotalPriceAttribute($value)
    {
        return $this->price * $this->quantity;
    }

    /**
     * اسم قطعة الزي
     */
    public function getItemNameAttribute()
    {
        return $this->uniformItem ? $this->uniformItem->name : 'غير محدد';
    }

    /**
     * نوع قطعة الزي
     */
    public function getItemTypeAttribute()
    {
        return $this->uniformItem ? $this->uniformItem->type : 'غير محدد';
    }

    // ==================== Mutators ====================

    /**
     * حفظ السعر الإجمالي تلقائياً عند التحديث
     */
    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = $this->price * $this->quantity;
    }

    // ==================== Scopes ====================

    /**
     * العناصر المسلمة فقط
     */
    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    /**
     * العناصر غير المسلمة
     */
    public function scopeNotDelivered($query)
    {
        return $query->where('is_delivered', false);
    }

    /**
     * عناصر طالب محدد
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * عناصر سجل مصروفات محدد
     */
    public function scopeForFeeRecord($query, $recordId)
    {
        return $query->where('student_fee_record_id', $recordId);
    }

    // ==================== Methods ====================

    /**
     * تحديث الكمية وإعادة حساب السعر الإجمالي
     */
    public function updateQuantity($newQuantity)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Updating quantity for student uniform item #{$this->id} from {$this->quantity} to {$newQuantity}");
            
            $this->update([
                'quantity' => $newQuantity,
                'total_price' => $this->price * $newQuantity
            ]);
            
            // تحديث إجمالي رسوم الزي في سجل المصروفات
            $this->updateFeeRecordUniformTotal();
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to update quantity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تحديث سعر القطعة وإعادة حساب السعر الإجمالي
     */
    public function updateItemPrice($newPrice)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Updating price for student uniform item #{$this->id} from {$this->price} to {$newPrice}");
            
            $this->update([
                'price' => $newPrice,
                'total_price' => $newPrice * $this->quantity
            ]);
            
            // تحديث إجمالي رسوم الزي في سجل المصروفات
            $this->updateFeeRecordUniformTotal();
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to update price: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تسجيل تسليم القطعة
     */
    public function markAsDelivered($deliveryDate = null)
    {
        $this->update([
            'is_delivered' => true,
            'delivery_date' => $deliveryDate ?: now(),
            'updated_by' => auth()->user()->name ?? 'System'
        ]);

        return $this;
    }

    /**
     * تحديث إجمالي رسوم الزي في سجل المصروفات
     */
    private function updateFeeRecordUniformTotal()
    {
        if ($this->student_fee_record_id) {
            $feeRecord = $this->studentFeeRecord;
            
            if ($feeRecord) {
                // حساب إجمالي رسوم الزي من جميع القطع
                $totalUniformFees = $feeRecord->studentUniformItems()->sum('total_price');
                
                // تحديث حقل رسوم الزي في سجل المصروفات
                $feeRecord->update(['uniform_fees' => $totalUniformFees]);
                
                // إعادة حساب إجمالي المصروفات
                $feeRecord->calculateTotalFees();
            }
        }
    }
}