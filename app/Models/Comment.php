<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'user_id',
        'comment',
        'type'
    ];

    // Accessor: تنسيق نوع التعليق
    public function getFormattedTypeAttribute()
    {
        $types = [
            'academic' => 'أكاديمي',
            'behavioral' => 'سلوكي',
            'general' => 'عام'
        ];

        return $types[$this->type] ?? $this->type;
    }

    // Scope: التعليقات الأكاديمية
    public function scopeAcademic($query)
    {
        return $query->where('type', 'academic');
    }

    // Scope: التعليقات السلوكية
    public function scopeBehavioral($query)
    {
        return $query->where('type', 'behavioral');
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // العلاقة مع المستخدم الذي كتب التعليق
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
