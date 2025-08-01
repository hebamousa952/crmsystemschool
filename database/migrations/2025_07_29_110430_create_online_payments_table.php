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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('installment_id')->nullable()->constrained('installments')->onDelete('set null')->comment('معرف القسط (اختياري)');

            // معلومات الدفعة
            $table->string('payment_reference')->unique()->comment('رقم مرجعي فريد للدفعة');
            $table->string('transaction_id')->nullable()->comment('معرف المعاملة من البوابة');
            $table->decimal('amount', 10, 2)->comment('مبلغ الدفعة');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->decimal('exchange_rate', 8, 4)->default(1)->comment('سعر الصرف');
            $table->decimal('amount_in_base_currency', 10, 2)->comment('المبلغ بالعملة الأساسية');

            // معلومات البوابة والطريقة
            $table->enum('payment_gateway', ['فوري', 'فيزا', 'ماستركارد', 'فودافون كاش', 'أورانج موني', 'اتصالات كاش', 'CIB', 'بنك مصر', 'الأهلي المصري', 'QNB', 'HSBC', 'PayPal', 'Stripe'])->comment('بوابة الدفع');
            $table->enum('payment_method', ['بطاقة ائتمان', 'بطاقة خصم', 'محفظة إلكترونية', 'تحويل بنكي', 'فوري', 'رصيد موبايل'])->comment('طريقة الدفع');
            $table->string('payment_channel')->nullable()->comment('قناة الدفع (موبايل/ويب/ATM)');

            // تفاصيل البطاقة (مشفرة)
            $table->string('card_type')->nullable()->comment('نوع البطاقة');
            $table->string('card_last_four')->nullable()->comment('آخر 4 أرقام من البطاقة');
            $table->string('card_brand')->nullable()->comment('علامة البطاقة التجارية');
            $table->string('card_country')->nullable()->comment('بلد إصدار البطاقة');

            // حالة الدفعة
            $table->enum('status', ['في انتظار', 'قيد المعالجة', 'مكتملة', 'فاشلة', 'ملغية', 'مسترجعة', 'معلقة', 'تحت المراجعة'])->default('في انتظار')->comment('حالة الدفعة');
            $table->string('gateway_status')->nullable()->comment('حالة الدفعة من البوابة');
            $table->text('failure_reason')->nullable()->comment('سبب فشل الدفعة');
            $table->integer('retry_count')->default(0)->comment('عدد محاولات الإعادة');

            // التواريخ والأوقات
            $table->timestamp('initiated_at')->nullable()->comment('وقت بدء الدفعة');
            $table->timestamp('completed_at')->nullable()->comment('وقت اكتمال الدفعة');
            $table->timestamp('failed_at')->nullable()->comment('وقت فشل الدفعة');
            $table->timestamp('expires_at')->nullable()->comment('وقت انتهاء صلاحية الدفعة');

            // الرسوم والعمولات
            $table->decimal('gateway_fee', 10, 2)->default(0)->comment('رسوم البوابة');
            $table->decimal('processing_fee', 10, 2)->default(0)->comment('رسوم المعالجة');
            $table->decimal('total_fees', 10, 2)->default(0)->comment('إجمالي الرسوم');
            $table->decimal('net_amount', 10, 2)->comment('المبلغ الصافي');

            // معلومات العميل
            $table->string('customer_name')->comment('اسم العميل');
            $table->string('customer_email')->nullable()->comment('بريد العميل الإلكتروني');
            $table->string('customer_phone')->nullable()->comment('هاتف العميل');
            $table->json('customer_address')->nullable()->comment('عنوان العميل (JSON)');

            // معلومات الجهاز والموقع
            $table->string('ip_address')->nullable()->comment('عنوان IP');
            $table->string('user_agent')->nullable()->comment('معلومات المتصفح');
            $table->string('device_type')->nullable()->comment('نوع الجهاز');
            $table->string('location_country')->nullable()->comment('البلد');
            $table->string('location_city')->nullable()->comment('المدينة');

            // الأمان ومكافحة الاحتيال
            $table->decimal('risk_score', 5, 2)->nullable()->comment('درجة المخاطر');
            $table->boolean('is_suspicious')->default(false)->comment('هل مشبوهة؟');
            $table->json('fraud_checks')->nullable()->comment('فحوصات الاحتيال (JSON)');
            $table->boolean('requires_3ds')->default(false)->comment('يتطلب 3D Secure؟');
            $table->boolean('is_3ds_verified')->default(false)->comment('تم التحقق بـ 3D Secure؟');

            // معلومات الاسترداد
            $table->boolean('is_refundable')->default(true)->comment('قابل للاسترداد؟');
            $table->decimal('refunded_amount', 10, 2)->default(0)->comment('المبلغ المسترد');
            $table->decimal('remaining_refundable', 10, 2)->comment('المتبقي للاسترداد');
            $table->integer('refund_count')->default(0)->comment('عدد عمليات الاسترداد');

            // البيانات الإضافية
            $table->json('gateway_response')->nullable()->comment('استجابة البوابة (JSON)');
            $table->json('webhook_data')->nullable()->comment('بيانات Webhook (JSON)');
            $table->json('metadata')->nullable()->comment('بيانات إضافية (JSON)');
            $table->text('notes')->nullable()->comment('ملاحظات');

            // معلومات التدقيق
            $table->string('processed_by')->nullable()->comment('معالج بواسطة');
            $table->string('verified_by')->nullable()->comment('تم التحقق بواسطة');
            $table->timestamp('verified_at')->nullable()->comment('وقت التحقق');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ السجل');
            $table->string('updated_by')->nullable()->comment('آخر محدث للسجل');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['installment_id', 'status']);
            $table->index('payment_reference');
            $table->index('transaction_id');
            $table->index('payment_gateway');
            $table->index('payment_method');
            $table->index('status');
            $table->index('initiated_at');
            $table->index('completed_at');
            $table->index('is_suspicious');
            $table->index('requires_3ds');
            $table->index('is_refundable');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'payment_gateway', 'status'], 'student_gateway_status_index');
            $table->index(['payment_reference', 'transaction_id'], 'payment_transaction_index');
            $table->index(['initiated_at', 'status'], 'initiated_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('online_payments');
    }
};
