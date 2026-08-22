@extends('layouts.panel')
@section('title', 'Categorías')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-6">Categorías de productos</h1>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3 text-right">Productos</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $category->products_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('panel.categorias.edit', $category) }}" class="font-medium text-emerald-600 hover:text-emerald-800">Editar</a>
                            <form action="{{ route('panel.categorias.destroy', $category) }}" method="POST" class="inline ml-3"
                                  onsubmit="return confirm('¿Eliminar «{{ $category->name }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-500 hover:text-red-700">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-gray-400">Sin categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm self-start">
        <h2 class="font-semibold mb-3">Nueva categoría</h2>
        <form action="{{ route('panel.categorias.store') }}" method="POST" class="flex flex-col gap-3">
            @csrf
            <input type="text" name="name" placeholder="Ej: Cervezas" required value="{{ old('name') }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Crear</button>
        </form>
    </div>
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
