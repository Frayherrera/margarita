@extends('layouts.panel')
@section('title', 'Caja')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">CAJA</h1>
<p class="mb-6 text-sm text-stone-500">Ingresos y egresos del negocio.</p>

{{-- Resumen tipo recibo --}}
<div class="rise mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="card receipt p-5">
        <p class="field-label">Ingresos del período</p>
        <p class="mt-2 font-mono text-2xl font-semibold text-billete">{{ money($summary->ingresos ?? 0) }}</p>
    </div>
    <div class="card receipt p-5">
        <p class="field-label">Egresos del período</p>
        <p class="mt-2 font-mono text-2xl font-semibold text-ladrillo">{{ money($summary->egresos ?? 0) }}</p>
    </div>
    <div class="card receipt p-5">
        <p class="field-label">Neto en caja</p>
        @php $neto = (float) ($summary->neto ?? 0) @endphp
        <p class="mt-2 font-mono text-2xl font-semibold {{ $neto >= 0 ? 'text-billete' : 'text-ladrillo' }}">{{ money($neto) }}</p>
    </div>
</div>

@if ($byMethod->isNotEmpty())
    <section class="card mb-8 overflow-x-auto p-4">
        <h2 class="px-1 pb-2 font-mono text-xs font-semibold uppercase tracking-widest text-stone-400">Por método de pago</h2>
        <div class="min-w-[480px]">
            <table class="w-full text-sm">
                <thead class="border-b border-stone-200 text-left font-mono text-[11px] uppercase tracking-wider text-stone-400">
                    <tr><th class="py-2 pr-4">Método</th><th class="px-4 py-2 text-right">Ingresos</th><th class="px-4 py-2 text-right">Egresos</th><th class="pl-4 py-2 text-right">Neto</th></tr>
                </thead>
                <tbody class="divide-y divide-dashed divide-stone-200">
                    @foreach ($byMethod as $row)
                        @php $mNeto = (float) $row->ingresos - (float) $row->egresos; @endphp
                        <tr>
                            <td class="py-2.5 pr-4 font-medium capitalize">{{ ucfirst($row->method) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-billete">{{ money($row->ingresos) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-ladrillo">{{ money($row->egresos) }}</td>
                            <td class="pl-4 py-2.5 text-right font-mono font-semibold {{ $mNeto >= 0 ? '' : 'text-ladrillo' }}">{{ money($mNeto) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

{{-- Formulario --}}
<section class="card mb-8 p-5 sm:p-6">
    <h2 class="font-mono text-xs font-semibold uppercase tracking-widest text-stone-400">Registrar movimiento</h2>
    <form action="{{ route('panel.caja.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
        @csrf
        <div>
            <label class="field-label" for="type">Tipo *</label>
            <select id="type" name="type" required class="input">
                @foreach (App\Models\CashMovement::TYPES as $t)
                    <option value="{{ $t }}" @selected(old('type', 'ingreso') === $t)>@if($t === 'ingreso')↑ Ingreso @else ↓ Egreso @endif</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="field-label" for="concept">Concepto *</label>
            <input id="concept" name="concept" type="text" required value="{{ old('concept') }}"
                   placeholder="Ej: Venta del viernes, compra de cervezas, pago de luz…"
                   class="input">
        </div>
        <div>
            <label class="field-label" for="amount">Monto *</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 pt-1.5 font-mono text-sm text-stone-400">$</span>
                <input id="amount" name="amount" type="number" inputmode="numeric" step="1" min="1" required value="{{ old('amount') }}" class="input pl-7 font-mono">
            </div>
        </div>
        <div>
            <label class="field-label" for="method">Método *</label>
            <select id="method" name="method" required class="input capitalize">
                @foreach (App\Models\CashMovement::METHODS as $m)
                    <option value="{{ $m }}" @selected(old('method', 'efectivo') === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label" for="cash_category_id">Categoría</label>
            <select id="cash_category_id" name="cash_category_id" class="input">
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
            <label class="field-label" for="occurred_at">Fecha *</label>
            <input id="occurred_at" name="occurred_at" type="date" required value="{{ old('occurred_at', now()->toDateString()) }}" class="input font-mono">
        </div>
        <div class="sm:col-span-2">
            <label class="field-label" for="notes">Notas</label>
            <input id="notes" name="notes" type="text" value="{{ old('notes') }}" class="input">
        </div>
        <div class="flex items-end justify-end sm:col-span-3">
            <button type="submit" class="btn-primary w-full sm:w-auto">Registrar movimiento</button>
        </div>
    </form>
</section>

{{-- Filtros --}}
<form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <select name="type" class="rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar" aria-label="Tipo">
        <option value="">Todos</option>
        <option value="ingreso" @selected(request('type') === 'ingreso')">Ingresos</option>
        <option value="egreso" @selected(request('type') === 'egreso')">Egresos</option>
    </select>
    <select name="method" class="rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar" aria-label="Método">
        <option value="">Todos los métodos</option>
        @foreach (App\Models\CashMovement::METHODS as $m)
            <option value="{{ $m }}" @selected(request('method') === $m)>{{ ucfirst($m) }}</option>
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
                <th class="px-4 py-3">Concepto</th>
                <th class="px-4 py-3">Tipo</th>
                <th class="px-4 py-3">Método</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3 text-right">Monto</th>
                <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td data-label="Fecha" class="whitespace-nowrap px-4 py-3 font-mono">{{ $movement->occurred_at->format('d/m/Y') }}</td>
                    <td data-label="Concepto" class="max-w-[280px] px-4 py-3">
                        <span class="block truncate font-medium" title="{{ $movement->concept }}">{{ $movement->concept }}</span>
                        @if ($movement->notes)
                            <span class="block truncate text-xs text-stone-400" title="{{ $movement->notes }}">{{ $movement->notes }}</span>
                        @endif
                    </td>
                    <td data-label="Tipo" class="px-4 py-3">
                        @if ($movement->type === 'ingreso')
                            <span class="stamp-in">Ingreso</span>
                        @else
                            <span class="stamp-out">Egreso</span>
                        @endif
                    </td>
                    <td data-label="Método" class="px-4 py-3 capitalize text-stone-600">{{ ucfirst($movement->method) }}</td>
                    <td data-label="Categoría" class="px-4 py-3 text-stone-500">{{ $movement->category?->name ?? '—' }}</td>
                    <td data-label="Monto" class="whitespace-nowrap px-4 py-3 text-right font-mono font-semibold {{ $movement->type === 'ingreso' ? 'text-billete' : 'text-ladrillo' }}">
                        {{ $movement->type === 'ingreso' ? '+' : '−' }}{{ money($movement->amount) }}
                    </td>
                    <td data-label="" class="px-4 py-3 text-right">
                        <form action="{{ route('panel.caja.destroy', $movement) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar «{{ $movement->concept }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-stone-400">No hay movimientos con este filtro.<br>Prueba ampliar las fechas o registra uno arriba.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
