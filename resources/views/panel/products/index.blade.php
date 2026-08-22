@extends('layouts.panel')
@section('title', 'Productos')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">PRODUCTOS</h1>
        <p class="text-sm text-stone-500">{{ $products->total() }} en catálogo</p>
    </div>
    <a href="{{ route('panel.productos.create') }}" class="btn-primary w-full sm:w-auto">+ Nuevo producto</a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" placeholder="Buscar por nombre…" value="{{ request('q') }}"
           class="w-full rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar sm:w-64">
    <select name="category_id" aria-label="Categoría"
            class="rounded-lg border-stone-300 bg-white text-sm shadow-sm focus:border-ambar focus:ring-ambar">
        <option value="">Todas las categorías</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-ghost !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
    <table class="rtable min-w-full divide-y divide-stone-200 text-sm">
        <thead class="bg-stone-50 text-left font-mono text-[11px] uppercase tracking-wider text-stone-400">
            <tr>
                <th class="px-4 py-3">Producto</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3 text-right">Stock</th>
                <th class="px-4 py-3 text-right">Compra</th>
                <th class="px-4 py-3 text-right">Venta</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td data-label="Producto" class="px-4 py-3">
                        <span class="font-medium">{{ $product->name }}</span>
                        <span class="block font-mono text-xs text-stone-400">{{ ucfirst($product->unit) }}</span>
                    </td>
                    <td data-label="Categoría" class="px-4 py-3 text-stone-500">{{ $product->category?->name ?? '—' }}</td>
                    <td data-label="Stock" class="whitespace-nowrap px-4 py-3 text-right">
                        @if ($product->isLowStock())
                            <span class="stamp-out" title="Bajo el mínimo ({{ qty($product->min_stock) }})">⚠ {{ qty($product->current_stock) }}</span>
                        @else
                            <span class="font-mono font-semibold">{{ qty($product->current_stock) }}</span>
                        @endif
                    </td>
                    <td data-label="Compra" class="whitespace-nowrap px-4 py-3 text-right font-mono text-stone-600">{{ $product->purchase_price ? money($product->purchase_price) : '—' }}</td>
                    <td data-label="Venta" class="whitespace-nowrap px-4 py-3 text-right font-mono text-stone-600">{{ $product->sale_price ? money($product->sale_price) : '—' }}</td>
                    <td data-label="Estado" class="px-4 py-3">
                        @if ($product->active)
                            <span class="stamp-in">Activo</span>
                        @else
                            <span class="stamp-neutral">Inactivo</span>
                        @endif
                    </td>
                    <td data-label="" class="whitespace-nowrap px-4 py-3 text-right">
                        <a href="{{ route('panel.productos.edit', $product) }}" class="link-edit">Editar</a>
                        <form action="{{ route('panel.productos.destroy', $product) }}" method="POST" class="mt-1 inline sm:ml-3 sm:mt-0"
                              onsubmit="return confirm('¿Eliminar «{{ $product->name }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-stone-400">
                    Aún no hay productos.<br>Crea el primero con el botón «+ Nuevo producto».
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
