<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniformItem extends Model
{
    use HasFactory;

    protected $table = 'uniform_items';

    protected $fillable = [
        'name',
        'type',
        'gender',
        'price',
        'grade_level',
        'grade',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع سجلات الزي المدرسي للطلاب
     */
    public function studentUniformItems(): HasMany
    {
        return $this->hasMany(StudentUniformItem::class);
    }

    // ==================== Accessors ====================

    /**
     * نوع الزي بالعربية
     */
    public function getTypeInArabicAttribute()
    {
        return $this->type;
    }

    /**
     * الجنس المناسب بالعربية
     */
    public function getGenderInArabicAttribute()
    {
        return $this->gender;
    }

    // ==================== Scopes ====================

    /**
     * القطع النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * قطع زي من نوع محدد
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * قطع زي لمرحلة دراسية محددة
     */
    public function scopeForGradeLevel($query, $level)
    {
        return $query->where('grade_level', $level);
    }

    /**
     * قطع زي لصف دراسي محدد
     */
    public function scopeForGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * قطع زي مناسبة لجنس محدد
     */
    public function scopeForGender($query, $gender)
    {
        return $query->where(function ($q) use ($gender) {
            $q->where('gender', $gender)
              ->orWhere('gender', 'الجميع');
        });
    }

    // ==================== Methods ====================

    /**
     * تفعيل/إلغاء تفعيل قطعة الزي
     */
    public function toggleActive()
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this->is_active;
    }

    /**
     * تحديث سعر قطعة الزي
     */
    public function updatePrice($newPrice)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Updating price for uniform item #{$this->id} from {$this->price} to {$newPrice}");
            
            $this->update(['price' => $newPrice]);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to update price for uniform item #{$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * نسخ قطعة الزي لعام دراسي جديد
     */
    public function duplicateForNewYear($newYear)
    {
        $newItem = $this->replicate();
        $newItem->created_by = auth()->user()->name ?? 'System';
        $newItem->save();

        return $newItem;
    }
}