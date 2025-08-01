<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeePlan extends Model
{
    use HasFactory;

    protected $table = 'fees_plans';

    protected $fillable = [
        // معلومات الخطة الأساسية
        'plan_name',
        'fee_type',
        'program_type',
        'academic_year',
        'grade_level',
        'grade',

        // المصروفات التفصيلية
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

        // حالة الخطة
        'is_active',
        'notes',

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
        'is_active' => 'boolean',
    ];

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
     * نوع المصروفات بالعربية
     */
    public function getFeeTypeInArabicAttribute()
    {
        return $this->fee_type;
    }

    /**
     * نوع البرنامج بالعربية
     */
    public function getProgramTypeInArabicAttribute()
    {
        return $this->program_type;
    }

    // ==================== Mutators ====================

    /**
     * حفظ إجمالي المصروفات تلقائياً عند التحديث
     */
    public function setTotalFeesAttribute($value)
    {
        $this->attributes['total_fees'] = $this->basic_fees +
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

    // ==================== العلاقات ====================

    /**
     * العلاقة مع سجلات مصاريف الطلاب
     */
    public function studentFeeRecords(): HasMany
    {
        return $this->hasMany(StudentFeeRecord::class, 'fee_plan_id');
    }

    // ==================== Scopes ====================

    /**
     * الخطط النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * خطط عام دراسي محدد
     */
    public function scopeForAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * خطط مرحلة دراسية محددة
     */
    public function scopeForGradeLevel($query, $level)
    {
        return $query->where('grade_level', $level);
    }

    /**
     * خطط برنامج محدد
     */
    public function scopeForProgram($query, $program)
    {
        return $query->where('program_type', $program);
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
     * تفعيل/إلغاء تفعيل الخطة
     */
    public function toggleActive()
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this->is_active;
    }

    /**
     * نسخ الخطة لعام دراسي جديد
     */
    public function duplicateForNewYear($newYear)
    {
        $newPlan = $this->replicate();
        $newPlan->academic_year = $newYear;
        $newPlan->plan_name = $this->plan_name . ' - ' . $newYear;
        $newPlan->save();

        return $newPlan;
    }
}
