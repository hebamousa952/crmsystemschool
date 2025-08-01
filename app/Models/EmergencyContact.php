<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'contact_name',
        'relationship',
        'phone',
        'address'
    ];

    /**
     * Validation rules
     */
    public static $rules = [
        'student_id' => 'required|exists:students,id',
        'contact_name' => 'required|string|max:255',
        'relationship' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string',
    ];

    // ==================== Mutators ====================

    /**
     * تنسيق اسم جهة الاتصال
     */
    public function setContactNameAttribute($value)
    {
        $cleanName = trim(preg_replace('/\s+/', ' ', $value));
        $this->attributes['contact_name'] = mb_convert_case($cleanName, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * تنسيق رقم الهاتف
     */
    public function setPhoneAttribute($value)
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $value);
        $this->attributes['phone'] = $cleanPhone;
    }

    // ==================== Accessors ====================

    // Accessor: تنسيق رقم الهاتف
    public function getFormattedPhoneAttribute()
    {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $this->phone);
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
