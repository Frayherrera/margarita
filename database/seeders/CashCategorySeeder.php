<?php

namespace Database\Seeders;

use App\Models\CashCategory;
use Illuminate\Database\Seeder;

class CashCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'ingreso' => ['Ventas', 'Otros ingresos'],
            'egreso' => ['Compra de mercancía', 'Nómina', 'Servicios públicos', 'Arriendo', 'Transporte', 'Mantenimiento', 'Impuestos', 'Otros gastos'],
        ];

        foreach ($categories as $type => $names) {
            foreach ($names as $name) {
                CashCategory::firstOrCreate(['name' => $name, 'type' => $type]);
            }
        }
    }
}
