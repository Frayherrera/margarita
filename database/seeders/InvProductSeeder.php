<?php

namespace Database\Seeders;

use App\Models\InvCategory;
use App\Models\InvProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvProductSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, categoría, unidad, stock inicial]
        $products = [
            ['Águila X30', 'Cervezas', 'botella', 82],
            ['Águila 750', 'Cervezas', 'botella', 70],
            ['Águila Lata', 'Cervezas', 'unidad', 0],
            ['Póker X30', 'Cervezas', 'botella', 2],
            ['Póker 750', 'Cervezas', 'botella', 16],
            ['Póker Lata', 'Cervezas', 'unidad', 7],
            ['Costeña X30', 'Cervezas', 'botella', 119],
            ['Costeñita', 'Cervezas', 'botella', 20],
            ['Costeña Lata', 'Cervezas', 'unidad', 39],
            ['Corona Lata', 'Cervezas', 'unidad', 5],
            ['Coronita', 'Cervezas', 'botella', 35],
            ['Club Colombia', 'Cervezas', 'botella', 17],
            ['Stella', 'Cervezas', 'botella', 17],
            ['Cola y Pola', 'Mezcladores (gaseosas, jugos)', 'botella', 7],
            ['Pony Malta', 'Mezcladores (gaseosas, jugos)', 'botella', 5],
            ['Speed Max', 'Mezcladores (gaseosas, jugos)', 'unidad', 5],
            ['Gatorade', 'Mezcladores (gaseosas, jugos)', 'botella', 3],
            ['Electrolit', 'Mezcladores (gaseosas, jugos)', 'botella', 3],
            ['Agua', 'Mezcladores (gaseosas, jugos)', 'botella', 0],
            ['Litro AA', 'Licores', 'botella', 3],
            ['Panchita AA', 'Licores', 'botella', 6],
            ['Litro Amarillo', 'Licores', 'botella', 0],
            ['Lucky', 'Cigarrillos', 'unidad', 125],
            ['Rumba', 'Cigarrillos', 'unidad', 41],
            ['Chitos', 'Snacks', 'paquete', 9],
            ['Lightcita', 'Cervezas', 'botella', 16],
        ];

        $categoryIds = InvCategory::pluck('id', 'name');

        DB::transaction(function () use ($products, $categoryIds) {
            foreach ($products as [$name, $category, $unit, $stock]) {
                $product = InvProduct::firstOrCreate(
                    ['name' => $name],
                    [
                        'inv_category_id' => $categoryIds[$category] ?? null,
                        'unit' => $unit,
                        'active' => true,
                    ]
                );

                // Solo carga stock inicial a productos recién creados (idempotente).
                if (! $product->wasRecentlyCreated || $stock <= 0) {
                    continue;
                }

                $product->movements()->create([
                    'type' => 'entrada',
                    'reason' => 'ajuste',
                    'quantity' => $stock,
                    'moved_at' => now(),
                    'notes' => 'Inventario inicial',
                ]);

                $product->increment('current_stock', $stock);
            }
        });
    }
}
