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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // معلومات العملية الأساسية
            $table->string('event_type')->comment('نوع الحدث');
            $table->enum('action', ['إنشاء', 'تحديث', 'حذف', 'عرض', 'دفع', 'استرداد', 'موافقة', 'رفض', 'تسجيل دخول', 'تسجيل خروج', 'أخرى'])->comment('نوع العملية');
            $table->string('table_name')->nullable()->comment('اسم الجدول المتأثر');
            $table->unsignedBigInteger('record_id')->nullable()->comment('معرف السجل المتأثر');

            // معلومات المستخدم
            $table->string('user_type')->comment('نوع المستخدم');
            $table->unsignedBigInteger('user_id')->nullable()->comment('معرف المستخدم');
            $table->string('user_name')->comment('اسم المستخدم');
            $table->string('user_role')->nullable()->comment('دور المستخدم');
            $table->string('user_email')->nullable()->comment('بريد المستخدم الإلكتروني');

            // معلومات الجلسة والاتصال
            $table->string('session_id')->nullable()->comment('معرف الجلسة');
            $table->string('ip_address')->nullable()->comment('عنوان IP');
            $table->string('user_agent')->nullable()->comment('معلومات المتصفح');
            $table->string('device_type')->nullable()->comment('نوع الجهاز');
            $table->string('browser')->nullable()->comment('المتصفح');
            $table->string('platform')->nullable()->comment('نظام التشغيل');

            // تفاصيل العملية
            $table->text('description')->comment('وصف العملية');
            $table->json('old_values')->nullable()->comment('القيم القديمة (JSON)');
            $table->json('new_values')->nullable()->comment('القيم الجديدة (JSON)');
            $table->json('changed_fields')->nullable()->comment('الحقول المتغيرة (JSON)');
            $table->json('metadata')->nullable()->comment('بيانات إضافية (JSON)');

            // معلومات مالية (للعمليات المالية)
            $table->decimal('amount', 10, 2)->nullable()->comment('المبلغ (للعمليات المالية)');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->string('payment_method')->nullable()->comment('طريقة الدفع');
            $table->string('transaction_reference')->nullable()->comment('مرجع المعاملة');

            // معلومات الطالب المرتبط (إن وجد)
            $table->unsignedBigInteger('student_id')->nullable()->comment('معرف الطالب المرتبط');
            $table->string('student_name')->nullable()->comment('اسم الطالب');
            $table->string('student_code')->nullable()->comment('كود الطالب');
            $table->string('class_name')->nullable()->comment('اسم الفصل');

            // معلومات ولي الأمر المرتبط (إن وجد)
            $table->unsignedBigInteger('guardian_id')->nullable()->comment('معرف ولي الأمر');
            $table->string('guardian_name')->nullable()->comment('اسم ولي الأمر');
            $table->string('guardian_phone')->nullable()->comment('هاتف ولي الأمر');

            // تصنيف وأولوية العملية
            $table->enum('category', ['مالي', 'أكاديمي', 'إداري', 'أمني', 'نظام', 'تقارير', 'مستخدمين', 'إعدادات'])->comment('فئة العملية');
            $table->enum('severity', ['منخفض', 'متوسط', 'عالي', 'حرج'])->default('متوسط')->comment('مستوى الأهمية');
            $table->enum('risk_level', ['آمن', 'منخفض', 'متوسط', 'عالي', 'خطر'])->default('آمن')->comment('مستوى المخاطر');

            // حالة العملية
            $table->enum('status', ['نجح', 'فشل', 'معلق', 'ملغي', 'جاري المعالجة'])->default('نجح')->comment('حالة العملية');
            $table->text('error_message')->nullable()->comment('رسالة الخطأ (في حالة الفشل)');
            $table->string('error_code')->nullable()->comment('كود الخطأ');

            // معلومات التوقيت
            $table->timestamp('event_time')->comment('وقت الحدث');
            $table->integer('execution_time_ms')->nullable()->comment('وقت التنفيذ بالميلي ثانية');
            $table->date('event_date')->comment('تاريخ الحدث');
            $table->time('event_time_only')->comment('وقت الحدث فقط');

            // معلومات الموقع الجغرافي (اختياري)
            $table->string('country')->nullable()->comment('البلد');
            $table->string('city')->nullable()->comment('المدينة');
            $table->decimal('latitude', 10, 8)->nullable()->comment('خط العرض');
            $table->decimal('longitude', 11, 8)->nullable()->comment('خط الطول');

            // معلومات المراجعة والتدقيق
            $table->boolean('requires_review')->default(false)->comment('يتطلب مراجعة؟');
            $table->boolean('is_reviewed')->default(false)->comment('تم مراجعته؟');
            $table->string('reviewed_by')->nullable()->comment('راجعه');
            $table->timestamp('reviewed_at')->nullable()->comment('وقت المراجعة');
            $table->text('review_notes')->nullable()->comment('ملاحظات المراجعة');

            // معلومات الأرشفة
            $table->boolean('is_archived')->default(false)->comment('مؤرشف؟');
            $table->timestamp('archived_at')->nullable()->comment('وقت الأرشفة');
            $table->integer('retention_days')->default(365)->comment('مدة الاحتفاظ بالأيام');
            $table->date('delete_after')->nullable()->comment('حذف بعد تاريخ');

            // معلومات إضافية للتتبع
            $table->string('correlation_id')->nullable()->comment('معرف الربط (لتتبع العمليات المترابطة)');
            $table->string('parent_event_id')->nullable()->comment('معرف الحدث الأب');
            $table->json('tags')->nullable()->comment('علامات للتصنيف (JSON)');
            $table->boolean('is_sensitive')->default(false)->comment('يحتوي على بيانات حساسة؟');

            $table->timestamps();

            // الفهارس الأساسية
            $table->index('event_type');
            $table->index('action');
            $table->index('table_name');
            $table->index('record_id');
            $table->index('user_id');
            $table->index('user_type');
            $table->index('student_id');
            $table->index('guardian_id');
            $table->index('category');
            $table->index('severity');
            $table->index('risk_level');
            $table->index('status');
            $table->index('event_time');
            $table->index('event_date');
            $table->index('ip_address');
            $table->index('session_id');
            $table->index('requires_review');
            $table->index('is_reviewed');
            $table->index('is_archived');
            $table->index('correlation_id');
            $table->index('is_sensitive');

            // فهارس مركبة للبحث السريع
            $table->index(['event_type', 'action', 'event_date'], 'event_action_date_index');
            $table->index(['user_id', 'event_date', 'category'], 'user_date_category_index');
            $table->index(['student_id', 'action', 'event_date'], 'student_action_date_index');
            $table->index(['table_name', 'record_id', 'action'], 'table_record_action_index');
            $table->index(['category', 'severity', 'event_date'], 'category_severity_date_index');
            $table->index(['status', 'requires_review', 'event_date'], 'status_review_date_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
