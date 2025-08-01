<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GuardianAccount extends Model
{
    use HasFactory;

    protected $table = 'guardian_accounts';

    protected $fillable = [
        // معلومات ولي الأمر الأساسية
        'guardian_name',
        'guardian_id_number',
        'guardian_type',
        'phone',
        'email',
        'address',

        // معلومات الحساب المالي
        'account_number',
        'account_balance',
        'credit_limit',
        'available_balance',
        'pending_amount',
        'total_paid',
        'total_outstanding',

        // إعدادات الحساب
        'account_status',
        'auto_pay_enabled',
        'auto_pay_limit',
        'notifications_enabled',
        'notification_preferences',

        // معلومات الدفع المفضلة
        'preferred_payment_method',
        'payment_methods',
        'default_bank_account',
        'default_card_last_four',

        // الأطفال المرتبطين
        'children_ids',
        'children_count',
        'is_primary_guardian',

        // إحصائيات الحساب
        'total_invoices',
        'paid_invoices',
        'overdue_invoices',
        'average_monthly_payment',
        'last_payment_date',
        'last_payment_amount',

        // معلومات الائتمان والتقييم
        'credit_rating',
        'payment_score',
        'late_payment_count',
        'on_time_payment_count',
        'payment_reliability_percentage',

        // معلومات الاتصال والتفضيلات
        'secondary_phone',
        'work_phone',
        'emergency_contact',
        'emergency_phone',
        'preferred_language',
        'preferred_communication',

        // معلومات الأمان
        'security_pin',
        'last_login',
        'last_login_ip',
        'failed_login_attempts',
        'account_locked_until',
        'two_factor_enabled',

        // معلومات إضافية
        'notes',
        'custom_fields',
        'account_manager',
        'vip_status',

        // معلومات الإنشاء والتحديث
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'account_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_outstanding' => 'decimal:2',
        'auto_pay_limit' => 'decimal:2',
        'average_monthly_payment' => 'decimal:2',
        'last_payment_amount' => 'decimal:2',
        'payment_reliability_percentage' => 'decimal:2',
        'total_invoices' => 'integer',
        'paid_invoices' => 'integer',
        'overdue_invoices' => 'integer',
        'payment_score' => 'integer',
        'late_payment_count' => 'integer',
        'on_time_payment_count' => 'integer',
        'children_count' => 'integer',
        'failed_login_attempts' => 'integer',
        'auto_pay_enabled' => 'boolean',
        'notifications_enabled' => 'boolean',
        'is_primary_guardian' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'children_ids' => 'array',
        'notification_preferences' => 'array',
        'payment_methods' => 'array',
        'custom_fields' => 'array',
        'last_payment_date' => 'date',
        'last_login' => 'datetime',
        'account_locked_until' => 'datetime',
    ];

    protected $hidden = [
        'security_pin',
    ];

    // ==================== العلاقات ====================

    /**
     * العلاقة مع الطلاب (الأطفال)
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'guardian_id', 'id');
    }

    /**
     * العلاقة مع الفواتير
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'guardian_id', 'id');
    }

    /**
     * العلاقة مع الدفعات الإلكترونية
     */
    public function onlinePayments(): HasMany
    {
        return $this->hasMany(OnlinePayment::class, 'guardian_id', 'id');
    }

    // ==================== Accessors ====================

    /**
     * حساب الرصيد المتاح
     */
    public function getAvailableBalanceAttribute($value)
    {
        return $this->account_balance + $this->credit_limit - $this->pending_amount;
    }

    /**
     * حساب نسبة موثوقية الدفع
     */
    public function getPaymentReliabilityPercentageAttribute($value)
    {
        $totalPayments = $this->on_time_payment_count + $this->late_payment_count;
        if ($totalPayments == 0) return 0;

        return round(($this->on_time_payment_count / $totalPayments) * 100, 2);
    }

    /**
     * هل الحساب نشط؟
     */
    public function getIsActiveAttribute()
    {
        return $this->account_status === 'نشط';
    }

    /**
     * هل الحساب مقفل؟
     */
    public function getIsLockedAttribute()
    {
        return $this->account_locked_until && Carbon::now()->lt($this->account_locked_until);
    }

    /**
     * هل يمكن الدفع التلقائي؟
     */
    public function getCanAutoPayAttribute()
    {
        return $this->auto_pay_enabled &&
            $this->is_active &&
            !$this->is_locked &&
            $this->available_balance > 0;
    }

    /**
     * نسبة الفواتير المدفوعة
     */
    public function getPaidInvoicesPercentageAttribute()
    {
        if ($this->total_invoices == 0) return 100;
        return round(($this->paid_invoices / $this->total_invoices) * 100, 2);
    }

    /**
     * نسبة الفواتير المتأخرة
     */
    public function getOverdueInvoicesPercentageAttribute()
    {
        if ($this->total_invoices == 0) return 0;
        return round(($this->overdue_invoices / $this->total_invoices) * 100, 2);
    }

    /**
     * التقييم الائتماني كرقم
     */
    public function getCreditScoreAttribute()
    {
        $ratings = [
            'ممتاز' => 90,
            'جيد جداً' => 80,
            'جيد' => 70,
            'مقبول' => 60,
            'ضعيف' => 40,
            'غير محدد' => 0,
        ];

        return $ratings[$this->credit_rating] ?? 0;
    }

    /**
     * هل العميل VIP؟
     */
    public function getIsVipAttribute()
    {
        return in_array($this->vip_status, ['VIP', 'VVIP', 'ذهبي', 'بلاتيني']);
    }

    /**
     * عدد الأطفال الفعلي
     */
    public function getActualChildrenCountAttribute()
    {
        return count($this->children_ids ?: []);
    }

    // ==================== Scopes ====================

    /**
     * الحسابات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('account_status', 'نشط');
    }

    /**
     * الحسابات المعلقة
     */
    public function scopeSuspended($query)
    {
        return $query->where('account_status', 'معلق');
    }

    /**
     * الحسابات المجمدة
     */
    public function scopeFrozen($query)
    {
        return $query->where('account_status', 'مجمد');
    }

    /**
     * أولياء الأمور الأساسيين
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary_guardian', true);
    }

    /**
     * العملاء VIP
     */
    public function scopeVip($query)
    {
        return $query->whereIn('vip_status', ['VIP', 'VVIP', 'ذهبي', 'بلاتيني']);
    }

    /**
     * الحسابات التي لديها رصيد موجب
     */
    public function scopeWithBalance($query)
    {
        return $query->where('account_balance', '>', 0);
    }

    /**
     * الحسابات المدينة
     */
    public function scopeWithDebt($query)
    {
        return $query->where('total_outstanding', '>', 0);
    }

    /**
     * الحسابات التي تستخدم الدفع التلقائي
     */
    public function scopeAutoPayEnabled($query)
    {
        return $query->where('auto_pay_enabled', true);
    }

    /**
     * الحسابات بتقييم ائتماني محدد
     */
    public function scopeWithCreditRating($query, $rating)
    {
        return $query->where('credit_rating', $rating);
    }

    /**
     * الحسابات بعدد أطفال محدد
     */
    public function scopeWithChildrenCount($query, $count)
    {
        return $query->where('children_count', $count);
    }

    // ==================== Methods ====================

    /**
     * إنشاء رقم حساب فريد
     */
    public static function generateAccountNumber()
    {
        do {
            $accountNumber = 'GA-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * تحديث رصيد الحساب
     */
    public function updateBalance($amount, $type = 'credit')
    {
        if ($type === 'credit') {
            $newBalance = $this->account_balance + $amount;
        } else {
            $newBalance = $this->account_balance - $amount;
        }

        $this->update([
            'account_balance' => $newBalance,
            'available_balance' => $newBalance + $this->credit_limit - $this->pending_amount,
        ]);

        return $this;
    }

    /**
     * إضافة طفل للحساب
     */
    public function addChild($studentId)
    {
        $childrenIds = $this->children_ids ?: [];

        if (!in_array($studentId, $childrenIds)) {
            $childrenIds[] = $studentId;

            $this->update([
                'children_ids' => $childrenIds,
                'children_count' => count($childrenIds),
            ]);
        }

        return $this;
    }

    /**
     * إزالة طفل من الحساب
     */
    public function removeChild($studentId)
    {
        $childrenIds = collect($this->children_ids ?: [])
            ->reject(function ($id) use ($studentId) {
                return $id == $studentId;
            })
            ->values()
            ->toArray();

        $this->update([
            'children_ids' => $childrenIds,
            'children_count' => count($childrenIds),
        ]);

        return $this;
    }

    /**
     * تسجيل دفعة
     */
    public function recordPayment($amount, $method = null, $onTime = true)
    {
        $this->update([
            'total_paid' => $this->total_paid + $amount,
            'last_payment_date' => now(),
            'last_payment_amount' => $amount,
            'on_time_payment_count' => $onTime ? $this->on_time_payment_count + 1 : $this->on_time_payment_count,
            'late_payment_count' => !$onTime ? $this->late_payment_count + 1 : $this->late_payment_count,
        ]);

        // تحديث متوسط الدفع الشهري
        $this->updateAverageMonthlyPayment();

        // تحديث التقييم الائتماني
        $this->updateCreditRating();

        return $this;
    }

    /**
     * تحديث إحصائيات الفواتير
     */
    public function updateInvoiceStats()
    {
        $invoices = $this->invoices();

        $this->update([
            'total_invoices' => $invoices->count(),
            'paid_invoices' => $invoices->where('is_paid', true)->count(),
            'overdue_invoices' => $invoices->where('is_overdue', true)->count(),
        ]);

        return $this;
    }

    /**
     * تحديث متوسط الدفع الشهري
     */
    public function updateAverageMonthlyPayment()
    {
        $monthsActive = max(1, Carbon::now()->diffInMonths($this->created_at));
        $average = $this->total_paid / $monthsActive;

        $this->update(['average_monthly_payment' => $average]);

        return $this;
    }

    /**
     * تحديث التقييم الائتماني
     */
    public function updateCreditRating()
    {
        $score = $this->calculateCreditScore();

        if ($score >= 90) {
            $rating = 'ممتاز';
        } elseif ($score >= 80) {
            $rating = 'جيد جداً';
        } elseif ($score >= 70) {
            $rating = 'جيد';
        } elseif ($score >= 60) {
            $rating = 'مقبول';
        } elseif ($score >= 40) {
            $rating = 'ضعيف';
        } else {
            $rating = 'غير محدد';
        }

        $this->update([
            'credit_rating' => $rating,
            'payment_score' => $score,
        ]);

        return $this;
    }

    /**
     * حساب نقاط الائتمان
     */
    private function calculateCreditScore()
    {
        $score = 0;

        // نسبة الدفع في الوقت المحدد (40 نقطة)
        $score += $this->payment_reliability_percentage * 0.4;

        // نسبة الفواتير المدفوعة (30 نقطة)
        $score += $this->paid_invoices_percentage * 0.3;

        // الرصيد المتاح (20 نقطة)
        if ($this->available_balance > 0) {
            $score += min(20, ($this->available_balance / 1000) * 2);
        }

        // عدم وجود فواتير متأخرة (10 نقاط)
        if ($this->overdue_invoices == 0) {
            $score += 10;
        }

        return min(100, max(0, round($score)));
    }

    /**
     * تفعيل الدفع التلقائي
     */
    public function enableAutoPay($limit = null)
    {
        $this->update([
            'auto_pay_enabled' => true,
            'auto_pay_limit' => $limit,
        ]);

        return $this;
    }

    /**
     * إلغاء تفعيل الدفع التلقائي
     */
    public function disableAutoPay()
    {
        $this->update([
            'auto_pay_enabled' => false,
            'auto_pay_limit' => null,
        ]);

        return $this;
    }

    /**
     * تعليق الحساب
     */
    public function suspend($reason = null)
    {
        $this->update([
            'account_status' => 'معلق',
            'notes' => $this->notes . "\n" . "تم التعليق: " . $reason,
        ]);

        return $this;
    }

    /**
     * تجميد الحساب
     */
    public function freeze($reason = null)
    {
        $this->update([
            'account_status' => 'مجمد',
            'notes' => $this->notes . "\n" . "تم التجميد: " . $reason,
        ]);

        return $this;
    }

    /**
     * إعادة تفعيل الحساب
     */
    public function activate($reason = null)
    {
        $this->update([
            'account_status' => 'نشط',
            'notes' => $this->notes . "\n" . "تم التفعيل: " . $reason,
        ]);

        return $this;
    }

    /**
     * قفل الحساب مؤقتاً
     */
    public function lockAccount($minutes = 30)
    {
        $this->update([
            'account_locked_until' => now()->addMinutes($minutes),
            'failed_login_attempts' => $this->failed_login_attempts + 1,
        ]);

        return $this;
    }

    /**
     * إلغاء قفل الحساب
     */
    public function unlockAccount()
    {
        $this->update([
            'account_locked_until' => null,
            'failed_login_attempts' => 0,
        ]);

        return $this;
    }

    /**
     * تسجيل محاولة دخول ناجحة
     */
    public function recordSuccessfulLogin($ipAddress = null)
    {
        $this->update([
            'last_login' => now(),
            'last_login_ip' => $ipAddress,
            'failed_login_attempts' => 0,
        ]);

        return $this;
    }

    /**
     * تحديث حالة VIP
     */
    public function updateVipStatus($status)
    {
        $this->update(['vip_status' => $status]);

        return $this;
    }

    /**
     * تحديث تفضيلات الإشعارات
     */
    public function updateNotificationPreferences($preferences)
    {
        $this->update([
            'notification_preferences' => $preferences,
            'notifications_enabled' => true,
        ]);

        return $this;
    }

    /**
     * إضافة طريقة دفع
     */
    public function addPaymentMethod($method, $details)
    {
        $paymentMethods = $this->payment_methods ?: [];

        $paymentMethods[] = [
            'id' => Str::uuid(),
            'method' => $method,
            'details' => $details,
            'is_default' => count($paymentMethods) == 0,
            'added_at' => now()->toISOString(),
        ];

        $this->update(['payment_methods' => $paymentMethods]);

        return $this;
    }

    /**
     * حذف طريقة دفع
     */
    public function removePaymentMethod($methodId)
    {
        $paymentMethods = collect($this->payment_methods ?: [])
            ->reject(function ($method) use ($methodId) {
                return $method['id'] === $methodId;
            })
            ->values()
            ->toArray();

        $this->update(['payment_methods' => $paymentMethods]);

        return $this;
    }

    /**
     * تحديث معلومات الاتصال
     */
    public function updateContactInfo($phone = null, $email = null, $secondaryPhone = null, $workPhone = null)
    {
        $updateData = [];

        if ($phone) $updateData['phone'] = $phone;
        if ($email) $updateData['email'] = $email;
        if ($secondaryPhone) $updateData['secondary_phone'] = $secondaryPhone;
        if ($workPhone) $updateData['work_phone'] = $workPhone;

        $this->update($updateData);

        return $this;
    }
}
