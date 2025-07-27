<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    /**
     * تنسيق المبالغ المالية
     */
    public static function formatCurrency($amount, $currency = 'جنيه')
    {
        if ($amount === null || $amount === '') {
            return 'غير محدد';
        }
        
        return number_format(floatval($amount), 2) . ' ' . $currency;
    }

    /**
     * تنسيق أرقام الهاتف المصرية
     */
    public static function formatEgyptianPhone($phone)
    {
        if (!$phone) return null;
        
        // إزالة أي رموز غير رقمية
        $cleanPhone = preg_replace('/[^\d]/', '', $phone);
        
        // تنسيق للأرقام المصرية (11 رقم تبدأ بـ 01)
        if (strlen($cleanPhone) == 11 && substr($cleanPhone, 0, 2) == '01') {
            return substr($cleanPhone, 0, 4) . '-' . substr($cleanPhone, 4, 3) . '-' . substr($cleanPhone, 7);
        }
        
        // تنسيق للأرقام الأرضية (8 أرقام + كود المنطقة)
        if (strlen($cleanPhone) >= 8) {
            return chunk_split($cleanPhone, 3, '-');
        }
        
        return $phone;
    }

    /**
     * تنسيق التواريخ بالعربية
     */
    public static function formatDateInArabic($date, $format = 'full')
    {
        if (!$date) return null;
        
        $carbonDate = Carbon::parse($date);
        
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        $days = [
            'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة',
            'Saturday' => 'السبت'
        ];
        
        switch ($format) {
            case 'short':
                return $carbonDate->day . '/' . $carbonDate->month . '/' . $carbonDate->year;
            
            case 'medium':
                return $carbonDate->day . ' ' . $months[$carbonDate->month] . ' ' . $carbonDate->year;
            
            case 'full':
                return $days[$carbonDate->format('l')] . ' ' . $carbonDate->day . ' ' . 
                       $months[$carbonDate->month] . ' ' . $carbonDate->year;
            
            case 'time':
                return $carbonDate->format('H:i');
            
            case 'datetime':
                return $carbonDate->day . ' ' . $months[$carbonDate->month] . ' ' . 
                       $carbonDate->year . ' - ' . $carbonDate->format('H:i');
            
            default:
                return $carbonDate->day . ' ' . $months[$carbonDate->month] . ' ' . $carbonDate->year;
        }
    }

    /**
     * تنسيق الأسماء (أول حرف كبير لكل كلمة)
     */
    public static function formatName($name)
    {
        if (!$name) return null;
        
        // إزالة المسافات الزائدة
        $cleanName = trim(preg_replace('/\s+/', ' ', $name));
        
        // جعل أول حرف من كل كلمة كبير
        return mb_convert_case($cleanName, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * تنسيق النسب المئوية
     */
    public static function formatPercentage($value, $decimals = 2)
    {
        if ($value === null || $value === '') {
            return '0%';
        }
        
        return round(floatval($value), $decimals) . '%';
    }

    /**
     * تنسيق أحجام الملفات
     */
    public static function formatFileSize($bytes)
    {
        if (!$bytes || $bytes <= 0) return 'غير محدد';
        
        $units = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت', 'تيرابايت'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * تنسيق الوقت المنقضي بالعربية
     */
    public static function formatTimeAgo($date)
    {
        if (!$date) return null;
        
        $carbonDate = Carbon::parse($date);
        $diffInSeconds = $carbonDate->diffInSeconds(Carbon::now());
        $diffInMinutes = $carbonDate->diffInMinutes(Carbon::now());
        $diffInHours = $carbonDate->diffInHours(Carbon::now());
        $diffInDays = $carbonDate->diffInDays(Carbon::now());
        
        if ($diffInSeconds < 60) {
            return 'منذ لحظات';
        } elseif ($diffInMinutes < 60) {
            return "منذ {$diffInMinutes} دقيقة";
        } elseif ($diffInHours < 24) {
            return "منذ {$diffInHours} ساعة";
        } elseif ($diffInDays == 1) {
            return 'أمس';
        } elseif ($diffInDays < 7) {
            return "منذ {$diffInDays} أيام";
        } elseif ($diffInDays < 30) {
            $weeks = ceil($diffInDays / 7);
            return "منذ {$weeks} أسبوع";
        } elseif ($diffInDays < 365) {
            $months = ceil($diffInDays / 30);
            return "منذ {$months} شهر";
        } else {
            $years = ceil($diffInDays / 365);
            return "منذ {$years} سنة";
        }
    }

    /**
     * تنسيق الأرقام القومية المصرية
     */
    public static function formatNationalId($nationalId)
    {
        if (!$nationalId) return null;
        
        $cleanId = preg_replace('/[^\d]/', '', $nationalId);
        
        if (strlen($cleanId) == 14) {
            return substr($cleanId, 0, 1) . ' ' . 
                   substr($cleanId, 1, 2) . ' ' . 
                   substr($cleanId, 3, 2) . ' ' . 
                   substr($cleanId, 5, 2) . ' ' . 
                   substr($cleanId, 7, 3) . ' ' . 
                   substr($cleanId, 10, 2) . ' ' . 
                   substr($cleanId, 12, 2);
        }
        
        return $nationalId;
    }

    /**
     * تنسيق العناوين
     */
    public static function formatAddress($address, $maxLength = null)
    {
        if (!$address) return null;
        
        $cleanAddress = trim(ucfirst(strtolower($address)));
        
        if ($maxLength && strlen($cleanAddress) > $maxLength) {
            return substr($cleanAddress, 0, $maxLength - 3) . '...';
        }
        
        return $cleanAddress;
    }

    /**
     * تنسيق الدرجات الأكاديمية
     */
    public static function formatGrade($grade, $maxGrade = 100)
    {
        if ($grade === null || $maxGrade === null) {
            return 'غير محدد';
        }
        
        $percentage = ($grade / $maxGrade) * 100;
        
        return $grade . ' من ' . $maxGrade . ' (' . round($percentage, 1) . '%)';
    }

    /**
     * تنسيق حالات النظام بالعربية
     */
    public static function formatStatus($status, $type = 'general')
    {
        $translations = [
            'general' => [
                'active' => 'نشط',
                'inactive' => 'غير نشط',
                'pending' => 'في الانتظار',
                'approved' => 'موافق عليه',
                'rejected' => 'مرفوض',
                'cancelled' => 'ملغي'
            ],
            'payment' => [
                'pending' => 'في الانتظار',
                'confirmed' => 'مؤكد',
                'cancelled' => 'ملغي',
                'partial' => 'مدفوع جزئياً',
                'paid' => 'مدفوع بالكامل',
                'overdue' => 'متأخر'
            ],
            'student' => [
                'active' => 'نشط',
                'inactive' => 'غير نشط',
                'graduated' => 'متخرج',
                'transferred' => 'منقول'
            ]
        ];
        
        return $translations[$type][$status] ?? $status;
    }

    /**
     * تنسيق قائمة العناصر
     */
    public static function formatList($items, $separator = '، ', $lastSeparator = ' و ')
    {
        if (!is_array($items) || empty($items)) {
            return '';
        }
        
        if (count($items) == 1) {
            return $items[0];
        }
        
        if (count($items) == 2) {
            return implode($lastSeparator, $items);
        }
        
        $lastItem = array_pop($items);
        return implode($separator, $items) . $lastSeparator . $lastItem;
    }
}
