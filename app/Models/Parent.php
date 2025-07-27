<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'student_id',
        'full_name',
        'national_id',
        'relationship',
        'job',
        'employer',
        'qualification',
        'phone',
        'alternative_phone',
        'email',
        'address',
        'marital_status',
        'has_legal_guardian',
        'guardian_details',
        'social_media'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'national_id', // حماية الرقم القومي
        'guardian_details', // حماية تفاصيل الوصي
    ];

    /**
     * The attributes that are guarded from mass assignment.
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Validation rules for mass assignment protection.
     */
    public static $rules = [
        'student_id' => 'required|exists:students,id',
        'full_name' => 'required|string|max:255',
        'national_id' => 'required|string|unique:parents,national_id|max:14',
        'relationship' => 'required|in:father,guardian',
        'phone' => 'required|string|max:20',
        'alternative_phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'required|string',
        'marital_status' => 'required|in:married,divorced,widowed,single',
        'has_legal_guardian' => 'boolean',
        'job' => 'nullable|string|max:255',
        'employer' => 'nullable|string|max:255',
        'qualification' => 'nullable|string|max:255',
        'social_media' => 'nullable|string|max:255'
    ];

    protected $casts = [
        'has_legal_guardian' => 'boolean'
    ];

    // ==================== Accessors (قراءة البيانات) ====================

    // Accessor: تنسيق رقم الهاتف
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return null;

        // تنسيق للأرقام المصرية
        $phone = preg_replace('/[^\d]/', '', $this->phone);

        if (strlen($phone) == 11 && substr($phone, 0, 2) == '01') {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }

        return $this->phone;
    }

    // Accessor: تنسيق الهاتف البديل
    public function getFormattedAlternativePhoneAttribute()
    {
        if (!$this->alternative_phone) return null;

        $phone = preg_replace('/[^\d]/', '', $this->alternative_phone);

        if (strlen($phone) == 11 && substr($phone, 0, 2) == '01') {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }

        return $this->alternative_phone;
    }

    // Accessor: العلاقة بالعربية
    public function getRelationshipInArabicAttribute()
    {
        $relationships = [
            'father' => 'والد',
            'guardian' => 'وصي'
        ];

        return $relationships[$this->relationship] ?? $this->relationship;
    }

    // Accessor: الحالة الاجتماعية بالعربية
    public function getMaritalStatusInArabicAttribute()
    {
        $statuses = [
            'married' => 'متزوج',
            'divorced' => 'مطلق',
            'widowed' => 'أرمل',
            'single' => 'أعزب'
        ];

        return $statuses[$this->marital_status] ?? $this->marital_status;
    }

    // Accessor: الاسم الأول
    public function getFirstNameAttribute()
    {
        $nameParts = explode(' ', $this->full_name);
        return $nameParts[0] ?? '';
    }

    // Accessor: الاسم الأخير
    public function getLastNameAttribute()
    {
        $nameParts = explode(' ', $this->full_name);
        return end($nameParts) ?? '';
    }

    // Accessor: معلومات الاتصال المختصرة
    public function getContactInfoAttribute()
    {
        $info = [];

        if ($this->phone) {
            $info[] = 'ت: ' . $this->formatted_phone;
        }

        if ($this->email) {
            $info[] = 'إ: ' . $this->email;
        }

        return implode(' | ', $info);
    }

    // Accessor: العنوان المختصر
    public function getShortAddressAttribute()
    {
        return strlen($this->address) > 50 ?
            substr($this->address, 0, 47) . '...' : $this->address;
    }

    // ==================== Mutators (كتابة البيانات) ====================

    // Mutator: تنسيق الاسم الكامل
    public function setFullNameAttribute($value)
    {
        $cleanName = trim(preg_replace('/\s+/', ' ', $value));
        $this->attributes['full_name'] = mb_convert_case($cleanName, MB_CASE_TITLE, 'UTF-8');
    }

    // Mutator: تنسيق رقم الهاتف
    public function setPhoneAttribute($value)
    {
        // إزالة أي رموز غير رقمية
        $cleanPhone = preg_replace('/[^\d]/', '', $value);

        // إضافة 0 في البداية إذا كان الرقم يبدأ بـ 1
        if (strlen($cleanPhone) == 10 && substr($cleanPhone, 0, 1) == '1') {
            $cleanPhone = '0' . $cleanPhone;
        }

        $this->attributes['phone'] = $cleanPhone;
    }

    // Mutator: تنسيق الهاتف البديل
    public function setAlternativePhoneAttribute($value)
    {
        if ($value) {
            $cleanPhone = preg_replace('/[^\d]/', '', $value);

            if (strlen($cleanPhone) == 10 && substr($cleanPhone, 0, 1) == '1') {
                $cleanPhone = '0' . $cleanPhone;
            }

            $this->attributes['alternative_phone'] = $cleanPhone;
        }
    }

    // Mutator: تنسيق البريد الإلكتروني
    public function setEmailAttribute($value)
    {
        if ($value) {
            $this->attributes['email'] = strtolower(trim($value));
        }
    }

    // Mutator: تنسيق الوظيفة
    public function setJobAttribute($value)
    {
        if ($value) {
            $this->attributes['job'] = trim(ucfirst(strtolower($value)));
        }
    }

    // Mutator: تنسيق جهة العمل
    public function setEmployerAttribute($value)
    {
        if ($value) {
            $this->attributes['employer'] = trim(ucwords(strtolower($value)));
        }
    }

    // Mutator: تنسيق المؤهل
    public function setQualificationAttribute($value)
    {
        if ($value) {
            $this->attributes['qualification'] = trim(ucfirst(strtolower($value)));
        }
    }

    // Mutator: تنسيق العنوان
    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: تنسيق الرقم القومي
    public function setNationalIdAttribute($value)
    {
        $cleanId = preg_replace('/[^\d]/', '', $value);
        $this->attributes['national_id'] = $cleanId;
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // ==================== Scopes ====================

    // أولياء الأمور الآباء
    public function scopeFathers($query)
    {
        return $query->where('relationship', 'father');
    }

    // أولياء الأمور الأوصياء
    public function scopeGuardians($query)
    {
        return $query->where('relationship', 'guardian');
    }

    // أولياء الأمور الذين لديهم وصي قانوني
    public function scopeWithLegalGuardian($query)
    {
        return $query->where('has_legal_guardian', true);
    }
}
