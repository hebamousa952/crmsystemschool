<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeSetting extends Model
{
    use HasFactory;

    protected $table = 'fee_settings';

    protected $fillable = [
        // معلومات الإعدادات الأساسية
        'academic_year',
        'grade_level',
        'grade',
        'program_type',
        
        // الرسوم الافتراضية
        'basic_fees',
        'registration_fees',
        'activities_fees',
        'bus_fees',
        'books_fees',
        'exam_fees',
        'platform_fees',
        'insurance_fees',
        'service_fees',
        'other_fees',
        'other_fees_description',
        
        // إعدادات الخصم والتقسيط
        'default_discounts',
        'max_installments',
        'default_installment_plans',
        
        // إعدادات الزي المدرسي
        'has_uniform',
        'default_uniform_items',
        
        // معلومات إضافية
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_fees' => 'decimal:2',
        'registration_fees' => 'decimal:2',
        'activities_fees' => 'decimal:2',
        'bus_fees' => 'decimal:2',
        'books_fees' => 'decimal:2',
        'exam_fees' => 'decimal:2',
        'platform_fees' => 'decimal:2',
        'insurance_fees' => 'decimal:2',
        'service_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'default_discounts' => 'array',
        'default_installment_plans' => 'array',
        'default_uniform_items' => 'array',
        'max_installments' => 'integer',
        'is_active' => 'boolean',
        'has_uniform' => 'boolean',
    ];

    // ==================== Accessors ====================

    /**
     * حساب إجمالي المصروفات الافتراضية
     */
    public function getTotalDefaultFeesAttribute()
    {
        return $this->basic_fees +
            $this->registration_fees +
            $this->activities_fees +
            $this->bus_fees +
            $this->books_fees +
            $this->exam_fees +
            $this->platform_fees +
            $this->insurance_fees +
            $this->service_fees +
            $this->other_fees;
    }

    /**
     * نوع البرنامج بالعربية
     */
    public function getProgramTypeInArabicAttribute()
    {
        return $this->program_type;
    }

    /**
     * المرحلة الدراسية بالعربية
     */
    public function getGradeLevelInArabicAttribute()
    {
        switch ($this->grade_level) {
            case 'primary':
                return 'ابتدائي';
            case 'preparatory':
                return 'إعدادي';
            default:
                return $this->grade_level;
        }
    }

    // ==================== Scopes ====================

    /**
     * الإعدادات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * إعدادات عام دراسي محدد
     */
    public function scopeForAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * إعدادات مرحلة دراسية محددة
     */
    public function scopeForGradeLevel($query, $level)
    {
        return $query->where('grade_level', $level);
    }

    /**
     * إعدادات صف دراسي محدد
     */
    public function scopeForGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * إعدادات برنامج محدد
     */
    public function scopeForProgram($query, $program)
    {
        return $query->where('program_type', $program);
    }

    // ==================== Methods ====================

    /**
     * البحث عن إعدادات الرسوم المناسبة
     */
    public static function findSettings($academicYear, $gradeLevel, $grade = null, $programType = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [FIND_FEE_SETTINGS] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Searching for settings: Year: {$academicYear}, Level: {$gradeLevel}, Grade: {$grade}, Program: {$programType}");
            
            $query = self::active()
                ->forAcademicYear($academicYear)
                ->forGradeLevel($gradeLevel);
            
            if ($grade) {
                $query->where(function ($q) use ($grade) {
                    $q->where('grade', $grade)
                      ->orWhereNull('grade');
                });
            }
            
            if ($programType) {
                $query->where('program_type', $programType);
            }
            
            $settings = $query->first();
            
            \Illuminate\Support\Facades\Log::info("Settings found: " . ($settings ? "Yes (ID: {$settings->id})" : "No"));
            
            return $settings;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error finding fee settings: " . $e->getMessage());
            return null;
        }
    }

    /**
     * تطبيق الإعدادات على سجل مصروفات طالب
     */
    public function applyToStudentFeeRecord(StudentFeeRecord $feeRecord)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [APPLY_FEE_SETTINGS] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Applying settings ID: {$this->id} to fee record ID: {$feeRecord->id}");
            
            // تطبيق الرسوم الأساسية
            $feeRecord->update([
                'basic_fees' => $this->basic_fees,
                'registration_fees' => $this->registration_fees,
                'activities_fees' => $this->activities_fees,
                'bus_fees' => $this->bus_fees,
                'books_fees' => $this->books_fees,
                'exam_fees' => $this->exam_fees,
                'platform_fees' => $this->platform_fees,
                'insurance_fees' => $this->insurance_fees,
                'service_fees' => $this->service_fees,
                'other_fees' => $this->other_fees,
                'other_fees_description' => $this->other_fees_description,
            ]);
            
            // إعادة حساب إجمالي المصروفات
            $feeRecord->calculateTotalFees();
            
            // إضافة قطع الزي المدرسي إذا كانت متاحة
            if ($this->has_uniform && !empty($this->default_uniform_items)) {
                $this->applyUniformItemsToFeeRecord($feeRecord);
            }
            
            \Illuminate\Support\Facades\Log::info("Settings applied successfully");
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying fee settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تطبيق قطع الزي المدرسي على سجل مصروفات طالب
     */
    private function applyUniformItemsToFeeRecord(StudentFeeRecord $feeRecord)
    {
        try {
            $uniformItems = $this->default_uniform_items;
            
            if (empty($uniformItems)) {
                return false;
            }
            
            // حذف قطع الزي السابقة إن وجدت
            $feeRecord->studentUniformItems()->delete();
            
            $totalUniformFees = 0;
            
            foreach ($uniformItems as $itemData) {
                // التحقق من وجود قطعة الزي
                $uniformItem = UniformItem::find($itemData['id'] ?? 0);
                
                if (!$uniformItem) {
                    continue;
                }
                
                $quantity = $itemData['quantity'] ?? 1;
                $price = $itemData['price'] ?? $uniformItem->price;
                $totalPrice = $price * $quantity;
                
                // إضافة قطعة الزي لسجل الطالب
                $feeRecord->studentUniformItems()->create([
                    'student_id' => $feeRecord->student_id,
                    'uniform_item_id' => $uniformItem->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'created_by' => auth()->user()->name ?? 'System',
                ]);
                
                $totalUniformFees += $totalPrice;
            }
            
            // تحديث إجمالي رسوم الزي في سجل المصروفات
            $feeRecord->update(['uniform_fees' => $totalUniformFees]);
            
            // إعادة حساب إجمالي المصروفات
            $feeRecord->calculateTotalFees();
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying uniform items: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تفعيل/إلغاء تفعيل الإعدادات
     */
    public function toggleActive()
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this->is_active;
    }

    /**
     * نسخ الإعدادات لعام دراسي جديد
     */
    public function duplicateForNewYear($newYear)
    {
        $newSettings = $this->replicate();
        $newSettings->academic_year = $newYear;
        $newSettings->created_by = auth()->user()->name ?? 'System';
        $newSettings->save();

        return $newSettings;
    }
}