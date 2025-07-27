<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'file_path',
        'file_type',
        'original_name',
        'description',
        'document_type',
        'file_size'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    // ==================== Accessors (قراءة البيانات) ====================

    // Accessor: حجم الملف بصيغة قابلة للقراءة
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'غير محدد';

        $bytes = $this->file_size;
        $units = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Accessor: تنسيق نوع المستند
    public function getFormattedDocumentTypeAttribute()
    {
        $types = [
            'birth_certificate' => 'شهادة ميلاد',
            'photo' => 'صورة شخصية',
            'medical_report' => 'تقرير طبي',
            'previous_grades' => 'درجات سابقة',
            'transfer_certificate' => 'شهادة نقل',
            'vaccination_record' => 'سجل التطعيمات',
            'parent_id' => 'بطاقة ولي الأمر',
            'other' => 'أخرى'
        ];

        return $types[$this->document_type] ?? $this->document_type;
    }

    // Accessor: امتداد الملف
    public function getFileExtensionAttribute()
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    // Accessor: اسم الملف بدون امتداد
    public function getFileNameWithoutExtensionAttribute()
    {
        return pathinfo($this->original_name, PATHINFO_FILENAME);
    }

    // Accessor: رابط تحميل الملف
    public function getDownloadUrlAttribute()
    {
        return route('documents.download', $this->id);
    }

    // Accessor: رابط عرض الملف
    public function getViewUrlAttribute()
    {
        if (in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            return route('documents.view', $this->id);
        }

        return $this->download_url;
    }

    // Accessor: أيقونة نوع الملف
    public function getFileIconAttribute()
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'jpg' => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png' => 'fas fa-file-image text-info',
            'gif' => 'fas fa-file-image text-info',
            'txt' => 'fas fa-file-alt text-secondary',
            'zip' => 'fas fa-file-archive text-warning',
            'rar' => 'fas fa-file-archive text-warning'
        ];

        return $icons[strtolower($this->file_type)] ?? 'fas fa-file text-muted';
    }

    // Accessor: هل الملف صورة
    public function getIsImageAttribute()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }

    // Accessor: هل الملف PDF
    public function getIsPdfAttribute()
    {
        return strtolower($this->file_type) === 'pdf';
    }

    // Accessor: هل يمكن عرض الملف في المتصفح
    public function getCanPreviewAttribute()
    {
        return $this->is_image || $this->is_pdf;
    }

    // Accessor: تاريخ الرفع منسق
    public function getUploadDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // Accessor: تاريخ الرفع بالعربية
    public function getUploadDateInArabicAttribute()
    {
        $date = $this->created_at;
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        return $date->day . ' ' . $months[$date->month] . ' ' . $date->year;
    }

    // Accessor: حالة المستند (مطلوب/اختياري)
    public function getRequiredStatusAttribute()
    {
        $requiredTypes = ['birth_certificate', 'photo', 'medical_report'];
        return in_array($this->document_type, $requiredTypes) ? 'مطلوب' : 'اختياري';
    }

    // ==================== Mutators (كتابة البيانات) ====================

    // Mutator: تنسيق اسم الملف الأصلي
    public function setOriginalNameAttribute($value)
    {
        // إزالة الأحرف الخطيرة من اسم الملف
        $cleanName = preg_replace('/[^a-zA-Z0-9\u0600-\u06FF._-]/', '_', $value);
        $this->attributes['original_name'] = $cleanName;
    }

    // Mutator: تحديد نوع الملف من الامتداد
    public function setFilePathAttribute($value)
    {
        $this->attributes['file_path'] = $value;

        // استخراج نوع الملف من المسار
        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        $this->attributes['file_type'] = $extension;
    }

    // Mutator: تنسيق الوصف
    public function setDescriptionAttribute($value)
    {
        if ($value) {
            $this->attributes['description'] = trim(strip_tags($value));
        }
    }

    // Mutator: تحديد حجم الملف تلقائياً
    public function setFileSizeFromPath()
    {
        if ($this->file_path && file_exists(storage_path('app/' . $this->file_path))) {
            $this->attributes['file_size'] = filesize(storage_path('app/' . $this->file_path));
        }
    }

    // ==================== وظائف مساعدة ====================

    // التحقق من وجود الملف فعلياً
    public function fileExists()
    {
        return $this->file_path && file_exists(storage_path('app/' . $this->file_path));
    }

    // حذف الملف من التخزين
    public function deleteFile()
    {
        if ($this->fileExists()) {
            unlink(storage_path('app/' . $this->file_path));
        }
    }

    // نسخ الملف إلى مكان آخر
    public function copyFile($newPath)
    {
        if ($this->fileExists()) {
            return copy(
                storage_path('app/' . $this->file_path),
                storage_path('app/' . $newPath)
            );
        }

        return false;
    }

    // Scope: المستندات المطلوبة
    public function scopeRequired($query)
    {
        return $query->whereIn('document_type', ['birth_certificate', 'photo']);
    }

    // ==================== العلاقات ====================

    // العلاقة مع الطالب (علاقة متعدد لواحد)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
