<?php

namespace Database\Seeders;

use App\Models\InvCategory;
use Illuminate\Database\Seeder;

class InvCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cervezas',
            'Licores',
            'Vinos',
            'Mezcladores (gaseosas, jugos)',
            'Snacks',
            'Hielo y carbón',
            'Cigarrillos',
            'Otros',
        ];

        foreach ($categories as $name) {
            InvCategory::firstOrCreate(['name' => $name]);
        }
    }
}
