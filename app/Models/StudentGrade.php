<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject',
        'grade',
        'max_grade',
        'academic_year',
        'term',
        'exam_type'
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'max_grade' => 'decimal:2'
    ];

    // ==================== Accessors (قراءة البيانات) ====================

    // Accessor: النسبة المئوية
    public function getPercentageAttribute()
    {
        if ($this->max_grade == 0) return 0;
        return round(($this->grade / $this->max_grade) * 100, 2);
    }

    // Accessor: النسبة المئوية المنسقة
    public function getFormattedPercentageAttribute()
    {
        return $this->percentage . '%';
    }

    // Accessor: تقدير الدرجة
    public function getLetterGradeAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 95) return 'ممتاز مرتفع';
        if ($percentage >= 90) return 'ممتاز';
        if ($percentage >= 85) return 'جيد جداً مرتفع';
        if ($percentage >= 80) return 'جيد جداً';
        if ($percentage >= 75) return 'جيد مرتفع';
        if ($percentage >= 70) return 'جيد';
        if ($percentage >= 65) return 'مقبول مرتفع';
        if ($percentage >= 60) return 'مقبول';
        if ($percentage >= 50) return 'ضعيف';
        return 'ضعيف جداً';
    }

    // Accessor: نقاط التقدير (GPA)
    public function getGradePointsAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 95) return 4.0;
        if ($percentage >= 90) return 3.7;
        if ($percentage >= 85) return 3.3;
        if ($percentage >= 80) return 3.0;
        if ($percentage >= 75) return 2.7;
        if ($percentage >= 70) return 2.3;
        if ($percentage >= 65) return 2.0;
        if ($percentage >= 60) return 1.7;
        if ($percentage >= 50) return 1.0;
        return 0.0;
    }

    // Accessor: حالة النجاح/الرسوب
    public function getPassStatusAttribute()
    {
        return $this->percentage >= 50 ? 'ناجح' : 'راسب';
    }

    // Accessor: لون التقدير للعرض
    public function getGradeColorAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) return 'success'; // أخضر
        if ($percentage >= 80) return 'info';    // أزرق
        if ($percentage >= 70) return 'warning'; // أصفر
        if ($percentage >= 60) return 'secondary'; // رمادي
        return 'danger'; // أحمر
    }

    // Accessor: الفصل الدراسي بالعربية
    public function getTermInArabicAttribute()
    {
        $terms = [
            'first' => 'الفصل الأول',
            'second' => 'الفصل الثاني',
            'final' => 'الامتحان النهائي'
        ];

        return $terms[$this->term] ?? $this->term;
    }

    // Accessor: نوع الامتحان بالعربية
    public function getExamTypeInArabicAttribute()
    {
        $types = [
            'quiz' => 'اختبار قصير',
            'midterm' => 'امتحان نصف الفصل',
            'final' => 'امتحان نهائي',
            'assignment' => 'واجب'
        ];

        return $types[$this->exam_type] ?? $this->exam_type;
    }

    // Accessor: الدرجة المنسقة
    public function getFormattedGradeAttribute()
    {
        return $this->grade . ' من ' . $this->max_grade;
    }

    // Accessor: النقاط المفقودة
    public function getLostPointsAttribute()
    {
        return $this->max_grade - $this->grade;
    }

    // Accessor: تحليل الأداء
    public function getPerformanceAnalysisAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) {
            return 'أداء ممتاز - استمر على هذا المستوى';
        } elseif ($percentage >= 80) {
            return 'أداء جيد جداً - يمكن تحسينه قليلاً';
        } elseif ($percentage >= 70) {
            return 'أداء جيد - يحتاج لمزيد من التركيز';
        } elseif ($percentage >= 60) {
            return 'أداء مقبول - يحتاج لتحسين كبير';
        } else {
            return 'أداء ضعيف - يحتاج لمراجعة شاملة';
        }
    }

    // ==================== Mutators (كتابة البيانات) ====================

    // Mutator: تنسيق الدرجة
    public function setGradeAttribute($value)
    {
        $this->attributes['grade'] = round(floatval($value), 2);
    }

    // Mutator: تنسيق الدرجة العظمى
    public function setMaxGradeAttribute($value)
    {
        $this->attributes['max_grade'] = round(floatval($value), 2);
    }

    // Mutator: تنسيق اسم المادة
    public function setSubjectAttribute($value)
    {
        $this->attributes['subject'] = trim(ucfirst(strtolower($value)));
    }

    // Mutator: التحقق من صحة الدرجة
    public function setGradeAndMaxGrade($grade, $maxGrade)
    {
        if ($grade > $maxGrade) {
            throw new \InvalidArgumentException('الدرجة لا يمكن أن تكون أكبر من الدرجة العظمى');
        }

        $this->attributes['grade'] = round(floatval($grade), 2);
        $this->attributes['max_grade'] = round(floatval($maxGrade), 2);
    }

    // Scope: درجات الفصل الأول
    public function scopeFirstTerm($query)
    {
        return $query->where('term', 'first');
    }

    // Scope: الامتحانات النهائية
    public function scopeFinalExams($query)
    {
        return $query->where('exam_type', 'final');
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
