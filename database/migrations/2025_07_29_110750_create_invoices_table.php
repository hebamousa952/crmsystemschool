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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('online_payment_id')->nullable()->constrained('online_payments')->onDelete('set null')->comment('معرف الدفعة الإلكترونية (اختياري)');

            // معلومات الفاتورة
            $table->string('invoice_number')->unique()->comment('رقم الفاتورة');
            $table->string('invoice_series')->default('INV')->comment('سلسلة الفاتورة');
            $table->enum('invoice_type', ['فاتورة مصروفات', 'إيصال دفع', 'فاتورة أقساط', 'فاتورة خصم', 'فاتورة استرداد', 'فاتورة غرامة', 'فاتورة رسوم إضافية'])->comment('نوع الفاتورة');
            $table->enum('invoice_category', ['مصروفات دراسية', 'رسوم إدارية', 'رسوم خدمات', 'غرامات', 'خصومات', 'استردادات', 'أخرى'])->comment('فئة الفاتورة');

            // التواريخ
            $table->date('invoice_date')->comment('تاريخ الفاتورة');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->date('payment_date')->nullable()->comment('تاريخ السداد');
            $table->integer('payment_terms_days')->default(30)->comment('مدة السداد بالأيام');

            // معلومات العميل (الطالب/ولي الأمر)
            $table->string('customer_name')->comment('اسم العميل');
            $table->string('customer_type')->default('طالب')->comment('نوع العميل');
            $table->string('customer_id_number')->nullable()->comment('رقم هوية العميل');
            $table->string('customer_phone')->nullable()->comment('هاتف العميل');
            $table->string('customer_email')->nullable()->comment('بريد العميل الإلكتروني');
            $table->text('customer_address')->nullable()->comment('عنوان العميل');

            // المبالغ والحسابات
            $table->decimal('subtotal', 10, 2)->default(0)->comment('المجموع الفرعي');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('مبلغ الخصم');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('نسبة الخصم');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('مبلغ الضريبة');
            $table->decimal('tax_percentage', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->decimal('additional_fees', 10, 2)->default(0)->comment('رسوم إضافية');
            $table->decimal('total_amount', 10, 2)->comment('إجمالي المبلغ');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('المبلغ المدفوع');
            $table->decimal('remaining_amount', 10, 2)->comment('المبلغ المتبقي');

            // تفاصيل البنود
            $table->json('invoice_items')->comment('بنود الفاتورة (JSON)');
            $table->json('payment_breakdown')->nullable()->comment('تفصيل المدفوعات (JSON)');
            $table->json('discount_details')->nullable()->comment('تفاصيل الخصومات (JSON)');

            // حالة الفاتورة
            $table->enum('status', ['مسودة', 'مرسلة', 'مستحقة', 'مدفوعة جزئياً', 'مدفوعة كاملاً', 'متأخرة', 'ملغية', 'مسترجعة'])->default('مسودة')->comment('حالة الفاتورة');
            $table->boolean('is_paid')->default(false)->comment('هل مدفوعة؟');
            $table->boolean('is_overdue')->default(false)->comment('هل متأخرة؟');
            $table->integer('overdue_days')->default(0)->comment('عدد أيام التأخير');

            // معلومات الطباعة والإرسال
            $table->boolean('is_printed')->default(false)->comment('هل تم طباعتها؟');
            $table->timestamp('printed_at')->nullable()->comment('وقت الطباعة');
            $table->integer('print_count')->default(0)->comment('عدد مرات الطباعة');
            $table->boolean('is_emailed')->default(false)->comment('هل تم إرسالها بالبريد؟');
            $table->timestamp('emailed_at')->nullable()->comment('وقت الإرسال بالبريد');
            $table->integer('email_count')->default(0)->comment('عدد مرات الإرسال');

            // معلومات الدفع
            $table->enum('payment_method', ['نقدي', 'شيك', 'تحويل بنكي', 'بطاقة ائتمان', 'دفع إلكتروني', 'فوري', 'محفظة إلكترونية', 'أخرى'])->nullable()->comment('طريقة الدفع');
            $table->string('payment_reference')->nullable()->comment('مرجع الدفعة');
            $table->text('payment_notes')->nullable()->comment('ملاحظات الدفع');

            // معلومات الاسترداد
            $table->boolean('is_refundable')->default(true)->comment('قابلة للاسترداد؟');
            $table->decimal('refunded_amount', 10, 2)->default(0)->comment('المبلغ المسترد');
            $table->integer('refund_count')->default(0)->comment('عدد عمليات الاسترداد');
            $table->text('refund_reason')->nullable()->comment('سبب الاسترداد');

            // معلومات المراجعة والاعتماد
            $table->boolean('requires_approval')->default(false)->comment('تحتاج موافقة؟');
            $table->enum('approval_status', ['في انتظار الموافقة', 'موافق عليها', 'مرفوضة'])->nullable()->comment('حالة الموافقة');
            $table->string('approved_by')->nullable()->comment('معتمدة بواسطة');
            $table->timestamp('approved_at')->nullable()->comment('وقت الاعتماد');
            $table->text('approval_notes')->nullable()->comment('ملاحظات الاعتماد');

            // معلومات الإلغاء
            $table->boolean('is_cancelled')->default(false)->comment('هل ملغية؟');
            $table->timestamp('cancelled_at')->nullable()->comment('وقت الإلغاء');
            $table->string('cancelled_by')->nullable()->comment('ملغية بواسطة');
            $table->text('cancellation_reason')->nullable()->comment('سبب الإلغاء');

            // معلومات إضافية
            $table->text('description')->nullable()->comment('وصف الفاتورة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->text('terms_and_conditions')->nullable()->comment('الشروط والأحكام');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->decimal('exchange_rate', 8, 4)->default(1)->comment('سعر الصرف');

            // معلومات الملفات
            $table->string('pdf_path')->nullable()->comment('مسار ملف PDF');
            $table->json('attachments')->nullable()->comment('المرفقات (JSON)');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ الفاتورة');
            $table->string('updated_by')->nullable()->comment('آخر محدث للفاتورة');
            $table->string('issued_by')->nullable()->comment('مصدر الفاتورة');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'status']);
            $table->index(['student_id', 'invoice_type']);
            $table->index(['online_payment_id', 'status']);
            $table->index('invoice_number');
            $table->index('invoice_type');
            $table->index('invoice_category');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('payment_date');
            $table->index('is_paid');
            $table->index('is_overdue');
            $table->index('is_cancelled');
            $table->index('approval_status');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'status', 'invoice_type'], 'student_status_type_index');
            $table->index(['invoice_date', 'status'], 'date_status_index');
            $table->index(['due_date', 'is_paid'], 'due_paid_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
