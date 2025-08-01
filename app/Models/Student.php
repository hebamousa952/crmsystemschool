<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Traits\SecureModel;

class Student extends Model
{
    use HasFactory, SecureModel;

    protected $fillable = [
        'student_id',
        'national_id',
        'full_name',
        'password',
        'birth_date',
        'birth_place',
        'nationality',
        'gender',
        'religion',
        'address',
        'special_needs',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        // لا توجد حقول حساسة في نموذج الطالب
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
        'national_id' => 'required|string|unique:students,national_id|max:14',
        'full_name' => 'required|string|max:255',
        'birth_date' => 'required|date|before:today',
        'birth_place' => 'required|string|max:255',
        'nationality' => 'string|max:255',
        'gender' => 'required|in:ذكر,أنثى',
        'religion' => 'string|max:255',
        'address' => 'required|string',
        'special_needs' => 'nullable|string',
        'notes' => 'nullable|string',
        'status' => 'in:active,inactive,graduated,transferred,suspended'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // ==================== Accessors (قراءة البيانات) ====================

    // Accessor: حساب العمر
    public function getAgeAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

    // Accessor: العمر بالسنوات والشهور
    public function getDetailedAgeAttribute()
    {
        if (!$this->birth_date) return null;

        $birthDate = Carbon::parse($this->birth_date);
        $now = Carbon::now();

        $years = $birthDate->diffInYears($now);
        $months = $birthDate->copy()->addYears($years)->diffInMonths($now);

        return "{$years} سنة و {$months} شهر";
    }

    // Accessor: الاسم الأول فقط
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

    /**
     * العلاقة مع البيانات الأكاديمية
     */
    public function academicInfo(): HasOne
    {
        return $this->hasOne(AcademicInfo::class);
    }

    /**
     * العلاقة مع أولياء الأمور
     */
    public function parentGuardians(): HasMany
    {
        return $this->hasMany(ParentGuardian::class);
    }

    /**
     * العلاقة مع الأب
     */
    public function father(): HasOne
    {
        return $this->hasOne(ParentGuardian::class)->where('guardian_type', 'father');
    }

    /**
     * العلاقة مع الأم
     */
    public function mother(): HasOne
    {
        return $this->hasOne(ParentGuardian::class)->where('guardian_type', 'mother');
    }

    /**
     * العلاقة مع جهات الاتصال في الطوارئ
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * العلاقة مع جهة الاتصال الأساسية في الطوارئ
     */
    public function primaryEmergencyContact(): HasOne
    {
        return $this->hasOne(EmergencyContact::class);
    }

    // Accessor: سنوات الدراسة
    public function getStudyYearsAttribute()
    {
        if (!$this->enrollment_date) return 0;

        return Carbon::parse($this->enrollment_date)->diffInYears(Carbon::now());
    }

    // Accessor: حالة الطالب بالعربية
    public function getStatusInArabicAttribute()
    {
        $statuses = [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'graduated' => 'متخرج',
            'transferred' => 'منقول'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    // Accessor: الجنس بالعربية
    public function getGenderInArabicAttribute()
    {
        return $this->gender === 'male' ? 'ذكر' : 'أنثى';
    }

    // Accessor: نوع القيد بالعربية
    public function getEnrollmentTypeInArabicAttribute()
    {
        $types = [
            'new' => 'جديد',
            'transfer' => 'منقول',
            'returning' => 'عائد'
        ];

        return $types[$this->enrollment_type] ?? $this->enrollment_type;
    }

    // Accessor: إجمالي المصروفات المستحقة
    public function getTotalFeesAttribute()
    {
        $tuitionFees = $this->tuitionFees()->sum('total_amount');
        $otherFees = $this->otherFees()->sum('total');

        return $tuitionFees + $otherFees;
    }

    // Accessor: إجمالي المدفوع
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'confirmed')->sum('amount');
    }

    // Accessor: المبلغ المتبقي
    public function getRemainingAmountAttribute()
    {
        return $this->total_fees - $this->total_paid;
    }

    // Accessor: نسبة السداد
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_fees == 0) return 100;

        return round(($this->total_paid / $this->total_fees) * 100, 2);
    }

    // ==================== Mutators (كتابة البيانات) ====================

    // Mutator: تنسيق الاسم الكامل
    public function setFullNameAttribute($value)
    {
        // إزالة المسافات الزائدة وتنسيق الاسم
        $cleanName = trim(preg_replace('/\s+/', ' ', $value));

        // جعل أول حرف من كل كلمة كبير
        $this->attributes['full_name'] = mb_convert_case($cleanName, MB_CASE_TITLE, 'UTF-8');
    }

    // Mutator: تنسيق الرقم القومي
    public function setNationalIdAttribute($value)
    {
        // إزالة أي مسافات أو رموز
        $cleanId = preg_replace('/[^0-9]/', '', $value);

        $this->attributes['national_id'] = $cleanId;
    }

    // Mutator: تنسيق مكان الميلاد
    public function setBirthPlaceAttribute($value)
    {
        $this->attributes['birth_place'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: تنسيق الجنسية
    public function setNationalityAttribute($value)
    {
        $this->attributes['nationality'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: تنسيق الديانة
    public function setReligionAttribute($value)
    {
        $this->attributes['religion'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: تنسيق اللغة الأولى
    public function setFirstLanguageAttribute($value)
    {
        $this->attributes['first_language'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: تنسيق اللغة الثانية
    public function setSecondLanguageAttribute($value)
    {
        if ($value) {
            $this->attributes['second_language'] = trim(ucfirst(strtolower($value)));
        }
    }

    // ==================== العلاقات ====================

    // ==================== العلاقات المالية الجديدة ====================

    /**
     * العلاقة مع سجلات المصروفات المالية
     */
    public function studentFeeRecords()
    {
        return $this->hasMany(StudentFeeRecord::class);
    }

    /**
     * العلاقة مع سجل المصروفات للعام الحالي
     */
    public function currentYearFeeRecord()
    {
        return $this->hasOne(StudentFeeRecord::class)
            ->where('academic_year', now()->year . '-' . (now()->year + 1))
            ->where('is_active', true);
    }

    /**
     * العلاقة مع جميع الأقساط عبر سجلات المصروفات
     */
    public function allInstallments()
    {
        return $this->hasManyThrough(Installment::class, StudentFeeRecord::class, 'student_id', 'student_fee_record_id');
    }

    /**
     * العلاقة مع جميع الخصومات عبر سجلات المصروفات
     */
    public function allDiscounts()
    {
        return $this->hasManyThrough(Discount::class, StudentFeeRecord::class, 'student_id', 'student_fee_record_id');
    }

    /**
     * العلاقة مع جميع المتأخرات عبر سجلات المصروفات
     */
    public function allArrears()
    {
        return $this->hasManyThrough(Arrear::class, StudentFeeRecord::class, 'student_id', 'student_fee_record_id');
    }

    /**
     * العلاقة مع جميع الفواتير عبر سجلات المصروفات
     */
    public function allInvoices()
    {
        return $this->hasManyThrough(Invoice::class, StudentFeeRecord::class, 'student_id', 'student_fee_record_id');
    }

    /**
     * العلاقة مع جميع الاستردادات عبر سجلات المصروفات
     */
    public function allRefunds()
    {
        return $this->hasManyThrough(Refund::class, StudentFeeRecord::class, 'student_id', 'student_fee_record_id');
    }

    // ==================== العلاقات المالية القديمة (للتوافق) ====================

    // العلاقة مع المصروفات الدراسية (علاقة واحد لمتعدد)
    public function tuitionFees()
    {
        return $this->hasMany(TuitionFee::class);
    }

    // العلاقة مع المصروفات الأخرى (علاقة واحد لمتعدد)
    public function otherFees()
    {
        return $this->hasMany(OtherFee::class);
    }

    // العلاقة مع المدفوعات (علاقة واحد لمتعدد)
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // العلاقة مع الإشعارات (علاقة واحد لمتعدد)
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // العلاقة مع التعليقات (علاقة واحد لمتعدد)
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // العلاقة مع الدرجات (علاقة واحد لمتعدد)
    public function grades()
    {
        return $this->hasMany(StudentGrade::class);
    }

    // العلاقة مع المستندات (علاقة واحد لمتعدد)
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // ==================== Scopes مفيدة ====================

    // الطلاب النشطين
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // الطلاب في مرحلة معينة
    public function scopeInGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    // الطلاب في فصل معين
    public function scopeInClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    // الطلاب في سنة دراسية معينة
    public function scopeInAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }
}
