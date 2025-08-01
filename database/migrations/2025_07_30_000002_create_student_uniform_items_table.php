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
        try {
            // بداية العملية
            \Illuminate\Support\Facades\Log::info("=== [CREATE_STUDENT_UNIFORM_ITEMS_TABLE] STARTED ===");
            
            Schema::create('student_uniform_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
                $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل المصروفات');
                $table->foreignId('uniform_item_id')->constrained('uniform_items')->onDelete('cascade')->comment('معرف قطعة الزي');
                $table->integer('quantity')->default(1)->comment('الكمية');
                $table->decimal('price', 10, 2)->comment('سعر القطعة المخصص');
                $table->decimal('total_price', 10, 2)->comment('السعر الإجمالي');
                $table->boolean('is_delivered')->default(false)->comment('هل تم تسليم القطعة؟');
                $table->date('delivery_date')->nullable()->comment('تاريخ التسليم');
                $table->string('created_by')->nullable()->comment('منشئ السجل');
                $table->string('updated_by')->nullable()->comment('آخر محدث للسجل');
                $table->timestamps();
                
                // الفهارس
                $table->index(['student_id', 'student_fee_record_id']);
                $table->index('uniform_item_id');
                $table->index('is_delivered');
            });
            
            \Illuminate\Support\Facades\Log::info("Student uniform items table created successfully");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create student uniform items table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_uniform_items');
    }
};