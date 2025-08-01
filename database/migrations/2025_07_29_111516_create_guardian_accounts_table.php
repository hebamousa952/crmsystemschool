<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guardian_accounts', function (Blueprint $table) {
            $table->id();

            // معلومات ولي الأمر الأساسية
            $table->string('guardian_name')->comment('اسم ولي الأمر');
            $table->string('guardian_id_number')->unique()->comment('رقم هوية ولي الأمر');
            $table->enum('guardian_type', ['أب', 'أم', 'جد', 'جدة', 'عم', 'عمة', 'خال', 'خالة', 'أخ', 'أخت', 'وصي قانوني', 'أخرى'])->comment('نوع القرابة');
            $table->string('phone')->comment('رقم الهاتف');
            $table->string('email')->unique()->nullable()->comment('البريد الإلكتروني');
            $table->text('address')->nullable()->comment('العنوان');

            // معلومات الحساب المالي
            $table->string('account_number')->unique()->comment('رقم الحساب المالي');
            $table->decimal('account_balance', 10, 2)->default(0)->comment('رصيد الحساب');
            $table->decimal('credit_limit', 10, 2)->default(0)->comment('حد الائتمان');
            $table->decimal('available_balance', 10, 2)->default(0)->comment('الرصيد المتاح');
            $table->decimal('pending_amount', 10, 2)->default(0)->comment('المبلغ المعلق');
            $table->decimal('total_paid', 10, 2)->default(0)->comment('إجمالي المدفوع');
            $table->decimal('total_outstanding', 10, 2)->default(0)->comment('إجمالي المستحق');

            // إعدادات الحساب
            $table->enum('account_status', ['نشط', 'معلق', 'مجمد', 'مغلق'])->default('نشط')->comment('حالة الحساب');
            $table->boolean('auto_pay_enabled')->default(false)->comment('الدفع التلقائي مفعل؟');
            $table->decimal('auto_pay_limit', 10, 2)->nullable()->comment('حد الدفع التلقائي');
            $table->boolean('notifications_enabled')->default(true)->comment('الإشعارات مفعلة؟');
            $table->json('notification_preferences')->nullable()->comment('تفضيلات الإشعارات (JSON)');

            // معلومات الدفع المفضلة
            $table->enum('preferred_payment_method', ['نقدي', 'شيك', 'تحويل بنكي', 'بطاقة ائتمان', 'دفع إلكتروني', 'فوري', 'محفظة إلكترونية'])->nullable()->comment('طريقة الدفع المفضلة');
            $table->json('payment_methods')->nullable()->comment('طرق الدفع المحفوظة (JSON)');
            $table->string('default_bank_account')->nullable()->comment('الحساب البنكي الافتراضي');
            $table->string('default_card_last_four')->nullable()->comment('آخر 4 أرقام من البطاقة الافتراضية');

            // الأطفال المرتبطين
            $table->json('children_ids')->comment('معرفات الأطفال المرتبطين (JSON)');
            $table->integer('children_count')->default(0)->comment('عدد الأطفال');
            $table->boolean('is_primary_guardian')->default(true)->comment('هل ولي الأمر الأساسي؟');

            // إحصائيات الحساب
            $table->integer('total_invoices')->default(0)->comment('إجمالي الفواتير');
            $table->integer('paid_invoices')->default(0)->comment('الفواتير المدفوعة');
            $table->integer('overdue_invoices')->default(0)->comment('الفواتير المتأخرة');
            $table->decimal('average_monthly_payment', 10, 2)->default(0)->comment('متوسط الدفع الشهري');
            $table->date('last_payment_date')->nullable()->comment('تاريخ آخر دفعة');
            $table->decimal('last_payment_amount', 10, 2)->default(0)->comment('مبلغ آخر دفعة');

            // معلومات الائتمان والتقييم
            $table->enum('credit_rating', ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف', 'غير محدد'])->default('غير محدد')->comment('التقييم الائتماني');
            $table->integer('payment_score')->default(0)->comment('نقاط الدفع (0-100)');
            $table->integer('late_payment_count')->default(0)->comment('عدد الدفعات المتأخرة');
            $table->integer('on_time_payment_count')->default(0)->comment('عدد الدفعات في الوقت المحدد');
            $table->decimal('payment_reliability_percentage', 5, 2)->default(0)->comment('نسبة موثوقية الدفع');

            // معلومات الاتصال والتفضيلات
            $table->string('secondary_phone')->nullable()->comment('هاتف ثانوي');
            $table->string('work_phone')->nullable()->comment('هاتف العمل');
            $table->string('emergency_contact')->nullable()->comment('جهة اتصال الطوارئ');
            $table->string('emergency_phone')->nullable()->comment('هاتف الطوارئ');
            $table->enum('preferred_language', ['العربية', 'English', 'Français'])->default('العربية')->comment('اللغة المفضلة');
            $table->enum('preferred_communication', ['SMS', 'Email', 'WhatsApp', 'مكالمة هاتفية', 'الكل'])->default('SMS')->comment('وسيلة التواصل المفضلة');

            // معلومات الأمان
            $table->string('security_pin')->nullable()->comment('رقم الأمان');
            $table->timestamp('last_login')->nullable()->comment('آخر تسجيل دخول');
            $table->string('last_login_ip')->nullable()->comment('آخر IP للدخول');
            $table->integer('failed_login_attempts')->default(0)->comment('محاولات الدخول الفاشلة');
            $table->timestamp('account_locked_until')->nullable()->comment('الحساب مقفل حتى');
            $table->boolean('two_factor_enabled')->default(false)->comment('المصادقة الثنائية مفعلة؟');

            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات على الحساب');
            $table->json('custom_fields')->nullable()->comment('حقول مخصصة (JSON)');
            $table->string('account_manager')->nullable()->comment('مدير الحساب');
            $table->enum('vip_status', ['عادي', 'VIP', 'VVIP', 'ذهبي', 'بلاتيني'])->default('عادي')->comment('حالة VIP');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ الحساب');
            $table->string('updated_by')->nullable()->comment('آخر محدث للحساب');

            $table->timestamps();

            // الفهارس
            $table->index('guardian_id_number');
            $table->index('account_number');
            $table->index('phone');
            $table->index('email');
            $table->index('account_status');
            $table->index('guardian_type');
            $table->index('is_primary_guardian');
            $table->index('credit_rating');
            $table->index('vip_status');
            $table->index('last_payment_date');
            $table->index('children_count');
            $table->index('auto_pay_enabled');
            $table->index('notifications_enabled');

            // فهرس مركب للبحث السريع
            $table->index(['guardian_name', 'account_status'], 'guardian_status_index');
            $table->index(['account_balance', 'credit_rating'], 'balance_rating_index');
            $table->index(['children_count', 'is_primary_guardian'], 'children_primary_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guardian_accounts');
    }
};
