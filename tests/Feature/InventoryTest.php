<?php

namespace Tests\Feature;

use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function movementData(array $overrides = []): array
    {
        return [
            'inv_product_id' => $this->product->id,
            'type' => 'entrada',
            'reason' => 'compra',
            'quantity' => 10,
            'moved_at' => today()->toDateString(),
            ...$overrides,
        ];
    }

    private InvProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(\App\Models\User::factory()->create());

        $this->product = new InvProduct([
            'name' => 'Poker 330ml',
            'unit' => 'unidad',
            'purchase_price' => 2000,
        ]);
        $this->product->current_stock = 10;
        $this->product->save();
    }

    public function test_entrada_increases_stock(): void
    {
        $this->post('/panel/inventario', $this->movementData(['quantity' => 15]));

        $this->assertEquals(25, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseCount('inv_movements', 1);
    }

    public function test_salida_decreases_stock(): void
    {
        $this->post('/panel/inventario', $this->movementData([
            'type' => 'salida',
            'reason' => 'venta',
            'quantity' => 4,
        ]));

        $this->assertEquals(6, (float) $this->product->fresh()->current_stock);
    }

    public function test_salida_greater_than_stock_is_rejected(): void
    {
        $response = $this->post('/panel/inventario', $this->movementData([
            'type' => 'salida',
            'reason' => 'merma',
            'quantity' => 50,
        ]));

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(10, (float) $this->product->fresh()->current_stock);
    }

    public function test_compra_with_unit_cost_updates_product_price(): void
    {
        $this->post('/panel/inventario', $this->movementData([
            'quantity' => 5,
            'unit_cost' => 2500,
        ]));

        $this->assertEquals(2500, (float) $this->product->fresh()->purchase_price);
    }

    public function test_history_filters_by_type(): void
    {
        InvMovement::create([
            'inv_product_id' => $this->product->id,
            'type' => 'salida',
            'reason' => 'venta',
            'quantity' => 2,
            'moved_at' => today(),
        ]);

        $this->get('/panel/inventario?type=salida')
            ->assertOk()
            ->assertSee('venta');
    }
}
