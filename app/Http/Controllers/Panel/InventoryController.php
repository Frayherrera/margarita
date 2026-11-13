<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovementRequest;
use App\Models\CashMovement;
use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'products' => InvProduct::orderBy('name')->get(['id', 'name', 'unit', 'current_stock', 'purchase_price', 'sale_price']),
        ]);
    }

    public function store(MovementRequest $request)
    {
        $count = count($request->items);

        DB::transaction(function () use ($request) {
            $products = InvProduct::whereIn('id', collect($request->items)->pluck('inv_product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $isPurchase = $request->type === 'entrada' && $request->reason === 'compra';
            $isSale = $request->type === 'salida' && $request->reason === 'venta';

            $cashTotal = 0;
            $cashLines = [];

            foreach ($request->items as $item) {
                $product = $products[$item['inv_product_id']];
                $quantity = (float) $item['quantity'];
                $unitCost = $item['unit_cost'] ?? null;

                $product->movements()->create([
                    'type' => $request->type,
                    'reason' => $request->reason,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'moved_at' => $request->moved_at,
                    'notes' => $request->notes,
                    'user_id' => auth()->id(),
                ]);

                if ($request->type === 'entrada') {
                    $product->increment('current_stock', $quantity);
                } else {
                    $product->decrement('current_stock', $quantity);
                }

                if ($isPurchase) {
                    if ($unitCost) {
                        $product->update(['purchase_price' => $unitCost]);
                    }
                    $cashTotal += (float) ($unitCost ?? $product->purchase_price ?? 0) * $quantity;
                } elseif ($isSale) {
                    $cashTotal += (float) ($unitCost ?? $product->sale_price ?? 0) * $quantity;
                }

                $cashLines[] = "{$product->name} × " . qty($quantity);
            }

            if ($isPurchase || $isSale) {
                CashMovement::create([
                    'type' => $isPurchase ? 'egreso' : 'ingreso',
                    'concept' => Str::limit(($isPurchase ? 'Compra: ' : 'Venta: ') . implode(', ', $cashLines), 250),
                    'amount' => $cashTotal,
                    'method' => $request->method ?: 'efectivo',
                    'occurred_at' => $request->moved_at,
                    'notes' => $request->notes,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()
            ->route('panel.dashboard')
            ->with('success', $count === 1
                ? 'Movimiento registrado correctamente.'
                : "{$count} movimientos registrados correctamente.");
    }
}
