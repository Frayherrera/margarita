@extends('layouts.panel')
@section('title', 'Movimientos de inventario')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">BODEGA</h1>
<p class="mb-6 text-sm text-stone-500">Cada botella que entra y sale, con su motivo.</p>

@php
    $productData = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'unit' => $p->unit,
        'stock' => (float) $p->current_stock,
        'purchase' => (float) $p->purchase_price,
        'sale' => (float) $p->sale_price,
    ])->values();

    $initialItems = collect(old('items', [[]]))->map(fn ($i) => [
        'inv_product_id' => (string) ($i['inv_product_id'] ?? ''),
        'quantity' => $i['quantity'] ?? '',
        'unit_cost' => $i['unit_cost'] ?? '',
    ])->values();
@endphp
<section class="card mb-8 p-5 sm:p-6">
    <h2 class="font-mono text-xs font-semibold uppercase tracking-widest text-stone-400">Registrar movimiento</h2>
    <form action="{{ route('panel.inventario.store') }}" method="POST"
          x-data="movementForm({
              products: @js($productData),
              items: @js($initialItems),
              type: @js(old('type', request('type', 'entrada'))),
              reason: @js(old('reason', request('reason', 'compra'))),
          })"
          class="mt-4 space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
            <div>
                <label class="field-label" for="type">Tipo *</label>
                <select id="type" name="type" x-model="type" required class="input">
                    @foreach (App\Models\InvMovement::TYPES as $t)
                        <option value="{{ $t }}">@if($t === 'entrada')↑ Entrada @else ↓ Salida @endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="reason">Motivo *</label>
                <select id="reason" name="reason" x-model="reason" required class="input capitalize">
                    @foreach (App\Models\InvMovement::REASONS as $r)
                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="moved_at">Fecha *</label>
                <input id="moved_at" name="moved_at" type="date" required value="{{ old('moved_at', now()->toDateString()) }}" class="input font-mono">
            </div>
        </div>

        <div>
            <p class="field-label">Productos *</p>
            @error('items')<p class="mb-2 text-sm text-ladrillo">{{ $message }}</p>@enderror

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="item.key">
                    <div class="grid grid-cols-2 gap-x-3 gap-y-2 rounded-lg border border-stone-200 p-3 sm:grid-cols-12 sm:items-start sm:border-0 sm:p-0">
                        <div class="col-span-2 sm:col-span-5">
                            <select :name="`items[${index}][inv_product_id]`" x-model="item.inv_product_id" class="input" aria-label="Producto">
                                <option value="">— Selecciona un producto —</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="String(p.id)" :selected="String(p.id) === item.inv_product_id"
                                            x-text="`${p.name} · en bodega: ${fmtQty(p.stock)} ${p.unit}`"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-ladrillo" x-show="errorFor(index, 'inv_product_id')" x-text="errorFor(index, 'inv_product_id')"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <input :name="`items[${index}][quantity]`" x-model="item.quantity" type="number" step="0.01" min="0.01" inputmode="decimal"
                                   placeholder="Cant." aria-label="Cantidad" class="input font-mono">
                            <p class="mt-1 text-xs text-ladrillo" x-show="errorFor(index, 'quantity')" x-text="errorFor(index, 'quantity')"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 pt-1.5 font-mono text-sm text-stone-400">$</span>
                                <input :name="`items[${index}][unit_cost]`" x-model="item.unit_cost" type="number" step="1" min="0" inputmode="numeric"
                                       :placeholder="defaultPrice(item) ? fmtMoney(defaultPrice(item)).replace('$', '') : 'Precio'"
                                       aria-label="Precio unitario" class="input pl-7 font-mono">
                            </div>
                            <p class="mt-1 text-xs text-ladrillo" x-show="errorFor(index, 'unit_cost')" x-text="errorFor(index, 'unit_cost')"></p>
                        </div>
                        <div class="flex items-center justify-between gap-2 col-span-2 sm:col-span-3 sm:pt-2">
                            <span class="font-mono text-sm text-stone-600" x-text="subtotal(item) ? fmtMoney(subtotal(item)) : '—'"></span>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                    class="btn-ghost !py-1 text-xs" title="Quitar producto">✕ Quitar</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <button type="button" @click="addItem()" class="btn-ghost !py-2">+ Agregar producto</button>
                <p class="font-mono text-sm" x-show="total() > 0">
                    <span class="text-stone-400">Total:</span>
                    <span class="font-semibold" x-text="fmtMoney(total())"></span>
                </p>
            </div>
            <p class="mt-2 text-xs text-stone-400">Si dejas el precio vacío se usa el del producto (el que aparece en gris). En compras, el precio que escribas actualiza el precio de compra.</p>
        </div>

        <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
            <div x-show="reason === 'venta' || reason === 'compra'">
                <label class="field-label" for="method">Método de pago</label>
                <select id="method" name="method" class="input capitalize">
                    @foreach (App\Models\CashMovement::METHODS as $m)
                        <option value="{{ $m }}" @selected(old('method', 'efectivo') === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-stone-400">El total se registra en caja como un solo movimiento.</p>
            </div>
            <div class="sm:col-span-2">
                <label class="field-label" for="notes">Notas</label>
                <input id="notes" name="notes" type="text" value="{{ old('notes') }}"
                       placeholder="Ej: factura #123 del proveedor, botella rota…"
                       class="input">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary w-full sm:w-auto"
                    x-text="filledCount() > 1 ? `Registrar ${filledCount()} productos` : 'Registrar movimiento'">Registrar movimiento</button>
        </div>
    </form>
</section>

<script>
    function movementForm({ products, items, type, reason }) {
        let nextKey = 0;
        const errors = @js($errors->getMessages());

        return {
            products,
            type,
            reason,
            items: items.map(i => ({ ...i, key: nextKey++ })),

            addItem() {
                this.items.push({ inv_product_id: '', quantity: '', unit_cost: '', key: nextKey++ });
            },
            removeItem(index) {
                this.items.splice(index, 1);
            },
            product(item) {
                return this.products.find(p => String(p.id) === item.inv_product_id);
            },
            defaultPrice(item) {
                const p = this.product(item);
                if (!p) return 0;
                if (this.reason === 'venta') return p.sale;
                return p.purchase;
            },
            subtotal(item) {
                const price = item.unit_cost !== '' && item.unit_cost !== null ? Number(item.unit_cost) : this.defaultPrice(item);
                return (Number(item.quantity) || 0) * (price || 0);
            },
            total() {
                return this.items.reduce((sum, item) => sum + this.subtotal(item), 0);
            },
            filledCount() {
                return this.items.filter(i => i.inv_product_id).length;
            },
            errorFor(index, field) {
                return (errors[`items.${index}.${field}`] || [])[0] || '';
            },
            fmtMoney(n) {
                return '$' + Math.round(n).toLocaleString('es-CO');
            },
            fmtQty(n) {
                return Number(n).toLocaleString('es-CO', { maximumFractionDigits: 2 });
            },
        };
    }
</script>

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
