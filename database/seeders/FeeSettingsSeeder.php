<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeSetting;
use App\Models\UniformItem;

class FeeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener IDs de prendas de uniforme para primaria
        $primarySummerItems = UniformItem::where('grade_level', 'primary')
            ->where('type', 'صيفي')
            ->pluck('id')
            ->toArray();
            
        $primaryWinterItems = UniformItem::where('grade_level', 'primary')
            ->where('type', 'شتوي')
            ->pluck('id')
            ->toArray();
            
        // Obtener IDs de prendas de uniforme para preparatoria
        $prepItems = UniformItem::where('grade_level', 'preparatory')
            ->pluck('id')
            ->toArray();
            
        // Crear configuraciones de tarifas para diferentes niveles y programas
        $feeSettings = [
            // Programa nacional - Primaria
            [
                'academic_year' => '2025-2026',
                'grade_level' => 'primary',
                'program_type' => 'وطني',
                'basic_fees' => 15000.00,
                'registration_fees' => 1000.00,
                'activities_fees' => 500.00,
                'bus_fees' => 2000.00,
                'books_fees' => 1200.00,
                'exam_fees' => 300.00,
                'platform_fees' => 200.00,
                'insurance_fees' => 150.00,
                'service_fees' => 500.00,
                'other_fees' => 0.00,
                'default_discounts' => json_encode([
                    ['type' => 'siblings', 'value' => 10, 'is_percentage' => true],
                    ['type' => 'staff', 'value' => 50, 'is_percentage' => true],
                    ['type' => 'scholarship', 'value' => 5000, 'is_percentage' => false],
                ]),
                'max_installments' => 10,
                'default_installment_plans' => json_encode([
                    ['name' => 'Plan trimestral', 'count' => 3],
                    ['name' => 'Plan mensual', 'count' => 10],
                ]),
                'has_uniform' => true,
                'default_uniform_items' => json_encode(array_map(function($id) {
                    return ['id' => $id, 'quantity' => 2, 'price' => null];
                }, array_merge($primarySummerItems, $primaryWinterItems))),
                'is_active' => true,
                'created_by' => 'System',
            ],
            
            // Programa de idiomas - Primaria
            [
                'academic_year' => '2025-2026',
                'grade_level' => 'primary',
                'program_type' => 'لغات',
                'basic_fees' => 20000.00,
                'registration_fees' => 1500.00,
                'activities_fees' => 800.00,
                'bus_fees' => 2000.00,
                'books_fees' => 1800.00,
                'exam_fees' => 500.00,
                'platform_fees' => 400.00,
                'insurance_fees' => 150.00,
                'service_fees' => 700.00,
                'other_fees' => 0.00,
                'default_discounts' => json_encode([
                    ['type' => 'siblings', 'value' => 10, 'is_percentage' => true],
                    ['type' => 'staff', 'value' => 50, 'is_percentage' => true],
                    ['type' => 'scholarship', 'value' => 7000, 'is_percentage' => false],
                ]),
                'max_installments' => 10,
                'default_installment_plans' => json_encode([
                    ['name' => 'Plan trimestral', 'count' => 3],
                    ['name' => 'Plan mensual', 'count' => 10],
                ]),
                'has_uniform' => true,
                'default_uniform_items' => json_encode(array_map(function($id) {
                    return ['id' => $id, 'quantity' => 2, 'price' => null];
                }, array_merge($primarySummerItems, $primaryWinterItems))),
                'is_active' => true,
                'created_by' => 'System',
            ],
            
            // Programa internacional - Primaria
            [
                'academic_year' => '2025-2026',
                'grade_level' => 'primary',
                'program_type' => 'دولي',
                'basic_fees' => 30000.00,
                'registration_fees' => 2000.00,
                'activities_fees' => 1200.00,
                'bus_fees' => 2500.00,
                'books_fees' => 2500.00,
                'exam_fees' => 800.00,
                'platform_fees' => 600.00,
                'insurance_fees' => 200.00,
                'service_fees' => 1000.00,
                'other_fees' => 0.00,
                'default_discounts' => json_encode([
                    ['type' => 'siblings', 'value' => 10, 'is_percentage' => true],
                    ['type' => 'staff', 'value' => 40, 'is_percentage' => true],
                    ['type' => 'scholarship', 'value' => 10000, 'is_percentage' => false],
                ]),
                'max_installments' => 12,
                'default_installment_plans' => json_encode([
                    ['name' => 'Plan trimestral', 'count' => 3],
                    ['name' => 'Plan mensual', 'count' => 10],
                    ['name' => 'Plan flexible', 'count' => 12],
                ]),
                'has_uniform' => true,
                'default_uniform_items' => json_encode(array_map(function($id) {
                    return ['id' => $id, 'quantity' => 2, 'price' => null];
                }, array_merge($primarySummerItems, $primaryWinterItems))),
                'is_active' => true,
                'created_by' => 'System',
            ],
            
            // Programa preparatorio
            [
                'academic_year' => '2025-2026',
                'grade_level' => 'preparatory',
                'program_type' => 'لغات',
                'basic_fees' => 25000.00,
                'registration_fees' => 1800.00,
                'activities_fees' => 1000.00,
                'bus_fees' => 2200.00,
                'books_fees' => 2000.00,
                'exam_fees' => 600.00,
                'platform_fees' => 500.00,
                'insurance_fees' => 180.00,
                'service_fees' => 800.00,
                'other_fees' => 0.00,
                'default_discounts' => json_encode([
                    ['type' => 'siblings', 'value' => 10, 'is_percentage' => true],
                    ['type' => 'staff', 'value' => 50, 'is_percentage' => true],
                    ['type' => 'scholarship', 'value' => 8000, 'is_percentage' => false],
                ]),
                'max_installments' => 10,
                'default_installment_plans' => json_encode([
                    ['name' => 'Plan trimestral', 'count' => 3],
                    ['name' => 'Plan mensual', 'count' => 10],
                ]),
                'has_uniform' => true,
                'default_uniform_items' => json_encode(array_map(function($id) {
                    return ['id' => $id, 'quantity' => 2, 'price' => null];
                }, $prepItems)),
                'is_active' => true,
                'created_by' => 'System',
            ],
        ];

        foreach ($feeSettings as $setting) {
            FeeSetting::create($setting);
        }
    }
}