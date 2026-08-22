@extends('layouts.panel')
@section('title', 'Productos')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Productos</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $products->total() }} productos registrados</p>
    </div>
    <a href="{{ route('panel.productos.create') }}"
       class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Nuevo producto</a>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" placeholder="Buscar por nombre…" value="{{ request('q') }}"
           class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
    <select name="category_id"
            class="rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        <option value="">Todas las categorías</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">Filtrar</button>
</form>

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">Producto</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3 text-right">Stock</th>
                <th class="px-4 py-3 text-right">Compra</th>
                <th class="px-4 py-3 text-right">Venta</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $product->name }}</span>
                        <span class="block text-xs text-gray-400">{{ ucfirst($product->unit) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($product->isLowStock())
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700"
                                  title="Bajo el mínimo ({{ qty($product->min_stock) }})">{{ qty($product->current_stock) }} ⚠</span>
                        @else
                            {{ qty($product->current_stock) }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $product->purchase_price ? money($product->purchase_price) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $product->sale_price ? money($product->sale_price) : '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($product->active)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Activo</span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('panel.productos.edit', $product) }}" class="font-medium text-emerald-600 hover:text-emerald-800">Editar</a>
                        <form action="{{ route('panel.productos.destroy', $product) }}" method="POST" class="inline ml-3"
                              onsubmit="return confirm('¿Eliminar «{{ $product->name }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No hay productos aún. Crea el primero.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
