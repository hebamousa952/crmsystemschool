<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'guardian_type',
        'full_name',
        'relationship',
        'national_id',
        'job_title',
        'workplace',
        'education_level',
        'mobile_phone',
        'alternative_phone',
        'email',
        'address',
        'marital_status',
        'has_legal_guardian',
        'social_media_accounts',
    ];

    protected $casts = [
        'has_legal_guardian' => 'boolean',
        'social_media_accounts' => 'array',
    ];

    /**
     * Validation rules
     */
    public static $rules = [
        'student_id' => 'required|exists:students,id',
        'guardian_type' => 'required|in:father,mother,legal_guardian',
        'full_name' => 'required|string|max:255',
        'relationship' => 'required|string|max:255',
        'national_id' => 'required|string|max:14|unique:parent_guardians,national_id',
        'job_title' => 'nullable|string|max:255',
        'workplace' => 'nullable|string|max:255',
        'education_level' => 'nullable|string|max:255',
        'mobile_phone' => 'required|string|max:20',
        'alternative_phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'required|string',
        'marital_status' => 'nullable|string|max:255',
        'has_legal_guardian' => 'boolean',
        'social_media_accounts' => 'nullable|array',
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
     * العلاقة مع الوصي القانوني
     */
    public function legalGuardian(): HasOne
    {
        return $this->hasOne(LegalGuardian::class);
    }

    // ==================== Accessors ====================

    /**
     * نوع ولي الأمر بالعربية
     */
    public function getGuardianTypeNameAttribute(): string
    {
        return match($this->guardian_type) {
            'father' => 'الأب',
            'mother' => 'الأم',
            'legal_guardian' => 'وصي قانوني',
            default => $this->guardian_type
        };
    }

    /**
     * الحالة الاجتماعية بالعربية
     */
    public function getMaritalStatusNameAttribute(): string
    {
        return match($this->marital_status) {
            'single' => 'أعزب',
            'married' => 'متزوج',
            'divorced' => 'مطلق',
            'widowed' => 'أرمل',
            default => $this->marital_status
        };
    }

    // ==================== Mutators ====================

    /**
     * تنسيق الاسم الكامل
     */
    public function setFullNameAttribute($value)
    {
        $cleanName = trim(preg_replace('/\s+/', ' ', $value));
        $this->attributes['full_name'] = mb_convert_case($cleanName, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * تنسيق الرقم القومي
     */
    public function setNationalIdAttribute($value)
    {
        $cleanId = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['national_id'] = $cleanId;
    }

    /**
     * تنسيق رقم الهاتف
     */
    public function setMobilePhoneAttribute($value)
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $value);
        $this->attributes['mobile_phone'] = $cleanPhone;
    }

    /**
     * تنسيق رقم الهاتف البديل
     */
    public function setAlternativePhoneAttribute($value)
    {
        if ($value) {
            $cleanPhone = preg_replace('/[^0-9+]/', '', $value);
            $this->attributes['alternative_phone'] = $cleanPhone;
        }
    }
}
