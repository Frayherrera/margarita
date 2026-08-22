<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\InvCategory;
use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = InvProduct::query()
            ->with('category')
            ->when(request('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(request('category_id'), fn ($q, $id) => $q->where('inv_category_id', $id))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = InvCategory::orderBy('name')->get();

        return view('panel.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('panel.products.create', [
            'categories' => InvCategory::orderBy('name')->get(),
            'units' => InvProduct::UNITS,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $this->productData($request);

        DB::transaction(function () use ($request, $data) {
            $product = InvProduct::create($data);

            if ((float) $request->initial_stock > 0) {
                $product->movements()->create([
                    'type' => InvMovement::TYPES[0],
                    'reason' => 'compra',
                    'quantity' => $request->initial_stock,
                    'unit_cost' => $data['purchase_price'] ?? null,
                    'moved_at' => now(),
                    'notes' => 'Inventario inicial',
                    'user_id' => auth()->id(),
                ]);

                $product->increment('current_stock', $request->initial_stock);
            }
        });

        return redirect()
            ->route('panel.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(InvProduct $producto)
    {
        return view('panel.products.edit', [
            'product' => $producto,
            'categories' => InvCategory::orderBy('name')->get(),
            'units' => InvProduct::UNITS,
        ]);
    }

    public function update(ProductRequest $request, InvProduct $producto)
    {
        $producto->update($this->productData($request));

        return redirect()
            ->route('panel.productos.index')
            ->with('success', 'Producto actualizado.');
    }

    public function destroy(InvProduct $producto)
    {
        if ($producto->movements()->exists()) {
            return redirect()
                ->route('panel.productos.index')
                ->with('error', "«{$producto->name}» tiene movimientos registrados. Desactívalo en lugar de eliminarlo.");
        }

        $producto->delete();

        return redirect()->route('panel.productos.index')->with('success', 'Producto eliminado.');
    }

    private function productData(ProductRequest $request): array
    {
        return [
            'name' => $request->name,
            'inv_category_id' => $request->inv_category_id ?: null,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock ?? 0,
            'purchase_price' => $request->purchase_price,
            'sale_price' => $request->sale_price,
            'active' => $request->boolean('active'),
            'notes' => $request->notes,
        ];
    }
}
