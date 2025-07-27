<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'title',
        'message',
        'type',
        'recipient_type',
        'status'
    ];

    // Accessor: تنسيق نوع الإشعار
    public function getFormattedTypeAttribute()
    {
        $types = [
            'general' => 'عام',
            'payment' => 'مالي',
            'academic' => 'أكاديمي',
            'disciplinary' => 'انضباطي',
            'event' => 'فعالية'
        ];

        return $types[$this->type] ?? $this->type;
    }

    // Scope: الإشعارات غير المقروءة
    public function scopeUnread($query)
    {
        return $query->where('status', '!=', 'read');
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد - اختيارية)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
