<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashRequest;
use App\Models\CashCategory;
use App\Models\CashMovement;

class CashController extends Controller
{
    public function index()
    {
        $query = CashMovement::query()
            ->with('category')
            ->when(request('type'), fn ($q, $type) => $q->where('type', $type))
            ->when(request('method'), fn ($q, $method) => $q->where('method', $method))
            ->when(request('cash_category_id'), fn ($q, $id) => $q->where('cash_category_id', $id))
            ->when(request('from'), fn ($q, $from) => $q->whereDate('occurred_at', '>=', $from))
            ->when(request('to'), fn ($q, $to) => $q->whereDate('occurred_at', '<=', $to));

        $summary = (clone $query)->selectRaw("SUM(CASE WHEN type = 'ingreso' THEN amount ELSE 0 END) as ingresos")
            ->selectRaw("SUM(CASE WHEN type = 'egreso' THEN amount ELSE 0 END) as egresos")
            ->selectRaw("SUM(CASE WHEN type = 'ingreso' THEN amount ELSE -amount END) as neto")
            ->first();

        $byMethod = (clone $query)
            ->selectRaw('method')
            ->selectRaw("SUM(CASE WHEN type = 'ingreso' THEN amount ELSE 0 END) as ingresos")
            ->selectRaw("SUM(CASE WHEN type = 'egreso' THEN amount ELSE 0 END) as egresos")
            ->groupBy('method')
            ->orderBy('method')
            ->get();

        $movements = (clone $query)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('panel.cash.index', [
            'movements' => $movements,
            'categories' => CashCategory::orderBy('name')->get(),
            'summary' => $summary,
            'byMethod' => $byMethod,
        ]);
    }

    public function store(CashRequest $request)
    {
        CashMovement::create([
            ...$request->validated(),
            'cash_category_id' => $request->cash_category_id ?: null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('panel.caja.index')->with('success', 'Movimiento de caja registrado.');
    }

    public function destroy(CashMovement $cashMovement)
    {
        $cashMovement->delete();

        return redirect()->route('panel.caja.index')->with('success', 'Movimiento eliminado.');
    }
}
