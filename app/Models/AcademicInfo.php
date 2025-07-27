<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicInfo extends Model
{
    use HasFactory;

    protected $table = 'academic_info';

    protected $fillable = [
        'student_id',
        'academic_year',
        'grade_level',
        'grade',
        'classroom',
        'enrollment_type',
        'enrollment_date',
        'previous_school',
        'transfer_reason',
        'previous_level',
        'second_language',
        'curriculum_type',
        'has_failed',
        'sibling_order',
        'attendance_type',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    /**
     * العلاقة مع الطالب
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * الحصول على اسم المرحلة بالعربية
     */
    public function getGradeLevelNameAttribute(): string
    {
        return match($this->grade_level) {
            'primary' => 'المرحلة الابتدائية',
            'preparatory' => 'المرحلة الإعدادية',
            default => $this->grade_level
        };
    }

    /**
     * الحصول على اسم نوع القيد بالعربية
     */
    public function getEnrollmentTypeNameAttribute(): string
    {
        return match($this->enrollment_type) {
            'new' => 'مستجد',
            'transfer' => 'تحويل',
            'return' => 'عائد من سفر',
            default => $this->enrollment_type
        };
    }

    /**
     * الحصول على اسم المستوى بالعربية
     */
    public function getPreviousLevelNameAttribute(): string
    {
        return match($this->previous_level) {
            'excellent' => 'تفوق',
            'good' => 'جيد',
            'needs_support' => 'يحتاج دعم',
            default => $this->previous_level
        };
    }

    /**
     * الحصول على اسم اللغة الثانية بالعربية
     */
    public function getSecondLanguageNameAttribute(): string
    {
        return match($this->second_language) {
            'french' => 'فرنسي',
            'german' => 'ألماني',
            'italian' => 'إيطالي',
            default => $this->second_language
        };
    }

    /**
     * الحصول على اسم نوع المنهج بالعربية
     */
    public function getCurriculumTypeNameAttribute(): string
    {
        return match($this->curriculum_type) {
            'national' => 'وطني',
            'international' => 'دولي',
            'languages' => 'لغات',
            default => $this->curriculum_type
        };
    }
}
