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
        Schema::create('financial_notifications', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('guardian_id')->nullable()->constrained('guardian_accounts')->onDelete('cascade')->comment('معرف ولي الأمر');
            $table->foreignId('student_fee_record_id')->nullable()->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل المصروفات');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->comment('معرف الفاتورة');
            $table->foreignId('installment_id')->nullable()->constrained('installments')->onDelete('set null')->comment('معرف القسط');

            // معلومات الإشعار الأساسية
            $table->string('notification_number')->unique()->comment('رقم الإشعار');
            $table->enum('notification_type', ['تذكير دفع', 'إشعار استحقاق', 'تأكيد دفع', 'إشعار تأخير', 'تحذير غرامة', 'إشعار خصم', 'تذكير قسط', 'إشعار استرداد', 'تحديث رصيد', 'إشعار عام'])->comment('نوع الإشعار');
            $table->enum('category', ['دفع', 'استحقاق', 'تأخير', 'غرامة', 'خصم', 'رصيد', 'عام', 'تذكير', 'تحذير', 'تأكيد'])->comment('فئة الإشعار');
            $table->enum('priority', ['منخفض', 'متوسط', 'عالي', 'عاجل', 'حرج'])->default('متوسط')->comment('أولوية الإشعار');

            // محتوى الإشعار
            $table->string('title')->comment('عنوان الإشعار');
            $table->text('message')->comment('نص الإشعار');
            $table->text('details')->nullable()->comment('تفاصيل إضافية');
            $table->json('message_data')->nullable()->comment('بيانات الرسالة (JSON)');
            $table->string('action_url')->nullable()->comment('رابط الإجراء');
            $table->string('action_text')->nullable()->comment('نص الإجراء');

            // معلومات مالية
            $table->decimal('amount', 10, 2)->nullable()->comment('المبلغ المرتبط');
            $table->decimal('due_amount', 10, 2)->nullable()->comment('المبلغ المستحق');
            $table->decimal('paid_amount', 10, 2)->nullable()->comment('المبلغ المدفوع');
            $table->decimal('remaining_amount', 10, 2)->nullable()->comment('المبلغ المتبقي');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->integer('days_overdue')->default(0)->comment('عدد أيام التأخير');

            // معلومات المستلم
            $table->string('recipient_name')->comment('اسم المستلم');
            $table->string('recipient_phone')->nullable()->comment('هاتف المستلم');
            $table->string('recipient_email')->nullable()->comment('بريد المستلم الإلكتروني');
            $table->enum('recipient_type', ['ولي أمر', 'طالب', 'إدارة', 'محاسب', 'نظام'])->comment('نوع المستلم');

            // قنوات الإرسال
            $table->json('channels')->comment('قنوات الإرسال (SMS, Email, Push, WhatsApp)');
            $table->boolean('sms_enabled')->default(true)->comment('إرسال SMS مفعل؟');
            $table->boolean('email_enabled')->default(true)->comment('إرسال Email مفعل؟');
            $table->boolean('push_enabled')->default(true)->comment('إرسال Push مفعل؟');
            $table->boolean('whatsapp_enabled')->default(false)->comment('إرسال WhatsApp مفعل؟');

            // حالة الإرسال
            $table->enum('status', ['معلق', 'جاري الإرسال', 'مرسل', 'مستلم', 'مقروء', 'فشل', 'ملغي'])->default('معلق')->comment('حالة الإشعار');
            $table->boolean('is_sent')->default(false)->comment('هل تم الإرسال؟');
            $table->boolean('is_delivered')->default(false)->comment('هل تم التسليم؟');
            $table->boolean('is_read')->default(false)->comment('هل تم القراءة؟');
            $table->boolean('is_clicked')->default(false)->comment('هل تم النقر؟');

            // تواريخ الإرسال والاستلام
            $table->timestamp('scheduled_at')->nullable()->comment('موعد الإرسال المجدول');
            $table->timestamp('sent_at')->nullable()->comment('وقت الإرسال');
            $table->timestamp('delivered_at')->nullable()->comment('وقت التسليم');
            $table->timestamp('read_at')->nullable()->comment('وقت القراءة');
            $table->timestamp('clicked_at')->nullable()->comment('وقت النقر');
            $table->timestamp('expires_at')->nullable()->comment('وقت انتهاء الصلاحية');

            // تفاصيل الإرسال لكل قناة
            $table->json('sms_details')->nullable()->comment('تفاصيل إرسال SMS (JSON)');
            $table->json('email_details')->nullable()->comment('تفاصيل إرسال Email (JSON)');
            $table->json('push_details')->nullable()->comment('تفاصيل إرسال Push (JSON)');
            $table->json('whatsapp_details')->nullable()->comment('تفاصيل إرسال WhatsApp (JSON)');

            // معلومات الأخطاء
            $table->text('error_message')->nullable()->comment('رسالة الخطأ');
            $table->string('error_code')->nullable()->comment('كود الخطأ');
            $table->integer('retry_count')->default(0)->comment('عدد محاولات الإعادة');
            $table->timestamp('last_retry_at')->nullable()->comment('وقت آخر محاولة إعادة');
            $table->timestamp('next_retry_at')->nullable()->comment('وقت المحاولة التالية');

            // إعدادات التكرار
            $table->boolean('is_recurring')->default(false)->comment('هل متكرر؟');
            $table->enum('recurrence_type', ['يومي', 'أسبوعي', 'شهري', 'سنوي', 'مخصص'])->nullable()->comment('نوع التكرار');
            $table->json('recurrence_settings')->nullable()->comment('إعدادات التكرار (JSON)');
            $table->date('recurrence_end_date')->nullable()->comment('تاريخ انتهاء التكرار');
            $table->integer('recurrence_count')->default(0)->comment('عدد مرات التكرار');

            // معلومات القالب
            $table->string('template_name')->nullable()->comment('اسم القالب المستخدم');
            $table->json('template_variables')->nullable()->comment('متغيرات القالب (JSON)');
            $table->string('language', 2)->default('ar')->comment('لغة الإشعار');

            // معلومات التتبع والتحليل
            $table->string('campaign_id')->nullable()->comment('معرف الحملة');
            $table->string('tracking_id')->unique()->comment('معرف التتبع');
            $table->json('analytics_data')->nullable()->comment('بيانات التحليل (JSON)');
            $table->string('source')->default('system')->comment('مصدر الإشعار');
            $table->json('tags')->nullable()->comment('علامات التصنيف (JSON)');

            // معلومات الموافقة والامتثال
            $table->boolean('requires_consent')->default(false)->comment('يتطلب موافقة؟');
            $table->boolean('consent_given')->default(true)->comment('تم إعطاء الموافقة؟');
            $table->timestamp('consent_given_at')->nullable()->comment('وقت إعطاء الموافقة');
            $table->boolean('can_unsubscribe')->default(true)->comment('يمكن إلغاء الاشتراك؟');
            $table->timestamp('unsubscribed_at')->nullable()->comment('وقت إلغاء الاشتراك');

            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->json('metadata')->nullable()->comment('بيانات إضافية (JSON)');
            $table->string('created_by')->nullable()->comment('منشئ الإشعار');
            $table->string('updated_by')->nullable()->comment('آخر محدث للإشعار');

            $table->timestamps();

            // الفهارس الأساسية
            $table->index('student_id');
            $table->index('guardian_id');
            $table->index('student_fee_record_id');
            $table->index('invoice_id');
            $table->index('installment_id');
            $table->index('notification_number');
            $table->index('notification_type');
            $table->index('category');
            $table->index('priority');
            $table->index('status');
            $table->index('recipient_type');
            $table->index('is_sent');
            $table->index('is_delivered');
            $table->index('is_read');
            $table->index('scheduled_at');
            $table->index('sent_at');
            $table->index('due_date');
            $table->index('expires_at');
            $table->index('is_recurring');
            $table->index('tracking_id');
            $table->index('campaign_id');
            $table->index('template_name');
            $table->index('language');
            $table->index('source');
            $table->index('requires_consent');
            $table->index('consent_given');
            $table->index('can_unsubscribe');

            // فهارس مركبة للبحث السريع
            $table->index(['student_id', 'notification_type', 'status'], 'student_type_status_index');
            $table->index(['guardian_id', 'category', 'is_sent'], 'guardian_category_sent_index');
            $table->index(['notification_type', 'priority', 'scheduled_at'], 'type_priority_scheduled_index');
            $table->index(['status', 'retry_count', 'next_retry_at'], 'status_retry_next_index');
            $table->index(['due_date', 'days_overdue', 'status'], 'due_overdue_status_index');
            $table->index(['is_recurring', 'recurrence_type', 'recurrence_end_date'], 'recurring_type_end_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_notifications');
    }
};
