<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'classroom_name',
        'full_name',
        'capacity',
        'current_students',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_students' => 'integer',
        'is_active' => 'boolean'
    ];

    // ==================== العلاقات ====================

    // العلاقة مع المرحلة (علاقة متعدد لواحد)
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // العلاقة مع الطلاب (علاقة واحد لمتعدد)
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // ==================== Accessors ====================

    // المقاعد المتاحة
    public function getAvailableSeatsAttribute()
    {
        return $this->capacity - $this->current_students;
    }

    // نسبة الإشغال
    public function getOccupancyPercentageAttribute()
    {
        if ($this->capacity == 0) return 0;
        return round(($this->current_students / $this->capacity) * 100, 2);
    }

    // هل الفصل ممتلئ
    public function getIsFullAttribute()
    {
        return $this->current_students >= $this->capacity;
    }

    // ==================== Scopes ====================

    // الفصول النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // الفصول المتاحة (غير ممتلئة)
    public function scopeAvailable($query)
    {
        return $query->whereRaw('current_students < capacity');
    }

    // الفصول في مرحلة معينة
    public function scopeInGrade($query, $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }
}
