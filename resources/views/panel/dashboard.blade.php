@extends('layouts.panel')
@section('title', 'Resumen')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-1">Hola, {{ auth()->user()->name }} 👋</h1>
<p class="mb-6 text-sm text-gray-500">Resumen de tu bar — {{ now()->translatedFormat('l d \d\e F \d\e Y') }}</p>

<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Ingresos hoy</p>
        <p class="mt-1 text-xl lg:text-2xl font-bold text-emerald-600">{{ money($ingresosHoy) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Egresos hoy</p>
        <p class="mt-1 text-xl lg:text-2xl font-bold text-red-600">{{ money($egresosHoy) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Neto del mes</p>
        @php $positivo = $netoMes >= 0 @endphp
        <p class="mt-1 text-xl lg:text-2xl font-bold {{ $positivo ? 'text-emerald-600' : 'text-red-600' }}">{{ money($netoMes) }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Valor del inventario</p>
        <p class="mt-1 text-xl lg:text-2xl font-bold text-gray-800">{{ money($inventoryValue) }}</p>
        <p class="mt-0.5 text-xs text-gray-400">a precio de compra</p>
    </div>
</div>

@if ($lowStock->isNotEmpty())
    <div class="mb-8 rounded-xl border border-red-200 bg-red-50 p-5 shadow-sm">
        <h2 class="font-semibold text-red-800 mb-3">⚠ Productos con stock bajo ({{ $lowStock->count() }})</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ($lowStock as $product)
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm border border-red-200">
                    {{ $product->name }}
                    <span class="ml-2 font-semibold text-red-600">{{ qty($product->current_stock) }}/{{ qty($product->min_stock) }} {{ $product->unit }}</span>
                </span>
            @endforeach
        </div>
        <a href="{{ route('panel.inventario.index') }}" class="mt-3 inline-block text-sm font-medium text-red-700 hover:underline">Registrar compra →</a>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Últimos movimientos de inventario</h2>
            <a href="{{ route('panel.inventario.index') }}" class="text-xs font-medium text-emerald-600 hover:underline">Ver todo →</a>
        </div>
        <ul class="divide-y divide-gray-100 text-sm">
            @forelse ($recentInventory as $movement)
                <li class="flex items-center justify-between py-2.5">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $movement->product->name }}</p>
                        <p class="text-xs capitalize text-gray-400">{{ $movement->moved_at->format('d/m/Y') }} · {{ $movement->reason }}</p>
                    </div>
                    <span class="{{ $movement->type === 'entrada' ? 'text-emerald-600' : 'text-red-600' }} font-semibold whitespace-nowrap ml-3">
                        {{ $movement->type === 'entrada' ? '+' : '−' }}{{ qty($movement->quantity) }}
                    </span>
                </li>
            @empty
                <li class="py-8 text-center text-gray-400">Sin movimientos aún.</li>
            @endforelse
        </ul>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Últimos movimientos de caja</h2>
            <a href="{{ route('panel.caja.index') }}" class="text-xs font-medium text-emerald-600 hover:underline">Ver todo →</a>
        </div>
        <ul class="divide-y divide-gray-100 text-sm">
            @forelse ($recentCash as $movement)
                <li class="flex items-center justify-between py-2.5">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $movement->concept }}</p>
                        <p class="text-xs capitalize text-gray-400">{{ $movement->occurred_at->format('d/m/Y') }} · {{ ucfirst($movement->method) }}</p>
                    </div>
                    <span class="{{ $movement->type === 'ingreso' ? 'text-emerald-600' : 'text-red-600' }} font-semibold whitespace-nowrap ml-3">
                        {{ $movement->type === 'ingreso' ? '+' : '−' }}{{ money($movement->amount) }}
                    </span>
                </li>
            @empty
                <li class="py-8 text-center text-gray-400">Sin movimientos aún.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
