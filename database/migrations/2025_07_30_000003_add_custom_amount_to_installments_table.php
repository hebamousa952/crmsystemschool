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
            \Illuminate\Support\Facades\Log::info("=== [ADD_CUSTOM_AMOUNT_TO_INSTALLMENTS] STARTED ===");
            
            Schema::table('installments', function (Blueprint $table) {
                $table->boolean('is_custom_amount')->default(false)->after('amount')->comment('هل القيمة مخصصة؟');
            });
            
            \Illuminate\Support\Facades\Log::info("Added is_custom_amount field to installments table successfully");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to add is_custom_amount field: " . $e->getMessage());
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
        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn('is_custom_amount');
        });
    }
};