<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovementRequest;
use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $movements = InvMovement::query()
            ->with('product')
            ->when(request('inv_product_id'), fn ($q, $id) => $q->where('inv_product_id', $id))
            ->when(request('type'), fn ($q, $type) => $q->where('type', $type))
            ->when(request('reason'), fn ($q, $reason) => $q->where('reason', $reason))
            ->when(request('from'), fn ($q, $from) => $q->whereDate('moved_at', '>=', $from))
            ->when(request('to'), fn ($q, $to) => $q->whereDate('moved_at', '<=', $to))
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('panel.inventory.index', [
            'movements' => $movements,
            'products' => InvProduct::orderBy('name')->get(['id', 'name', 'unit', 'current_stock']),
        ]);
    }

    public function store(MovementRequest $request)
    {
        DB::transaction(function () use ($request) {
            $product = InvProduct::lockForUpdate()->findOrFail($request->inv_product_id);

            $product->movements()->create([
                'type' => $request->type,
                'reason' => $request->reason,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'moved_at' => $request->moved_at,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            if ($request->type === 'entrada') {
                $product->increment('current_stock', $request->quantity);

                if ($request->reason === 'compra' && $request->unit_cost) {
                    $product->update(['purchase_price' => $request->unit_cost]);
                }
            } else {
                $product->decrement('current_stock', $request->quantity);
            }
        });

        return redirect()
            ->route('panel.inventario.index')
            ->with('success', 'Movimiento registrado correctamente.');
    }
}
