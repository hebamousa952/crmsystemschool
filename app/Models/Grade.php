<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_name',
        'grade_code',
        'level',
        'grade_number',
        'is_active'
    ];

    protected $casts = [
        'grade_number' => 'integer',
        'is_active' => 'boolean'
    ];

    // ==================== العلاقات ====================

    // العلاقة مع الفصول (علاقة واحد لمتعدد)
    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    // العلاقة مع الطلاب (علاقة واحد لمتعدد)
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // ==================== Accessors ====================

    // عدد الفصول في هذه المرحلة
    public function getClassroomsCountAttribute()
    {
        return $this->classrooms()->count();
    }

    // عدد الطلاب في هذه المرحلة
    public function getStudentsCountAttribute()
    {
        return $this->students()->count();
    }

    // ==================== Scopes ====================

    // المراحل النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // المراحل الابتدائية
    public function scopePrimary($query)
    {
        return $query->where('level', 'primary');
    }

    // المراحل الإعدادية
    public function scopePreparatory($query)
    {
        return $query->where('level', 'preparatory');
    }
}
