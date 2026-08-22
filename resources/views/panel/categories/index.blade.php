@extends('layouts.panel')
@section('title', 'Categorías')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">CATEGORÍAS</h1>
<p class="mb-6 text-sm text-stone-500">Organiza el catálogo por tipo de producto.</p>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="card overflow-hidden lg:col-span-2">
        <table class="rtable min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left font-mono text-[11px] uppercase tracking-wider text-stone-400">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3 text-right">Productos</th>
                    <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td data-label="Categoría" class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td data-label="Productos" class="px-4 py-3 text-right font-mono">{{ $category->products_count }}</td>
                        <td data-label="" class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('panel.categorias.edit', $category) }}" class="link-edit">Editar</a>
                            <form action="{{ route('panel.categorias.destroy', $category) }}" method="POST" class="mt-1 inline sm:ml-3 sm:mt-0"
                                  onsubmit="return confirm('¿Eliminar «{{ $category->name }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-stone-400">Sin categorías.<br>Crea la primera en el formulario de la derecha.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-stone-200 p-3">{{ $categories->links() }}</div>
    </div>

    <div class="card self-start p-5">
        <h2 class="font-mono text-xs font-semibold uppercase tracking-widest text-stone-400">Nueva categoría</h2>
        <form action="{{ route('panel.categorias.store') }}" method="POST" class="mt-3 flex flex-col gap-3">
            @csrf
            <input type="text" name="name" placeholder="Ej: Cervezas" required value="{{ old('name') }}" class="input !mt-0">
            <button type="submit" class="btn-primary w-full">Crear categoría</button>
        </form>
    </div>
</div>
@endsection
