<?php

namespace Tests\Feature;

use App\Models\InvCategory;
use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/panel/productos')->assertRedirect('/login');
        $this->post('/panel/productos', [])->assertRedirect('/login');
    }

    public function test_product_can_be_created_with_initial_stock(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $category = InvCategory::create(['name' => 'Cervezas']);

        $response = $this->post('/panel/productos', [
            'name' => 'Poker 330ml',
            'inv_category_id' => $category->id,
            'unit' => 'unidad',
            'initial_stock' => 24,
            'min_stock' => 6,
            'purchase_price' => 2200,
            'sale_price' => 3500,
            'active' => '1',
        ]);

        $product = InvProduct::where('name', 'Poker 330ml')->first();

        $this->assertNotNull($product);
        $this->assertEquals(24, (float) $product->current_stock);
        $this->assertTrue($product->movements()->where('notes', 'Inventario inicial')->exists());
        $response->assertRedirect(route('panel.productos.index'));
    }

    public function test_stock_cannot_be_modified_through_update(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $product = new InvProduct(['name' => 'Ron Viejo', 'unit' => 'botella']);
        $product->current_stock = 5;
        $product->save();

        $this->put("/panel/productos/{$product->id}", [
            'name' => 'Ron Viejo Caldas',
            'unit' => 'botella',
            'initial_stock' => 999,
            'active' => '1',
        ]);

        $this->assertEquals(5, (float) $product->fresh()->current_stock);
    }

    public function test_product_with_movements_cannot_be_deleted(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $product = new InvProduct(['name' => 'Aguardiente', 'unit' => 'botella']);
        $product->save();
        InvMovement::create([
            'inv_product_id' => $product->id,
            'type' => 'entrada',
            'reason' => 'compra',
            'quantity' => 3,
            'moved_at' => today(),
        ]);

        $response = $this->delete("/panel/productos/{$product->id}");

        $this->assertDatabaseHas('inv_products', ['id' => $product->id]);
        $response->assertRedirect(route('panel.productos.index'));
    }

    public function test_index_shows_low_stock_warning(): void
    {
        $product = new InvProduct(['name' => 'Coca-Cola', 'unit' => 'unidad', 'min_stock' => 5]);
        $product->current_stock = 2;
        $product->save();

        $this->actingAs(\App\Models\User::factory()->create())
            ->get('/panel/productos')
            ->assertOk()
            ->assertSee('⚠');
    }
}
