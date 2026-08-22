@extends('layouts.panel')
@section('title', 'Caja')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-6">Caja · Ingresos y egresos</h1>

{{-- Resumen del período filtrado --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Ingresos</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ money($summary->ingresos ?? 0) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Egresos</p>
        <p class="mt-1 text-2xl font-bold text-red-600">{{ money($summary->egresos ?? 0) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Neto</p>
        @php $neto = (float) ($summary->neto ?? 0) @endphp
        <p class="mt-1 text-2xl font-bold {{ $neto >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ money($neto) }}</p>
    </div>
</div>

@if ($byMethod->isNotEmpty())
    <div class="mb-8 overflow-x-auto rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="px-1 pb-2 text-sm font-semibold text-gray-500">Por método de pago</h2>
        <table class="min-w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-gray-400">
                <tr><th class="px-1 py-1">Método</th><th class="px-1 py-1 text-right">Ingresos</th><th class="px-1 py-1 text-right">Egresos</th><th class="px-1 py-1 text-right">Neto</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($byMethod as $row)
                    @php $mNeto = (float) $row->ingresos - (float) $row->egresos; @endphp
                    <tr>
                        <td class="px-1 py-1.5 capitalize font-medium">{{ ucfirst($row->method) }}</td>
                        <td class="px-1 py-1.5 text-right text-emerald-600">{{ money($row->ingresos) }}</td>
                        <td class="px-1 py-1.5 text-right text-red-500">{{ money($row->egresos) }}</td>
                        <td class="px-1 py-1.5 text-right font-semibold {{ $mNeto >= 0 ? '' : 'text-red-600' }}">{{ money($mNeto) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Formulario --}}
<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm mb-8">
    <h2 class="font-semibold mb-4">Registrar movimiento</h2>
    <form action="{{ route('panel.caja.store') }}" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700" for="type">Tipo *</label>
            <select id="type" name="type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                @foreach (App\Models\CashMovement::TYPES as $t)
                    <option value="{{ $t }}" @selected(old('type', 'ingreso') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700" for="concept">Concepto *</label>
            <input id="concept" name="concept" type="text" required value="{{ old('concept') }}"
                   placeholder="Ej: Venta día viernes, compra cervezas, pago luz…"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="amount">Monto *</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">$</span>
                <input id="amount" name="amount" type="number" step="1" min="1" required value="{{ old('amount') }}"
                       class="block w-full rounded-md border-gray-300 pl-6 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="method">Método *</label>
            <select id="method" name="method" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                @foreach (App\Models\CashMovement::METHODS as $m)
                    <option value="{{ $m }}" @selected(old('method', 'efectivo') === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="cash_category_id">Categoría</label>
            <select id="cash_category_id" name="cash_category_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                <option value="">— Sin categoría —</option>
                @foreach (['ingreso', 'egreso'] as $groupType)
                    <optgroup label="{{ ucfirst($groupType) }}s">
                        @foreach ($categories->where('type', $groupType) as $cat)
                            <option value="{{ $cat->id }}" @selected(old('cash_category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="occurred_at">Fecha *</label>
            <input id="occurred_at" name="occurred_at" type="date" required value="{{ old('occurred_at', now()->toDateString()) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700" for="notes">Notas</label>
            <input id="notes" name="notes" type="text" value="{{ old('notes') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div class="sm:col-span-3 flex justify-end">
            <button type="submit"
                    class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Registrar</button>
        </div>
    </form>
</div>

{{-- Filtros --}}
<form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <select name="type" class="rounded-md border-gray-300 shadow-sm sm:text-sm">
        <option value="">Ingresos y egresos</option>
        <option value="ingreso" @selected(request('type') === 'ingreso')">Ingresos</option>
        <option value="egreso" @selected(request('type') === 'egreso')">Egresos</option>
    </select>
    <select name="method" class="rounded-md border-gray-300 shadow-sm sm:text-sm">
        <option value="">Todos los métodos</option>
        @foreach (App\Models\CashMovement::METHODS as $m)
            <option value="{{ $m }}" @selected(request('method') === $m)>{{ ucfirst($m) }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" title="Desde"
           class="rounded-md border-gray-300 shadow-sm sm:text-sm">
    <input type="date" name="to" value="{{ request('to') }}" title="Hasta"
           class="rounded-md border-gray-300 shadow-sm sm:text-sm">
    <button type="submit" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">Filtrar</button>
</form>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">Fecha</th>
                <th class="px-4 py-3">Concepto</th>
                <th class="px-4 py-3">Tipo</th>
                <th class="px-4 py-3">Método</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3 text-right">Monto</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $movement->occurred_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $movement->concept }}</span>
                        @if ($movement->notes)
                            <span class="block max-w-[260px] truncate text-xs text-gray-400" title="{{ $movement->notes }}">{{ $movement->notes }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($movement->type === 'ingreso')
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">↑ Ingreso</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">↓ Egreso</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 capitalize text-gray-600">{{ ucfirst($movement->method) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $movement->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold {{ $movement->type === 'ingreso' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $movement->type === 'ingreso' ? '+' : '−' }}{{ money($movement->amount) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('panel.caja.destroy', $movement) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este movimiento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Sin movimientos para este filtro.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
