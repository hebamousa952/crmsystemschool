<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mother extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'full_name',
        'national_id',
        'job',
        'employer',
        'phone',
        'email',
        'qualification',
        'address',
        'relationship'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'national_id', // حماية الرقم القومي
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
        'national_id' => 'required|string|unique:mothers,national_id|max:14',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'required|string',
        'relationship' => 'in:mother,stepmother,guardian',
        'job' => 'nullable|string|max:255',
        'employer' => 'nullable|string|max:255',
        'qualification' => 'nullable|string|max:255'
    ];

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

    // ==================== Scopes ====================

    // الأمهات العاملات
    public function scopeWorking($query)
    {
        return $query->whereNotNull('job');
    }

    // الأمهات غير العاملات
    public function scopeNotWorking($query)
    {
        return $query->whereNull('job');
    }
}
