@extends('layouts.panel')
@section('title', 'Editar categoría')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-6">Editar categoría</h1>

<div class="max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <form action="{{ route('panel.categorias.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <label class="block text-sm font-medium text-gray-700" for="name">Nombre *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $category->name) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        <div class="mt-5 flex gap-3">
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Guardar</button>
            <a href="{{ route('panel.categorias.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
        </div>
    </form>
</div>
@endsection
