<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_guardian_id',
        'full_name',
        'national_id',
        'relationship',
        'phone',
        'address',
        'legal_document_number',
        'legal_document_details',
    ];

    /**
     * Validation rules
     */
    public static $rules = [
        'parent_guardian_id' => 'required|exists:parent_guardians,id',
        'full_name' => 'required|string|max:255',
        'national_id' => 'required|string|max:14|unique:legal_guardians,national_id',
        'relationship' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string',
        'legal_document_number' => 'nullable|string|max:255',
        'legal_document_details' => 'nullable|string',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع ولي الأمر
     */
    public function parentGuardian(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class);
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
    public function setPhoneAttribute($value)
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $value);
        $this->attributes['phone'] = $cleanPhone;
    }
}
