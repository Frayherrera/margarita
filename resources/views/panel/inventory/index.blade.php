@extends('layouts.panel')
@section('title', 'Movimientos de inventario')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-6">Movimientos de inventario</h1>

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm mb-8">
    <h2 class="font-semibold mb-4">Registrar movimiento</h2>
    <form action="{{ route('panel.inventario.store') }}" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @csrf
        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-700" for="inv_product_id">Producto *</label>
            <select id="inv_product_id" name="inv_product_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                <option value="">— Selecciona un producto —</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected(old('inv_product_id') == $p->id)>
                        {{ $p->name }} · stock: {{ qty($p->current_stock) }} {{ $p->unit }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="type">Tipo *</label>
            <select id="type" name="type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                @foreach (App\Models\InvMovement::TYPES as $t)
                    <option value="{{ $t }}" @selected(old('type', 'entrada') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="reason">Motivo *</label>
            <select id="reason" name="reason" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                @foreach (App\Models\InvMovement::REASONS as $r)
                    <option value="{{ $r }}" @selected(old('reason', 'compra') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="quantity">Cantidad *</label>
            <input id="quantity" name="quantity" type="number" step="0.01" min="0.01" required value="{{ old('quantity') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="unit_cost">Costo unitario</label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">$</span>
                <input id="unit_cost" name="unit_cost" type="number" step="1" min="0" value="{{ old('unit_cost') }}"
                       class="block w-full rounded-md border-gray-300 pl-6 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            </div>
            <p class="mt-1 text-xs text-gray-500">En compras actualiza el precio del producto.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700" for="moved_at">Fecha *</label>
            <input id="moved_at" name="moved_at" type="date" required value="{{ old('moved_at', now()->toDateString()) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700" for="notes">Notas</label>
            <input id="notes" name="notes" type="text" value="{{ old('notes') }}"
                   placeholder="Ej: factura #123 proveedor X, botella rota…"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
        <div class="sm:col-span-3 flex justify-end">
            <button type="submit"
                    class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Registrar</button>
        </div>
    </form>
</div>

<form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <select name="inv_product_id" class="rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        <option value="">Todos los productos</option>
        @foreach ($products as $p)
            <option value="{{ $p->id }}" @selected(request('inv_product_id') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="type" class="rounded-md border-gray-300 shadow-sm sm:text-sm">
        <option value="">Entradas y salidas</option>
        <option value="entrada" @selected(request('type') === 'entrada')">Entradas</option>
        <option value="salida" @selected(request('type') === 'salida')">Salidas</option>
    </select>
    <select name="reason" class="rounded-md border-gray-300 shadow-sm sm:text-sm">
        <option value="">Todos los motivos</option>
        @foreach (App\Models\InvMovement::REASONS as $r)
            <option value="{{ $r }}" @selected(request('reason') === $r)>{{ ucfirst($r) }}</option>
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
                <th class="px-4 py-3">Producto</th>
                <th class="px-4 py-3">Tipo</th>
                <th class="px-4 py-3">Motivo</th>
                <th class="px-4 py-3 text-right">Cantidad</th>
                <th class="px-4 py-3 text-right">Costo unit.</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
                <th class="px-4 py-3">Notas</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $movement->moved_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-medium">{{ $movement->product->name }}</td>
                    <td class="px-4 py-3">
                        @if ($movement->type === 'entrada')
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">↑ Entrada</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">↓ Salida</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 capitalize text-gray-600">{{ $movement->reason }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ qty($movement->quantity) }} {{ $movement->product->unit }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $movement->unit_cost ? money($movement->unit_cost) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $movement->unit_cost ? money((float) $movement->unit_cost * (float) $movement->quantity) : '—' }}</td>
                    <td class="px-4 py-3 max-w-[220px] truncate text-gray-500" title="{{ $movement->notes }}">{{ $movement->notes ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Sin movimientos para este filtro.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
