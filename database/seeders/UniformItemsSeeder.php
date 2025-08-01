<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UniformItem;

class UniformItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear prendas de uniforme de ejemplo
        $uniformItems = [
            // Uniformes de verano
            [
                'name' => 'Camiseta polo',
                'type' => 'صيفي',
                'gender' => 'الجميع',
                'price' => 120.00,
                'grade_level' => 'primary',
                'description' => 'Camiseta polo con logo de la escuela',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Pantalón corto',
                'type' => 'صيفي',
                'gender' => 'ذكر',
                'price' => 100.00,
                'grade_level' => 'primary',
                'description' => 'Pantalón corto para niños',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Falda',
                'type' => 'صيفي',
                'gender' => 'أنثى',
                'price' => 110.00,
                'grade_level' => 'primary',
                'description' => 'Falda para niñas',
                'is_active' => true,
                'created_by' => 'System',
            ],
            
            // Uniformes de invierno
            [
                'name' => 'Sudadera',
                'type' => 'شتوي',
                'gender' => 'الجميع',
                'price' => 180.00,
                'grade_level' => 'primary',
                'description' => 'Sudadera con logo de la escuela',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Pantalón largo',
                'type' => 'شتوي',
                'gender' => 'الجميع',
                'price' => 150.00,
                'grade_level' => 'primary',
                'description' => 'Pantalón largo para invierno',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Chaqueta',
                'type' => 'شتوي',
                'gender' => 'الجميع',
                'price' => 250.00,
                'grade_level' => 'primary',
                'description' => 'Chaqueta de invierno con logo',
                'is_active' => true,
                'created_by' => 'System',
            ],
            
            // Uniformes para nivel preparatorio
            [
                'name' => 'Camisa formal',
                'type' => 'موحد',
                'gender' => 'الجميع',
                'price' => 140.00,
                'grade_level' => 'preparatory',
                'description' => 'Camisa formal con logo de la escuela',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Pantalón formal',
                'type' => 'موحد',
                'gender' => 'ذكر',
                'price' => 160.00,
                'grade_level' => 'preparatory',
                'description' => 'Pantalón formal para niños',
                'is_active' => true,
                'created_by' => 'System',
            ],
            [
                'name' => 'Falda formal',
                'type' => 'موحد',
                'gender' => 'أنثى',
                'price' => 160.00,
                'grade_level' => 'preparatory',
                'description' => 'Falda formal para niñas',
                'is_active' => true,
                'created_by' => 'System',
            ],
        ];

        foreach ($uniformItems as $item) {
            UniformItem::create($item);
        }
    }
}