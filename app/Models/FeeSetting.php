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
        
        // إعدادات الخصم والتقسيط المرنة
        'default_discounts',
        'flexible_discount_rules',
        'max_installments',
        'default_installment_plans',
        'installment_flexibility',
        
        // إعدادات الزي المدرسي المرن
        'has_uniform',
        'default_uniform_items',
        'seasonal_uniform_config',
        'summer_uniform_items',
        'winter_uniform_items',
        
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
        'flexible_discount_rules' => 'array',
        'default_installment_plans' => 'array',
        'installment_flexibility' => 'array',
        'default_uniform_items' => 'array',
        'seasonal_uniform_config' => 'array',
        'summer_uniform_items' => 'array',
        'winter_uniform_items' => 'array',
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
        try {
            \Illuminate\Support\Facades\Log::info("=== [DUPLICATE_FEE_SETTING] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Duplicating fee setting ID: {$this->id} for year: {$newYear}");
            
            $newSettings = $this->replicate();
            $newSettings->academic_year = $newYear;
            $newSettings->created_by = auth()->user()->name ?? 'System';
            $newSettings->save();

            \Illuminate\Support\Facades\Log::info("New fee setting created with ID: {$newSettings->id}");
            return $newSettings;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error duplicating fee setting: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * تطبيق الخصومات المرنة
     */
    public function applyFlexibleDiscounts($studentFeeRecord, $discountData = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [APPLY_FLEXIBLE_DISCOUNTS] STARTED ===");
            
            if (!method_exists($studentFeeRecord, 'discounts')) {
                \Illuminate\Support\Facades\Log::error("StudentFeeRecord does not have 'discounts' relationship");
                return false;
            }
            
            $discountRules = $discountData ?: $this->flexible_discount_rules;
            
            if (empty($discountRules)) {
                \Illuminate\Support\Facades\Log::info("No discount rules to apply");
                return true;
            }
            
            foreach ($discountRules as $rule) {
                if (!isset($rule['type']) || !isset($rule['value']) || !isset($rule['is_percentage'])) {
                    continue;
                }
                
                $discountAmount = $rule['is_percentage'] 
                    ? ($studentFeeRecord->basic_fees * $rule['value'] / 100)
                    : $rule['value'];
                
                // إنشاء خصم جديد
                $studentFeeRecord->discounts()->create([
                    'type' => $rule['type'],
                    'amount' => $discountAmount,
                    'percentage' => $rule['is_percentage'] ? $rule['value'] : null,
                    'is_percentage' => $rule['is_percentage'],
                    'reason' => $rule['reason'] ?? 'خصم افتراضي من الإعدادات',
                    'applied_by' => auth()->user()->name ?? 'System',
                ]);
            }
            
            \Illuminate\Support\Facades\Log::info("Flexible discounts applied successfully");
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying flexible discounts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * تطبيق الزي الموسمي المرن
     */
    public function applySeasonalUniform($studentFeeRecord, $season = 'both')
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [APPLY_SEASONAL_UNIFORM] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Applying seasonal uniform for season: {$season}");
            
            if (!method_exists($studentFeeRecord, 'studentUniformItems')) {
                \Illuminate\Support\Facades\Log::error("StudentFeeRecord does not have 'studentUniformItems' relationship");
                return false;
            }
            
            $uniformItems = [];
            
            // تحديد قطع الزي بناء على الموسم
            switch ($season) {
                case 'summer':
                    $uniformItems = $this->summer_uniform_items ?: [];
                    break;
                case 'winter':
                    $uniformItems = $this->winter_uniform_items ?: [];
                    break;
                case 'both':
                    $uniformItems = array_merge(
                        $this->summer_uniform_items ?: [],
                        $this->winter_uniform_items ?: []
                    );
                    break;
                default:
                    $uniformItems = $this->default_uniform_items ?: [];
            }
            
            if (empty($uniformItems)) {
                \Illuminate\Support\Facades\Log::info("No uniform items to apply");
                return true;
            }
            
            $totalUniformFees = 0;
            
            foreach ($uniformItems as $item) {
                if (!isset($item['name']) || !isset($item['price']) || !isset($item['quantity'])) {
                    continue;
                }
                
                $totalPrice = $item['price'] * $item['quantity'];
                $totalUniformFees += $totalPrice;
                
                // إضافة قطعة الزي
                $studentFeeRecord->studentUniformItems()->create([
                    'student_id' => $studentFeeRecord->student_id,
                    'uniform_item_name' => $item['name'],
                    'uniform_item_type' => $item['type'] ?? 'general',
                    'season' => $item['season'] ?? $season,
                    'gender' => $item['gender'] ?? 'mixed',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $totalPrice,
                    'created_by' => auth()->user()->name ?? 'System',
                ]);
            }
            
            // تحديث إجمالي رسوم الزي
            $studentFeeRecord->update(['uniform_fees' => $totalUniformFees]);
            $studentFeeRecord->calculateTotalFees();
            
            \Illuminate\Support\Facades\Log::info("Seasonal uniform applied successfully");
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying seasonal uniform: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * حساب إجمالي رسوم الزي الموسمي
     */
    public function calculateSeasonalUniformTotal($season = 'both')
    {
        try {
            $uniformItems = [];
            
            switch ($season) {
                case 'summer':
                    $uniformItems = $this->summer_uniform_items ?: [];
                    break;
                case 'winter':
                    $uniformItems = $this->winter_uniform_items ?: [];
                    break;
                case 'both':
                    $uniformItems = array_merge(
                        $this->summer_uniform_items ?: [],
                        $this->winter_uniform_items ?: []
                    );
                    break;
            }
            
            $total = 0;
            foreach ($uniformItems as $item) {
                if (isset($item['price']) && isset($item['quantity'])) {
                    $total += $item['price'] * $item['quantity'];
                }
            }
            
            return $total;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error calculating seasonal uniform total: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * تطبيق مرونة التقسيط
     */
    public function applyInstallmentFlexibility($studentFeeRecord, $customPlan = null)
    {
        try {
            \Illuminate\Support\Facades\Log::info("=== [APPLY_INSTALLMENT_FLEXIBILITY] STARTED ===");
            
            if (!method_exists($studentFeeRecord, 'createInstallments')) {
                \Illuminate\Support\Facades\Log::error("StudentFeeRecord does not have 'createInstallments' method");
                return false;
            }
            
            $plan = $customPlan ?: $this->installment_flexibility;
            
            if (empty($plan) || !isset($plan['count'])) {
                \Illuminate\Support\Facades\Log::info("No installment plan to apply");
                return true;
            }
            
            $count = $plan['count'];
            $amounts = $plan['amounts'] ?? null;
            $startDate = isset($plan['start_date']) ? \Carbon\Carbon::parse($plan['start_date']) : null;
            $reasons = $plan['reasons'] ?? null;
            
            return $studentFeeRecord->createInstallments($count, $amounts, $startDate, $reasons);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error applying installment flexibility: " . $e->getMessage());
            return false;
        }
    }
}