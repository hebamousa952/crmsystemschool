<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Check if user has permission to access student data
     */
    public static function canAccessStudentData($studentId, $userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        
        if (!$user) {
            return false;
        }

        // المديرون يمكنهم الوصول لجميع البيانات
        if ($user->role === 'admin') {
            return true;
        }

        // المحاسبون يمكنهم الوصول للبيانات المالية فقط
        if ($user->role === 'accountant') {
            return true; // سيتم تقييد الحقول في مكان آخر
        }

        // المدرسون يمكنهم الوصول للبيانات الأكاديمية فقط
        if ($user->role === 'teacher') {
            return true; // سيتم تقييد الحقول في مكان آخر
        }

        // السكرتيرون يمكنهم الوصول للبيانات الأساسية فقط
        if ($user->role === 'secretary') {
            return true; // سيتم تقييد الحقول في مكان آخر
        }

        return false;
    }

    /**
     * Get allowed fields for user role
     */
    public static function getAllowedFields($model, $userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        
        if (!$user) {
            return [];
        }

        $modelName = class_basename($model);
        
        $permissions = [
            'admin' => [
                'Student' => ['*'], // جميع الحقول
                'Payment' => ['*'],
                'TuitionFee' => ['*'],
                'Parent' => ['*'],
                'Mother' => ['*']
            ],
            'accountant' => [
                'Student' => ['id', 'full_name_ar', 'grade_id', 'classroom_id'],
                'Payment' => ['*'],
                'TuitionFee' => ['*'],
                'Parent' => ['id', 'full_name', 'phone', 'email'],
                'Mother' => ['id', 'full_name', 'phone', 'email']
            ],
            'teacher' => [
                'Student' => ['id', 'full_name_ar', 'grade_id', 'classroom_id', 'academic_year'],
                'Payment' => ['id', 'amount', 'payment_date', 'status'],
                'TuitionFee' => ['id', 'academic_year', 'status'],
                'Parent' => ['id', 'full_name', 'phone'],
                'Mother' => ['id', 'full_name', 'phone']
            ],
            'secretary' => [
                'Student' => ['id', 'full_name_ar', 'grade_id', 'classroom_id', 'status'],
                'Payment' => ['id', 'amount', 'payment_date', 'status'],
                'TuitionFee' => ['id', 'academic_year', 'total_amount', 'status'],
                'Parent' => ['id', 'full_name', 'phone', 'email'],
                'Mother' => ['id', 'full_name', 'phone', 'email']
            ]
        ];

        return $permissions[$user->role][$modelName] ?? [];
    }

    /**
     * Filter model data based on user permissions
     */
    public static function filterModelData($model, $data, $userId = null)
    {
        $allowedFields = static::getAllowedFields($model, $userId);
        
        if (in_array('*', $allowedFields)) {
            return $data;
        }

        if (is_array($data)) {
            return array_intersect_key($data, array_flip($allowedFields));
        }

        if (is_object($data)) {
            $filtered = new \stdClass();
            foreach ($allowedFields as $field) {
                if (isset($data->$field)) {
                    $filtered->$field = $data->$field;
                }
            }
            return $filtered;
        }

        return $data;
    }

    /**
     * Check if user can perform specific action
     */
    public static function canPerformAction($action, $model = null, $userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        
        if (!$user) {
            return false;
        }

        $permissions = [
            'admin' => ['create', 'read', 'update', 'delete', 'approve_payments'],
            'accountant' => ['read', 'update', 'approve_payments'],
            'teacher' => ['read'],
            'secretary' => ['create', 'read', 'update']
        ];

        $userPermissions = $permissions[$user->role] ?? [];
        
        return in_array($action, $userPermissions);
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = [])
    {
        Log::channel('security')->info($event, array_merge([
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ], $details));
    }

    /**
     * Validate sensitive data access
     */
    public static function validateSensitiveAccess($field, $model = null)
    {
        $sensitiveFields = [
            'national_id', 'password', 'guardian_details',
            'discount_amount', 'original_amount'
        ];

        if (in_array($field, $sensitiveFields)) {
            static::logSecurityEvent('Sensitive field access', [
                'field' => $field,
                'model' => $model ? class_basename($model) : null
            ]);

            // التحقق من الصلاحية
            if (!static::canPerformAction('read', $model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate secure token for sensitive operations
     */
    public static function generateSecureToken($purpose = 'general')
    {
        return hash('sha256', uniqid($purpose, true) . time() . Auth::id());
    }

    /**
     * Verify secure token
     */
    public static function verifySecureToken($token, $purpose = 'general', $maxAge = 3600)
    {
        // هذا مثال بسيط - في التطبيق الحقيقي يجب حفظ التوكن في قاعدة البيانات
        // مع وقت الانتهاء والغرض
        return !empty($token) && strlen($token) === 64;
    }

    /**
     * Check for suspicious activity patterns
     */
    public static function detectSuspiciousActivity($userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        
        if (!$user) {
            return false;
        }

        // فحص عدد المحاولات في فترة زمنية قصيرة
        // فحص الوصول لبيانات متعددة في وقت قصير
        // فحص أنماط غير طبيعية في الاستخدام
        
        // هذا مثال بسيط - يمكن تطويره أكثر
        return false;
    }

    /**
     * Get security summary for user
     */
    public static function getUserSecuritySummary($userId = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        
        if (!$user) {
            return null;
        }

        return [
            'user_id' => $user->id,
            'role' => $user->role,
            'permissions' => static::getAllowedFields('Student', $user->id),
            'can_approve_payments' => static::canPerformAction('approve_payments', null, $user->id),
            'last_login' => $user->updated_at,
            'security_level' => static::calculateSecurityLevel($user)
        ];
    }

    /**
     * Calculate user security level
     */
    protected static function calculateSecurityLevel($user)
    {
        $levels = [
            'admin' => 'high',
            'accountant' => 'medium',
            'teacher' => 'low',
            'secretary' => 'medium'
        ];

        return $levels[$user->role] ?? 'unknown';
    }
}
