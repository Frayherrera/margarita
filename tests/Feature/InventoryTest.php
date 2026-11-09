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
            'sale_price' => 3000,
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

    public function test_venta_decreases_stock_and_registers_cash_income(): void
    {
        $this->post('/panel/inventario', $this->movementData([
            'type' => 'salida',
            'reason' => 'venta',
            'quantity' => 4,
            'method' => 'nequi',
        ]))->assertRedirect(route('panel.dashboard'));

        $this->assertEquals(6, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'ingreso',
            'amount' => 12000,
            'method' => 'nequi',
        ]);
    }

    public function test_venta_uses_given_unit_price_for_cash_income(): void
    {
        $this->post('/panel/inventario', $this->movementData([
            'type' => 'salida',
            'reason' => 'venta',
            'quantity' => 2,
            'unit_cost' => 2500,
        ]));

        $this->assertEquals(8, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'ingreso',
            'amount' => 5000,
        ]);
    }

    public function test_venta_without_price_is_rejected(): void
    {
        $this->product->update(['sale_price' => null]);

        $response = $this->post('/panel/inventario', $this->movementData([
            'type' => 'salida',
            'reason' => 'venta',
            'quantity' => 4,
        ]));

        $response->assertSessionHasErrors('unit_cost');
        $this->assertEquals(10, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseCount('cash_movements', 0);
    }

    public function test_compra_increases_stock_and_registers_cash_expense(): void
    {
        $this->post('/panel/inventario', $this->movementData([
            'quantity' => 5,
            'unit_cost' => 2500,
            'method' => 'transferencia',
        ]))->assertRedirect(route('panel.dashboard'));

        $this->assertEquals(15, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'egreso',
            'amount' => 12500,
            'method' => 'transferencia',
        ]);
    }

    public function test_compra_uses_product_purchase_price_when_not_given(): void
    {
        $this->post('/panel/inventario', $this->movementData(['quantity' => 5]));

        $this->assertEquals(15, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'egreso',
            'amount' => 10000,
        ]);
    }

    public function test_compra_without_price_is_rejected(): void
    {
        $this->product->update(['purchase_price' => null]);

        $response = $this->post('/panel/inventario', $this->movementData(['quantity' => 5]));

        $response->assertSessionHasErrors('unit_cost');
        $this->assertEquals(10, (float) $this->product->fresh()->current_stock);
        $this->assertDatabaseCount('cash_movements', 0);
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
