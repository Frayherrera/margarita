@extends('layouts.panel')
@section('title', 'Resumen')

@section('content')
<div class="mb-8 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="font-display text-3xl leading-tight tracking-wide">¡Buenas, {{ strtok(auth()->user()->name, ' ') }}!</h1>
        <p class="mt-1.5 font-mono text-xs uppercase tracking-widest text-stone-500">{{ now()->translatedFormat('l d \d\e F \d\e Y') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('panel.inventario.index', ['type' => 'salida', 'reason' => 'venta']) }}" class="btn-ghost">Venta</a>
        <a href="{{ route('panel.inventario.index', ['type' => 'entrada', 'reason' => 'compra']) }}" class="btn-primary">Compra</a>
    </div>
</div>

<div class="rise mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="card receipt p-4 sm:p-5">
        <p class="field-label">Ingresos hoy</p>
        <p class="mt-2 font-mono text-xl font-semibold text-billete lg:text-2xl">{{ money($ingresosHoy) }}</p>
        <div class="tear mt-3 pt-2 font-mono text-[11px] uppercase tracking-wider text-stone-400">plata que entró</div>
    </div>
    <div class="card receipt p-4 sm:p-5">
        <p class="field-label">Egresos hoy</p>
        <p class="mt-2 font-mono text-xl font-semibold text-ladrillo lg:text-2xl">{{ money($egresosHoy) }}</p>
        <div class="tear mt-3 pt-2 font-mono text-[11px] uppercase tracking-wider text-stone-400">plata que salió</div>
    </div>
    <div class="card receipt p-4 sm:p-5">
        <p class="field-label">Neto del mes</p>
        @php $positivo = $netoMes >= 0 @endphp
        <p class="mt-2 font-mono text-xl font-semibold lg:text-2xl {{ $positivo ? 'text-billete' : 'text-ladrillo' }}">{{ money($netoMes) }}</p>
        <div class="tear mt-3 pt-2 font-mono text-[11px] uppercase tracking-wider text-stone-400">{{ now()->translatedFormat('F') }}</div>
    </div>
    <div class="card receipt p-4 sm:p-5">
        <p class="field-label">Valor inventario</p>
        <p class="mt-2 font-mono text-xl font-semibold lg:text-2xl">{{ money($inventoryValue) }}</p>
        <div class="tear mt-3 pt-2 font-mono text-[11px] uppercase tracking-wider text-stone-400">a precio de compra</div>
    </div>
</div>

@if ($lowStock->isNotEmpty())
    <div class="rise mb-8 rounded-xl border border-ladrillo/40 bg-ladrillo/10 p-5">
        <h2 class="font-mono text-sm font-semibold uppercase tracking-wider text-ladrillo">⚠ Reposición · stock bajo ({{ $lowStock->count() }})</h2>
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($lowStock as $product)
                <li class="inline-flex items-center rounded-full border border-ladrillo/30 bg-white px-3 py-1.5 text-sm">
                    {{ $product->name }}
                    <span class="ml-2 font-mono text-xs font-semibold text-ladrillo" title="Stock actual / mínimo">{{ qty($product->current_stock) }}/{{ qty($product->min_stock) }} {{ $product->unit }}</span>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('panel.inventario.index') }}" class="link-edit mt-3 inline-block text-sm font-semibold">Registrar compra →</a>
    </div>
@endif

<div class="rise grid gap-6 lg:grid-cols-2">
    <section class="card p-5">
        <header class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Bodega · últimos movimientos</h2>
            <a href="{{ route('panel.inventario.index') }}" class="link-edit text-xs font-semibold uppercase tracking-wider">Ver todo →</a>
        </header>
        <ul class="divide-y divide-dashed divide-stone-200 text-sm">
            @forelse ($recentInventory as $movement)
                <li class="flex items-center justify-between gap-3 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $movement->product->name }}</p>
                        <p class="font-mono text-xs capitalize text-stone-400">{{ $movement->moved_at->format('d/m/Y') }} · {{ $movement->reason }}</p>
                    </div>
                    <span class="stamp {{ $movement->type === 'entrada' ? 'stamp-in' : 'stamp-out' }}">
                        {{ $movement->type === 'entrada' ? '+' : '−' }}{{ qty($movement->quantity) }}
                    </span>
                </li>
            @empty
                <li class="py-8 text-center text-sm text-stone-400">Aún no hay movimientos.<br>Registra tu primera compra o venta.</li>
            @endforelse
        </ul>
    </section>

    <section class="card p-5">
        <header class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Caja · últimos movimientos</h2>
            <a href="{{ route('panel.caja.index') }}" class="link-edit text-xs font-semibold uppercase tracking-wider">Ver todo →</a>
        </header>
        <ul class="divide-y divide-dashed divide-stone-200 text-sm">
            @forelse ($recentCash as $movement)
                <li class="flex items-center justify-between gap-3 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $movement->concept }}</p>
                        <p class="font-mono text-xs capitalize text-stone-400">{{ $movement->occurred_at->format('d/m/Y') }} · {{ ucfirst($movement->method) }}</p>
                    </div>
                    <span class="font-mono text-sm font-semibold {{ $movement->type === 'ingreso' ? 'text-billete' : 'text-ladrillo' }}">
                        {{ $movement->type === 'ingreso' ? '+' : '−' }}{{ money($movement->amount) }}
                    </span>
                </li>
            @empty
                <li class="py-8 text-center text-sm text-stone-400">La caja está en ceros.<br>Anota tus ingresos y gastos del día.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
