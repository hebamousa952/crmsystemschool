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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');

            // معلومات الخصم
            $table->string('discount_name')->comment('اسم الخصم');
            $table->enum('discount_type', ['نسبة مئوية', 'مبلغ ثابت', 'منحة كاملة', 'منحة جزئية', 'خصم أخوة', 'خصم موظفين', 'خصم متفوقين', 'خصم اجتماعي'])->comment('نوع الخصم');
            $table->enum('discount_category', ['أكاديمي', 'اجتماعي', 'موظفين', 'أخوة', 'متفوقين', 'رياضي', 'فني', 'خاص'])->comment('فئة الخصم');

            // قيمة الخصم
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('نسبة الخصم (%)');
            $table->decimal('discount_amount', 10, 2)->nullable()->comment('مبلغ الخصم الثابت');
            $table->decimal('calculated_discount', 10, 2)->default(0)->comment('قيمة الخصم المحسوبة');
            $table->decimal('max_discount_amount', 10, 2)->nullable()->comment('الحد الأقصى لقيمة الخصم');

            // نطاق تطبيق الخصم
            $table->enum('applies_to', ['إجمالي المصروفات', 'المصروفات الأساسية', 'رسوم محددة', 'أقساط محددة'])->default('إجمالي المصروفات')->comment('نطاق تطبيق الخصم');
            $table->json('specific_fees')->nullable()->comment('الرسوم المحددة للخصم (JSON)');
            $table->json('specific_installments')->nullable()->comment('الأقساط المحددة للخصم (JSON)');

            // شروط الخصم
            $table->text('conditions')->nullable()->comment('شروط الحصول على الخصم');
            $table->decimal('minimum_amount', 10, 2)->nullable()->comment('الحد الأدنى للمبلغ لتطبيق الخصم');
            $table->date('valid_from')->nullable()->comment('تاريخ بداية صلاحية الخصم');
            $table->date('valid_until')->nullable()->comment('تاريخ انتهاء صلاحية الخصم');

            // حالة الخصم
            $table->enum('status', ['نشط', 'معلق', 'منتهي الصلاحية', 'ملغي', 'مطبق'])->default('نشط')->comment('حالة الخصم');
            $table->boolean('is_applied')->default(false)->comment('هل تم تطبيق الخصم؟');
            $table->date('applied_date')->nullable()->comment('تاريخ تطبيق الخصم');
            $table->boolean('is_recurring')->default(false)->comment('هل الخصم متكرر؟');

            // معلومات الموافقة
            $table->boolean('requires_approval')->default(false)->comment('هل يتطلب موافقة؟');
            $table->enum('approval_status', ['في انتظار الموافقة', 'موافق عليه', 'مرفوض'])->nullable()->comment('حالة الموافقة');
            $table->string('approved_by')->nullable()->comment('الشخص الذي وافق');
            $table->date('approval_date')->nullable()->comment('تاريخ الموافقة');
            $table->text('approval_notes')->nullable()->comment('ملاحظات الموافقة');

            // المستندات المطلوبة
            $table->json('required_documents')->nullable()->comment('المستندات المطلوبة (JSON)');
            $table->json('submitted_documents')->nullable()->comment('المستندات المقدمة (JSON)');
            $table->boolean('documents_verified')->default(false)->comment('هل تم التحقق من المستندات؟');

            // معلومات إضافية
            $table->text('description')->nullable()->comment('وصف الخصم');
            $table->text('notes')->nullable()->comment('ملاحظات على الخصم');
            $table->string('reference_number')->nullable()->comment('رقم مرجعي للخصم');
            $table->boolean('is_active')->default(true)->comment('هل الخصم نشط؟');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ الخصم');
            $table->string('updated_by')->nullable()->comment('آخر محدث للخصم');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'discount_type']);
            $table->index(['student_id', 'status']);
            $table->index('discount_type');
            $table->index('discount_category');
            $table->index('status');
            $table->index('is_applied');
            $table->index('approval_status');
            $table->index(['valid_from', 'valid_until']);
            $table->index('is_active');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'discount_type', 'status'], 'student_discount_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
