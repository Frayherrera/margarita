<?php

namespace Tests\Feature;

use App\Models\CashCategory;
use App\Models\CashMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingreso_and_egreso_can_be_registered(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $this->post('/panel/caja', [
            'type' => 'ingreso',
            'concept' => 'Venta viernes',
            'amount' => 500000,
            'method' => 'efectivo',
            'occurred_at' => today()->toDateString(),
        ])->assertRedirect(route('panel.caja.index'));

        $this->post('/panel/caja', [
            'type' => 'egreso',
            'concept' => 'Pago luz',
            'amount' => 80000,
            'method' => 'nequi',
            'occurred_at' => today()->toDateString(),
        ]);

        $this->assertDatabaseCount('cash_movements', 2);
    }

    public function test_summary_shows_totals(): void
    {
        $category = CashCategory::create(['name' => 'Ventas', 'type' => 'ingreso']);

        CashMovement::create([
            'cash_category_id' => $category->id,
            'type' => 'ingreso',
            'concept' => 'Venta 1',
            'amount' => 300000,
            'method' => 'efectivo',
            'occurred_at' => today(),
        ]);
        CashMovement::create([
            'type' => 'ingreso',
            'concept' => 'Venta 2',
            'amount' => 200000,
            'method' => 'datafono',
            'occurred_at' => today(),
        ]);
        CashMovement::create([
            'type' => 'egreso',
            'concept' => 'Compra hielo',
            'amount' => 50000,
            'method' => 'efectivo',
            'occurred_at' => today(),
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get('/panel/caja')
            ->assertOk()
            ->assertSee('$500.000')
            ->assertSee('$50.000')
            ->assertSee('$450.000');
    }

    public function test_movement_can_be_deleted(): void
    {
        $movement = CashMovement::create([
            'type' => 'egreso',
            'concept' => 'Error de registro',
            'amount' => 10000,
            'method' => 'efectivo',
            'occurred_at' => today(),
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->delete("/panel/caja/{$movement->id}")
            ->assertRedirect(route('panel.caja.index'));

        $this->assertModelMissing($movement);
    }

    public function test_invalid_method_is_rejected(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $this->post('/panel/caja', [
            'type' => 'ingreso',
            'concept' => 'Test',
            'amount' => 1000,
            'method' => 'paypal',
            'occurred_at' => today()->toDateString(),
        ])->assertSessionHasErrors('method');
    }
}
