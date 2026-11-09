@extends('layouts.panel')
@section('title', 'Movimientos de inventario')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">BODEGA</h1>
<p class="mb-6 text-sm text-stone-500">Cada botella que entra y sale, con su motivo.</p>

<section class="card mb-8 p-5 sm:p-6">
    <h2 class="font-mono text-xs font-semibold uppercase tracking-widest text-stone-400">Registrar movimiento</h2>
    <form action="{{ route('panel.inventario.store') }}" method="POST" x-data="{ reason: @js(old('reason', request('reason', 'compra'))) }" class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
        @csrf
        <div class="sm:col-span-3">
            <label class="field-label" for="inv_product_id">Producto *</label>
            <select id="inv_product_id" name="inv_product_id" required class="input">
                <option value="">— Selecciona un producto —</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected(old('inv_product_id') == $p->id)>
                        {{ $p->name }} · en bodega: {{ qty($p->current_stock) }} {{ $p->unit }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label" for="type">Tipo *</label>
            <select id="type" name="type" required class="input">
                @foreach (App\Models\InvMovement::TYPES as $t)
                    <option value="{{ $t }}" @selected(old('type', request('type', 'entrada')) === $t)>@if($t === 'entrada')↑ Entrada @else ↓ Salida @endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label" for="reason">Motivo *</label>
            <select id="reason" name="reason" x-model="reason" required class="input capitalize">
                @foreach (App\Models\InvMovement::REASONS as $r)
                    <option value="{{ $r }}" @selected(old('reason', request('reason', 'compra')) === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label" for="quantity">Cantidad *</label>
            <input id="quantity" name="quantity" type="number" step="0.01" min="0.01" inputmode="decimal" required value="{{ old('quantity') }}" class="input font-mono">
        </div>
        <div x-show="reason === 'venta' || reason === 'compra'">
            <label class="field-label" for="method">Método de pago</label>
            <select id="method" name="method" class="input capitalize">
                @foreach (App\Models\CashMovement::METHODS as $m)
                    <option value="{{ $m }}" @selected(old('method', 'efectivo') === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-stone-400">Con este método se registra el movimiento en caja.</p>
        </div>
        <div>
            <label class="field-label" for="unit_cost">Costo / precio unitario</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 pt-1.5 font-mono text-sm text-stone-400">$</span>
                <input id="unit_cost" name="unit_cost" type="number" step="1" min="0" inputmode="numeric" value="{{ old('unit_cost') }}" class="input pl-7 font-mono">
            </div>
            <p class="mt-1 text-xs text-stone-400">En compras actualiza el precio de compra y suma el egreso a caja. En ventas suma el ingreso a caja. Si lo dejas vacío se usa el precio del producto.</p>
        </div>
        <div>
            <label class="field-label" for="moved_at">Fecha *</label>
            <input id="moved_at" name="moved_at" type="date" required value="{{ old('moved_at', now()->toDateString()) }}" class="input font-mono">
        </div>
        <div class="sm:col-span-2">
            <label class="field-label" for="notes">Notas</label>
            <input id="notes" name="notes" type="text" value="{{ old('notes') }}"
                   placeholder="Ej: factura #123 del proveedor, botella rota…"
                   class="input">
        </div>
        <div class="flex items-end justify-end sm:col-span-3">
            <button type="submit" class="btn-primary w-full sm:w-auto">Registrar movimiento</button>
        </div>
    </form>
</section>

<form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <select name="inv_product_id" class="max-w-[180px] rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar" aria-label="Producto">
        <option value="">Todos los productos</option>
        @foreach ($products as $p)
            <option value="{{ $p->id }}" @selected(request('inv_product_id') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="type" class="rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar" aria-label="Tipo">
        <option value="">Entradas y salidas</option>
        <option value="entrada" @selected(request('type') === 'entrada')">Entradas</option>
        <option value="salida" @selected(request('type') === 'salida')">Salidas</option>
    </select>
    <select name="reason" class="rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar" aria-label="Motivo">
        <option value="">Todos los motivos</option>
        @foreach (App\Models\InvMovement::REASONS as $r)
            <option value="{{ $r }}" @selected(request('reason') === $r)>{{ ucfirst($r) }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" title="Desde"
           class="rounded-lg border-stone-300 bg-white font-mono text-sm shadow-sm focus:border-ambar focus:ring-ambar">
    <span class="-mx-1 hidden text-stone-300 sm:inline">→</span>
    <input type="date" name="to" value="{{ request('to') }}" title="Hasta"
           class="rounded-lg border-stone-300 bg-white font-mono text-sm shadow-sm focus:border-ambar focus:ring-ambar">
    <button type="submit" class="btn-ghost !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
    <table class="rtable min-w-full divide-y divide-stone-200 text-sm">
        <thead class="bg-stone-50 text-left font-mono text-[11px] uppercase tracking-wider text-stone-400">
            <tr>
                <th class="px-4 py-3">Fecha</th>
                <th class="px-4 py-3">Producto</th>
                <th class="px-4 py-3">Movimiento</th>
                <th class="px-4 py-3 text-right">Costo unit.</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
                <th class="px-4 py-3">Notas</th>
                <th class="px-4 py-3 text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td data-label="Fecha" class="whitespace-nowrap px-4 py-3 font-mono">{{ $movement->moved_at->format('d/m/Y') }}</td>
                    <td data-label="Producto" class="px-4 py-3 font-medium">{{ $movement->product->name }}</td>
                    <td data-label="Movimiento" class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1.5 sm:justify-start">
                            @if ($movement->type === 'entrada')
                                <span class="stamp-in">Entrada</span>
                            @else
                                <span class="stamp-out">Salida</span>
                            @endif
                            <span class="text-xs capitalize text-stone-400">{{ $movement->reason }}</span>
                        </div>
                    </td>
                    <td data-label="Costo unit." class="whitespace-nowrap px-4 py-3 text-right font-mono text-stone-600">{{ $movement->unit_cost ? money($movement->unit_cost) : '—' }}</td>
                    <td data-label="Subtotal" class="whitespace-nowrap px-4 py-3 text-right font-mono text-stone-600">{{ $movement->unit_cost ? money((float) $movement->unit_cost * (float) $movement->quantity) : '—' }}</td>
                    <td data-label="Notas" class="max-w-[200px] px-4 py-3"><span class="block truncate text-stone-500" title="{{ $movement->notes }}">{{ $movement->notes ?? '—' }}</span></td>
                    <td data-label="Cantidad" class="whitespace-nowrap px-4 py-3 text-right font-mono font-semibold {{ $movement->type === 'entrada' ? 'text-billete' : 'text-ladrillo' }}">
                        {{ $movement->type === 'entrada' ? '+' : '−' }}{{ qty($movement->quantity) }}
                        <span class="text-xs font-normal text-stone-400">{{ $movement->product->unit }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-stone-400">Sin movimientos para este filtro.<br>Registra el primero con el formulario de arriba.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
