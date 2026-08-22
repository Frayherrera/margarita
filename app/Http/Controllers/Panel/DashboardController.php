<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = CashMovement::query()
            ->whereDate('occurred_at', today())
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'ingreso' THEN amount ELSE 0 END), 0) as ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'egreso' THEN amount ELSE 0 END), 0) as egresos")
            ->first();

        $month = CashMovement::query()
            ->whereMonth('occurred_at', now()->month)
            ->whereYear('occurred_at', now()->year)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'ingreso' THEN amount ELSE -amount END), 0) as neto")
            ->first();

        $inventoryValue = (float) InvProduct::query()
            ->where('active', true)
            ->sum(DB::raw('current_stock * COALESCE(purchase_price, 0)'));

        return view('panel.dashboard', [
            'ingresosHoy' => (float) $today->ingresos,
            'egresosHoy' => (float) $today->egresos,
            'netoMes' => (float) $month->neto,
            'inventoryValue' => $inventoryValue,
            'lowStock' => InvProduct::where('active', true)->orderBy('name')->get()->filter->isLowStock()->values(),
            'recentInventory' => InvMovement::with('product')->latest('moved_at')->latest('id')->limit(6)->get(),
            'recentCash' => CashMovement::with('category')->latest('occurred_at')->latest('id')->limit(6)->get(),
        ]);
    }
}
